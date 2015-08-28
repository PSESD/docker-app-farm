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

    public function getService($id)
    {
    	if (isset($this->_services[$id])) {
    		return $this->_services[$id];
    	}
    	return false;
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
        		return false;
        	}
        }

        // create services
        $this->updateStatus('creating_services');
        $this->statusLog->addInfo('Creating services');


        // wait for services to start
        $this->updateStatus('waiting');
        $this->statusLog->addInfo('Waiting for services to start up');

        // set up application
        $this->updateStatus('setting_up');
        $this->statusLog->addInfo('Setting up application');

        // verify application
        $this->updateStatus('verifying');
        $this->statusLog->addInfo('Verifying install');


        return false;
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

    public function getActions()
    {
    	$actions = [];
		$actions['view_status_log'] = [
			'icon' => 'fa fa-exclamation-circle',
			'label' => 'View Status Log',
			'url' => Url::to(['/instance/view-status-log', 'id' => $this->model->id]),
			'background' => true
		];
    	if (in_array($this->status , ['stopped', 'failed'])) {
    		$actions['terminate'] = [
    			'icon' => 'fa fa-trash',
    			'label' => 'Terminate'
    		];
    	}
    	if (empty($this->model->initialized)) {
    		return $actions;
    	}
    	if ($this->status === 'running') {
    		$actions['stop'] = [
    			'icon' => 'fa fa-stop',
    			'label' => 'Stop'
    		];
    	} elseif ($this->status === 'stopped') {
    		$actions['start'] = [
    			'icon' => 'fa fa-play',
    			'label' => 'Start'
    		];
    	}
    	if ($this->application && $this->status === 'running') {
    		$actions = array_merge($actions, $this->application->getActions($this));
    	}
    	return $actions;
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

	public function getPackage()
	{
		$p = [];
		$p['id'] = $this->model->id;
		$p['application_id'] = $this->model->application_id;
		$p['initialized'] = $this->model->initialized;
		$p['name'] = $this->model->name;
		$p['status'] = $this->status;
		$p['prefix'] = $this->prefix;
		$p['attributes'] = $this->attributes;
		$p['actions'] = $this->actions;
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