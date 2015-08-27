<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\base;

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
            'Registry' => 'canis\appFarm\models\Registry',
            'Relation' => 'canis\appFarm\models\Relation',

            'Aca' => 'canis\appFarm\models\Aca',
            'Acl' => 'canis\appFarm\models\Acl',
            'AclRole' => 'canis\appFarm\models\AclRole',
            'Role' => 'canis\appFarm\models\Role',

            'User' => 'canis\appFarm\models\User',
            'Group' => 'canis\appFarm\models\Group',
            'IdentityProvider' => 'canis\appFarm\models\IdentityProvider',
            'Identity' => 'canis\appFarm\models\Identity',

            'Storage' => 'canis\appFarm\models\Storage',
            'StorageEngine' => 'canis\appFarm\models\StorageEngine',

            'Audit' => 'canis\appFarm\models\Audit',

            'SearchTermResult' => 'canis\appFarm\components\db\behaviors\SearchTermResult',
        ];
    }
}
