<?php
use yii\helpers\Html;
use yii\helpers\Url;
// $t = Yii::$app->docker->getContainerById('62d6955d9ad7eb69b921bfde20d4d1013b77730898e6689d12fdb4e87283ffea');
// $command = ['/bin/bash', '-c', 'sleep 11 && echo hi' . ""];
// \d($t->executeCommand($command));exit;


$this->title = 'Instance';
canis\appFarm\components\web\assetBundles\InstanceManagerAsset::register($this);
$options = [];
$options['packageUrl'] = Url::to('/instance/package');
$options['actionUrl'] = Url::to('/instance/action');
$options['createInstanceUrl'] = Url::to('/instance/create');
$options['applications'] = Yii::$app->collectors['applications']->getPackage();
echo Html::tag('div', '', ['data-instance-manager' => json_encode($options)]);