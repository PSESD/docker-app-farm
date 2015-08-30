<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;

abstract class StorageService extends Service
{
	public $daemon = false;
	public function getImage()
	{
		return 'busybox';
	}
}
?>