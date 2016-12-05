<?php

namespace Dravencms\Packager;

use Nette\DI\Container;
use Nette\Neon\Neon;


/**
 * Class Packager
 * @package Dravencms\Packager
 */
class Packager extends \Nette\Object
{
    const CONFIG_DIR = 'packages';

    const INSTALLED_PACKAGES_LIST = 'packages.neon';

    const SUM_ALGORITHM = 'md5';

    const PACKAGE_TYPE = 'dravencms-package';

    private $configDir;
    private $vendorDir;
    private $composer;
    private $container;

    public function __construct($configDir, $vendorDir, Composer $composer, Container $container)
    {
        $this->configDir = $configDir;
        $this->vendorDir = $vendorDir;
        $this->composer = $composer;
        $this->container = $container;
    }

    public function createPackageInstance($name)
    {
        if (!$this->composer->isInstalled($name)) {
            throw new \Exception('Composer package ' . $name . ' is not installed');
        }

        $data = $this->composer->getData($name);

        return new Package($data);
    }

    public function getConfigPath(IPackage $package)
    {
        return $this->configDir . '/' . self::CONFIG_DIR . '/' . $package->getName() . '.neon';
    }

    public function getConfigSumPath(IPackage $package)
    {
        return $this->configDir . '/' . self::CONFIG_DIR . '/' . $package->getName() . '.' . self::SUM_ALGORITHM;
    }

    public function getInstalledPackagesPath()
    {
        return $this->configDir . '/' . self::CONFIG_DIR . '/' . self::INSTALLED_PACKAGES_LIST;
    }

    public function getInstalledPackagesConf()
    {
        $data = Neon::decode(file_get_contents($this->getInstalledPackagesPath()));
        if (is_null($data)) {
            return [];
        }

        return $data;
    }

    public function getInstalledPackages()
    {
        $data = $this->getInstalledPackagesConf();

        if (!array_key_exists('includes', $data) || is_null($data['includes'])) {
            return [];
        }

        $return = array_flip($data['includes']);
        // convert to array
        foreach ($return AS $k => &$item) {
            $item = preg_replace('/\\.[^.\\s]{3,4}$/', '', $k);
        }

        return array_flip($return);
    }

    public function isInstalled(IPackage $package)
    {
        $installedPackages = $this->getInstalledPackages();

        return array_key_exists($package->getName(), $installedPackages) && file_exists($this->getConfigPath($package));
    }

    public function install(IPackage $package)
    {
        $this->addPackageToInstalled($package);
    }

    public function addPackageToInstalled(IPackage $package)
    {
        if ($this->isInstalled($package)) {
            return true;
        }

        $data = $this->getInstalledPackagesConf();
        $data['includes'][] = $package->getName() . '.neon';

        file_put_contents($this->getInstalledPackagesPath(), Neon::encode($data, Neon::BLOCK));
    }

    public function removePackageFromInstalled(IPackage $package)
    {
        if (!$this->isInstalled($package)) {
            return true;
        }

        $data = $this->getInstalledPackagesConf();

        $modified = array_flip($data['includes']);
        // convert to array
        foreach ($modified AS $k => &$item) {
            $item = preg_replace('/\\.[^.\\s]{3,4}$/', '', $k);
        }

        $keyArray = array_flip($modified);

        unset($keyArray[$package->getName()]);

        $includes = [];
        foreach($keyArray AS $row)
        {
            $includes[] = $row;
        }

        $data['includes'] = $includes;

        file_put_contents($this->getInstalledPackagesPath(), Neon::encode($data, Neon::BLOCK));
    }

    public function uninstall(IPackage $package, $purge = false)
    {
        $this->removePackageFromInstalled($package);

        if ($purge) {
            unlink($this->getConfigPath($package));
            unlink($this->getConfigSumPath($package));
        }
    }

    public function isConfigUserModified(IPackage $package)
    {
        if (file_exists($this->getConfigPath($package))) {
            if (!file_exists($this->getConfigSumPath($package))) {
                return true;
            }

            $configInstallationSum = file_get_contents($this->getConfigSumPath($package));
            //We do this to be sure that SUM will not differ cos some comments or new whitespace
            $installedConfig = Neon::decode(file_get_contents($this->getConfigPath($package)));
            $installedConfigNeon = $this->neonEncode($installedConfig);
            $installedConfigNeonSum = hash(self::SUM_ALGORITHM, $installedConfigNeon);

            return $configInstallationSum != $installedConfigNeonSum;
        }

        return false;
    }

    /**
     * @param array $array
     * @return mixed
     */
    public function neonEncode($array)
    {
        $neon = Neon::encode($array, Neon::BLOCK);

        //!FIXME hotfix for issue #1
        return preg_replace_callback('/^((?:.+|)\-.+?)\"(.+?@.+?)\"$/m', function($matches){
            return $matches[1].$matches[2];
        }, $neon);
    }

    public function generatePackageConfig(IPackage $package)
    {
        $installConfigurationNeon = $this->neonEncode($package->getConfiguration());
        $installConfigurationNeonSum = hash(self::SUM_ALGORITHM, $installConfigurationNeon);

        if ($this->isConfigUserModified($package)) {
            rename($this->getConfigPath($package), $this->getConfigPath($package) . '.old');
        }

        $dir = dirname($this->getConfigPath($package));
        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->getConfigPath($package), $installConfigurationNeon);
        file_put_contents($this->getConfigSumPath($package), $installConfigurationNeonSum);
    }

    /**
     * @return \Generator|Package[]
     * @throws \Exception
     */
    public function installAvailable()
    {
        foreach ($this->composer->getInstalled() AS $packageName => $package) {
            if ($package['type'] == self::PACKAGE_TYPE) {
                $virtualPackage = $this->createPackageInstance($packageName);

                if (!$this->isInstalled($virtualPackage)) {
                    $this->generatePackageConfig($virtualPackage);
                    $this->install($virtualPackage);
                    yield $virtualPackage;
                }
            }
        }
    }

    /**
     * @return \Generator|Package[]
     * @throws \Exception
     */
    public function uninstallAbsent()
    {
        foreach ($this->getInstalledPackages() AS $packageName => $packageConf) {
            if (!$this->composer->isInstalled($packageName)) {
                $virtualPackage =  new Package(['name'=> $packageName]);
                $this->uninstall($virtualPackage);

                yield $virtualPackage;
            }
        }
    }
}
