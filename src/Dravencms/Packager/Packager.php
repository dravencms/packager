<?php declare(strict_types = 1);

namespace Dravencms\Packager;

use Nette\DI\Container;
use Nette\Neon\Neon;
use Nette\Utils\Finder;
use Nette\SmartObject;


/**
 * Class Packager
 * @package Dravencms\Packager
 */
class Packager
{
    use SmartObject;

    /** @var string */
    const CONFIG_DIR = 'packages';

    /** @var string */
    const INSTALLED_PACKAGES_LIST = 'packages.neon';

    /** @var string */
    const SUM_ALGORITHM = 'md5';

    /** @var string */
    const PACKAGE_TYPE = 'dravencms-package';

    /** @var string */
    private $configDir;

    /** @var string */
    private $vendorDir;

    /** @var string */
    private $appDir;

    /** @var string */
    private $wwwDir;


    /** @var Composer */
    private $composer;

    /** @var Container */
    private $container;

    /**
     * Packager constructor.
     * @param string $configDir
     * @param string $vendorDir
     * @param string $appDir
     * @param string $wwwDir
     * @param Composer $composer
     * @param Container $container
     */
    public function __construct(string $configDir, string $vendorDir, string $appDir, string $wwwDir, Composer $composer, Container $container)
    {
        $this->configDir = $configDir;
        $this->vendorDir = $vendorDir;
        $this->appDir = $appDir;
        $this->wwwDir = $wwwDir;
        $this->composer = $composer;
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return Package
     * @throws \Nette\Utils\JsonException
     * @throws \Exception
     */
    public function createPackageInstance(string $name): Package
    {
        if (!$this->composer->isInstalled($name)) {
            throw new \Exception('Composer package ' . $name . ' is not installed');
        }

        $data = $this->composer->getData($name);

        return new Package($data);
    }

    /**
     * @param IPackage $package
     * @return string
     */
    public function getConfigPath(IPackage $package): string
    {
        return $this->configDir . '/' . self::CONFIG_DIR . '/' . $package->getName() . '.neon';
    }

    /**
     * @param IPackage $package
     * @return string
     */
    public function getConfigSumPath(IPackage $package): string
    {
        return $this->configDir . '/' . self::CONFIG_DIR . '/' . $package->getName() . '.' . self::SUM_ALGORITHM;
    }

    /**
     * @return string
     */
    public function getInstalledPackagesPath(): string
    {
        return $this->configDir . '/' . self::CONFIG_DIR . '/' . self::INSTALLED_PACKAGES_LIST;
    }

    /**
     * @return array
     */
    public function getInstalledPackagesConf(): array
    {
        $data = Neon::decode(file_get_contents($this->getInstalledPackagesPath()));
        if (is_null($data)) {
            return [];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getInstalledPackages(): array
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

    /**
     * @param IPackage $package
     * @return bool
     */
    public function isInstalled(IPackage $package): bool
    {
        $installedPackages = $this->getInstalledPackages();

        return array_key_exists($package->getName(), $installedPackages) && file_exists($this->getConfigPath($package));
    }

    /**
     * @param IPackage $package
     * @return string
     */
    public function getPackageRoot(IPackage $package): string
    {
        return $this->vendorDir . '/' . $package->getName();
    }

    /**
     * @param string $path
     * @return string
     */
    public function expandPath(string $path)
    {
        return strtr($path, ['%appDir%' => $this->appDir, '%wwwDir%' => $this->wwwDir, '%configDir%' => $this->configDir, '%vendorDir%' => $this->vendorDir]);
    }

    /**
     * @param IPackage $package
     */
    public function processFiles(IPackage $package): void
    {
        $packageRoot = $this->getPackageRoot($package);
        foreach ($package->getFiles() AS $from => $to) {
            $fromParts = explode('/', $from);
            $file = array_pop($fromParts);

            $searchPath = $packageRoot . '/' . implode('/', $fromParts);

            foreach (Finder::findFiles($file)->from($searchPath) as $file) {
                $fromFull = $file->getPathname();
                $toFull = $this->expandPath($to) . str_replace($searchPath, '', $file->getPathname());
                $toFullInfo = new \SplFileInfo($toFull);

                if (!is_dir($toFullInfo->getPath()))
                {
                    mkdir($toFullInfo->getPath(), 0777, true);
                }

                copy($fromFull, $toFull);
            }
        }
    }

    /**
     * @param IPackage $package
     */
    public function install(IPackage $package): void
    {
        $this->processFiles($package);
        $this->addPackageToInstalled($package);
    }

    /**
     * @param IPackage $package
     */
    public function addPackageToInstalled(IPackage $package): void
    {
        if ($this->isInstalled($package)) {
            return;
        }

        $data = $this->getInstalledPackagesConf();
        $data['includes'][] = $package->getName() . '.neon';

        file_put_contents($this->getInstalledPackagesPath(), Neon::encode($data, Neon::BLOCK));
    }

    /**
     * @param IPackage $package
     */
    public function removePackageFromInstalled(IPackage $package): void
    {
        if (!$this->isInstalled($package)) {
            return;
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
        foreach ($keyArray AS $row) {
            $includes[] = $row;
        }

        $data['includes'] = $includes;

        file_put_contents($this->getInstalledPackagesPath(), Neon::encode($data, Neon::BLOCK));
    }

    /**
     * @param IPackage $package
     * @param bool $purge
     */
    public function uninstall(IPackage $package, bool $purge = false): void
    {
        $this->removePackageFromInstalled($package);

        if ($purge) {
            unlink($this->getConfigPath($package));
            unlink($this->getConfigSumPath($package));
        }
    }

    /**
     * @param IPackage $package
     * @return bool
     */
    public function isConfigUserModified(IPackage $package): bool
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
     * @param $array
     * @return string
     */
    public function neonEncode($array): string
    {
        $neon = Neon::encode($array, Neon::BLOCK);

        //!FIXME hotfix for issue #1
        return preg_replace_callback('/^((?:.+|)\-.+?)\"(.+?@.+?)\"$/m', function ($matches) {
            return $matches[1] . $matches[2];
        }, $neon);
    }

    /**
     * @param IPackage $package
     */
    public function generatePackageConfig(IPackage $package): void
    {
        if (is_string($package->getConfiguration())) {
            $configFilePath = $this->getPackageRoot($package).(substr($package->getConfiguration(), 0, 1 ) === "/" ? '': '/').$package->getConfiguration();
            $installConfigurationNeon = file_get_contents($configFilePath);
        } else {
            $installConfigurationNeon = $this->neonEncode($package->getConfiguration());
        }

        $installConfigurationNeonSum = hash(self::SUM_ALGORITHM, $installConfigurationNeon);

        if ($this->isConfigUserModified($package)) {
            rename($this->getConfigPath($package), $this->getConfigPath($package) . '.old');
        }

        $dir = dirname($this->getConfigPath($package));
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->getConfigPath($package), $installConfigurationNeon);
        file_put_contents($this->getConfigSumPath($package), $installConfigurationNeonSum);
    }

    /**
     * @return \Generator|Package[]
     * @throws \Exception
     */
    public function installAvailable(): \Generator
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
    public function uninstallAbsent(): \Generator
    {
        foreach ($this->getInstalledPackages() AS $packageName => $packageConf) {
            if (!$this->composer->isInstalled($packageName)) {
                $virtualPackage = new Package(['name' => $packageName]);
                $this->uninstall($virtualPackage);

                yield $virtualPackage;
            }
        }
    }
}
