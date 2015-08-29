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
use canis\appFarm\components\applications\ApplicationInstance;

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
        if (empty($_GET['application_id']) || !($application = Yii::$app->collectors['applications']->getByPk($_GET['application_id']))) {
            throw new NotFoundHttpException("Application not found");
        }
        $this->params['application'] = $application;
        $this->params['model'] = new Instance;
        $this->params['model']->application_id = $application->applicationObject->primaryKey;
        $this->params['applicationInstance'] = $this->params['model']->dataObject = new ApplicationInstance;

        if (!empty($_POST)) {
            $data = false;
            if (isset($_POST['Instance']['data'])) {
                $data = $_POST['Instance']['data'];
                unset($_POST['Instance']['data']);
            }
            $this->params['model']->load($_POST);
            $this->params['applicationInstance']->attributes = $data;
            $this->params['model']->active = 1;
            if ($this->params['model']->save()) {
                Yii::$app->response->success = 'Instance of \'' . $application->name .'\' created!';
                Yii::$app->response->task = 'trigger';
                Yii::$app->response->trigger = [['refresh', '.instance-manager']];
                return;
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Create';
        Yii::$app->response->taskOptions = ['title' => 'Create Instance of ' . $application->name, 'width' => '800px'];
    }

    /**
     * [[@doctodo method_description:actionViewLog]].
     *
     * @throws HttpException [[@doctodo exception_description:HttpException]]
     * @return [[@doctodo return_type:actionViewLog]] [[@doctodo return_description:actionViewLog]]
     *
     */
    public function actionViewStatusLog()
    {
        if (empty($_GET['id']) || !($instance = Instance::get($_GET['id']))) {
            throw new HttpException(404, 'Instance could not be found');
        }
        $this->params['instance'] = $instance;
        if (Yii::$app->request->isAjax && !empty($_GET['package'])) {
            Yii::$app->response->data = $instance->statusLogPackage;
            return;
        } elseif (Yii::$app->request->isAjax) {
            Yii::$app->response->taskOptions = ['title' => 'View Log', 'modalClass' => 'modal-xl'];
            Yii::$app->response->task = 'dialog';
        }
        Yii::$app->response->view = 'view_status_log';
    }

    public function actionAction()
    {
        if (empty($_POST['id']) || !($instance = Instance::get($_POST['id']))) {
            throw new HttpException(404, 'Instance could not be found');
        }
        Yii::$app->response->task = 'trigger';
        $instance->dataObject->handleAction($_POST['action']);
    }
    
    /**
     * The landing page for the application.
     */
    public function actionPackage()
    {
        Yii::$app->response->data = [];
        Yii::$app->response->data['instances'] = [];
        foreach (Instance::find()->all() as $instance) {
            Yii::$app->response->data['instances'][$instance->id] = $instance->dataObject->package;
        }
        return;
    }
}
?>