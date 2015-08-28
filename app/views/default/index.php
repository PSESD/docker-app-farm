<?php
use yii\helpers\Html;
use yii\helpers\Url;
canis\appFarm\components\web\assetBundles\InstanceManagerAsset::register($this);
$options = [];
$options['packageUrl'] = Url::to('/instance/package');
$options['createInstanceUrl'] = Url::to('/instance/create');
$options['applications'] = Yii::$app->collectors['applications']->getPackage();
echo Html::tag('div', '', ['data-instance-manager' => json_encode($options)]);