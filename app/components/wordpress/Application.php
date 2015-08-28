<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\wordpress;

use Yii;

class Application extends \canis\appFarm\components\applications\Application
{	
	public function init()
	{
		parent::init();
	}

	public function setupFields()
	{
		$fields = [];
		return $fields;
	}

	public function getRecipe()
	{
		$recipeClass = Recipe::className();
		return new $recipeClass;
	}

	public function getActions($instance)
	{
		$actions = [];
		return $actions;
	}
}
?>