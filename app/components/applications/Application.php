<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;
use canis\base\collector\CollectedObjectTrait;

abstract class Application extends \canis\base\Component implements \canis\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    public function getName()
    {
    	return $this->collectorItem->name;
    }

    public function getPackage()
    {
    	return [
    		'id' => $this->collectorItem->applicationObject->primaryKey,
    		'system_id' => $this->collectorItem->systemId,
    		'name' => $this->name
    	];
    }

    abstract public function getVersion();
    abstract public function setupFields();
    abstract public function getRecipe();
    abstract public function instanceActions($instance);
    abstract public function webActions($instance);

    final public function baseFields()
    {
    	$fields = [];
    	$fields['hostname'] = [
    		'label' => 'Hostname',
    		'help' => 'Seperate multiple hostnames by a comma; make primary hostname first',
    		'type' => 'text'
    	];
		return $fields;
    }

    public function getSetupFields()
    {
    	return array_merge(static::baseFields(), static::setupFields());
    }

    public function initialize()
    {

    }

    public function hasBackupTask()
    {
        return $this->backupTaskClass !== false;
    }

    public function hasRestoreTask()
    {
        return $this->restoreTaskClass !== false;
    }

    public function getBackupTaskClass()
    {
        return false;
    }

    public function getRestoreTaskClass()
    {
        return false;
    }

    public function getBackupTask($applicationInstance)
    {
        if (!$this->backupTaskClass) {
            return false;
        }
        return Yii::createObject(['class' => $this->backupTaskClass, 'applicationInstance' => $applicationInstance]);
    }

    public function getRestoreTask($applicationInstance)
    {
        if (!$this->restoreTaskClass) {
            return false;
        }
        return Yii::createObject(['class' => $this->restoreTaskClass, 'applicationInstance' => $applicationInstance]);
    }
}
?>