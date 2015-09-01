<?php
$params = [];
$params['defaultStorageEngine'] = 'local';
$params['migrationAliases'] = [];
$params['migrationAliases'][] = '@canis/db/migrations';
$params['migrationAliases'][] = '@canis/appFarm/migrations';
return $params;