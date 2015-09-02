<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\docker;

use Yii;
use Clue\React\Docker\Factory as DockerFactory;
use Clue\React\Docker\Client as DockerClient;
use Clue\React\Block;
use Clue\React\Docker\Io\StreamingParser;

class Container extends \canis\base\Component
{
	public $Id;
	public $Names;
	public $Image;
	public $Command;
	public $Created;
	public $Ports;
	public $Mounts;
	public $Labels;
	public $Status;
	public $Env;
	public $Volumes;
	public $ExposedPorts;
	public $HostConfig;

	public $manager;

	public function getId()
	{
		return $this->Id;
	}

	public function setName($name)
	{
		$this->Names = [$name];
	}

	public function getName()
	{
		if (!isset($this->Names[0])) {
			return null;
		}
		return $this->Names[0];
	}

	public function getSettings()
	{
		$s = [];
		$parts = ['Env', 'Image', 'Mounts', 'ExposedPorts', 'HostConfig'];
		foreach ($parts as $part) {
			$s[$part] = $this->{$part};
		}
		return $s;
	}

	public function create($onSuccess = null, $onError = null)
	{
		$promise = $this->manager->client->containerCreate($this->settings, $this->getName());
		$container = $this->manager->waitReturn($promise, $onSuccess, $onError);
		if (isset($container['Id'])) {
			$this->Id = $container['Id'];
			return true;
		}
		return false;
	}

	public function getIsRunning()
	{
		if ($this->getId() === null) {
			return false;
		}
		if (!($inspection = $this->inspect()) || empty($inspection['State']) || empty($inspection['State']['Running'])) {
			return false;
		}
		return true;
	}

	public function inspect($onSuccess = null, $onError = null)
	{
		if ($this->getId() === null) {
			return false;
		}
		$promise = $this->manager->client->containerInspect($this->getId());
		$response = $this->manager->waitReturn($promise, $onSuccess, $onError);
		if ($response) {
			return $response;
		}
		return false;
	}

	public function terminate($onSuccess = null, $onError = null)
	{
		if ($this->getId() === null) {
			return false;
		}
		$promise = $this->manager->client->containerRemove($this->getId(), true);
		$response = $this->manager->waitReturn($promise, $onSuccess, $onError);
		if ($response !== false) {
			return true;
		}
		return false;
	}

	public function start($onSuccess = null, $onError = null)
	{
		if ($this->getId() === null) {
			return false;
		}
		if ($this->isRunning) {
			return true;
		}
		$promise = $this->manager->client->containerStart($this->getId());
		$response = $this->manager->waitReturn($promise, $onSuccess, $onError);
		if ($response !== false) {
			return true;
		}
		return false;
	}

	public function stop($onSuccess = null, $onError = null, $timeout = 10)
	{
		if ($this->getId() === null) {
			return false;
		}
		if (!$this->isRunning) {
			return true;
		}
		$promise = $this->manager->client->containerStop($this->getId(), $timeout);
		$response = $this->manager->waitReturn($promise, $onSuccess, $onError);
		if ($response !== false) {
			return true;
		}
		return false;
	}

	public function executeCommand($command, $onSuccess = null, $onError = null, $timeout = null)
	{
		if ($this->getId() === null) {
			return false;
		}
		$createResponse = $this->executeCreate($command, null, $onError, $timeout);
		if (!$createResponse) {
			throw new \Exception("Failed at creation");
			return false;
		}
		return $this->executeStart($createResponse, $onSuccess, $onError, $timeout);
	}

	public function executeCreate($command, $onSuccess = null, $onError = null, $timeout = null, $config = [])
	{
		$defaultConfig = ['AttachStdin' => false, 'AttachStdout' => true, 'AttachStderr' => true, 'Tty' => false];
		$config = array_merge($defaultConfig, $config);
		$config['Cmd'] = $command;
		if (!is_array($config['Cmd'])) {
			$config['Cmd'] = [$config['Cmd']];
		}
		$promise = $this->manager->client->execCreate($this->getId(), $config);
		$response = $this->manager->waitReturn($promise, $onSuccess, $onError, $timeout);
		if ($response && !empty($response['Id'])) {
			return $response['Id'];
		}
		return false;
	}

	public function executeStart($exec, $onSuccess = null, $onError = null, $timeout = null)
	{
		$config = ['Detach' => false, 'Tty' => false];
		$streamingParser = new StreamingParser();
		$promise = $this->manager->client->execStart($exec, $config);
		$stream = $streamingParser->parsePlainStream($promise);
		$response = $this->manager->waitReturnStream($stream, $promise, $onSuccess, $onError, $timeout);
		if ($response !== false) {
			return $response;
		}
		return false;
	}
}