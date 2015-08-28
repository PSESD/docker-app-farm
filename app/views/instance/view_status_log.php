<?php
use canis\helpers\Html;

\canis\web\assetBundles\CanisLogViewerAsset::register($this);
$this->title = "View Status Log";
// $this->params['breadcrumbs'][] = ['label' => 'Administration', 'url' => ['/admin/dashboard/index']];
// $this->params['breadcrumbs'][] = ['label' => 'Interfaces', 'url' => ['admin/interface/index']];
// $this->params['breadcrumbs'][] = ['label' => $interfaceModel->name, 'url' => ['admin/interface/view-logs', 'id' => $interfaceModel->primaryKey]];

// $this->params['breadcrumbs'][] = $this->title;

echo Html::tag('div', '', [
    'data-log' => json_encode($instance->statusLogPackage),
]);
