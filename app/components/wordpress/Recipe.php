<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\wordpress;

use Yii;

class Recipe extends \canis\appFarm\components\applications\Recipe
{
	public function getServiceConfig()
	{
		$services = [];
		$services['web'] = [];
		$services['web']['class'] = WebService::className();

		$services['db'] = [];
		$services['db']['class'] = DatabaseService::className();
		return $services;
	}
}
?>