<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\setup;


/**
 * Setup Perform the web setup for the application.
 */
class Setup extends \canis\setup\Setup
{
	public function getSetupTaskConfig()
	{
		$tasks = [];
		$tasks[] = [
			'class' => \canis\setup\tasks\Environment::className()
		];
		$tasks[] = [
			'class' => \canis\setup\tasks\Database::className()
		];
		return $tasks;
	}
}
