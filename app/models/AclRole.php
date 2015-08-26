<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\models;

/**
 * AclRole is the model class for table "acl_role".
 */
class AclRole extends \canis\db\models\AclRole
{
    public static $queryClass = 'canis\wdf\models\AclRoleQuery';
}