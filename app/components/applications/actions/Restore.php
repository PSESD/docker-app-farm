<?php
namespace canis\appFarm\components\applications\actions;

use Yii;
use canis\deferred\components\Result;
use canis\appFarm\models\Instance;
use canis\appFarm\models\Backup as BackupModel;

class Restore extends Action
{
    public function run()
    {
        if (empty($this->config['instanceId']) || !($instance = Instance::get($this->config['instanceId'])) || !($instanceObject = $instance->dataObject)) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be found!';
            return false;
        }
        if (empty($this->config['backupId']) || !($backup = BackupModel::get($this->config['backupId'])) || !($backupObject = $backup->dataObject)) {
            $this->result->isSuccess = false;
            $this->result->message = 'Backup could not be found!';
            return false;
        }

        // try {
        //     if (!$instance->dataObject->application->object->hasRestoreTask() || !$instance->dataObject->restore()) {
        //         $this->result->isSuccess = false;
        //         $this->result->message = 'Instance could not be restored';
        //         return false;
        //     }
        // } catch (\Exception $e) {
        //     $this->result->isSuccess = false;
        //     $this->result->message = 'Instance could not be restored: ' . $e->__toString();
        //     return false;
        // }

        $this->result->message = 'Instance was restored';
        $this->result->isSuccess = true;
        return true;
    }

    public static function confirm()
    {
        return true;
    }

    public function getDescriptor()
    {
        $extra = '';
        if (!empty($this->config['instanceId']) && ($instance = Instance::get($this->config['instanceId'])) && ($instanceObject = $instance->dataObject)) {
            $extra = ': '. $instance->name;
        }
        if (!empty($this->config['backupId']) && ($backup = BackupModel::get($this->config['backupId'])) && ($backupObject = $backup->dataObject)) {
            $extra = ' from '. $backupObject->data['descriptor'];
        }
        return 'Restore Instance' . $extra;
    }

    public static function handleInput(&$config)
    {
        if (!empty($_GET['from']) && ($backup = BackupModel::get($_GET['from'])) && ($backupObject = $backup->dataObject)) {
            $config['backupId'] = $_GET['from'];
        }
    }

    public function requiredConfigParams()
    {
        return array_merge(parent::requiredConfigParams(), ['backupId']);
    }


    public function getResultConfig()
    {
        return [
            'class' => \canis\deferred\components\LogResult::className(),
        ];
    }
}
?>
