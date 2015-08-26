<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\models;

use canis\db\behaviors\Relatable;

/**
 * Registry is the model class for table "registry".
 */
class Registry extends \canis\db\models\Registry
{
    /**
    * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'Relatable' => [
                'class' => Relatable::className(),
            ],
        ]);
    }
}