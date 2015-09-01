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
use canis\appFarm\models\Instance;
use canis\appFarm\models\Backup;

class BackupController extends Controller
{

    public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }
    
    public function actionInstance()
    {

        if (empty($_GET['id']) || !($instance = Instance::get($_GET['id']))) {
            throw new HttpException(404, 'Instance could not be found');
        }
        $this->params['instance'] = $instance;
        $this->params['backups'] = Backup::find()->where(['instance_id' => $instance->id])->orderBy(['created' => SORT_DESC])->all();

        Yii::$app->response->view = 'instance';
    }
    public function actionDownload()
    {

        if (empty($_GET['id']) || !($backup = Backup::get($_GET['id']))) {
            throw new HttpException(404, 'Backup could not be found');
        }
        $this->params['backup'] = $backup;
        return $backup->serveBackup();
    }
    
    public function actionDelete()
    {

        if (empty($_GET['id']) || !($backup = Backup::get($_GET['id']))) {
            throw new HttpException(404, 'Backup could not be found');
        }
        $this->params['backup'] = $backup;
        if (!empty($_GET['confirm'])) {
            if ($backup->delete()) {
                Yii::$app->response->refresh = true;
                Yii::$app->response->task = 'message';
                Yii::$app->response->success = 'Backup was deleted!';
            } else {
                Yii::$app->response->task = 'message';
                Yii::$app->response->error = 'An error occurred while deleting the backup';
            }
            return;
        }
        // isConfirmation

        Yii::$app->response->taskOptions = ['title' => 'Delete Backup', 'isConfirmDeletion' => true];
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->view = 'delete';
    }
    
}
?>