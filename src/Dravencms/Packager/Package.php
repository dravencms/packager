<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Packager;


class Package implements IPackage
{
    const EXTRA_KEY = 'extra';
    const EXTRA_VENDOR_KEY = 'dravencms';
    const EXTRA_VENDOR_CONFIG_KEY = 'configuration';
    const EXTRA_VENDOR_SCRIPTS_KEY = 'scripts';
    const EXTRA_VENDOR_FILES_KEY = 'files';

    /**
     * @var array
     */
    public $composerFileData = [];

    /**
     * Package constructor.
     * @param array $composerFileData
     */
    public function __construct(array $composerFileData)
    {
        $this->composerFileData = $composerFileData;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->composerFileData['name'];
    }

    /**
     * @return array
     */
    public function getAuthors(): array
    {
        return $this->composerFileData['authors'];
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        if (!isset($this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_CONFIG_KEY]))
        {
            return "";
        }

        return $this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_CONFIG_KEY];
    }

    /**
     * @return array|mixed
     */
    public function getFiles()
    {
        if (!isset($this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_FILES_KEY]))
        {
            return [];
        }

        return $this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_FILES_KEY];
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->composerFileData['description'];
    }

    /**
     * @return array
     */
    public function getScripts(): array
    {
        if (!isset($this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_SCRIPTS_KEY]))
        {
            return [];
        }
        
        return $this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_SCRIPTS_KEY];
    }

    /**
     * @return array
     */
    public function getKeywords(): array
    {
        return $this->composerFileData['keywords'];
    }

    /**
     * @return string
     */
    public function getLicence(): string
    {
        return $this->composerFileData['licence'];
    }
}