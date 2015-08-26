<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\controllers;

use Yii;

class DefaultController extends \canis\wdf\components\web\Controller
{
	/**
     * The landing page for the application.
     */
    public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }
}
?>