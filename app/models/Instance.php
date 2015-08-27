<?php

namespace canis\appFarm\models;

use Yii;

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
}
