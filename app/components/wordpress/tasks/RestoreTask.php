<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\wordpress\tasks;

use Yii;
use yii\helpers\FileHelper;
use canis\appFarm\components\wordpress\WebService;

class RestoreTask extends \canis\appFarm\components\applications\tasks\CommandRestoreTask
{
	public function commandSettings($restoreInstance)
	{
		$c = [];
		$wpConfigRetrievalPoint = $restoreInstance->generateRetrievalPoint('wp-config.php');
		$c[] = [
			'description' => 'Restoring wp-config.php file',
			'service' => 'web',
			'cmd' => 'cp ' . $wpConfigRetrievalPoint .' /var/www/web/wp-config.php',
		];
		$databaseRetrievalPoint = $restoreInstance->generateRetrievalPoint('database.sql');
		$c[] = [
			'description' => 'Restore database',
			'service' => 'web',
			'cmd' => '/var/www/client/wp db import ' . WebService::generateParams([$databaseRetrievalPoint]),
		];

		$fileRetrievalPoint = $restoreInstance->generateRetrievalPoint('wp-content.tar');
		$c[] = [
			'description' => 'Restoring files',
			'service' => 'web',
			'cmd' => 'tar -x -f "'. $fileRetrievalPoint .'" -C /var/www/web'
		];
		return $c;
	}
}
?>