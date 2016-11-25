<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Packager;

use Nette\DI\Container;

class Script
{
    const SCRIPT_PRE_INSTALL = 'pre-install';
    const SCRIPT_POST_INSTALL = 'post-install';
    const SCRIPT_PRE_UNINSTALL = 'pre-uninstall';
    const SCRIPT_POST_UNINSTALL = 'post-uninstall';

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    public function runScript(IPackage $package, $script = self::SCRIPT_PRE_INSTALL)
    {
        $scripts = $package->getScripts();

        if (array_key_exists($script, $scripts))
        {
            $scriptToRun = $scripts[$script];

            /** @var IScript $instance */
            $instance = $this->container->createInstance($scriptToRun);
            $instance->run($package);
        }
    }
}