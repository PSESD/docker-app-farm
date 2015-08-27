<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\models;

/**
 * Identity is the model class for table "identity".
 */
class Identity extends \canis\db\models\Identity
{
    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }
}
