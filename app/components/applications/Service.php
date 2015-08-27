<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;

class Service extends \canis\base\Component
{
	public $recipe;
	abstract public function getImage();
	
	public function afterCreate($serviceInstance)
	{
		return true;
	}

	public function getBaseEnvironment()
	{
		return [];
	}

	public function getVolumes()
	{
		return null;
	}

	public function getPorts()
	{
		return null;
	}

	public function getExpose()
	{
		return null;
	}

	public function getLinks()
	{
		return null;
	}

	public function getPriviledged()
	{
		return null;
	}

	public function getRestart()
	{
		return null;
	}

	final public function getEnvironment()
	{
		$env = $this->getBaseEnvironment();
		return $env;
	}
}
?>