<?php

namespace canis\appFarm\models;

use Yii;

/**
 * This is the model class for table "application".
 *
 * @property string $id
 * @property string $name
 * @property string $system_id
 * @property string $created
 * @property string $modified
 *
 * @property Registry $id0
 */
class Application extends \canis\db\ActiveRecordRegistry
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'application';
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
            [['system_id'], 'required'],
            [['created', 'modified'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['name', 'system_id'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'system_id' => 'System ID',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(Registry::className(), ['id' => 'id']);
    }
}
