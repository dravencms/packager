<?php declare(strict_types = 1);
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
    public function getName(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return array
     */
    public function getKeywords(): array;

    /**
     * @return array
     */
    public function getLicence(): string;

    /**
     * @return array
     */
    public function getAuthors(): array;

    /**
     * @return string
     */
    public function getConfiguration();

    /**
     * @return array
     */
    public function getScripts(): array;

    /**
     * @return mixed
     */
    public function getFiles();
}