<?php
defined('YII_DEBUG') 					|| define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL')				|| define('YII_TRACE_LEVEL', 3);
defined('YII_ENV')						|| define('YII_ENV', 'dev');

defined('CANIS_APP_ID')					|| define('CANIS_APP_ID', 'appFarm');
defined('CANIS_APP_NAME')				|| define('CANIS_APP_NAME', 'appFarm');
defined('CANIS_APP_NAMESPACE')			|| define('CANIS_APP_NAMESPACE', 'canis\appFarm');

defined('CANIS_APP_INSTANCE_ID')		|| define('CANIS_APP_INSTANCE_ID', '');
defined('CANIS_APP_INSTANCE_VERSION')	|| define('CANIS_APP_INSTANCE_VERSION', false);
defined('CANIS_APP_INSTALL_PATH')		|| define('CANIS_APP_INSTALL_PATH', dirname(__DIR__));
defined('CANIS_APP_VENDOR_PATH')			|| define('CANIS_APP_VENDOR_PATH', CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'vendor');
defined('CANIS_APP_PATH') 				|| define('CANIS_APP_PATH', CANIS_APP_VENDOR_PATH . DIRECTORY_SEPARATOR . 'canis' . DIRECTORY_SEPARATOR . 'docker-app-farm-lib' . DIRECTORY_SEPARATOR . 'src');
defined('CANIS_APP_CONFIG_PATH')			|| define('CANIS_APP_CONFIG_PATH', CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'config');

defined('CANIS_APP_WEB_HOST')			|| define('CANIS_APP_WEB_HOST', '');
defined('CANIS_APP_DATABASE_HOST')		|| define('CANIS_APP_DATABASE_HOST', '');
defined('CANIS_APP_DATABASE_PORT')		|| define('CANIS_APP_DATABASE_PORT', '');
defined('CANIS_APP_DATABASE_USERNAME')	|| define('CANIS_APP_DATABASE_USERNAME', '');
defined('CANIS_APP_DATABASE_PASSWORD')	|| define('CANIS_APP_DATABASE_PASSWORD', '');
defined('CANIS_APP_DATABASE_DBNAME')		|| define('CANIS_APP_DATABASE_DBNAME', 'appFarm');

defined('CANIS_APP_REDIS_HOST')			|| define('CANIS_APP_REDIS_HOST', '');
defined('CANIS_APP_REDIS_PORT')			|| define('CANIS_APP_REDIS_PORT', 6379);
defined('CANIS_APP_REDIS_DATABASE')		|| define('CANIS_APP_REDIS_DATABASE', 0);

defined('CANIS_APP_S3_ACCESS_KEY')		|| define('CANIS_APP_S3_ACCESS_KEY', '');
defined('CANIS_APP_S3_SECRET_KEY')		|| define('CANIS_APP_S3_SECRET_KEY', '');
defined('CANIS_APP_S3_BUCKET')			|| define('CANIS_APP_S3_BUCKET', '');
defined('CANIS_APP_S3_REGION')			|| define('CANIS_APP_S3_REGION', '');
defined('CANIS_APP_S3_ENCRYPT')			|| define('CANIS_APP_S3_ENCRYPT', false);
defined('CANIS_APP_S3_RRS')				|| define('CANIS_APP_S3_RRS', false);
defined('CANIS_APP_S3_SERVE_LOCALLY')	|| define('CANIS_APP_S3_SERVE_LOCALLY', false);

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'docker_host_env.php')) {
	require __DIR__ . DIRECTORY_SEPARATOR . 'docker_host_env.php';
}
defined('DOCKER_HOST')					|| define('DOCKER_HOST', 'http://192.168.59.103:2375/');
defined('DOCKER_PEER_NAME') 			|| define('DOCKER_PEER_NAME', 'boot2docker');
defined('DOCKER_SELF_CONTAINER')		|| define('DOCKER_SELF_CONTAINER', false);
defined('DOCKER_TRANSFER_CONTAINER')	|| define('DOCKER_TRANSFER_CONTAINER', false);
defined('DOCKER_PROXY_CONTAINER')		|| define('DOCKER_PROXY_CONTAINER', false);
defined('DOCKER_DB_CONTAINER')			|| define('DOCKER_DB_CONTAINER', false);
?>