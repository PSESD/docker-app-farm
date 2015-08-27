<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

canis\appFarm\components\web\assetBundles\AppAsset::register($this);


if (YII_ENV_DEV) {
    Html::addCssClass($this->bodyHtmlOptions, 'development');
}
$post = [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken];
$this->bodyHtmlOptions['data-post'] = json_encode($post);

$this->beginPage();
echo '<!DOCTYPE html>';
echo Html::beginTag('html', ['lang' => 'en']);
echo Html::beginTag('head');
echo Html::tag('meta', false, ['charset' => 'utf-8']);
echo Html::tag('meta', false, ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
echo Html::csrfMetaTags();
$title = '';
if (!empty($this->title)) {
    $title .= Html::encode(strip_tags($this->title)) . ' &mdash; ';
}
$title .= Html::encode(Yii::$app->params['siteName']);
echo Html::tag('title', $title);
$this->head();
echo Html::endTag('head');
echo Html::beginTag('body', $this->bodyHtmlOptions);
$this->beginBody();
echo $content;
$this->endBody();
echo Html::endTag('body');
echo Html::endTag('html');
$this->endPage();