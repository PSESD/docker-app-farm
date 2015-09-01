<?php
namespace canis\appFarm\components\applications\actions;

use Yii;
use canis\deferred\components\Result;
use canis\appFarm\models\Instance;

class Backup extends Action
{
    public $reason = false;
    public function run()
    {
    	if (empty($this->config['instanceId']) || !($instance = Instance::get($this->config['instanceId'])) || !($instanceObject = $instance->dataObject)) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be found!';
            return false;
        }
        $config = [];
        if ($this->reason) {
            $config['reason'] = $this->reason;
        }
        try {
            if (!$instance->dataObject->canBackup() || !$instance->dataObject->backup($this->result)) {
            	$this->result->isSuccess = false;
                $this->result->message = 'Instance could not be backed up';
                return false;
            }
        } catch (\Exception $e) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be backed up: ' . $e->__toString();
            return false;
        }

        $this->result->message = 'Instance was backed up';
        $this->result->isSuccess = true;
        return true;
    }

    public function getDescriptor()
    {
        $extra = '';
        if (!empty($this->config['instanceId']) && ($instance = Instance::get($this->config['instanceId'])) && ($instanceObject = $instance->dataObject)) {
            $extra = ': '. $instance->name;
        }
        return 'Backup Instance' . $extra;
    }

    public function getResultConfig()
    {
        return [
            'class' => \canis\deferred\components\LogResult::className(),
        ];
    }
}
?>
