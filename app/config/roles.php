<?php
return [
    'class' => 'canis\security\role\Collector',
    'initialItems' => [
        'owner' => [
            'name' => 'Owner',
            'systemId' => 'owner',
            'exclusive' => true,
            'inheritedEditable' => false,
            'level' => CANIS_ROLE_LEVEL_OWNER,
        ],
        'manager' => [
            'name' => 'Manager',
            'systemId' => 'manager',
            'level' => CANIS_ROLE_LEVEL_MANAGER,
        ],
        'viewer' => [
            'name' => 'Viewer',
            'systemId' => 'viewer',
            'level' => CANIS_ROLE_LEVEL_VIEWER,
        ],
    ],
];
