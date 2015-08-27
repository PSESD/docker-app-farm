<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace canis\appFarm\setup\tasks;

use canis\appFarm\models\Group;
use canis\appFarm\models\User;
use canis\base\exceptions\Exception;

/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AdminUser extends \canis\setup\tasks\BaseTask
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Admin User';
    }

    /**
     * @inheritdoc
     */
    public function test()
    {
        return User::find()->disableAccessCheck()->andWhere(['not', ['email' => User::SYSTEM_EMAIL]])->count() > 0;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $user = new User();
        $user->scenario = 'creation';
        $user->attributes = $this->input['admin'];
        $user->status = User::STATUS_ACTIVE;
        $superGroup = Group::find()->disableAccessCheck()->where(['system' => 'super_administrators'])->one();
        if (!$superGroup) {
            throw new Exception("Unable to find super_administrators group!");
        }
        $user->relationModels = [['parent_object_id' => $superGroup->primaryKey]];
        if ($user->save()) {
            return true;
        }
        foreach ($user->errors as $field => $errors) {
            $this->fieldErrors[$field] = implode('; ', $errors);
        }
        var_dump($this->fieldErrors);
        exit;

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getFields()
    {
        $fields = [];
        $fields['admin'] = ['label' => 'First Admin User', 'fields' => []];
        $fields['admin']['fields']['first_name'] = ['type' => 'text', 'label' => 'First Name', 'required' => true, 'value' => function () { return 'Super'; }];
        $fields['admin']['fields']['last_name'] = ['type' => 'text', 'label' => 'Last Name', 'required' => true, 'value' => function () { return 'Admin'; }];
        $fields['admin']['fields']['email'] = ['type' => 'text', 'label' => 'E-mail', 'required' => true, 'value' => function () { return 'admin@system.local'; }];
        $fields['admin']['fields']['password'] = ['type' => 'text', 'label' => 'Password', 'required' => true, 'value' => function () { return 'adminadmin'; }];

        return $fields;
    }
}
