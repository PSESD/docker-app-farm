<?php
use yii\helpers\Html;
echo Html::beginForm('', 'post', ['class' => 'ajax']);
echo Html::beginTag('div', ['class' => 'form']);
//echo Html::activeHiddenInput($model, 'application_id');

echo Html::beginTag('div', ['class' => 'form-group']);
echo Html::activeLabel($model, 'name');
echo Html::activeTextInput($model, 'name', ['class' => 'form-control']);
echo Html::endTag('div');
$baseDataId = Html::getInputId($model, 'data');
$baseDataName = Html::getInputName($model, 'data');
foreach ($application->object->setupFields as $id => $field) {
	$value = isset($model->data->settings[$id]) ? $model->data->settings[$id] : '';
	$fieldId = $baseDataId . '_' . $id;
	$fieldName = $baseDataName . '[' . $id .']';
	echo Html::beginTag('div', ['class' => 'form-group']);
	echo Html::label($field['label'], $fieldId);
	switch ($field['type']) {
		default:
			echo Html::textInput($fieldName, $value, ['class' => 'form-control', 'id' => $fieldId]);
		break;
	}
	if (isset($field['help'])) {
		echo Html::tag('p', $field['help'], ['class' => 'help-block']);
	}
	echo Html::endTag('div');

}
echo Html::endTag('div');
echo Html::endForm();