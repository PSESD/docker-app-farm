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
use canis\appFarm\models\Backup as BackupModel;
use canis\caching\Cacher;

class ApplicationInstance extends \canis\base\Component
{
	const EVENT_HOSTNAME_CHANGE = '_hostnameChange';
	public $model;
    public $restoreBackupId = false;
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

    public function getServiceInstances()
    {
        return $this->_services;
    }

    public function isBackupRestore()
    {
        if ($this->canRestore() && !empty($this->restoreBackupId)) {
            $backup = BackupModel::get($this->restoreBackupId);
            if ($backup) {       
                return true;
            }
        }
        return false;
    }
    public function handleAction($actionId, $config = false)
    {
    	$actions = array_merge(static::instanceActions(), $this->application->object->instanceActions($this));
    	if (!isset($actions[$actionId])) {
    		Yii::$app->response->error = 'Unable to find action \''. $actionId .'\'';
    		return true;
    	}
        if (empty($config)) {
            $config = [];
        }
    	$action = $actions[$actionId];
    	$actionHandler = Yii::$app->response->params['actionHandler'] = $action['handler'];
        $setupFields = Yii::$app->response->params['setupFields'] = $actionHandler::setupFields();
        if (isset($_POST['config']) && is_string($_POST['config'])) {
            $config = unserialize($_POST['config']);
        }
        if (empty($config)) {
            $config = [];
        }
        $actionHandler::handleInput($config);
        Yii::$app->response->params['config'] = $config;
    	if (empty($setupFields) || !empty($config)) {
    		Yii::$app->response->params['config']['instanceId'] = $this->model->id;
            if ($actionHandler::confirm() && empty($_POST['confirm'])) {
                Yii::$app->response->view = 'action_confirm';  
                Yii::$app->response->taskOptions = ['title' => 'Confirm', 'isConfirmation' => true];
                Yii::$app->response->task = 'dialog';
                return true;
            }
    		try {
	    		if (!($deferredAction = $actionHandler::setup(Yii::$app->response->params['config']))) {
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
			Yii::$app->response->view = 'action_setup';
			return false;
		}
    }

    public function initialize()
    {
        if ($this->status !== 'starting') {
            return true;
        }
        $backup = false;
        if ($this->restoreBackupId) {
            if (!$this->canRestore()) {       
                $this->statusLog->addError('No valid restore task for \''. $this->applicationId .'\'');
                return false;
            }
            $backup = BackupModel::get($this->restoreBackupId);
            if (!$backup) {       
                $this->statusLog->addError('Could not load the backup ('.$this->restoreBackupId.') while restoring \''. $this->applicationId .'\'');
                return false;
            }
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
            $this->_services[$id]->backupRestorePrep($backup);
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

        if ($backup) {
            $this->updateStatus('pre_restore');
            $this->restore($backup);
        }

        // verify application
        $this->updateStatus('verifying');
        $this->statusLog->addInfo('Verifying install');

        $this->updateStatus('ready');
        return true;
    }

    public function terminate()
    {
        $self = $this;
        $tries = 5;
        $allTerminated = false;
        if ($this->canBackup()) {
            $this->statusLog->addInfo('Backing up instance before termination');
            if (!$this->backup($this->statusLog, ['reason' => 'Terminating'])) {
                $this->statusLog->addError('Termination failed because the backup failed');
                return false;
            }
        }
        $this->statusLog->addInfo('Terminating all services');
        $this->updateStatus('terminating');
        while (!$allTerminated) {
            $tries--;
            $allTerminated = true;
            foreach ($this->_services as $serviceId => $serviceInstance) {
                if (!$serviceInstance->containerExists()) {
                    continue;
                }
                if (!$serviceInstance->terminate(true)) {
                    $allTerminated = false;
                }
            }
        }
        if (!$allTerminated) {
            $allTerminated = true;
            foreach ($this->_services as $serviceId => $serviceInstance) {
                if (!$serviceInstance->containerExists()) {
                    continue;
                }
                if (!$serviceInstance->terminate()) {
                    $allTerminated = false;
                }
            }
        }
        if (!$allTerminated) {
            $this->updateStatus('failed');
        } else {
            $this->model->terminated = gmdate('Y-m-d H:i:s');
            $this->model->active = 0;
            $this->updateStatus('terminated');
            return $this->model->save();
        }
        return $allTerminated;
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
        	if (!empty($serviceInstance->service->dependencies)) {
	        	foreach ($serviceInstance->service->dependencies as $dependentServiceId) {
        			if (!$startService($dependentServiceId)) {
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
        	if (!empty($serviceInstance->service->dependencies)) {
                foreach ($serviceInstance->service->dependencies as $dependentServiceId) {
                    if (!$stopService($dependentServiceId)) {
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
            //$event = 
    		//$this->trigger(static::EVENT_HOSTNAME_CHANGE, $oldHostname, $newHostname);
    	}
    }

    public function webActions()
    {
        return [];
    }

    public function instanceActions()
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

        $actions['backup'] = [
            'options' => [
                'icon' => 'fa fa-cloud-download',
                'label' => 'Backup'
            ],
            'available' => function($self) {
                return $self->canBackup();
            },
            'handler' => \canis\appFarm\components\applications\actions\Backup::className()
        ];


        $actions['restore'] = [
            'options' => [
                'icon' => 'fa fa-cloud-upload',
                'label' => 'Restore'
            ],
            'available' => function($self) {
                return $self->canRestore();
            },
            'handler' => \canis\appFarm\components\applications\actions\Restore::className()
        ];
		return $actions;
    }

    public function getInstanceActions()
    {
    	$actions = array_merge(static::instanceActions(), $this->application->object->instanceActions($this));
    	$defaultAction = [
    		'available' => function($self) {
    			return true;
    		}
    	];
    	$instanceActions = [];
    	$instanceActions['view_status_log'] = [
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
	    			$instanceActions[$actionId] = $action['options'];
	    		}
	    	}
	    }
    	return $instanceActions;
    }

    public function getWebActions()
    {
        $actions = array_merge(static::webActions(), $this->application->object->webActions($this));
        $defaultAction = [
            'available' => function($self) {
                return true;
            }
        ];
        $webActions = [];
        foreach ($actions as $actionId => $action) {
            $action = array_merge($defaultAction, $action);
            if ($action['available']($this)) {
                $webActions[$actionId] = $action['options'];
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
        $p['instanceActions'] = $this->instanceActions;
        $p['webActions'] = $this->webActions;
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
        	$checkLog = Cacher::get(['ApplicationInstance__StatusLog', $this->model->primaryKey, $this->model->created]);
        	if (!$checkLog && !isset($this->_statusLog)) {	
		    	$checkLog = $this->_statusLog = new Status;
		    	$checkLog->lastUpdate = microtime(true);
		    	$this->saveCache()->save();
        	}
        	if ($checkLog && $checkLog->lastUpdate && $this->_statusLog->lastUpdate && $checkLog->lastUpdate > $this->_statusLog->lastUpdate) {
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
        Cacher::set(['ApplicationInstance__StatusLog', $this->model->primaryKey, $this->model->created], $this->_statusLog, 3600);
        return $this;
    }

    public function save()
    {
    	return $this->model->save();
    }

    public function canBackup()
    {
        return $this->status === 'ready' && $this->application->object->hasBackupTask();
    }

    public function canRestore()
    {
        return $this->application->object->hasRestoreTask();
    }

    public function getRestoreTransferPath()
    {
        $path = $this->getParentTransferPath() . DIRECTORY_SEPARATOR . 'restore';
        if (!is_dir($path)) {
            mkdir($path, 0755);
        }
        return $path;
    }

    public function getParentTransferPath()
    {
        return '/var/transfer';
    }

    public function restore($backup, $status = null, $config = [])
    {
        if ($status === null) {
            $status = $this->statusLog;
        }
        $stopAfter = false;
        if (!$this->canRestore()) {
            $status->addError("Unable to restore back up: no restore task available");
            return false;
        }
        if ($this->applicationStatus !== 'running') {
            sleep(5);
            $tries = 3;
            $stopAfter = $this->applicationStatus === 'stopped';
            while ($tries > 0 && $this->applicationStatus !== 'running') {
                $tries--;
                $this->start();
                sleep(5);
            }
            if ($this->applicationStatus !== 'running') {
                $status->addError("Unable to restore back up: couldn't restart services");
                return false;
            }
        }
        $restoreInstance = tasks\RestoreInstance::setup($this, $backup, $config);
        $restoreInstanceResult = $restoreInstance->run($status);
        if ($stopAfter) {
            $this->stop();
        }
        return $restoreInstanceResult;
    }

    public function backup($status = null, $config = [])
    {
        if ($status === null) {
            $status = $this->statusLog;
        }
        $stopAfter = false;
        if (!$this->canBackup()) {
            $status->addError("Unable to back up: no backup task available");
            return false;
        }
        if ($this->applicationStatus !== 'running') {
            sleep(5);
            $tries = 3;
            $stopAfter = $this->applicationStatus === 'stopped';
            while ($tries > 0 && $this->applicationStatus !== 'running') {
                $tries--;
                $this->start();
                sleep(5);
            }
            if ($this->applicationStatus !== 'running') {
                $status->addError("Unable to back up: couldn't restart services");
                return false;
            }
        }
        $backupInstance = tasks\BackupInstance::setup($this, $config);
        $backupInstanceResult = $backupInstance->run($status);
        if ($stopAfter) {
            $this->stop();
        }
        return $backupInstanceResult;
    }
}
?>