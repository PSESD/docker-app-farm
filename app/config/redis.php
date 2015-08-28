<?php
/**
 * ./app/config/environments/common/cache.php.
 *
 * @author Jacob Morrison <jmorrison@psesd.org>
 */

return [
    'class' => 'yii\redis\Connection',
    'hostname' => CANIS_APP_REDIS_HOST,
    'port' => CANIS_APP_REDIS_PORT,
    'database' => CANIS_APP_REDIS_DATABASE
];
