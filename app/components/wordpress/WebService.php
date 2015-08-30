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

	public function generateMeta($serviceInstance)
	{
		$meta = [];
		$meta['url'] = 'http://' . $serviceInstance->applicationInstance->attributes['hostname'];
		$meta['title'] = $serviceInstance->applicationInstance->attributes['title'];
		$meta['adminUser'] = 'farm' . rand(100,999);
		$meta['adminPassword'] = substr(hash('sha512',rand()),0,12);
		$meta['adminEmail'] = $serviceInstance->applicationInstance->attributes['adminEmail'];
		return $meta;
	}

	public static function generateParams($p)
	{
		$s = '';
		foreach ($p as $k => $v) {
			$s .= ' --' . $k .'="'.$v.'"';
		}
		return $s;
	}

	public function getWordPressPlugins()
	{
		return [
			'jetpack' => 'jetpack'
		];
	}

	public function afterCreate($serviceInstance)
	{
		if (!parent::afterCreate($serviceInstance)) {
			return false;
		}
		$self = $this;
		$meta = $serviceInstance->meta;
		$commandTasks = [];
		$commandTasks['cli'] = [
			'description' => 'Installation of WordPress CLI',
			'cmd' => 'curl -sS https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/install_wordpress_cli.sh | /bin/bash',
			'test' => '----WORDPRESS_CLI_INSTALL_SUCCESS----'
		];
		$commandTasks['core_download'] = [
			'description' => 'Installation of WordPress Core',
			'cmd' => '/var/www/wp core download',
			'test' => 'Success: WordPress downloaded.'
		];
		$commandTasks['wp_config'] = [
			'description' => 'Generate wp-config.php',
			'cmd' => '/var/www/wp core config',
			'test' => 'Success: Generated wp-config.php file.'
		];
		$commandTasks['wp_install'] = [
			'description' => 'Install WordPress',
			'cmd' => '/var/www/wp core install' . static::generateParams(['url' => $meta['url'], 'title' => $meta['title'], 'admin_username' => $meta['adminUser'], 'admin_password' => $meta['adminPassword'], 'admin_email' => $meta['adminEmail']]),
			'test' => 'Success: WordPress installed successfully.'
		];
		foreach ($this->getWordPressPlugins() as $id => $plugin) {
			$commandTasks['plugin_install_' . $id] = [
				'description' => 'Install Plugin: ' . $id,
				'cmd' => '/var/www/wp plugin install "' . $plugin .'" --activate '. static::generateParams(['user' => $meta['adminUser']]),
				'test' => 'Plugin installed successfully.'
			];
		}

		foreach ($commandTasks as $id => $command) {
			$response = $serviceInstance->execCommand([
				"/bin/bash", "-c", $command['cmd']
			]);
			$responseBody = $response->getBody()->__toString();
			$responseBody = preg_replace('/[^\x20-\x7E]/','', $responseBody);
			if (strpos($responseBody, $command['test']) === false) {
				$serviceInstance->applicationInstance->statusLog->addError('Command:' . $command['description'] . ' failed', ['data' => $responseBody]);
				return false;
			}  else {
				$serviceInstance->applicationInstance->statusLog->addInfo('Command:' . $command['description'] . ' succeeded', ['data' => $responseBody]);
			}
		}


		return true;
	}
}
?>