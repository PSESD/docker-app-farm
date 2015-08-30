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

	public function getLinks()
	{
		return [
			'db'
		];
	}

	public function getVolumesFrom()
	{
		return [
			'webStorage'
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
			if (is_numeric($k)) {
				$s .= ' ' . $v;
				continue;
			}
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
			'cmd' => '/var/www/client/wp core download',
			'test' => 'Success: WordPress downloaded.'
		];
		$commandTasks['wp_config'] = [
			'description' => 'Generate wp-config.php',
			'cmd' => '/var/www/client/wp core config',
			'test' => 'Success: Generated wp-config.php file.'
		];
		$commandTasks['wp_install'] = [
			'description' => 'Install WordPress',
			'cmd' => '/var/www/client/wp core install' . static::generateParams(['url' => $meta['url'], 'title' => $meta['title'], 'admin_user' => $meta['adminUser'], 'admin_password' => $meta['adminPassword'], 'admin_email' => $meta['adminEmail']]),
			'test' => 'Success: WordPress installed successfully.'
		];
		$commandTasks['uninstall_hello'] = [
			'description' => 'Uninstall Hello Plugin',
			'cmd' => '/var/www/client/wp plugin delete hello' . static::generateParams(['user' => $meta['adminUser']]),
			'test' => 'Success: Deleted'
		];
		$commandTasks['modify_admin_user'] = [
			'description' => 'Update Admin User',
			'cmd' => '/var/www/client/wp user update ' . static::generateParams([$meta['adminUser'], 'display_name' => 'Admin', 'first_name ' => 'Admin', 'user_email ' => 'admin@localhost.docker', 'user' => $meta['adminUser']]),
			'test' => 'Success: Updated user'
		];
		$commandTasks['create_initial_user'] = [
			'description' => 'Create Initial User',
			'cmd' => '/var/www/client/wp user create ' . static::generateParams([$serviceInstance->applicationInstance->attributes['initialUsername'], $serviceInstance->applicationInstance->attributes['adminEmail'], 'user_pass' => $serviceInstance->applicationInstance->attributes['initialPassword'], 'user' => $meta['adminUser'], 'role' => 'administrator']),
			'test' => 'Success: Created user',
			'obfuscate' => [$serviceInstance->applicationInstance->attributes['initialPassword']]
		];
		$commandTasks['remove_post_1'] = [
			'description' => 'Delete Demo Post',
			'cmd' => '/var/www/client/wp post delete 1' . static::generateParams(['user' => $meta['adminUser']]),
			'test' => 'Success: Trashed post'
		];
		$serviceInstance->applicationInstance->clearAttribute('initialUsername');
		$serviceInstance->applicationInstance->clearAttribute('initialPassword');
		// $serviceInstance->applicationInstance->clearAttribute('initialEmail');
		foreach ($this->getWordPressPlugins() as $id => $plugin) {
			$commandTasks['plugin_install_' . $id] = [
				'description' => 'Install Plugin: ' . $id,
				'cmd' => '/var/www/client/wp plugin install "' . $plugin .'" --activate '. static::generateParams(['user' => $meta['adminUser']]),
				'test' => 'Plugin installed successfully.'
			];
		}

		foreach ($commandTasks as $id => $command) {
			$obfuscate = [];
			if (!empty($command['obfuscate'])) {
				$obfuscate = $command['obfuscate'];
			}
			$response = $serviceInstance->execCommand($command['cmd'], false, $obfuscate);
			$responseBody = $response->getBody()->__toString();
			$responseTest = strpos($responseBody, $command['test']) === false;
			$responseBody = preg_replace('/[^\x20-\x7E]/','', $responseBody);

            foreach ($obfuscate as $o) {
                $responseBody = str_replace($o, str_repeat('*', strlen($o)), $responseBody);
            }
			if ($responseTest) {
				$serviceInstance->applicationInstance->statusLog->addError('Command: ' . $command['description'] . ' failed', ['data' => $responseBody]);
				return false;
			}  else {
				$serviceInstance->applicationInstance->statusLog->addInfo('Command: ' . $command['description'] . ' succeeded', ['data' => $responseBody]);
			}
		}


		return true;
	}
}
?>