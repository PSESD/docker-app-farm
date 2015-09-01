<?php
namespace canis\appFarm\components\applications\actions;

use Yii;
use canis\deferred\components\Result;
use canis\appFarm\models\Instance;

class Stop extends Action
{
    public function run()
    {
    	if (empty($this->config['instanceId']) || !($instance = Instance::get($this->config['instanceId'])) || !($instanceObject = $instance->dataObject)) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be found!';
            return false;
        }

        try {
            if (!$instance->dataObject->stop()) {
            	$this->result->isSuccess = false;
                $this->result->message = 'Instance could not be stopped';
                return false;
            }
        } catch (\Exception $e) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be stopped: ' . $e->__toString();
            return false;
        }

        $this->result->message = 'Instance was stopped';
        $this->result->isSuccess = true;
        return true;
    }

    public function getDescriptor()
    {
        $extra = '';
        if (!empty($this->config['instanceId']) && ($instance = Instance::get($this->config['instanceId'])) && ($instanceObject = $instance->dataObject)) {
            $extra = ': '. $instance->name;
        }
        return 'Stop Instance' . $extra;
    }
}
?>
