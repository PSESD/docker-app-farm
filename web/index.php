<?php
require(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
if (!class_exists('canis\base\ApplicationEngine')) {
	throw new \Exception('Canis core libraries have not been installed. Have you ran `composer install`?');
}
canis\base\ApplicationEngine::runWebApplication(dirname(__DIR__), ['setupExists' => file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'setup.php')]);