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

use Clue\React\Docker\Factory as DockerFactory;
use Clue\React\Docker\Client as DockerClient;
use Clue\React\Block;
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
            $loop = \React\EventLoop\Factory::create();
            $factory = new DockerFactory($loop);
            $client = $factory->createClient(DOCKER_HOST);
            $containerPromise = $client->containerList(true);
            try {
                $allContainers = Block\await($containerPromise, $loop);
            } catch (\Exception $e) {
            }

            foreach ($allContainers as $container) {
                if (in_array($container['Image'], ['busybox'])) {
                    if (isset($container['Names'][0]) && strpos($container['Names'][0], 'transfer')) {
                        $transferContainerDefault = trim($container['Names'][0], '/');
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
