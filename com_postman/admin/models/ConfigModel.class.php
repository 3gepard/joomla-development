<?php
/**
 * @package     Postman
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
jimport("mvc3gepard.proximityview");

// listLimit = 10;
// sender = "noreply@";
// placeholders = array("NAME", "UNREGISTER_LINK");
// batchSendLimit = 20;
// showUnregisterLink = true;
// onlyConfirmedAccounts = true;
// limit, listLimit
// list_limit 
// mailfrom
// fromname
// secret

final class ConfigModel {
	static private $_parameters = null;
	static private $_app = null;

	static function get($varname, $default="") {

		//set static parameter component parameter
		if (self::$_parameters == null) {
			self::$_parameters = JComponentHelper::getParams("com_postman");
		}

		//read value from component parameter
		$newvalue = self::$_parameters->get($varname, null);
		if (!$newvalue == null) return $newvalue;

		//set static config configuration variable
		if (self::$_app == null) {
			self::$_app = JFactory::getApplication();	
		}

		//read value from application configuration
		$newvalue = self::$_app->getCfg('' . $varname, null);

		if (!$newvalue == null) return $default;

		return $default;
	}
}
?>