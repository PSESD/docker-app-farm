<?php
$config = include __DIR__ . DIRECTORY_SEPARATOR .  'base.php';

$config['controllerNamespace'] = 'canis\appFarm\controllers';
if (isset($base['components']['redis'])) {
	$config['components']['session'] = [
	    'class' => 'yii\redis\Session',
	    'timeout' => '4000', 
	];
}
$config['components']['urlManager'] = [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'cache' => null, // disable in production
    'rules' => [
        // a standard rule mapping '/' to 'site/index' action
        '' => 'default/index',
        '<controller:\w+>' => '<controller>/index>',
        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
    ],
];
$config['components']['assetManager'] = [
    'linkAssets' => false,
];
return $config;