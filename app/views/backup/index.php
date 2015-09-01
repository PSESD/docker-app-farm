<?php
use canis\helpers\Html;
use canis\appFarm\models\Instance;
use canis\appFarm\models\Backup;

$instances = Instance::find()->orderBy(['name' => SORT_ASC])->all();
// \d($instances);exit;
$this->title = 'Backups';
$this->params['breadcrumbs'][] = ['label' => $this->title];

echo Html::pageHeader('Backups of Instances');
echo Html::beginTag('div', ['class' => 'list-group']);
foreach ($instances as $instanceId => $instance) {
	$backupCount = Backup::find()->where(['instance_id' => $instance->id])->count();
	if (empty($backupCount) || $backupCount === '0') {
		continue;
	}
	$extras = [];
	switch ($instance->dataObject->status) {
		case 'failed':
			continue 2;
		break;
		case 'ready':

		break;
		case 'terminated':
			$extras[] = Html::tag('span', 'Terminated', ['class' => 'label label-danger pull-right', 'title' => 'Terminated on ' . date("M d, Y G:i:sa", strtotime($instance->terminated))]);
		break;
	}
	$extras[] = Html::tag('span', $backupCount, ['class' => 'badge pull-right']);
    echo Html::a(
        Html::tag('h4', implode($extras) . $instance->name, ['class' => 'list-group-item-heading']) .
        Html::tag('div', $instance->dataObject->application->object->name, ['class' => 'list-group-item-text']),
        ['/backup/instance', 'id' => $instance->id], ['class' => 'list-group-item']);
}

echo Html::endTag('div');
