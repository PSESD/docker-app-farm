<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications\tasks;

use Yii;

abstract class CommandRetrieveBackupTask extends BackupTask
{
	public function run($backupInstance, $status) {
		$files = [];
		$commands = $this->commandSettings($backupInstance);
		$requiredParams = ['service', 'cmd', 'description', 'retrieveFile', 'fileName'];
		foreach ($commands as $command) {
			foreach ($requiredParams as $p) {
				if (!isset($command[$p])) {
					$status->addError ('Backup command not set up correctly', ['command' => $command]);
					return false;
				}
			}
			Yii::$app->fileStorage->registerTempFile($command['retrieveFile']);
			$serviceInstance = $this->applicationInstance->getServiceInstance($command['service']);
			if (!$serviceInstance) {
				$status->addError ('Backup command not set up correctly: invalid service', ['command' => $command]);
				return false;
			}
			if (is_string($command['cmd'])) {
				$command['cmd'] = [
					'cmd' => $command['cmd']
				];
			}
			$commandResponse = $serviceInstance->runCommand($command['cmd'], $status);
			if ($commandResponse && file_exists($command['retrieveFile'])) {
				$files[$command['retrieveFile']] = $command['fileName'];
				$status->addInfo('Backup Task Succeeded: ' . $command['description'], ['command' => $command]);
			} else {
				$status->addError('Backup Task Failed: ' . $command['description'], ['command' => $command, 'commandResponse' => $commandResponse]);
				return false;
			}
		}

		if (empty($files) || count($files) !== count($commands)) {
			return false;
		}
		$package = $backupInstance->packageFiles($files, $status);
		return $package;
	}

	public function cleanupTransferFiles($files)
	{
		foreach ($files as $file) {
			if (file_exists($file)) {

			}
		}
	}

	abstract public function commandSettings($backupInstance);
}
?>