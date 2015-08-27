<?php
use yii\helpers\Html;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\widgets\Breadcrumbs;

$this->beginContent('@canis/appFarm/views/layouts/frame.php');
NavBar::begin([
    'brandLabel' => Yii::$app->params['siteName'],
    'brandUrl' => ['default/index'],
    'options' => ['class' => 'navbar-inverse navbar-fixed-top'],
]);
// echo Nav::widget([
//     'options' => ['class' => 'nav navbar-nav navbar-right'],
//     'items' => [
//     ],
// ]);
$userMenu = [];
//$userMenu[] = DeferredNavItem::widget([]);
if (Yii::$app->user->isGuest) {
    $userMenu[] = ['label' => 'Sign In', 'url' => ['/user/login'],
                    'linkOptions' => ['data-method' => 'post'], ];
} else {
    $userMenuItem = [
        'label' =>  '<span class="glyphicon glyphicon-user"></span> <span class="nav-label hidden-xs hidden-sm">' . Yii::$app->user->identity->first_name . '</span>',
        'url' => '#',
        'linkOptions' => [],
        'items' => [],
    ];
    $userMenuItem['items'][] = [
        'label' => 'Logout' ,
        'url' => ['/user/logout'],
        'linkOptions' => ['data-method' => 'post', 'title' => 'Logout'],
    ];
    $userMenu[] = $userMenuItem;
}
echo Nav::widget([
    'options' => ['class' => 'navbar-nav pull-right'],
    'encodeLabels' => false,
    'items' => $userMenu,
]);
NavBar::end();
echo Html::beginTag('div', ['class' => 'inner-container container']);
echo Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    'encodeLabels' => false,
]);
if (($success = Yii::$app->session->getFlash('success', false, true))) {
    echo Html::tag('div', $success, ['class' => 'alert alert-success']);
}
if (($error = Yii::$app->session->getFlash('error', false, true))) {
    echo Html::tag('div', $error, ['class' => 'alert alert-danger']);
}
echo $content;
echo Html::endTag('div');
$this->endContent();
