<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Packager;

/**
 * Interface IPackage
 * @package Dravencms\Packager
 */
interface IPackage
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return array
     */
    public function getKeywords();

    /**
     * @return array
     */
    public function getLicence();

    /**
     * @return array
     */
    public function getAuthors();

    /**
     * @return array
     */
    public function getConfiguration();

    /**
     * @return array
     */
    public function getScripts();

    /**
     * @return mixed
     */
    public function getFiles();
}