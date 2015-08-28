<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\engine;

use Yii;
use canis\appFarm\models\Application;
use canis\appFarm\models\Instance;

class Engine extends \canis\base\Component
{
	static public function checkUninitialized()
	{
		$uninitialized = Instance::find()->where(['initialized' => 0])->all();
		foreach ($uninitialized as $instance) {
			if ($instance->dataObject->status !== 'uninitialized') {
				continue;
			}
			try {
				$instance->initialize();
				$instance->save();
			} catch (\Exception $e) {
				$instance->dataObject->statusLog->addError('Exception raised', ['error' => $e->__toString()]);
				$instance->dataObject->updateStatus('failed');
			}
		}
	}
	static public function failUninitialized()
	{
		$uninitialized = Instance::find()->where(['initialized' => 0])->all();
		foreach ($uninitialized as $instance) {
			if ($instance->dataObject->status === 'uninitialized' || $instance->dataObject->status === 'failed') {
				continue;
			}
			$instance->dataObject->statusLog->addError('Failed at post-tick check');
			$instance->dataObject->updateStatus('failed');
			$instance->initialized = 1;
			$instance->save();
		}
		throw new \Exception("o");
	}
}
?>