<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\components\base;

/**
 * ClassManager Class name helper for the application.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ClassManager extends \canis\base\ClassManager
{
    /**
     * @inheritdoc
     */
    public function baseClasses()
    {
        return [
            'Registry' => 'canis\wdf\models\Registry',
            'Relation' => 'canis\wdf\models\Relation',

            'Aca' => 'canis\wdf\models\Aca',
            'Acl' => 'canis\wdf\models\Acl',
            'AclRole' => 'canis\wdf\models\AclRole',
            'Role' => 'canis\wdf\models\Role',

            'User' => 'canis\wdf\models\User',
            'Group' => 'canis\wdf\models\Group',
            'IdentityProvider' => 'canis\wdf\models\IdentityProvider',
            'Identity' => 'canis\wdf\models\Identity',

            'Storage' => 'canis\wdf\models\Storage',
            'StorageEngine' => 'canis\wdf\models\StorageEngine',

            'Audit' => 'canis\wdf\models\Audit',

            'SearchTermResult' => 'canis\wdf\components\db\behaviors\SearchTermResult',
        ];
    }
}
