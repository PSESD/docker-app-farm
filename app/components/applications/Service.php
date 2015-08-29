<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;

abstract class Service extends \canis\base\Component
{
	public $instance;
	public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["instance"];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }
        return $keys;
    }

	abstract public function getServiceName();
	abstract public function getImage();
	
	public function createInstance($id, $applicationInstance)
	{
		$serviceInstance = [];
		$serviceInstance['class'] = ServiceInstance::className();
		$serviceInstance['service'] = $this;
		$serviceInstance['serviceId'] = $id;
		$serviceInstance['applicationInstance'] = $applicationInstance;
		return $this->instance = Yii::createObject($serviceInstance);
	}

	public function afterCreate($serviceInstance)
	{
		return true;
	}

	public function getBaseEnvironment()
	{
		return [];
	}

	public function getVolumes()
	{
		return null;
	}

	public function getPorts()
	{
		return null;
	}

	public function getExpose()
	{
		return null;
	}

	public function getLinks()
	{
		return null;
	}

	public function getPriviledged()
	{
		return null;
	}

	public function getRestart()
	{
		return null;
	}

	final public function getEnvironment()
	{
		$env = $this->getBaseEnvironment();
		return $env;
	}
}
?>