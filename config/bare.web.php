<?php
$parent = CANIS_APP_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'web.php';
$config = include $parent;
$config['id'] = CANIS_APP_ID;
$config['name'] = CANIS_APP_NAME;
return $config;