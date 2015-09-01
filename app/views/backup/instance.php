<?php
use canis\helpers\Html;
use canis\appFarm\models\Instance;
use canis\appFarm\models\Backup;
// \d($instances);exit;
$this->title = 'Backups of ' . $instance->name;
$this->params['breadcrumbs'][] = ['label' => 'Backups', 'url' => ['/backup/index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];

echo Html::pageHeader('Backups of Instances');
if (empty($backups)) {
	echo Html::tag('div', 'No backups exist for this instance', ['class' => 'alert alert-warning']);
}
echo Html::beginTag('div', ['class' => 'list-group']);
foreach ($backups as $backupId => $backup) {
	echo $this->renderFile('@app/views/backup/_backup.php', ['backup' => $backup]);
}

echo Html::endTag('div');
