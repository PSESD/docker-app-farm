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
		$fields['title'] = [
			'label' => 'Site Title',
			'type' => 'text'
		];
		$fields['initialUsername'] = [
			'label' => 'Initial User Username',
			'type' => 'text'
		];
		$fields['initialPassword'] = [
			'label' => 'Initial User Password',
			'type' => 'text'
		];
		$fields['adminEmail'] = [
			'label' => 'Admin Email',
			'type' => 'text'
		];
		return $fields;
	}

	public function getRecipe()
	{
		$recipeClass = Recipe::className();
		return new $recipeClass;
	}

	public function actions($instance)
	{
		$actions = [];
		return $actions;
	}
}
?>