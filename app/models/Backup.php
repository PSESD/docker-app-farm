<?php

namespace canis\appFarm\models;

use Yii;

/**
 * This is the model class for table "backup".
 *
 * @property string $id
 * @property string $instance_id
 * @property string $local_storage_id
 * @property string $cloud_storage_id
 * @property resource $data
 * @property string $created
 *
 * @property Storage $localStorage
 * @property Storage $cloudStorage
 * @property Instance $instance
 * @property Registry $id0
 */
class Backup extends \canis\db\ActiveRecordRegistry
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
    public static function isAccessControlled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backup';
    }

    public function behaviors()
    {
        $storageBehaviors = [
            'StorageLocal' => [
                'class' => 'canis\storageHandlers\StorageBehavior',
                'storageAttribute' => 'local_storage_id',
                'required' => false
            ]
        ];

        if (!empty(Yii::$app->params['cloudStorageEngine'])) {
            $storageEngineClass = Yii::$app->classes['StorageEngine'];
            $cloudStorageEngine = $storageEngineClass::find()->setAction('read')->andWhere(['handler' => Yii::$app->params['cloudStorageEngine']])->one();
            $storageBehaviors['StorageCloud'] = [
                'class' => 'canis\storageHandlers\StorageBehavior',
                'storageAttribute' => 'cloud_storage_id',
                'storageEngine' => $cloudStorageEngine,
                'required' => false
            ];
        }
        return array_merge(parent::behaviors(), $storageBehaviors);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['instance_id'], 'required'],
            [['data'], 'string'],
            [['created'], 'safe'],
            [['id', 'instance_id'], 'string', 'max' => 36],
            //[['local_storage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Storage::className(), 'targetAttribute' => ['local_storage_id' => 'id']],
            //[['cloud_storage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Storage::className(), 'targetAttribute' => ['cloud_storage_id' => 'id']],
            [['instance_id'], 'exist', 'skipOnError' => true, 'targetClass' => Instance::className(), 'targetAttribute' => ['instance_id' => 'id']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => Registry::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'instance_id' => 'Instance ID',
            'local_storage_id' => 'Local Storage ID',
            'cloud_storage_id' => 'Cloud Storage ID',
            'data' => 'Data',
            'created' => 'Created',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocalStorage()
    {
        return $this->hasOne(Storage::className(), ['id' => 'local_storage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCloudStorage()
    {
        return $this->hasOne(Storage::className(), ['id' => 'cloud_storage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInstance()
    {
        return $this->hasOne(Instance::className(), ['id' => 'instance_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(Registry::className(), ['id' => 'id']);
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

    public function getFile()
    {
        if (!empty($this->local_storage_id) && file_exists($this->getBehavior('StorageLocal')->getStoragePath())) {
            return $this->getBehavior('StorageLocal')->getStoragePath();
        }
        if (!empty($this->cloud_storage_id) && file_exists($this->getBehavior('StorageCloud')->getStoragePath())) {
            return $this->getBehavior('StorageCloud')->getStoragePath();
        }
        return false;
    }

    public function serveBackup()
    {
        if (!empty($this->local_storage_id)) {
            return $this->getBehavior('StorageLocal')->serve();
        }
        if (!empty($this->cloud_storage_id)) {
            return $this->getBehavior('StorageCloud')->serve();
        }
        throw new \yii\web\NotFoundHttpException("Backup could not be found!");
    }
}
