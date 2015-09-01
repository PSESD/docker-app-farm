<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\wordpress;

use Yii;

class Application extends \canis\appFarm\components\applications\Application
{	
	public function init()
	{
		parent::init();
		$this->on(static::EVENT_HOSTNAME_CHANGE, [$this, 'updateHostName']);
	}

	public function updateHostName($event)
	{
		if (!in_array($event->applicationInstance->status, ['ready', 'restoring', 'verifying'])) {
			$event->applicationInstance->statusLog->addError('Skipping hostname change because the application isn\'t ready', ['status' => $event->applicationInstance->status]);
			return true;
		}
		$webServiceInstance = $event->applicationInstance->getServiceInstance('web');
		if (!$webServiceInstance) {
			return false;
		}
		$event->applicationInstance->statusLog->addInfo('Changing hostname from \''.$event->oldHostname. '\' to \''.$event->newHostname. '\'');
		$commandTasks = [];

		$meta = $webServiceInstance->meta;
		if (empty($meta['adminUser'])) {
			$event->applicationInstance->statusLog->addError('Host did not have the adminUser meta field set!');
			return false;
		}
		$commandTasks['http_replace'] = [
			'description' => 'Installation of WordPress CLI',
			'cmd' => '/var/www/client/wp search-replace ' . WebService::generateParams(['user' => $meta['adminUser'], 'skip-columns' => 'guid', 'http://' . $event->oldHostname, 'http://' . $event->newHostname]),
			'test' => 'Success: Made'
		];
		$commandTasks['https_replace'] = [
			'description' => 'Installation of WordPress CLI',
			'cmd' => '/var/www/client/wp search-replace ' . WebService::generateParams(['user' => $meta['adminUser'], 'skip-columns' => 'guid', 'https://' . $event->oldHostname, 'https://' . $event->newHostname]),
			'test' => 'Success: Made'
		];
		foreach ($commandTasks as $id => $command) {
			if(!$webServiceInstance->runCommand($command)) {
				return false;
			}
		}

		$event->applicationInstance->statusLog->addInfo('Hostname was successfully changed!');
		return true;
	}

	public function getVersion()
	{
		return 1;
	}

	public function setupFields()
	{
		$fields = [];
		$fields['title'] = [
			'label' => 'Site Title',
			'type' => 'text',
			'hideRestore' => true,
			'required' => true
		];
		$fields['initialUsername'] = [
			'label' => 'Initial User Username',
			'type' => 'text',
			'hideRestore' => true,
			'required' => true
		];
		$fields['initialPassword'] = [
			'label' => 'Initial User Password',
			'type' => 'text',
			'hideRestore' => true,
			'required' => true
		];
		$fields['adminEmail'] = [
			'label' => 'Admin Email',
			'type' => 'text',
			'hideRestore' => true,
			'required' => true
		];
		return $fields;
	}

	public function getRecipe()
	{
		$recipeClass = Recipe::className();
		return new $recipeClass;
	}

	public function webActions($instance)
	{
		$actions = [];
		if ($instance->realStatus === 'running') {
			$actions['visitSite'] = [
				'options' => [
					'label' => 'Visit',
					'icon' => 'fa fa-home',
					'url' => 'http://' . $instance->primaryHostname,
					'attributes' => ['target' => '_blank']
				]
			];
			$actions['visitDashboard'] = [
				'options' => [
					'label' => 'Dashboard',
					'icon' => 'fa fa-tachometer',
					'url' => 'http://' . $instance->primaryHostname .'/wp-admin',
					'attributes' => ['target' => '_blank']
				]
			];
		}
		return $actions;
	}

	public function instanceActions($instance)
	{
		$actions = [];
		return $actions;
	}

    public function getBackupTaskClass()
    {
        return tasks\BackupTask::className();
    }

    public function getRestoreTaskClass()
    {
        return tasks\RestoreTask::className();
    }
}
?>