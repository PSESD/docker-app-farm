<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\components\applications;

use Yii;

class ApplicationInstance extends \canis\base\Component
{
	const EVENT_HOSTNAME_CHANGE = '_hostnameChange';
	public $applicationId;
	public $model;
	protected $_attributes = [];

	protected $_cache = [];

	public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["\0*\0_cache", "model"];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }

        return $keys;
    }

    public function getAttributes()
    {
    	return $this->_attributes;
    }

    public function setAttributes($attributes)
    {
    	$oldHostname = $newHostname = false;
    	if (isset($attributes['hostname']) && isset($this->_attributes['hostname']) && $this->_attributes['hostname'] !== $attributes['hostname']) {
			$newHostname = $attributes['hostname'];
			$oldHostname = $this->_attributes['hostname'];
    	}
    	$this->_attributes = $attributes;
    	if ($oldHostname) {
    		$this->trigger(static::EVENT_HOSTNAME_CHANGE, $oldHostname, $newHostname);
    	}
    }

	public function getPrimaryHostname()
	{
		if (empty($this->hostname)) {
			return null;
		}
		$hostnames = explode(',', $this->hostname);
		return $hostnames[0];
	}

	public function getHostname()
	{
		if (!isset($this->attributes['hostname'])) {
			return null;
		}
		return $this->attributes['hostname'];
	}

	public function setHostname($hostname)
	{
		$oldHostname = $this->hostname;
		$this->_attributes['hostname'] = $hostname;
		if (!empty($oldHostname)) {
			$this->trigger(static::EVENT_HOSTNAME_CHANGE, $oldHostname, $hostname);
		}
		return $this;
	}
}
?>