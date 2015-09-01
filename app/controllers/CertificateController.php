<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use canis\appFarm\models\Backup;

class CertificateController extends Controller
{

    public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }
    
}
?>