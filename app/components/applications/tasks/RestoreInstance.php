<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications\tasks;

use Yii;
use canis\appFarm\models\Backup as BackupModel;

class RestoreInstance extends \yii\base\Component
{
    public $startTime;
	public $applicationId;
	public $restoreTask;
    public $backupId;
	public $settings = [];
    protected $_restoreId;
    protected $_cache = [];

	public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["\0*\0_cache", "\0*\0_model", "restoreTask"];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }

        return $keys;
    }

    public function getBackup()
    {
        if (!isset($this->_cache['backup'])) {
            $this->_cache['backup'] = BackupModel::get($this->backupId);
        }
        return $this->_cache['backup'];
    }
    
    public function getApplication()
    {
    	if (!isset($this->_cache['application'])) {
    		$this->_cache['application'] = Yii::$app->collectors['applications']->getByPk($this->applicationId);
    	}
    	return $this->_cache['application'];
    }

    public function getRestoreId()
    {
        if (!isset($this->_restoreId)) {
            $this->_restoreId = md5(microtime(true));
        }
        return $this->_restoreId;
    }

    public function extractBackup($status)
    {
        $backupPath = $this->backup->file;
        if (!$backupPath || !file_exists($backupPath)) { 
            $status->addInfo('Backup file could not be retrieved', ['backupId' => $this->backupId]);
            return false; 
        }
        $tmp = Yii::$app->fileStorage->getTempFile(false, 'tar');
        $tmpGz = $tmp .'.gz';
        Yii::$app->fileStorage->registerTempFile($tmpGz);
        Yii::$app->fileStorage->registerTempFile($this->extractPath);
        copy($backupPath, $tmpGz);
        if (filesize($backupPath) !== filesize($tmpGz)) {
            $status->addInfo('Unable to copy backup file', ['backupId' => $this->backupId]);
            return false;
        }
        $p = new \PharData($tmpGz);
        $p->decompress();
        if (!file_exists($tmp)) {
            $status->addInfo('Unable to decompress backup file', ['backupId' => $this->backupId]);
            return false;
        }
        $phar = new \PharData($tmp);
        if (!$phar->extractTo($this->extractPath, null, true)) {
            return false;
        }
        return is_dir($this->extractPath);
    }

    public function run($status = null)
    {
        $this->startTime = microtime(true);
    	if (empty($status)) {
    		$status = new \canis\action\Status();
    	}
    	$status->addInfo('Initiating restore');
        if (!$this->extractBackup($status)) {
            $status->addInfo('Unable to extract backup', ['backupId' => $this->backupId]);
            return false;
        }
    	if ($this->restoreTask->run($this, $status)) {
    		$status->addInfo('Restore succeeded');

            if ($this->backup->dataObject->data['applicationInstance']['hostname'] !== $this->restoreTask->applicationInstance->primaryHostname) {
                $this->restoreTask->applicationInstance->triggerHostNameChange($this->backup->dataObject->data['applicationInstance']['hostname'], $this->restoreTask->applicationInstance->primaryHostname);
            }
            return true;
    	}
    	$status->addError('Restore failed');
    	return false;
    }

    public function getExtractPath()
    {
        $path = $this->restoreTask->applicationInstance->getRestoreTransferPath() . DIRECTORY_SEPARATOR . $this->restoreId;
        if (!is_dir($path)) {
            mkdir($path, 0755);
        }
        return $path;
    }

    public function generateRetrievalPoint($file)
    {
        return $this->extractPath . DIRECTORY_SEPARATOR . $file;
    }

    public static function setup($applicationInstance, $backup, $config = [])
    {
    	$obj = [];
        if (is_object($backup)) {
            $backup = $backup->id;
        }
    	$obj['class'] = static::className();
        $obj['backupId'] = $backup;
    	$obj['applicationId'] = $applicationInstance->application->applicationObject->primaryKey;
    	$obj['restoreTask'] = $applicationInstance->application->object->getRestoreTask($applicationInstance);
    	$obj['settings'] = $config;
    	return Yii::createObject($obj);
    }

}
?>