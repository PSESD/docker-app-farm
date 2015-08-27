<?php
$lazyLoad = !(defined('IS_CONSOLE') && IS_CONSOLE);

return [
    'class' => 'canis\base\collector\Component',
    'cacheTime' => 120,
    'collectors' => [
        'roles' => include(CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'roles.php'),
        'identityProviders' => include(CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'identityProviders.php'),
        'storageHandlers' => [
            'class' => 'canis\storageHandlers\Collector',
            'initialItems' => [
                'local' => [
                    'object' => [
                        'class' => 'canis\storageHandlers\core\LocalHandler',
                        'bucketFormat' => '{year}.{month}',
                        'baseDir' => CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'storage',
                    ],
                    'publicEngine' => true,
                ],
            ],
        ],
        'applications' => [
            'class' => 'canis\appFarm\components\applications\Collector',
            'initialItems' => [
                'wordpress' => [
                    'name' => 'WordPress',
                    'object' => [
                        'class' => 'canis\appFarm\components\wordpress\Application'
                    ]
                ],
            ],
        ],
    ],
];
