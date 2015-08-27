<?php
return [
    'class' => 'canis\appFarm\components\docker\Manager',
    'dsn' => DOCKER_HOST,
    'tlsVerify' => DOCKER_TLS_VERIFY,
    'certPath' => DOCKER_CERT_PATH,
    'caCertPath' => DOCKER_CA_CERT_PATH,
    'peerName' => DOCKER_PEER_NAME
];
