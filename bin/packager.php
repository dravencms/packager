<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
$rootDir = getcwd();
$vendorDir = $rootDir . '/vendor';
$wwwDir = $rootDir . '/www';
$appDir = $rootDir . '/app';
$tempDir = $rootDir . '/temp';

if (!file_exists($vendorDir . '/autoload.php')) {
    throw new \Exception($vendorDir . '/autoload.php not found!');
}

require_once $vendorDir . '/autoload.php';

$configurator = new \Nette\Bootstrap\Configurator;


$configurator->setDebugMode(true);

$configurator->setTempDirectory($tempDir);
$configurator->addStaticParameters([
    'appDir' => $appDir,
    'wwwDir' => $wwwDir,
]);
$configurator->addConfig(dirname(__DIR__) . '/config/config.neon');
$configurator->defaultExtensions['application'][1][1] = null; // disable scan dirs when Nette\Bridges\ApplicationDI\ApplicationExtension is installed
$configurator->createContainer()->getByType(\Contributte\Console\Application::class)->run();
