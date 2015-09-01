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

class BackupInstance extends \yii\base\Component
{
	public $applicationId;
	public $backupTask;
	public $descriptor;
	public $time;
	public $meta = [];
	public $settings = [];
    public $startTime;
    protected $_model;
    protected $_data;
    protected $_cache = [];
    protected $_packageFileName;

	public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["\0*\0_cache", "\0*\0_model", "backupTask"];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }

        return $keys;
    }
    
    public function getApplication()
    {
    	if (!isset($this->_cache['application'])) {
    		$this->_cache['application'] = Yii::$app->collectors['applications']->getByPk($this->applicationId);
    	}
    	return $this->_cache['application'];
    }


    public function setModel($model)
    {
        $this->_model = $model;
    }

    public function getModel()
    {
    	return $this->_model;
    }

    public function setupModel($backupFile)
    {
    	if (!is_object($backupFile) || !($backupFile instanceof \canis\base\File)) {
    		throw new \Exception("Backup file result must be an instance of canis\base\File");
    	}
    	$model = $this->_model = new BackupModel;
    	$model->dataObject = $this;
    	$model->instance_id = $this->backupTask->applicationInstance->model->id;
    	$model->getBehavior('StorageLocal')->setStorage($backupFile);
    	return $model->save();
    }

    public function run($status = null)
    {
        $this->startTime = microtime(true);
    	if (empty($status)) {
    		$status = new \canis\action\Status();
    	}
    	$status->addInfo('Initiating backup');
    	if (($backupFile = $this->backupTask->run($this, $status))) {
    		if ($this->setupModel($backupFile)) {
    			$status->addInfo('Backup succeeded');
    		} else {
    			$status->addError('Backup failed (saving model)', ['errors' => $this->_model->errors, 'model' => $this->_model->attributes]);
    		}
    		return $this->model;
    	}
    	$status->addError('Backup failed');
    	return false;
    }

    public function getPackageInfo()
    {
        $applicationInstance = $this->backupTask->applicationInstance;
        $package = [];
        $package['descriptor'] = $applicationInstance->model->name . ' (' .$applicationInstance->application->object->name .') at ' . date("F d, Y g:i:sa");
        $package['timestamp'] = time();
        $package['settings'] = $this->settings;
        $package['backupProcessingTime'] = microtime(true) - $this->startTime;
        $package['farm'] = md5(Yii::$app->params['salt']);
        $package['application'] = [];
        $package['application']['id'] = $applicationInstance->application->applicationObject->primaryKey;
        $package['application']['systemId'] = $applicationInstance->application->systemId;
        $package['application']['version'] = $applicationInstance->application->object->version;

        $package['applicationInstance'] = [];
        $package['applicationInstance']['name'] = $applicationInstance->model->name;
        $package['applicationInstance']['prefix'] = $applicationInstance->prefix;
        $package['applicationInstance']['hostname'] = $applicationInstance->primaryHostname;
        $package['applicationInstance']['attributes'] = $applicationInstance->attributes;
        $package['applicationInstance']['services'] = [];
        foreach ($applicationInstance->serviceInstances as $id => $serviceInstance) {
            $package['applicationInstance']['services'][$id] = [
                'containerName' => $serviceInstance->containerName,
                'meta' => $serviceInstance->meta
            ];
        }
        $package['applicationInstance']['statusAtBackup'] = [
            'initialization' => $applicationInstance->status,
            'application' => $applicationInstance->applicationStatus
        ];
        $package['applicationInstance']['created'] = $applicationInstance->model->created;
        $package['applicationInstance']['modified'] = $applicationInstance->model->modified;
        return $package;
    }
    public function getData()
    {
        if (!isset($this->_data) && isset($this->backupTask)) {
            $this->_data = $this->getPackageInfo();
        }
        return $this->_data;
    }
    public function packageFiles($files)
    {
        $tmpInfo = Yii::$app->fileStorage->getTempFile();
        file_put_contents($tmpInfo, json_encode($this->data));
        $files[$tmpInfo] = 'package.json';
    	$tmp = Yii::$app->fileStorage->getTempFile(false, 'tar');
    	$tarGzFile = $tmp .'.gz';
    	Yii::$app->fileStorage->registerTempFile($tarGzFile);
    	$a = new \PharData($tmp);
    	foreach ($files as $filePath => $fileName) {
    		$a->addFile($filePath, $fileName);
    	}
    	$a->compress(\Phar::GZ);
    	if (file_exists($tarGzFile)) {
    		return \canis\base\File::createInstance($this->packageFileName, $tarGzFile, 'application/x-gzip', filesize($tarGzFile));
    	}
    	return false;
    }

    public function getPackageFileName()
    {
        if (!isset($this->_packageFileName)) {
            $this->_packageFileName = $this->backupTask->applicationInstance->prefix .'-backup_'. date("Y-m-d-H-i-s") .'.tar.gz';
        }
        return $this->_packageFileName;
    }

    public function generateRetrievalPoint($serviceId, $stub)
    {
        $serviceInstance = $this->backupTask->applicationInstance->getServiceInstance($serviceId);
        if (!$serviceInstance) {
            throw new \Exception('Backup task requested an invalid service: '. $serviceId);
        }
        return $serviceInstance->getTransferPath() . DIRECTORY_SEPARATOR . md5(microtime(true)) . '.' . $stub;
    }

    public static function setup($applicationInstance, $config = [])
    {
    	$obj = [];
    	$obj['class'] = static::className();
    	$obj['applicationId'] = $applicationInstance->application->applicationObject->primaryKey;
    	$obj['backupTask'] = $applicationInstance->application->object->getBackupTask($applicationInstance);
    	$obj['settings'] = $config;
    	return Yii::createObject($obj);
    }

}
?>