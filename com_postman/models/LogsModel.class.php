<?php
/**
 * @package		Postman
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."ProximityModel.class.php");

define('LOG_TABLE', '#__postman_log');

final class LogsModel extends ProximityModel {

	static $EMPTY_ARRAY = array();
	private $_dbPrefix;
	private $_params;
	private $_isDebug = false;

	public function __construct() {
		parent::__construct();

		$config = JFactory::getConfig();
		$this->_dbPrefix = $config->getValue("config.dbprefix");
		$this->_params = JComponentHelper::getParams("com_postman");
		$this->_isDebug = $this->_params->get('debug', 0);
	}
	public function log($message) {
		$this->append($message);
	}
	/*
	 * Please replace append with log in next version
	*/
	public function append($message) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert($db->quoteName(LOG_TABLE));
		$query->columns(array($db->quoteName('time_stamp'),
				$db->quoteName('message')));
		$query->values('NOW() ,'.
				$db->quote($message));
		$db->setQuery((string)$query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function debug($message) {

		if (!$this->_isDebug) return;
		return $this->append($message);
	}
	public function emptyLog() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName(LOG_TABLE));
		$db->setQuery((string)$query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function findByText($text) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(LOG_TABLE));
		$query->where($db->quote('message') ."=".
				$db->quote("%{$text}%"));
		$db->setQuery((string)$query);
		$result = $db->loadObject();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function getAll() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(LOG_TABLE));
		$query->where($db->quote('message') ."=".
				$db->quote("%{$text}%"));
		$db->setQuery((string)$query);
		$result = $db->loadObjectList();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function getBlockWise($offset, $length) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(LOG_TABLE));
		
		//if offset is provided
		if ($offset != null && $limit != null) {
			$db->setQuery((string)$query,$offset, $limit);
		}else{
			$db->setQuery((string)$query);
		}
		
		$records = $db->loadObjectList();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		if ($records == null) {
			$records = LogsModel::$EMPTY_ARRAY;
		}
		
		return $records;		
		
	}
	public function count() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(LOG_TABLE));
		$db->setQuery((string)$query);
		$result = $db->loadResult();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
}
?>