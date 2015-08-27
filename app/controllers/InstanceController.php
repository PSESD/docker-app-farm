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
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use canis\appFarm\models\Instance;

class InstanceController extends \canis\appFarm\components\web\Controller
{
	/**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['error'],
                        'allow' => true
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'refresh' => ['post'],
                ],
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->isGuest && !Yii::$app->docker->isConnected) {
            throw new \canis\appFarm\components\exceptions\DockerException("Could not connect to docker daemon. Verify configuration.");
            return false;
        }
        return true;
    }

	public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }

    public function actionCreate()
    {
        if (!empty($_GET['application_id']) || !($application = Yii::$app->collectors['applications']->getByPk($_GET['application_id']))) {
            throw new NotFoundHttpException("Application not found");
        }
        $this->params['application'] = $application;
        Yii::$app->response->view = 'create';
    }
}
?>