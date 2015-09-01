<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications\tasks;

use Yii;

class CommandRestoreTask extends RestoreTask implements RestoreInterface
{
	public $applicationInstance;

	public function run($restoreInstance, $status) {
		$files = [];
		$commands = $this->commandSettings($restoreInstance);
		$requiredParams = ['service', 'cmd', 'description'];
		foreach ($commands as $command) {
			foreach ($requiredParams as $p) {
				if (!isset($command[$p])) {
					$status->addError ('Restore command not set up correctly', ['command' => $command]);
					return false;
				}
			}
			$serviceInstance = $this->applicationInstance->getServiceInstance($command['service']);
			if (!$serviceInstance) {
				$status->addError ('Restore command not set up correctly: invalid service', ['command' => $command]);
				return false;
			}
			if (is_string($command['cmd'])) {
				$command['cmd'] = [
					'cmd' => $command['cmd']
				];
			}
			$commandResponse = $serviceInstance->runCommand($command['cmd'], $status);
			if ($commandResponse) {
				$status->addInfo('Restore Task Succeeded: ' . $command['description'], ['command' => $command]);
			} else {
				$status->addError('Restore Task Failed: ' . $command['description'], ['command' => $command, 'commandResponse' => $commandResponse]);
				return false;
			}
		}
		return true;
	}
}
?>