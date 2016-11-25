<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Packager;


use Nette\Utils\Json;

class Composer
{
    const COMPOSER_LOCK = 'composer.lock';

    /** @var string */
    private $vendorDir;

    /** @var string */
    private $lockFilePath;

    /** @var array */
    private $lockFileData = [];

    /**
     * Composer constructor.
     * @param $vendorDir
     * @throws \Exception
     */
    public function __construct($vendorDir)
    {
        $this->vendorDir = $vendorDir;
        $this->lockFilePath = $vendorDir.'/../'.self::COMPOSER_LOCK;

        if (!file_exists($this->lockFilePath))
        {
            throw new \Exception($this->lockFilePath.' not found');
        }

        $this->getLockFileData();
    }

    /**
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    private function getLockFileData()
    {
        if (empty($this->lockFileData)) {
            $data = Json::decode(file_get_contents($this->lockFilePath), Json::FORCE_ARRAY);
            foreach ($data['packages'] AS $package) {
                $this->lockFileData[$package['name']] = $package;
            }
        }
        return $this->lockFileData;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function isInstalled($name)
    {
        return array_key_exists($name, $this->getInstalled());
    }

    /**
     * @return array
     */
    public function getInstalled()
    {
        return $this->getLockFileData();
    }

    public function getData($name)
    {
        if (!$this->isInstalled($name))
        {
            return [];
        }

        return $this->getInstalled()[$name];
    }
}