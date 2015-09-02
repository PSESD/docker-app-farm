<?php
use yii\helpers\Html;
use yii\helpers\Url;

//\d(Yii::$app->docker->getContainers());
// $t = Yii::$app->docker->getContainerById('9d6af40ea06b902b46534bd74cf52798d29dc1090dce91dbe798c64fe2455b40');
// \d($t->inspect()['State']);
// \d($t->start());
// exit;


$this->title = 'Instance';
canis\appFarm\components\web\assetBundles\InstanceManagerAsset::register($this);
$options = [];
$options['packageUrl'] = Url::to('/instance/package');
$options['actionUrl'] = Url::to('/instance/action');
$options['createInstanceUrl'] = Url::to('/instance/create');
$options['applications'] = Yii::$app->collectors['applications']->getPackage();
echo Html::tag('div', '', ['data-instance-manager' => json_encode($options)]);