<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;

abstract class Recipe extends \canis\base\Component
{
	public $application;

	protected $_services;

	abstract public function getServiceConfig();

	public function getServices()
	{
		if ($this->_services === null) {
			$this->_services = [];
			foreach ($this->serviceConfig as $id => $config) {
				$config['recipe'] = $this;
				$this->_services[$id] = Yii::createObject($config);
			}
		}
		return $this->_services;
	}
}
?>