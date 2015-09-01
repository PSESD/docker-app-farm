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
	public $daemon = true;
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
	
	public function generateMeta($serviceInstance)
	{
		return [];
	}

	public function afterCreate($serviceInstance)
	{

		$self = $this;
		$meta = $serviceInstance->meta;
		$commandTasks = [];
		$transferPath = $serviceInstance->containerName;
		$commandTasks['prepare_transfer'] = [
			'description' => 'Preparing transfer storage',
			'cmd' => 'curl -sS https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/prepare_transfer.sh | /bin/bash -s ' . $transferPath,
			'test' => '----TRANSFER_PREPARE_SUCCESS----'
		];

		foreach ($commandTasks as $id => $command) {
			if(!$serviceInstance->runCommand($command)) {
				return false;
			}
		}
		return true;
	}

	public function getBaseEnvironment($service)
	{
		return [];
	}

	public function getVolumes()
	{
		return null;
	}

	public function getVolumesFrom()
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

	public function getDependencies()
	{
		$d = [];
		if (!empty($this->links)) {
			$d = array_merge($d, $this->links);
		}
		if (!empty($this->volumesFrom)) {
			$d = array_merge($d, $this->volumesFrom);
		}
		return array_unique($d);
	}

	public function getPriviledged()
	{
		return null;
	}

	public function getRestart()
	{
		return null;
	}

	final public function getEnvironment($instance)
	{
		$env = $this->getBaseEnvironment($instance);
		$env['TRANSFER_PATH'] = $instance->transferPath;
		return $env;
	}
}
?>