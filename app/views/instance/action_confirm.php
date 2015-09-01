<?php
use yii\helpers\Html;
$dummyItem = $actionHandler::setup($config, true);

$this->title = 'Confirm';
echo Html::beginForm('', 'post', ['class' => 'ajax']);
echo Html::beginTag('div', ['class' => 'form']);
echo Html::hiddenInput('confirm', 1);
echo Html::hiddenInput('config', serialize($config));
echo Html::hiddenInput('confirm', 1);
echo Html::tag('div', 'Are you sure you want to perform the action <i>'. $dummyItem->descriptor .'</i>?', ['class' => 'alert alert-warning']);
echo Html::endTag('div');
echo Html::endForm();