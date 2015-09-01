<?php
use canis\helpers\Html;
use canis\appFarm\models\Instance;
$application = Yii::$app->collectors['applications']->getOne($backup->dataObject->data['application']['systemId']);
if (empty($application->object)) {
	$application = false;
}
$extras = [];
$extras[] = Html::beginTag('div', ['class' => ['btn-group btn-group-sm']]);
if (!empty($backup->instance) && $backup->instance->active === 1) {
	$extras[] = Html::a('Restore (Original Instance)', ['/instance/action',  'action' => 'restore', 'id' => $backup->instance_id, 'from' => $backup->id], ['class' => 'btn btn-sm btn-danger', 'title' => 'Will over-write current instance!', 'data-handler' => 'background']);
}
if (!empty($application)) {
	$extras[] = Html::a('Restore (New Instance)', ['/instance/create', 'application_id' => $application->applicationObject->id, 'from' => $backup->id], ['class' => 'btn btn-default', 'data-handler' => 'background']);
}
$extras[] = Html::a('Download', ['/backup/download', 'id' => $backup->id], ['class' => 'btn btn-default']);
$extras[] = Html::a('Delete', ['/backup/delete', 'id' => $backup->id], ['class' => 'btn btn-sm btn-warning', 'data-handler' => 'background']);
$extras[] = Html::endTag('div');
echo Html::tag('div',
    Html::tag('h4', $backup->dataObject->data['descriptor'], ['class' => 'list-group-item-heading']) .
    Html::tag('div', implode($extras), ['class' => 'list-group-item-text']),
    ['class' => 'list-group-item']);
?>