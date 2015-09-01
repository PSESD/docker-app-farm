<?php
namespace canis\appFarm\components\applications\actions;

use Yii;
use canis\deferred\components\Result;
use canis\appFarm\models\Instance;

class Terminate extends Action
{
    public function run()
    {
    	if (empty($this->config['instanceId']) || !($instance = Instance::get($this->config['instanceId'])) || !($instanceObject = $instance->dataObject)) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be found!';
            return false;
        }
        try {
            if (!$instance->dataObject->terminate()) {
                $this->result->isSuccess = false;
                $this->result->message = 'Instance could not be terminated';
                return false;
            }
        } catch (\Exception $e) {
            $this->result->isSuccess = false;
            $this->result->message = 'Instance could not be terminated: ' . $e->__toString();
            return false;
        }

        $this->result->message = 'Instance was terminated';
        $this->result->isSuccess = true;
        return true;
    }

    public function getDescriptor()
    {
        $extra = '';
        if (!empty($this->config['instanceId']) && ($instance = Instance::get($this->config['instanceId'])) && ($instanceObject = $instance->dataObject)) {
            $extra = ': '. $instance->name;
        }
        return 'Terminate Instance'. $extra;
    }

    public function getResultConfig()
    {
        return [
            'class' => Result::className()
        ];
    }

    public function requiredConfigParams()
    {
        return array_merge(parent::requiredConfigParams(), ['instanceId']);
    }
    
    public static function confirm()
    {
        return true;
    }
}
?>
