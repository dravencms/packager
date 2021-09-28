<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Packager;


interface IScript
{
    /**
     * @param IPackage $package
     */
    public function run(IPackage $package): void;
}