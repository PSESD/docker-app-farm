<?php
namespace canis\appFarm\components\applications\actions;

use Yii;
use canis\deferred\components\Result;
use canis\appFarm\models\Instance;

class Restart extends Action
{
    public function run()
    {
    	if (empty($this->config['instanceId']) || !($instance = Instance::get($this->config['instanceId'])) || !($instanceObject = $instance->dataObject)) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be found!';
            return false;
        }
        if (!$instance->dataObject->restart()) {
        	$this->result->isSuccess = false;
            $this->result->message = 'Instance could not be restarted';
            return false;
        }

        $this->result->message = 'Instance was restarted';
        $this->result->isSuccess = true;
        return true;
    }

    public function getDescriptor()
    {
        $extra = '';
        if (!empty($this->config['instanceId']) && ($instance = Instance::get($this->config['instanceId'])) && ($instanceObject = $instance->dataObject)) {
            $extra = ': '. $instance->name;
        }
        return 'Restart Instance'.$extra;
    }

    public function getResultConfig()
    {
        return [
            'class' => Result::className()
        ];
    }
}
?>
