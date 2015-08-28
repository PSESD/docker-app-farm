<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\wordpress;

use Yii;

class WebService extends \canis\appFarm\components\applications\Service
{
	public function getImage()
	{
		return 'jacobom/lemp:web';
	}
	
	public function getBaseEnvironment()
	{
		return [
			'DB_NAME' => 'wordpress',
			'NGINX_ERROR_LOG_LEVEL' => 'notice'
		];
	}
	
	public function getExpose()
	{
		return ['80'];
	}

	public function getPorts()
	{
		return ['80'];
	}

	public function getVolumes()
	{
		return [
			'/var/www'
		];
	}

	public function getLinks()
	{
		return [
			'db'
		];
	}

	public function afterCreate($serviceInstance)
	{
		if (!parent::afterCreate($serviceInstance)) {
			return false;
		}
		return true;
	}
}
?>