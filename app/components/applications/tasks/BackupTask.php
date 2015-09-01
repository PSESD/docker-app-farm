<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications\tasks;

use Yii;

abstract class BackupTask extends \yii\base\Component implements BackupInterface
{
	public $applicationInstance;

	abstract public function run($backupInstance, $status);
}
?>