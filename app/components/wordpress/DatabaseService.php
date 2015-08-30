<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\wordpress;

use Yii;

class DatabaseService extends \canis\appFarm\components\applications\Service
{
	public function getServiceName()
	{
		return 'Database';
	}
	public function getImage()
	{
		return 'jacobom/lemp:mysql';
	}
	public function getBaseEnvironment($service)
	{
		return [
			'DB_NAME' => 'wordpress'
		];
	}
	public function getExpose()
	{
		return ['3360'];
	}
	public function getRestart()
	{
		return 'always';
	}
	public function getVolumesFrom()
	{
		return [
			'dbStorage'
		];
	}
}
?>