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
	protected $_hostname;

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
		return $this->_hostname;
	}

	public function setHostname($hostname)
	{
		$oldHostname = $this->_hostname;
		$this->_hostname = $hostname;
		if (!empty($oldHostname)) {
			$this->trigger(static::EVENT_HOSTNAME_CHANGE, $oldHostname, $hostname);
		}
		return $this;
	}
}
?>