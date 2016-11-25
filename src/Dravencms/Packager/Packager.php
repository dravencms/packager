<?php

namespace Dravencms\Packager;

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

    private $configDir;
    private $composer;

    public function __construct($configDir, Composer $composer)
    {
        $this->configDir = $configDir;
        $this->composer = $composer;
    }

    public function createPackageInstance($name)
    {
        if (!$this->composer->isInstalled($name))
        {
            throw new \Exception('Composer package '.$name.' is not installed');
        }
        
        $data = $this->composer->getData($name);

        return new VirtualPackage($data);
    }

    public function getConfigPath(IPackage $package)
    {
        return $this->configDir.'/'.self::CONFIG_DIR.'/'.$package->getName().'.neon';
    }

    public function getConfigSumPath(IPackage $package)
    {
        return $this->configDir.'/'.self::CONFIG_DIR.'/'.$package->getName().'.'.self::SUM_ALGORITHM;
    }
    
    public function getInstalledPackagesPath()
    {
        return $this->configDir.'/'.self::CONFIG_DIR.'/'.self::INSTALLED_PACKAGES_LIST;
    }

    public function getInstalledPackagesConf()
    {
        return Neon::decode(file_get_contents($this->getInstalledPackagesPath()));
    }

    public function getInstalledPackages()
    {
        $data = $this->getInstalledPackagesConf();

        if (!array_key_exists('includes', $data) || is_null($data['includes']))
        {
            return [];
        }

        $return = array_flip($data['includes']);
        // convert to array
        foreach ($return AS $k => &$item)
        {
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
        //run hooks
    }

    public function addPackageToInstalled(IPackage $package)
    {
        if ($this->isInstalled($package))
        {
            return true;
        }

        $data = $this->getInstalledPackagesConf();
        $data['includes'][] = $package->getName().'.neon';

        file_put_contents($this->getInstalledPackagesPath(), Neon::encode($data, Neon::BLOCK));
    }

    public function uninstall(IPackage $package, $purge = false)
    {
        
    }

    public function isConfigUserModified(IPackage $package)
    {
        if (file_exists($this->getConfigPath($package)))
        {
            if (!file_exists($this->getConfigSumPath($package)))
            {
                return true;
            }

            $configInstallationSum = file_get_contents($this->getConfigSumPath($package));
            //We do this to be sure that SUM will not differ cos some comments or new whitespace
            $installedConfig = Neon::decode(file_get_contents($this->getConfigPath($package)));
            $installedConfigNeon = Neon::encode($installedConfig, Neon::BLOCK);
            $installedConfigNeonSum = hash(self::SUM_ALGORITHM, $installedConfigNeon);

            return $configInstallationSum != $installedConfigNeonSum;
        }

        return false;
    }

    public function generatePackageConfig(IPackage $package)
    {
        $installConfigurationNeon = Neon::encode($package->getConfiguration(), Neon::BLOCK);
        $installConfigurationNeonSum = hash(self::SUM_ALGORITHM, $installConfigurationNeon);

        if ($this->isConfigUserModified($package))
        {
            rename($this->getConfigPath($package), $this->getConfigPath($package).'.old');
        }

        file_put_contents($this->getConfigPath($package), $installConfigurationNeon);
        file_put_contents($this->getConfigSumPath($package), $installConfigurationNeonSum);
    }
}
