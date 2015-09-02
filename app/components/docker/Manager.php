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

class Manager extends \canis\base\Component
{
	public $dsn;
	public $tlsVerify = false;
	public $certPath = false;
	public $caCertPath = false;
	public $peerName = false;

	public $isConnected = false;
	protected $_loop;
	protected $_client;
	protected $_factory;

	public function init()
	{
		parent::init();
		$self = $this;
		try {
			$this->_loop = \React\EventLoop\Factory::create();
			$this->_factory = new DockerFactory($this->_loop);
			$this->_client = $this->_factory->createClient($this->dsn);
			$version = $this->getVersion(function($self) {
				$self->isConnected = true;
			}, function($self) {
				$self->isConnected = false;
			}, 5);
		} catch (\Exception $e) {
			$this->isConnected = false;
			throw $e;
		}
		// if ($this->isConnected) {
		// 	\d($this->getContainerById('da7774f8468c615d1397a09e8d37de02aaa7b7f1c67d97404fa9f89697d60d1f'));exit;
		// }
	}

	public function waitReturn($promise, $onSuccess = null, $onFail = null, $timeout = null)
	{
		$self = $this;
		$response = false;
		if ($timeout === null) {
			$timeout = 10;
		}
		try {
			$promise->then(function($response) use (&$self, &$onSuccess) {
				if ($onSuccess !== null) {
					$onSuccess($self, $response);
				}
			},
			function (\Exception $exception) use (&$self, &$onFail) {
				if ($onFail !== null) {
					$onFail($self, $exception, false);
				}
			});
			if ($timeout) {
				$promise = \React\Promise\Timer\timeout($promise, $timeout, $this->loop);
			}
            $response = Block\await($promise, $this->loop);
        } catch (\Exception $e) {
            $response = false;
			if (!empty($onFail)) {
				$onFail($this, $e, $response);
			}
        }
        return $response;
	}

	public function waitReturnStream($stream, $promise, $onSuccess = null, $onFail = null, $timeout = null)
	{
		$response = false;
		if ($timeout === null) {
			$timeout = 10;
		}
		try {
			
			$promise->then(function($response) use (&$self, &$onSuccess) {
				if ($onSuccess !== null) {
					$onSuccess($self, $response);
				}
			},
			function (\Exception $exception) use (&$self, &$onFail) {
				if ($onFail !== null) {
					$onFail($self, $exception, false);
				}
			});
			if ($timeout) {
				$promise = \React\Promise\Timer\timeout($promise, $timeout, $this->loop);
			}
			$stream->on('data', function ($data) use(&$response) {
				if (!is_string($data)) { return; }
				if (!$response) {
					$response = '';
				}
				$response .= trim($data);
			});
            $result = Block\await($promise, $this->loop);
			$response = str_replace("\0", "", $response);
			$response = trim($response);
			if (empty($response)) {
				$response = true;
			}
        } catch (\Exception $e) {
            $response = false;
			if (!empty($onFail)) {
				$onFail($this, $e, $response);
			}
        }
        return $response;
	}

	public function getVersion($onSuccess, $onError, $timeout = 5)
	{
		return $this->waitReturn($this->client->version(), $onSuccess, $onError, $timeout);
	}

	public function getMissingRequiredContainers()
	{
		static $missingContainers;
		if ($missingContainers === null) {
			$missingContainers = [];
			$required = ['proxy' => ['jwilder/nginx-proxy', 'codekitchen/dinghy-http-proxy']];

            $allContainers = $this->waitReturn($this->client->containerList(true));

			$hasStorageContainer = false;
			foreach ($allContainers as $container) {
				if ($container['Names'][0] === DOCKER_TRANSFER_CONTAINER) {

				}
			}
			foreach ($required as $containerSetId => $containers) {
				if (!is_array($containers)) {
					$containers = [$containers];
				}
				$hasContainer = false;
				foreach ($allContainers as $container) {
					if ($container['Image'] && in_array($container['Image'], $containers)) {
						$hasContainer = true;
					}
				}
				if (!$hasContainer) {
					$missingContainers[] = $containerSetId;
					break;
				}
			}
		}
		return $missingContainers;
	}

	public function getContainers()
    {
    	$containersRaw = $this->waitReturn($this->client->containerList(true));
    	$containers = [];
    	foreach ($containersRaw as $container) {
			$container['class'] = Container::className();
			$container['manager'] = $this;
			$containers[$container['Id']] = Yii::createObject($container);
    	}
   		return $containers;
    }

	public function getContainerById($containerId)
    {
    	$containers = $this->containers;
    	if (isset($containers[$containerId])) {
    		return $containers[$containerId];
    	}
    	return false;
    }

	public function setupContainer($containerSettings)
    {
    	
		$containerSettings['class'] = Container::className();
		$containerSettings['manager'] = $this;
		$container = Yii::createObject($containerSettings);
		return $container;
    }

	public function hasRequiredContainers()
	{
		return empty($this->missingRequiredContainers);
	}

	public function getClient()
	{
		return $this->_client;
	}
	public function getLoop()
	{
		return $this->_loop;
	}
}
?>