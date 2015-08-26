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
 * Role is the model class for table "role".
 */
class Role extends \canis\db\models\Role
{
	use ActiveRecordRegistryTrait;

    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }
}
