<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace canis\appFarm\components\applications;

/**
 * Collector collector for the data interfaces.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \canis\base\collector\Module
{
    /**
     * @var [[@doctodo var_type:_initialItems]] [[@doctodo var_description:_initialItems]]
     */
    protected $_initialItems = [];

    /**
     * @inheritdoc
     */
    public function getCollectorItemClass()
    {
        return Item::className();
    }

    /**
     * @inheritdoc
     */
    public function getModulePrefix()
    {
        return 'Application';
    }

    /**
     * @inheritdoc
     */
    public function getInitialItems()
    {
        return $this->_initialItems;
    }

    /**
     * Set initial items.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setInitialItems($value)
    {
        $this->_initialItems = $value;
    }

    /**
     * Get by pk.
     *
     * @param mixed $pk the primary key
     *
     * @return Item data app item
     */
    public function getByPk($pk)
    {
        foreach ($this->getAll() as $app) {
            if ($app->applicationObject->primaryKey === $pk) {
                return $app;
            }
        }

        return false;
    }

    public function getListByPk()
    {
        $list = [];
        foreach ($this->getAll() as $item) {
            $list[$item->applicationObject->primaryKey] = $item->name;
        }
        return $list;
    }

    public function getPackage()
    {
        $list = [];
        foreach ($this->getAll() as $item) {
            $list[$item->applicationObject->primaryKey] = $item->object->package;
        }
        return $list;
    }
}
