<?php

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."ProximityModel.class.php");


final class ConfigModel extends ProximityModel {

//	public $listLimit = 10;
//	public $sender = "noreply@";
//	public $placeholders = array("NAME", "UNREGISTER_LINK");
//	public $batchSendLimit = 20;
//	public $showUnregisterLink = true;
//	public $onlyConfirmedAccounts = true;
//
//	public $emailperbatch = 100;
//	public $batchinterval = 1;
//	public $scripttimeout = 60;

	public function getDb() {

		return JFactory::getDBO();
	}
}
?>