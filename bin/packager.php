<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
$rootDir = getcwd();
$vendorDir = $rootDir . '/vendor';
$wwwDir = $rootDir . '/www';
$appDir = $rootDir . '/app';
$logDir = $rootDir . '/log';
$tempDir = $rootDir . '/temp';

if (!file_exists($vendorDir . '/autoload.php')) {
    throw new \Exception($vendorDir . '/autoload.php not found!');
}

require_once $vendorDir . '/autoload.php';
$configurator = new Nette\Configurator;

$configurator->addParameters(array(
    'appDir' => $appDir,
    'wwwDir' => $wwwDir
));

$configurator->setDebugMode(true);

$configurator->enableDebugger($logDir);
$configurator->setTempDirectory($tempDir);
$configurator->addConfig(dirname(__DIR__) . '/config/config.neon');
$container = $configurator->createContainer();
$container->getService('application')->run();
