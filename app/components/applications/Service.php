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
			if(!$this->runCommand($serviceInstance, $command)) {
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
		return $env;
	}

	public function runCommand($serviceInstance, $command)
	{
		$self = $this;
		$obfuscate = [];
		if (!empty($command['obfuscate'])) {
			$obfuscate = $command['obfuscate'];
		}
		$response = $serviceInstance->execCommand($command['cmd'], false, $obfuscate);
		$responseBody = $response->getBody()->__toString();
		$responseTest = strpos($responseBody, $command['test']) === false;
		$responseBody = preg_replace('/[^\x20-\x7E]/','', $responseBody);

        foreach ($obfuscate as $o) {
            $responseBody = str_replace($o, str_repeat('*', strlen($o)), $responseBody);
        }
		if ($responseTest) {
			$serviceInstance->applicationInstance->statusLog->addError('Command FAILED on \''. $serviceInstance->serviceId .'\': ' . $command['description'] . '', ['data' => $responseBody]);
			return false;
		}  else {
			$serviceInstance->applicationInstance->statusLog->addInfo('Command SUCCESS on \''. $serviceInstance->serviceId .'\': ' . $command['description'] . '', ['data' => $responseBody]);
		}
		return true;
	}
}
?>