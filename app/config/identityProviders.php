<?php
return [
    'class' => 'canis\security\identity\providers\Collector',
    'initialItems' => [],
    'handlers' => [
        'Ldap' => [
            'class' => 'canis\security\identity\providers\Ldap',
        ],
    ],
];
