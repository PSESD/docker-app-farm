<?php
namespace canis\appFarm\console\controllers;

use Yii;
use canis\appFarm\models\Instance;

ini_set('memory_limit', -1);

class BackupController extends \canis\console\Controller
{
    public $verbose = false;
    protected $_instance;
    
    public function actionIndex($instance = false)
    {
        if ($instance) {
            $this->instance = $instance;
        }
        $this->out("Backing Up Instance: " . $this->instance->name);
        $instanceObject = $this->instance->dataObject;
        $application = $instanceObject->application->object;
        if (!$application->hasBackupTask()) {
            throw new \Exception("Application does not have backup task");
        }
        $status = new \canis\action\ConsoleStatus;
        if ($this->verbose) {
            $status->includeData = true;
        }
        if ($instanceObject->backup($status)) {
            $this->out("Success!");
        } else {
            $this->error("Failed");
        }
    }

    public function getInstance()
    {
        if (!$this->started) {
            return $this->_instance;
        }
        if ($this->_instance !== null) {
            return $this->_instance;
        }
        $this->instance = $this->prompt("What is the instance name?", ['required' => true]);
        return $this->_instance;
    }

    public function setInstance($instance)
    {
        if (($instanceModel = Instance::find()->where(['id' => $instance])->one())) {
            $this->_instance = $instanceModel;
        } else if (($instanceModel = Instance::find()->where(['name' => $instance])->one())) {
            $this->_instance = $instanceModel;
        } else {
            throw new \Exception("Invalid instance!");
        }
    }

    /**
     * @inheritdoc
     */
    public function options($id)
    {
        return array_merge(parent::options($id), ['instance', 'verbose']);
    }
}
