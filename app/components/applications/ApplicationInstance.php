<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;
use yii\helpers\Url;
use canis\action\Status;
use canis\caching\Cacher;

class ApplicationInstance extends \canis\base\Component
{
	const EVENT_HOSTNAME_CHANGE = '_hostnameChange';
	public $model;
	public $status = 'uninitialized';

	protected $_prefix;
	protected $_attributes = [];
	protected $_services = [];
	protected $_cache = [];
	protected $_statusLog;
	protected $_startedOrder = [];

	public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["\0*\0_cache", "model"];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }

        return $keys;
    }

    public function __wakeup()
    {
        foreach ($this->_services as $id => $service) {
        	$service->applicationInstance = $this;
        }
    }

    public function getServiceInstance($id)
    {
    	if (isset($this->_services[$id])) {
    		return $this->_services[$id];
    	}
    	return false;
    }

    public function handleAction($actionId, $config = false)
    {
    	$actions = array_merge(static::actions(), $this->application->object->actions($this));
    	if (!isset($actions[$actionId])) {
    		Yii::$app->response->error = 'Unable to find action \''. $actionId .'\'';
    		return true;
    	}
    	$action = $actions[$actionId];
    	$actionHandler = $action['handler'];
    	$setupFields = $actionHandler::setupFields();
    	if (empty($setupFields) || !empty($config)) {
    		if (empty($config)) {
    			$config = [];
    		}
    		$config['instanceId'] = $this->model->id;
    		try {
	    		if (!($deferredAction = $actionHandler::setup($config))) {
		    		Yii::$app->response->error = 'Unable to initiate deferred action \''. $actionId .'\'';
		    		return true;
	    		} else {
	            	Yii::$app->response->task = 'client';
	            	Yii::$app->response->clientTask = 'deferredAction';
	            	Yii::$app->response->taskOptions = $deferredAction->package();
	    		}
	    	} catch (\Exception $e) {
	    		Yii::$app->response->error = $e->__toString();
	    		return true;
	    	}
    	} else {
    		
			Yii::$app->response->view = 'setup_action';
			return false;
		}
    }

    public function initialize()
    {
        if ($this->status !== 'starting') {
            return true;
        }
        
        $applicationItem = $this->application;
        if (!$applicationItem) {
    		$this->statusLog->addError('Could not find application \''. $this->applicationId .'\'');
    		return false;
        }
        $application = $applicationItem->object;
        $recipe = $application->recipe;
        $services = $recipe->services;

        // set up service objects
        $this->statusLog->addInfo('Initiating service objects');
        foreach ($services as $id => $service) {
        	$this->_services[$id] = $service->createInstance($id, $this);
        }
        $this->statusLog->addInfo('Checking service objects');
        foreach ($this->_services as $id => $service) {
        	if (!$service->check()) {
        		$this->statusLog->addError('Service \''. $id .'\' failed its settings checkup');
        		$this->updateStatus('failed');
        		return false;
        	}
        }

        // create services
        $this->updateStatus('creating_services');
        $this->statusLog->addInfo('Creating services');
        foreach ($this->_services as $id => $service) {
        	if (!$service->getContainer()) {
        		$this->updateStatus('failed');
        		return false;
        	}
        }
        $this->save();

        // starting services
        $this->updateStatus('starting_services');
        if(!$this->start()) {
        	$this->updateStatus('failed');
        	return false;
        }

        // wait for services to start
        $this->updateStatus('waiting');
        $this->statusLog->addInfo('Waiting for services to start up');
        sleep(5);

        // set up application
        $this->updateStatus('setting_up');
        $this->statusLog->addInfo('Setting up application');
        foreach ($this->_services as $serviceId => $serviceInstance) {
        	if (!$serviceInstance->service->afterCreate($serviceInstance)) {
        		return false;
        	}
        }

        // verify application
        $this->updateStatus('verifying');
        $this->statusLog->addInfo('Verifying install');

        $this->updateStatus('ready');
        return true;
    }

    public function terminate()
    {
    	$status = $this->applicationStatus;
        $this->statusLog->addInfo('Terminating');
    	// if ($status && $status !== 'stopped') {
     //    	$this->statusLog->addError('Could not terminate due to application status ('. $status .')');
    	// 	return false;
    	// }
        $this->updateStatus('terminating');
        foreach ($this->_services as $id => $serviceInstance) {
        	if (!$serviceInstance->terminate()) {
        		$this->statusLog->addError('Could not terminate service \''. $id .'\'');
        		return false;
        	}
        }
        $this->model->terminated = date('Y-m-d H:i:s');
        $this->model->active = 0;
        $this->updateStatus('terminated');
        return $this->model->save();
    }

    public function getApplicationStatus()
    {
    	if (!($this->status === 'ready' || $this->status === 'failed')) {
    		return false;
    	}
    	if (empty($this->_services)) {
    		return false;
    	}
    	$running = [];
    	$stopped = [];
    	foreach ($this->_services as $id => $serviceInstance) {
        	if (!$serviceInstance->service->daemon) {
        		continue;
        	}
    		if ($serviceInstance->isRunning()) {
    			$running[] = $id;
    		} else {
    			$stopped[] = $id;
    		}
    	}
    	if (empty($stopped)) {
    		return 'running';
    	} elseif (!empty($stopped) && !empty($running)) {
    		return 'partially_running';
    	} else {
    		return 'stopped';
    	}
    }

    public function clearAttribute($k)
    {
    	unset($this->_attributes[$k]);
    }
    public function getAttributes()
    {
    	return $this->_attributes;
    }

    public function getPrefix()
    {
    	if (!isset($this->_prefix)) {
    		$this->_prefix = 'app-' . substr(md5(microtime(true)), 0, 6);
    	}
    	return $this->_prefix;
    }

    public function getApplicationId()
    {
    	if (!$this->model) {
    		return false;
    	}
    	return $this->model->application_id;
    }

    public function getApplication()
    {
    	if (!isset($this->_cache['application'])) {
    		$this->_cache['application'] = Yii::$app->collectors['applications']->getByPk($this->applicationId);
    	}
    	return $this->_cache['application'];
    }

    public function restart()
    {
    	if ($this->stop()) {
    		if ($this->start()) {
    			return true;
    		}
    	}
    	return false;
    }

    public function start()
    {
    	$self = $this;
    	$starting = [];
    	$started = [];
        $this->statusLog->addInfo('Starting services');
        $startService = function ($serviceId) use ($self, $started, $starting, &$startService) {
        	if (in_array($serviceId, $started)) { return true; }
        	if (in_array($serviceId, $starting)) { return false; }
        	$starting[] = $serviceId;
        	if (!($serviceInstance = $self->getServiceInstance($serviceId))) {
        		return false;
        	}
        	if (!$serviceInstance->service->daemon) {
        		return true;
        	}
        	if (!empty($serviceInstance->service->links)) {
	        	foreach ($serviceInstance->service->links as $linkedServiceId) {
        			if (!$startService($linkedServiceId)) {
        				return false;
        			}
	        	}
	        	sleep(5);
        	}
        	if ($serviceInstance->isStarted() || $serviceInstance->start()) {
        		$started[] = $serviceId;
        		$self->statusLog->addInfo('Service \''. $serviceId .'\' has been started');
        		return true;
        	}
        	$self->statusLog->addError('Unable to start service  \''. $serviceId .'\'');
        	return false;
        };


        foreach ($this->_services as $serviceId => $serviceInstance) {
        	if (!$startService($serviceId)) {
        		return false;
        	}
        }
        return true;
    }

    public function stop()
    {
    	$self = $this;
    	$stopping = [];
    	$stopped = [];
        $this->statusLog->addInfo('Stopping services');
        $stopService = function ($serviceId) use ($self, $stopped, $stopping, &$stopService) {
        	if (in_array($serviceId, $stopped)) { return true; }
        	if (in_array($serviceId, $stopping)) { return false; }
        	$stopping[] = $serviceId;
        	if (!($serviceInstance = $self->getServiceInstance($serviceId))) {
        		return false;
        	}
        	if (!empty($serviceInstance->service->links)) {
	        	foreach ($serviceInstance->service->links as $linkedServiceId) {
        			if (!$stopService($linkedServiceId)) {
        				return false;
        			}
	        	}
	        	sleep(5);
        	}
        	if (!$serviceInstance->isStarted() || $serviceInstance->stop()) {
        		$stopped[] = $serviceId;
        		$self->statusLog->addInfo('Service \''. $serviceId .'\' has been stopped');
        		return true;
        	}
        	$self->statusLog->addInfo('Unable to stop service  \''. $serviceId .'\'');
        	return false;
        };


        foreach ($this->_services as $serviceId => $serviceInstance) {
        	if (!$stopService($serviceId)) {
        		return false;
        	}
        }
        return true;
    }

    public function setAttributes($attributes)
    {
    	$oldHostname = $newHostname = false;
    	if (isset($attributes['hostname']) && isset($this->_attributes['hostname']) && $this->_attributes['hostname'] !== $attributes['hostname']) {
			$newHostname = $attributes['hostname'];
			$oldHostname = $this->_attributes['hostname'];
    	}
    	$this->_attributes = $attributes;
    	if ($oldHostname) {
    		$this->trigger(static::EVENT_HOSTNAME_CHANGE, $oldHostname, $newHostname);
    	}
    }

    public function actions()
    {
    	$self = $this;
    	$actions = [];
    	$actions['terminate'] = [
    		'options' => [
				'icon' => 'fa fa-trash',
				'label' => 'Terminate'
			],
			'available' => function($self) {
				return in_array($self->realStatus, ['stopped', 'failed']);
			},
			'handler' => \canis\appFarm\components\applications\actions\Terminate::className()
		];
		$actions['stop'] = [
    		'options' => [
				'icon' => 'fa fa-stop',
				'label' => 'Stop'
			],
			'available' => function($self) {
				return in_array($self->realStatus , ['running']);
			},
			'handler' => \canis\appFarm\components\applications\actions\Stop::className()
		];
		$actions['restart'] = [
    		'options' => [
				'icon' => 'fa fa-refresh',
				'label' => 'Restart'
			],
			'available' => function($self) {
				return in_array($self->realStatus , ['running']);
			},
			'handler' => \canis\appFarm\components\applications\actions\Restart::className()
		];
		$actions['start'] = [
    		'options' => [
				'icon' => 'fa fa-play',
				'label' => 'Start'
			],
			'available' => function($self) {
				return in_array($self->realStatus , ['stopped']);
			},
			'handler' => \canis\appFarm\components\applications\actions\Start::className()
		];
		return $actions;
    }

    public function getWebActions()
    {
    	$actions = array_merge(static::actions(), $this->application->object->actions($this));
    	$defaultAction = [
    		'available' => function($self) {
    			return true;
    		}
    	];
    	$webActions = [];
    	$webActions['view_status_log'] = [
			'icon' => 'fa fa-exclamation-circle',
			'label' => 'View Status Log',
			'url' => Url::to(['/instance/view-status-log', 'id' => $this->model->id]),
			'background' => true
		];
		$deferredActions = $this->model->deferredActions;
		if (empty($deferredActions)) {
	    	foreach ($actions as $actionId => $action) {
	    		$action = array_merge($defaultAction, $action);
	    		if ($action['available']($this)) {
	    			$webActions[$actionId] = $action['options'];
	    		}
	    	}
	    }
    	return $webActions;
    }

	public function getPrimaryHostname()
	{
		if (empty($this->hostname)) {
			return null;
		}
		$hostnames = explode(',', $this->hostname);
		return $hostnames[0];
	}

	public function getHostname()
	{
		if (!isset($this->attributes['hostname'])) {
			return null;
		}
		return $this->attributes['hostname'];
	}

	public function setHostname($hostname)
	{
		$oldHostname = $this->hostname;
		$this->_attributes['hostname'] = $hostname;
		if (!empty($oldHostname)) {
			$this->trigger(static::EVENT_HOSTNAME_CHANGE, $oldHostname, $hostname);
		}
		return $this;
	}

	public function getRealStatus()
	{
		if ($this->status === 'ready' && ($appStatus = $this->applicationStatus)) {
			return $appStatus;
		} else {
			return $this->status;
		}
	}

	public function getPackage()
	{
		$p = [];
		$p['id'] = $this->model->id;
		$p['application_id'] = $this->model->application_id;
		$p['initialized'] = $this->model->initialized;
		$p['name'] = $this->model->name;
		$p['status'] = $this->realStatus;
		$p['initStatus'] = $this->status;
		$p['appStatus'] = $this->applicationStatus;
		$p['prefix'] = $this->prefix;
		$p['attributes'] = $this->attributes;
		$p['actions'] = $this->webActions;
		$p['services'] = false;
		if (!empty($this->_services)) {
			$p['services'] = [];
			foreach ($this->_services as $id => $serviceInstance) {
	        	if (!$serviceInstance->service->daemon) {
	        		continue;
	        	}
				$p['services'][$id] = $serviceInstance->getPackage();
			}
		}
		return $p;
	}

	public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
        return $this->save();
    }

    public function getStatusLog()
    {
        if (!isset($this->_statusLog)) {
        	$this->_statusLog = new Status;
        	$this->_statusLog->lastUpdate = microtime(true);
        	$this->saveCache()->save();
        } else {
        	$checkLog = Cacher::get([get_called_class(), $this->model->primaryKey, $this->model->created]);
        	if (!$checkLog) {	
		    	$checkLog = $this->_statusLog = new Status;
		    	$checkLog->lastUpdate = microtime(true);
		    	$this->saveCache()->save();
        	}
        	if ($checkLog->lastUpdate && $this->_statusLog->lastUpdate && $checkLog->lastUpdate > $this->_statusLog->lastUpdate) {
        		$this->_statusLog = $checkLog;
        	}
        }
        $this->_statusLog->log = $this;

        return $this->_statusLog;
    }

    /**
     * [[@doctodo method_description:saveCache]].
     */
    public function saveCache()
    {
    	if (!isset($this->_statusLog)) {
    		return $this;
    	}
        $this->_statusLog->lastUpdate = microtime(true);
        Cacher::set([get_called_class(), $this->model->primaryKey, $this->model->created], $this->_statusLog, 3600);
        return $this;
    }
    public function save()
    {
    	return $this->model->save();
    }


}
?>