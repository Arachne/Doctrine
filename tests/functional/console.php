#!/usr/bin/env php
<?php

use Arachne\Codeception\Module\NetteDIModule;
use Nette\Utils\FileSystem;
use Symfony\Component\Yaml\Yaml;

require __DIR__.'/../../vendor/autoload.php';

$value = Yaml::parse(file_get_contents(__DIR__.'.suite.yml'));

$modules = $value['modules']['enabled'];
$config = null;
foreach ($modules as $module) {
    if (is_array($module) && key($module) === NetteDIModule::class) {
        $config = reset($module);
        break;
    }
}

if (!$config) {
    throw new Exception('NetteDIModule configuration not found.');
}

$configurator = new $config['configurator']();

if (isset($config['logDir'])) {
    $configurator->enableDebugger(__DIR__.'/'.$config['logDir']);
}

$tempDir = __DIR__.'/'.$config['tempDir'];
FileSystem::delete($tempDir);
FileSystem::createDir($tempDir);
$configurator->setTempDirectory($tempDir);

if (isset($config['debugMode'])) {
    $configurator->setDebugMode($config['debugMode']);
}

if (isset($config['configFiles'])) {
    foreach ($config['configFiles'] as $file) {
        $configurator->addConfig(__DIR__.'/'.$file, false);
    }
}

$container = $configurator->createContainer();

// Run console application.
$container->getByType('Symfony\Component\Console\Application')->run();
