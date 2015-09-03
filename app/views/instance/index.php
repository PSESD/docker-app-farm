<?php
use yii\helpers\Html;
use yii\helpers\Url;

//\d(Yii::$app->docker->getContainers());
// $t = Yii::$app->docker->getContainerById('daf58d5cfd127ce55e8214a92a94f35235db4a3627d79b27f2118582269650f2');
// $response = $t->executeCommand(['/bin/bash', '-c', 'ps aux']);
// \d($response);


$this->title = 'Instance';
canis\appFarm\components\web\assetBundles\InstanceManagerAsset::register($this);
$options = [];
$options['packageUrl'] = Url::to('/instance/package');
$options['actionUrl'] = Url::to('/instance/action');
$options['createInstanceUrl'] = Url::to('/instance/create');
$options['applications'] = Yii::$app->collectors['applications']->getPackage();
echo Html::tag('div', '', ['data-instance-manager' => json_encode($options)]);