<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\wordpress\tasks;

use Yii;
use canis\appFarm\components\wordpress\WebService;

class BackupTask extends \canis\appFarm\components\applications\tasks\CommandRetrieveBackupTask
{
	public function commandSettings($backupInstance)
	{
		$c = [];
		$databaseRetrievalPoint = $backupInstance->generateRetrievalPoint('web', 'database.sql');
		$c[] = [
			'description' => 'Backing up database',
			'retrieveFile' => $databaseRetrievalPoint,
			'fileName' => 'database.sql',
			'service' => 'web',
			'cmd' => '/var/www/client/wp db export ' . WebService::generateParams([$databaseRetrievalPoint]),
		];

		$fileRetrievalPoint = $backupInstance->generateRetrievalPoint('web', 'files.tar');
		$c[] = [
			'description' => 'Backing up files',
			'retrieveFile' => $fileRetrievalPoint,
			'fileName' => 'wp-content.tar',
			'service' => 'web',
			'cmd' => 'tar -c -f "'. $fileRetrievalPoint .'" -C /var/www/web wp-content'
		];


		$wpConfigRetrievalPoint = $backupInstance->generateRetrievalPoint('web', 'wp-config.php');
		$c[] = [
			'description' => 'Copying wp-config.php file',
			'retrieveFile' => $wpConfigRetrievalPoint,
			'fileName' => 'wp-config.php',
			'service' => 'web',
			'cmd' => 'cp /var/www/web/wp-config.php ' . $wpConfigRetrievalPoint,
		];
		return $c;
	}
}
?>