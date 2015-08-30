<?php
defined('YII_DEBUG') 					|| define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL')				|| define('YII_TRACE_LEVEL', 3);
defined('YII_ENV')						|| define('YII_ENV', 'dev');

defined('CANIS_APP_ID')					|| define('CANIS_APP_ID', 'appFarm');
defined('CANIS_APP_NAME')				|| define('CANIS_APP_NAME', 'appFarm');
defined('CANIS_APP_NAMESPACE')			|| define('CANIS_APP_NAMESPACE', 'canis\appFarm');

defined('CANIS_APP_INSTANCE_VERSION')	|| define('CANIS_APP_INSTANCE_VERSION', false);
defined('CANIS_APP_INSTALL_PATH')		|| define('CANIS_APP_INSTALL_PATH', dirname(__DIR__));
defined('CANIS_APP_VENDOR_PATH')			|| define('CANIS_APP_VENDOR_PATH', CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'vendor');
defined('CANIS_APP_PATH') 				|| define('CANIS_APP_PATH', CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'app');
defined('CANIS_APP_CONFIG_PATH')			|| define('CANIS_APP_CONFIG_PATH', CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'config');

defined('CANIS_APP_DATABASE_HOST')		|| define('CANIS_APP_DATABASE_HOST', '');
defined('CANIS_APP_DATABASE_PORT')		|| define('CANIS_APP_DATABASE_PORT', '');
defined('CANIS_APP_DATABASE_USERNAME')	|| define('CANIS_APP_DATABASE_USERNAME', '');
defined('CANIS_APP_DATABASE_PASSWORD')	|| define('CANIS_APP_DATABASE_PASSWORD', '');
defined('CANIS_APP_DATABASE_DBNAME')		|| define('CANIS_APP_DATABASE_DBNAME', 'appFarm');

defined('CANIS_APP_REDIS_HOST')			|| define('CANIS_APP_REDIS_HOST', '');
defined('CANIS_APP_REDIS_PORT')		|| define('CANIS_APP_REDIS_PORT', 6379);
defined('CANIS_APP_REDIS_DATABASE')		|| define('CANIS_APP_REDIS_DATABASE', 0);


if (file_exists('docker_host_env.php')) {
	require 'docker_host_env.php';
}
defined('DOCKER_HOST')			|| define('DOCKER_HOST', 'tcp://172.17.42.1:2376');
defined('DOCKER_TLS_VERIFY') 	|| define('DOCKER_TLS_VERIFY', 1);
defined('DOCKER_CERT_PATH') 	|| define('DOCKER_CERT_PATH', '/var/www/certs/docker-cert.pem');
defined('DOCKER_CA_CERT_PATH') 	|| define('DOCKER_CA_CERT_PATH', '/var/www/certs/ca.pem');
defined('DOCKER_PEER_NAME') 	|| define('DOCKER_PEER_NAME', 'boot2docker');
?>