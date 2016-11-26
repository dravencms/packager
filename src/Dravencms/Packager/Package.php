<?php
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


    public $composerFileData = [];

    public function getName()
    {
        return $this->composerFileData['name'];
    }

    public function getAuthors()
    {
        return $this->composerFileData['authors'];
    }

    public function getConfiguration()
    {
        if (!isset($this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_CONFIG_KEY]))
        {
            return [];
        }

        return $this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_CONFIG_KEY];
    }

    public function getDescription()
    {
        return $this->composerFileData['description'];
    }

    public function getScripts()
    {
        if (!isset($this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_SCRIPTS_KEY]))
        {
            return [];
        }
        
        return $this->composerFileData[self::EXTRA_KEY][self::EXTRA_VENDOR_KEY][self::EXTRA_VENDOR_SCRIPTS_KEY];
    }

    public function getKeywords()
    {
        return $this->composerFileData['keywords'];
    }

    public function getLicence()
    {
        return $this->composerFileData['licence'];
    }
}