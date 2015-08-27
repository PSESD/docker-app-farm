<?php
$configDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config';
$hostEnvFile = $configDir . DIRECTORY_SEPARATOR . 'docker_host_env.php';
if (!file_exists($hostEnvFile)) {
	$hostIP = `netstat -nr | grep '^0\.0\.0\.0' | awk '{print $2}'`;
	$hostIP = trim($hostIP);
	$envFile = <<< END
<?php
defined('DOCKER_HOST')			|| define('DOCKER_HOST', 'tcp://{$hostIP}:2376');

END;
	file_put_contents($hostEnvFile, $envFile);
	echo "Generated docker host env file!\n";
}
