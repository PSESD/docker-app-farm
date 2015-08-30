<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\docker;

use Yii;
use Docker\Http\DockerClient;
use Docker\Docker;

class Manager extends \canis\base\Component
{
	public $dsn;
	public $tlsVerify = false;
	public $certPath = false;
	public $caCertPath = false;
	public $peerName = false;

	public $isConnected = false;
	protected $_client;
	protected $_docker;

	public function init()
	{
		parent::init();
		$context = null;
		if ($this->tlsVerify) {
			if (!$this->certPath || !file_exists($this->certPath)) {
				$this->tlsVerify = false;
			} else {
		        $peername = $this->peerName ? $this->peerName : 'boot2docker';
		        $context = stream_context_create([
	                'ssl' => [
	                    'cafile' => $this->caCertPath,
	                    'local_cert' => $this->certPath,
	                    'peer_name' => $peername,
	                ],
	            ]);
			}
		}
		try {
			$this->_client = new DockerClient([], $this->dsn, $context, $this->tlsVerify);
			$this->_docker = new Docker($this->client);
			$this->docker->getVersion();
			$this->isConnected = true;
		} catch (\Exception $e) {
			$this->isConnected = false;
		}

	}

	public function getVersion()
	{
		if (!$this->isConnected) { return false; }
		return $this->docker->getVersion();
	}

	public function getMissingRequiredContainers()
	{
		static $missingContainers;
		if ($missingContainers === null) {
			$missingContainers = [];
			$required = ['proxy' => ['jwilder/nginx-proxy', 'codekitchen/dinghy-http-proxy']];
			$allContainers = $this->docker->getContainerManager()->findAll(['all' => 1]);
			$hasStorageContainer = false;
			foreach ($allContainers as $container) {
				if ($container->getName() === DOCKER_TRANSFER_CONTAINER) {

				}
			}
			foreach ($required as $containerSetId => $containers) {
				if (!is_array($containers)) {
					$containers = [$containers];
				}
				$hasContainer = false;
				foreach ($allContainers as $container) {
					if ($container->getImage() && in_array($container->getImage()->getRepository(), $containers)) {
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

	public function hasRequiredContainers()
	{
		return empty($this->missingRequiredContainers);
	}

	public function getDocker()
	{
		return $this->_docker;
	}

	public function getClient()
	{
		return $this->_client;
	}
}
?>