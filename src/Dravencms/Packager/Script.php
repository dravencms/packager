<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Packager;

use Nette\DI\Container;

class Script
{
    const SCRIPT_INSTALL = 'install';
    const SCRIPT_UNINSTALL = 'uninstall';

    private $container;

    private $packager;

    private $composer;

    private $configDir;

    public function __construct($configDir, Container $container, Packager $packager, Composer $composer)
    {
        $this->container = $container;
        $this->packager = $packager;
        $this->composer = $composer;
        $this->configDir =$configDir;
    }


    public function runScript(IPackage $package, $script = self::SCRIPT_INSTALL)
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

    public function getScriptLockPath(IPackage $package)
    {
        return $this->configDir . '/' . Packager::CONFIG_DIR . '/' . $package->getName() . '.lock';
    }

    public function isInstalled(IPackage $package)
    {
        return file_exists($this->getScriptLockPath($package));
    }
    
    /**
     * @return \Generator|Package[]
     * @throws \Exception
     */
    public function installAvailable()
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
    public function uninstallAbsent()
    {
        foreach ($this->getInstalledPackages() AS $packageName => $packageConf) {
            if (!$this->composer->isInstalled($packageName)) {
                $virtualPackage = $this->createPackageInstance($packageName);
                $this->uninstall($virtualPackage);

                yield $virtualPackage;
            }
        }
    }
}