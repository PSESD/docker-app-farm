<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\wordpress;

use Yii;

class WebStorageService extends \canis\appFarm\components\applications\StorageService
{
	public function getServiceName()
	{
		return 'Web Storage';
	}
	public function getVolumes()
	{
		return [
			'/var/www/web'
		];
	}
}
?>