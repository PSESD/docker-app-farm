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