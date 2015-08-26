<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\models;

use canis\db\ActiveRecordRegistryTrait;

/**
 * User is the model class for table "user".
 */
class User extends \canis\db\models\User
{
	use ActiveRecordRegistryTrait;

    const SYSTEM_EMAIL = 'system@system.local';

    /**
     * @inheritdoc
     */
    public $descriptorField = ['first_name', 'last_name'];

    /**
     * __method_systemUser_description__
     * @return __return_systemUser_type__ __return_systemUser_description__
     * @throws Exception                  __exception_Exception_description__
     */
    public static function systemUser()
    {
        $user = self::findOne([self::tableName().'.'.'email' => self::SYSTEM_EMAIL], false);
        if (empty($user)) {
            $superGroup = Group::find()->disableAccessCheck()->where(['system' => 'super_administrators'])->one();
            if (!$superGroup) {
                return false;
            }
            $userClass = self::className();
            $user = new $userClass();
            $user->scenario = 'creation';
            $user->first_name = 'System';
            $user->last_name = 'User';
            $user->email = self::SYSTEM_EMAIL;
            $user->status = static::STATUS_INACTIVE;
            $user->password =  Yii::$app->security->generateRandomKey();
            $user->relationModels = [['parent_object_id' => $superGroup->primaryKey]];
            if (!$user->save()) {
                \d($user->email);
                \d($user->errors);
                throw new Exception("Unable to save system user!");
            }
        }

        return $user;
    }
}
