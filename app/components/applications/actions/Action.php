<?php
namespace canis\appFarm\components\applications\actions;

use Yii;

class Action extends \canis\deferred\components\Action
{
    public function requiredConfigParams()
    {
        return array_merge(parent::requiredConfigParams(), ['instanceId']);
    }

    public static function setupFields()
    {
        return false;
    }
}
?>
