<?php
namespace canis\appFarm\components\applications\actions;

use Yii;
use canis\deferred\components\Result;
use canis\appFarm\models\Instance;

class Start extends Action
{
    public function run()
    {
    	if (empty($this->config['instanceId']) || !($instance = Instance::get($this->config['instanceId'])) || !($instanceObject = $instance->dataObject)) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be found!';
            return false;
        }
        if (!$instance->dataObject->start()) {
        	$this->result->isSuccess = false;
            $this->result->message = 'Instance could not be started';
            return false;
        }

        $this->result->message = 'Instance was started';
        $this->result->isSuccess = true;
        return true;
    }

    public function getDescriptor()
    {
        $extra = '';
        if (!empty($this->config['instanceId']) && ($instance = Instance::get($this->config['instanceId'])) && ($instanceObject = $instance->dataObject)) {
            $extra = ': '. $instance->name;
        }
        return 'Start Instance'.$extra;
    }

    public function getResultConfig()
    {
        return [
            'class' => Result::className()
        ];
    }
}
?>
