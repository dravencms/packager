<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Packager;

use Nette\DI\Container;

class Script
{
    /** @var string */
    const SCRIPT_INSTALL = 'install';

    /** @var string */
    const SCRIPT_UNINSTALL = 'uninstall';

    /** @var Container */
    private $container;

    /** @var Packager */
    private $packager;

    /** @var Composer */
    private $composer;

    /** @var string */
    private $configDir;

    /**
     * Script constructor.
     * @param string $configDir
     * @param Container $container
     * @param Packager $packager
     * @param Composer $composer
     */
    public function __construct(string $configDir, Container $container, Packager $packager, Composer $composer)
    {
        $this->container = $container;
        $this->packager = $packager;
        $this->composer = $composer;
        $this->configDir =$configDir;
    }

    /**
     * @param IPackage $package
     * @param string $script
     * @throws \Exception
     */
    public function runScript(IPackage $package, $script = self::SCRIPT_INSTALL): void
    {
        $scripts = $package->getScripts();

        if (array_key_exists($script, $scripts))
        {
            $scriptToRun = $scripts[$script];

            /** @var IScript $instance */
            $instance = $this->container->createInstance($scriptToRun);
            $instance->run($package);

            if ($script == self::SCRIPT_INSTALL)
            {
                file_put_contents($this->getScriptLockPath($package), (new \DateTime())->format(\DateTime::ATOM));
            }
            else if ($script == self::SCRIPT_UNINSTALL)
            {
                @unlink($this->getScriptLockPath($package));
            }
        }
    }

    /**
     * @param IPackage $package
     * @return string
     */
    public function getScriptLockPath(IPackage $package): string
    {
        return $this->configDir . '/' . Packager::CONFIG_DIR . '/' . $package->getName() . '.lock';
    }

    /**
     * @param IPackage $package
     * @return bool
     */
    public function isInstalled(IPackage $package): bool
    {
        return file_exists($this->getScriptLockPath($package));
    }
    
    /**
     * @return \Generator|Package[]
     * @throws \Exception
     */
    public function installAvailable(): \Generator
    {
        foreach ($this->packager->getInstalledPackages() AS $packageName => $packageConf)
        {
            $package = $this->packager->createPackageInstance($packageName);
            if (!$this->isInstalled($package))
            {
                $this->runScript($package, self::SCRIPT_INSTALL);
                yield $package;
            }
        }
    }

    /**
     * @return \Generator|Package[]
     * @throws \Exception
     */
    public function uninstallAbsent(): \Generator
    {
        foreach ($this->packager->getInstalledPackages() AS $packageName => $packageConf) {
            if (!$this->composer->isInstalled($packageName)) {
                $virtualPackage = $this->packager->createPackageInstance($packageName);
                $this->packager->uninstall($virtualPackage);

                yield $virtualPackage;
            }
        }
    }
}