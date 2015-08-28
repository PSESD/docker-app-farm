<?php

namespace canis\appFarm\models;

use Yii;
use canis\caching\Cacher;
use canis\helpers\Date;
use canis\helpers\StringHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "instance".
 *
 * @property string $id
 * @property string $application_id
 * @property string $name
 * @property resource $data
 * @property integer $active
 * @property integer $initialized
 * @property string $checked
 * @property string $created
 * @property string $modified
 *
 * @property Application $application
 * @property Registry $id0
 */
class Instance extends \canis\db\ActiveRecordRegistry
{
    protected $_dataObject;

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'serializeData']);
    }

     /**
     * [[@doctodo method_description:serializeAction]].
     */
    public function serializeData()
    {
        if (isset($this->_dataObject)) {
            try {
                $this->data = serialize($this->_dataObject);
            } catch (\Exception $e) {
                \d($this->_dataObject);
                exit;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'instance';
    }
    
    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['application_id'], 'required'],
            [['data'], 'string'],
            [['active', 'initialized'], 'integer'],
            [['checked', 'created', 'modified'], 'safe'],
            [['id', 'application_id'], 'string', 'max' => 36],
            [['name'], 'string', 'max' => 255],
            [['application_id'], 'exist', 'skipOnError' => true, 'targetClass' => Application::className(), 'targetAttribute' => ['application_id' => 'id']],
            //[['id'], 'exist', 'skipOnError' => true, 'targetClass' => Registry::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'application_id' => 'Application ID',
            'name' => 'Name',
            'data' => 'Data',
            'active' => 'Active',
            'initialized' => 'Initialized',
            'checked' => 'Checked',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApplication()
    {
        return $this->hasOne(Application::className(), ['id' => 'application_id']);
    }

    /**
     * Set action object.
     *
     * @param [[@doctodo param_type:ao]] $ao [[@doctodo param_description:ao]]
     */
    public function setDataObject($do)
    {
        $this->_dataObject = $do;
    }

    /**
     * Get action object.
     *
     * @return [[@doctodo return_type:getActionObject]] [[@doctodo return_description:getActionObject]]
     */
    public function getDataObject()
    {
        if (!isset($this->_dataObject) && !empty($this->data)) {
            $this->_dataObject = unserialize($this->data);
            $this->_dataObject->model = $this;
        }

        return $this->_dataObject;
    }

    public function initialize()
    {
        if ($this->dataObject->status !== 'uninitialized') {
            return true;
        }
        $this->dataObject->statusLog->addInfo('Starting initialization process');
        $this->dataObject->updateStatus('starting');

        if ($this->dataObject->initialize() === true) {
            $this->initialized = 1;
        }
        return $this->save();
    }

    /**
     * Get data package.
     *
     * @return [[@doctodo return_type:getDataPackage]] [[@doctodo return_description:getDataPackage]]
     */
    public function getStatusLogPackage()
    {
        $p = [];
        $p['_'] = [];
        $p['_']['url'] = Url::to(['/instance/view-status-log', 'id' => $this->id, 'package' => 1]);
        $p['_']['id'] = $this->id;
        $p['_']['started'] = false;
        $p['_']['ended'] = false;
        $p['_']['duration'] = false;
        $p['_']['status'] = $this->dataObject->status;
        $p['_']['estimatedTimeRemaining'] = false;
        $p['_']['log_status'] = 'fine';

        $p['_']['menu'] = [];

        if ($this->dataObject->statusLog->hasError) {
            $p['_']['log_status'] = 'error';
        } elseif ($this->dataObject->statusLog->hasWarning) {
            $p['_']['log_status'] = 'warning';
        }
        $p['_']['last_update'] = false;
        $p['_']['peak_memory'] = false;
        $p['progress'] = false;
        $p['tasks'] = false;
        $p['messages'] = [];
        $lasttime = $started = $this->dataObject->statusLog->started;
        foreach ($this->dataObject->statusLog->messages as $key => $message) {
            $key = $key . '-' . substr(md5($key), 0, 5);
            $timestamp = (float) $message['time'];
            $duration = $timestamp - $lasttime;
            $lasttime = $timestamp;
            $fromStart = $timestamp-$started;
            $p['messages'][$key] = [
                'message' => $message['message'],
                'duration' => Date::shortDuration($duration),
                'fromStart' => Date::shortDuration($fromStart),
                'level' => $message['level'],
                'data' => $message['data'],
                'memory' => StringHelper::humanFilesize($message['memory']),
            ];
        }
        $p['output'] = false;
        return $p;
    }
}
