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
	public function getServiceName()
	{
		return 'Web';
	}

	public function getImage()
	{
		return 'jacobom/lemp:web';
	}
	
	public function getBaseEnvironment($instance)
	{
		return [
			'DB_NAME' => 'wordpress',
			'NGINX_ERROR_LOG_LEVEL' => 'notice',
			'VIRTUAL_HOST' => $instance->applicationInstance->attributes['hostname']
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
		$self = $this;
		$callback = function($command, $response) use (&$self, &$serviceInstance) {
			file_put_contents('/var/www/'.time().'.txt', $response);
		};
		$serviceInstance->execCommand([
			"/bin/bash", "-c", "curl -sS https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/install_wordpress.sh | /bin/bash"
		], $callback);
		return true;
	}
}
?>