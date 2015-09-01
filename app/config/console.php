<?php
defined('IS_CONSOLE') || define('IS_CONSOLE', true);
$config = include __DIR__ . DIRECTORY_SEPARATOR .  'base.php';

$config['controllerNamespace'] = 'canis\appFarm\console\controllers';
$config['controllerMap'] = [
    'migrate' => 'canis\console\controllers\MigrateController',
];
unset($config['modules']['debug']);
return $config;