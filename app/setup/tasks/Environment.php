<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace canis\appFarm\setup\tasks;

use canis\appFarm\models\Group;
use canis\appFarm\models\Relation;

use Docker\Http\DockerClient;
use Docker\Docker;
/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Environment extends \canis\setup\tasks\Environment
{
    public function getExtraInput(&$input)
    {
        $input['docker'] = [];
        $input['docker']['transfer'] = DOCKER_TRANSFER_CONTAINER;
    }
    public function getFields()
    {
        $transferContainerDefault = 'transfer';
        try {
            $context = null;
            if (DOCKER_TLS_VERIFY) {
                $peername = defined('DOCKER_PEER_NAME') ? DOCKER_PEER_NAME : 'boot2docker';
                $context = stream_context_create([
                    'ssl' => [
                        'cafile' => DOCKER_CA_CERT_PATH,
                        'local_cert' => DOCKER_CERT_PATH,
                        'peer_name' => $peername,
                    ],
                ]);
            }
            $client = new DockerClient([], DOCKER_HOST, $context, DOCKER_TLS_VERIFY);
            $docker = new Docker($client);
            $allContainers = $docker->getContainerManager()->findAll(['all' => 1]);
            foreach ($allContainers as $container) {
                if ($container->getImage() && in_array($container->getImage()->getRepository(), ['busybox'])) {
                    $data = $container->getData();
                    if (isset($data['Names'][0])) {
                        $transferContainerDefault = trim($data['Names'][0], '/');
                    }
                }
            }
        } catch (\Exception $e) {
            var_dump($e->__toString());
        }
        $fields = parent::getFields();
        $fields['docker'] = ['label' => 'Docker', 'fields' => []];
        $fields['docker']['fields']['transfer'] = ['type' => 'text', 'label' => 'Transfer Container', 'required' => true, 'value' => function () use ($transferContainerDefault) { return defined('DOCKER_TRANSFER_CONTAINER') && DOCKER_TRANSFER_CONTAINER ? DOCKER_TRANSFER_CONTAINER : $transferContainerDefault; }];

        return $fields;
    }
}
