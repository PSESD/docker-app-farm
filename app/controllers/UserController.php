<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\controllers;

use Yii;
use canis\wdf\models\LoginForm;

class UserController extends \canis\wdf\components\web\Controller
{
	/**
     * The login page for the application.
     */
    public function actionLogin()
    {
    	$model = new LoginForm();
        if ($model->load($_POST) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

	/**
     * The login page for the application.
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
?>
