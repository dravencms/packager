<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Packager;


interface IPackage
{
    public function getName();

    public function getDescription();

    public function getKeywords();

    public function getLicence();

    public function getAuthors();

    public function getConfiguration();

    public function getScripts();
}