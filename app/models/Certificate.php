<?php

namespace canis\appFarm\models;

use Yii;

/**
 * This is the model class for table "certificate".
 *
 * @property string $id
 * @property string $crt_storage_id
 * @property string $key_storage_id
 * @property string $name
 * @property integer $is_wildcard
 * @property string $expires
 * @property string $created
 * @property string $modified
 *
 * @property Storage $crtStorage
 * @property Storage $keyStorage
 * @property Registry $id0
 */
class Certificate extends \canis\db\ActiveRecordRegistry
{

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
        return 'certificate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['expires'], 'required'],
            [['is_wildcard'], 'integer'],
            [['expires', 'created', 'modified'], 'safe'],
            [['id', 'crt_storage_id', 'key_storage_id'], 'string', 'max' => 36],
            [['name'], 'string', 'max' => 255],
            [['crt_storage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Storage::className(), 'targetAttribute' => ['crt_storage_id' => 'id']],
            [['key_storage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Storage::className(), 'targetAttribute' => ['key_storage_id' => 'id']],
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
            'crt_storage_id' => 'Crt Storage ID',
            'key_storage_id' => 'Key Storage ID',
            'name' => 'Name',
            'is_wildcard' => 'Is Wildcard',
            'expires' => 'Expires',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrtStorage()
    {
        return $this->hasOne(Storage::className(), ['id' => 'crt_storage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeyStorage()
    {
        return $this->hasOne(Storage::className(), ['id' => 'key_storage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(Registry::className(), ['id' => 'id']);
    }
}
