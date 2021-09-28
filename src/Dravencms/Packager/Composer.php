<?php declare(strict_types = 1);
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
    public function __construct(string $vendorDir)
    {
        $this->vendorDir = $vendorDir;
        $this->lockFilePath = $vendorDir.'/../'.self::COMPOSER_LOCK;

        if (file_exists($this->lockFilePath))
        {
            $this->getLockFileData();
        }
    }

    /**
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    private function getLockFileData(): array
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
     * @param $name
     * @return bool
     * @throws \Nette\Utils\JsonException
     */
    public function isInstalled($name): bool
    {
        return array_key_exists($name, $this->getInstalled());
    }

    /**
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    public function getInstalled(): array
    {
        return $this->getLockFileData();
    }

    /**
     * @param $name
     * @return array|mixed
     * @throws \Nette\Utils\JsonException
     */
    public function getData(string $name)
    {
        if (!$this->isInstalled($name))
        {
            return [];
        }

        return $this->getInstalled()[$name];
    }
}