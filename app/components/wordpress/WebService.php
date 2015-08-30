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
		// Primary WordPress Install
		$response = $serviceInstance->execCommand([
			"/bin/bash", "-c", "curl -sS https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/install_wordpress.sh | /bin/bash"
		]);
		$installWordPressResponse = $response->getBody()->__toString();
		$installWordPressResponse = preg_replace('/[^\x20-\x7E]/','', $installWordPressResponse);
		if (strpos($installWordPressResponse, '----WORDPRESS_INSTALL_SUCCESS----') === false) {
			$serviceInstance->applicationInstance->statusLog->addError('Installation of WordPress failed', ['data' => $installWordPressResponse]);
			return false;
		}  else {
			$serviceInstance->applicationInstance->statusLog->addInfo('Installation of WordPress succeeded', ['data' => $installWordPressResponse]);
		}

		// WordPress CLI Install
		$response = $serviceInstance->execCommand([
			"/bin/bash", "-c", "curl -sS https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/install_wordpress_cli.sh | /bin/bash"
		]);
		$installWordPressCLIResponse = $response->getBody()->__toString();
		$installWordPressCLIResponse = preg_replace('/[^\x20-\x7E]/','', $installWordPressResponse);
		if (strpos($installWordPressClientResponse, '----WORDPRESS_CLI_INSTALL_SUCCESS----') === false) {
			$serviceInstance->applicationInstance->statusLog->addError('Installation of WordPress CLI failed', ['data' => $installWordPressCLIResponse]);
			return false;
		}  else {
			$serviceInstance->applicationInstance->statusLog->addInfo('Installation of WordPress CLI succeeded', ['data' => $installWordPressCLIResponse]);
		}
		return true;
	}
}
?>