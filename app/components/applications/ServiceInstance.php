<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;
use Docker\Port;
use Docker\PortCollection;

class ServiceInstance extends \canis\base\Component
{
	public $applicationInstance;
	public $serviceId;
	public $service;
	public $containerId = false;
	public $containerName = false;

	protected $initializing = false;
	protected $linkedContainerIds = [];
	protected $container;

	public function __wakeup()
    {
        $this->service->instance = $this;
    }

	public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["applicationInstance", "\0*\0container", ];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }
        return $keys;
    }

    public function check()
    {
    	if (!empty($this->service->links)) {
	    	foreach ($this->service->links as $linkId) {
	    		if (!$this->applicationInstance->getServiceInstance($linkId)) {
	    			return false;
	    		}
	    	}
	    }
    	return true;
    }

    public static function getContainerById($containerId)
    {
    	$container = Yii::$app->docker->docker->getContainerManager()->find($containerId);
    	if (empty($container)) {
    		return false;
    	}
    	return $container;
    }

    public function getContainerSettings()
    {
    	$settings = [];
    	$settings['Image'] = $this->service->image;
    	//$settings['Hostname'] = $this->containerName;
    	$settings['HostConfig'] = [];
    	if (!empty($this->linkedContainerIds)) {
    		$settings['HostConfig']['Links'] = $this->linkedContainerIds;
    	}
    	if (($environment = $this->service->getEnvironment($this)) && !empty($environment)) {
    		$settings['Env'] = [];
    		foreach ($environment as $key => $value) {
    			$settings['Env'][] = $key .'='. $value;
    		}
    	}
    	if (($restartPolicy = $this->service->restart) && !empty($restartPolicy)) {
    		$settings['HostConfig']['RestartPolicy'] = ['Name' => $restartPolicy];
    	}
    	if (($priviledged = $this->service->priviledged) && !empty($priviledged)) {
    		$settings['HostConfig']['Privileged'] = $priviledged;
    	}
    	if (($binds = $this->service->volumes) && !empty($binds)) {
    		$settings['HostConfig']['Binds'] = [];
    		$settings['Volumes'] = [];
    		foreach ($binds as $bind) {
    			if (strpos($bind, ':') === false) {
    				$settings['Volumes'][$bind] = [];
    			} else {
    				$settings['HostConfig']['Binds'][] = $bind;
    			}
    		}
    		if (empty($settings['Volumes'])) {
    			unset($settings['Volumes']);
    		}
    		if (empty($settings['HostConfig']['Binds'])) {
    			unset($settings['HostConfig']['Binds']);
    		}
    	}
    	if (($ports = $this->service->ports) && !empty($ports)) {
    		$portCollection = new PortCollection();
    		foreach ($ports as $port) {
    			$portCollection->add(new Port($port));
    		}
    		$settings['HostConfig']['PortBindings'] = $portCollection->toSpec();
    	}
    	if (($expose = $this->service->expose) && !empty($expose)) {
    		$portCollection = new PortCollection();
    		foreach ($expose as $port) {
    			$portCollection->add(new Port($port));
    		}
    		$settings['ExposedPorts'] = $portCollection->toExposedPorts();
    	}
    	return $settings;
    }

    public function getContainer()
    {
    	if ($this->container) {
    		return $this->container;
    	}
    	if ($this->containerId) {
    		return $this->container = static::getContainerById($this->containerId);
    	}
    	if ($this->initializing) {
    		$this->applicationInstance->statusLog->addError('Dependency loop for service \''. $this->serviceId .'\'');
    		return false;
    	}
    	$this->initializing = true;
    	$this->applicationInstance->statusLog->addInfo('Creating container for \''. $this->serviceId .'\'');
		$this->containerName = $this->applicationInstance->prefix .'-'. $this->serviceId;
		if (!empty($this->service->links)) {
			foreach ($this->service->links as $linkedService) {
	    		if (!($service = $this->applicationInstance->getServiceInstance($linkedService))) {	
		    		$this->applicationInstance->statusLog->addError('Invalid linked service for \''. $this->serviceId .'\' to \''. $linkedService .'\'');
		    		return false;
	    		}
	    		$this->applicationInstance->statusLog->addInfo('Setting up linked service \''. $linkedService .'\' before setting up \''. $this->serviceId .'\'');
	    		if (!($container = $service->getContainer())) {	
		    		$this->applicationInstance->statusLog->addError('Unable to set up service \''. $this->serviceId .'\' due to the linked service \''. $linkedService .'\' failing initialization');
		    		return false;
	    		}
	    		$this->linkedContainerIds[] = $container->getId() .':'. $linkedService;
			}
		}
		$containerSettings = $this->containerSettings;
		$this->applicationInstance->statusLog->addInfo('Setting up container for service \''. $this->serviceId .'\'', ['settings' => $containerSettings]);
		$container = new \Docker\Container($containerSettings);
		$container->setName($this->containerName);
		$this->applicationInstance->statusLog->addInfo('Creating container for service \''. $this->serviceId .'\'');
		try {
			Yii::$app->docker->docker->getContainerManager()->create($container);
		} catch (\Exception $e) {
			$this->applicationInstance->statusLog->addError('Unable to create container for \''. $this->serviceId .'\'', ['error' => $e->__toString()]);
		    return false;
		}

		if ($container->getId() === null) {
			$this->applicationInstance->statusLog->addError('Unable to create container for \''. $this->serviceId .'\' for unknown reasons');
		    return false;
		}
		$this->containerId = $container->getId();
		$this->container = $container;
		$this->initializing = false;
		return $container;
    }

    public function isRunning()
    {
    	return $this->status === 'running';
    }

    public function isStopped()
    {
    	$status = $this->status;
    	return $status === 'stopped' || !$status;
    }

    public function isStarted()
    {
    	$status = $this->status;
    	return $status === 'running' || $status === 'restarting' || $status === 'paused';
    }

    public function getUptime()
    {
    	$stateInfo = $this->stateInfo;
    	if (!$stateInfo) {
    		return false;
    	}
    	if (!empty($stateInfo['Running'])) {
    		return time() - strtotime($stateInfo['StartedAt']);
    	}
    	return false;
    }

    public function getStatus()
    {
    	$stateInfo = $this->stateInfo;
    	if (!$stateInfo) {
    		return 'stat_error';
    	}
    	if (!empty($stateInfo['Restarting'])) {
    		return 'restarting';
    	}
    	if (!empty($stateInfo['Paused'])) {
    		return 'paused';
    	}
    	if (!empty($stateInfo['Running'])) {
    		return 'running';
    	}
    	return 'stopped';
    }

    public function getStateInfo()
    {
    	try {
    		if (!($container = $this->getContainer())) {
    			return 'no_container';
    		}
    		$container->setRuntimeInformations([]);
    		Yii::$app->docker->docker->getContainerManager()->inspect($container);
    		$info = $container->getRuntimeInformations();
    		if (isset($info['State'])) {
    			return $info['State'];
    		}
    		return false;
		} catch (\Exception $e) {
			return 'exception';
		}
		return 'other';
    }

    public function terminate()
    {
    	if (!$this->getContainer()) {
    		return true;
    	}
    	if ($this->isStarted()) {
    		$this->stop();
    	}
    	$this->applicationInstance->statusLog->addInfo('Terminating container for service \''. $this->serviceId .'\'');
		try {
			Yii::$app->docker->docker->getContainerManager()->remove($this->container, true);
		} catch (\Exception $e) {
			$this->applicationInstance->statusLog->addError('Unable to terminate container for \''. $this->serviceId .'\'', ['error' => $e->__toString()]);
		    return false;
		}
		return true;
    }

    public function start()
    {
    	if (!$this->getContainer()) {
    		return false;
    	}
    	$this->applicationInstance->statusLog->addInfo('Starting container for service \''. $this->serviceId .'\'');
		try {
			Yii::$app->docker->docker->getContainerManager()->start($this->container);
			if ($this->isStarted()) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			$this->applicationInstance->statusLog->addError('Unable to start container for \''. $this->serviceId .'\'', ['error' => $e->__toString()]);
		    return false;
		}
		return true;
    }

    public function stop()
    {
    	if (!$this->getContainer()) {
    		return false;
    	}
    	$this->applicationInstance->statusLog->addInfo('Stopping container for service \''. $this->serviceId .'\'');
		try {
			Yii::$app->docker->docker->getContainerManager()->stop($this->container);
			if (!$this->isStarted()) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			$this->applicationInstance->statusLog->addError('Unable to stop container for \''. $this->serviceId .'\'', ['error' => $e->__toString()]);
		    return false;
		}
		return true;
    }

    public function execCommand($command, $callback = false)
    {
    	if (!$this->getContainer()) {
    		return false;
    	}
    	$this->applicationInstance->statusLog->addInfo('Running command on \''. $this->serviceId .'\'', ['commands' => $command]);
		try {
			$execute = Yii::$app->docker->docker->getContainerManager()->exec($this->container, $command);
			$response = Yii::$app->docker->docker->getContainerManager()->execstart($execute);
			if ($callback) {
				$callback($command, $response);
			}
			return $response;
		} catch (\Exception $e) {
			$this->applicationInstance->statusLog->addError('Command failed to run', ['error' => $e->__toString(), 'command' => $command, 'usedCallback' => $callback !== false]);
		    return false;
		}
    }

    public function execCommands($commands, $callback = false, $fatalFail = true) {
    	foreach ($commands as $command) {
    		if (!$this->execCommand($command, $callback) && $fatalFail) {
    			return false;
    		}
    	}
    	return true;
    }

    public function getPackage()
    {
		$s = [];
		$s['name'] = $this->service->serviceName;
		$s['uptime'] = $this->uptime;
		$s['status'] = $this->status;
		$s['containerId'] = $this->containerId;
		return $s;
    }
}
?>