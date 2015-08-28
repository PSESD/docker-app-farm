<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;

class ServiceInstance extends \canis\base\Component
{
	public $applicationInstance;
	public $instanceId;
	public $service;

	public function __wakeup()
    {
        $this->service->instance = $this;
    }

	public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["applicationInstance"];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }
        return $keys;
    }

    public function check()
    {
    	if (!empty($this->service->links)) {
	    	foreach ($this->service->links as $linkId) {
	    		if (!$this->applicationInstance->getService($linkId)) {
	    			return false;
	    		}
	    	}
	    }
    	return true;
    }
}
?>