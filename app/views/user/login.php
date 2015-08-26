<?php
use canis\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var canis\wdf\models\LoginForm $model
 */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
$formOptions = ['class' => 'form-horizontal'];
if (!Yii::$app->request->isAjax) {
    echo '<div class="col-md-offset-3 col-md-6">';
} else {
    Html::addCssClass($formOptions, 'ajax');
}
$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => $formOptions,
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-3 control-label'],
    ],
]);

echo $form->field($model, 'email');
echo $form->field($model, 'password')->passwordInput();
echo $form->field($model, 'rememberMe', [
    'template' => "<div class=\"col-md-offset-1 col-lg-9 \">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
])->checkbox();
echo '<div class="submit-group col-md-offset-1 col-lg-9 ">';
echo Html::submitButton('Login', ['class' => 'btn btn-primary']);
echo '</div>';
echo '</div>';
ActiveForm::end();
