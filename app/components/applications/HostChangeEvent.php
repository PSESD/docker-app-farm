<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;

class HostChangeEvent extends \yii\base\Event
{
	public $oldHostname;
	public $newHostname;
	public $applicationInstance;
}
?>