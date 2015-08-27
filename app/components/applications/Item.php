<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace canis\appFarm\components\applications;

use canis\appFarm\models\Application;
use canis\base\exceptions\Exception;

/**
 * Item [[@doctodo class_description:cascade\components\dataInterface\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \canis\base\collector\Item
{
    /**
     * @var [[@doctodo var_type:error]] [[@doctodo var_description:error]]
     */
    public $error;

    /**
     * @var [[@doctodo var_type:_name]] [[@doctodo var_description:_name]]
     */
    private $_name;
    /**
     * @var [[@doctodo var_type:_module]] [[@doctodo var_description:_module]]
     */
    private $_module;
    /**
     * @var [[@doctodo var_type:_checked]] [[@doctodo var_description:_checked]]
     */
    private $_checked;
    /**
     * @var [[@doctodo var_type:_applicationObject]] [[@doctodo var_description:_applicationObject]]
     */
    protected $_applicationObject;

    public function getName()
    {
        return $this->_name;
    }

    public function setName($val)
    {
        $this->_name = $val;
        return $this;
    }

    public function getApplicationObject()
    {
        if (is_null($this->_applicationObject)) {
            $this->_applicationObject = Application::find()->where(['system_id' => $this->systemId])->one();
            if (empty($this->_applicationObject)) {
                $this->_applicationObject = new Application();
                $this->_applicationObject->name = $this->name;
                $this->_applicationObject->system_id = $this->systemId;
                if (!$this->_applicationObject->save()) {
                    var_dump($this->_applicationObject->errors);
                    throw new Exception("Unable to save interface object!");
                }
            }
        }

        return $this->_applicationObject;
    }
}
