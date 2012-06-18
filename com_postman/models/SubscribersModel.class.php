<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."ProximityModel.class.php");

define('SUBSCRIBERS_TABLE', '#__postman_subscribers');
// define('NEWSGROUP_SUBSCRIBERS_TABLE', '#__postman_newsgroups_subscribers');

final class SubscribersModel extends ProximityModel {

	static $EMPTY_ARRAY = array();

	public function __construct($logger = null) {
		parent::__construct();

		$this->_logger = $logger;
		$config = JFactory::getConfig();
		$this->_dbPrefix = $config->getValue("config.dbprefix");
	}
	public function create($name, $email, $confirmed, $checked_out = 1) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert(SUBSCRIBERS_TABLE);
		$query->columns(array($db->quoteName('name'),
			$db->quoteName('email'),
			$db->quoteName('confirmed'),
			$db->quoteName('subscribe_date'),
			$db->quoteName('checked_out'),
			$db->quoteName('checked_out_time')));
		$query->values($db->quote($name) .",".
			$db->quote($email) .",".
			$db->quote($confirmed) .",".
			'NOW() ,'.
			$checked_out .",".
			'NOW()');
		
		$db->setQuery((string) $query);
	 	$db->query();
	 	
	 	//throw exception on error
	 	if ($db->getErrorNum()) {
	 		throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
	 	}
	}
	public function update($subscriberId, $name, $email, $confirmed) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName(SUBSCRIBERS_TABLE));
		$query->set($db->quoteName('name').' = '.$db->quote($name));
		$query->set($db->quoteName('email').' = '.$db->quote($email));
		$query->set($db->quoteName('confirmed').' = '.$db->quote($confirmed));
		$query->where("subscriber_id = " .(int) $subscriberId);
		$db->setQuery((string)$query);
		$db->query();
			
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
	}
	public function delete($subscriberId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName(SUBSCRIBERS_TABLE));
		$query->where("subscriber_id = " .(int) $subscriberId);
		$db->setQuery((string)$query);
		$db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
	}
	public function findById($subsriberId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(SUBSCRIBERS_TABLE));
		$query->where("subscriber_id = " .(int) $subsriberId);
		$db->setQuery((string)$query);
		$record = $db->loadObject();
				
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $record;
	}
	public function findByName($name) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName(SUBSCRIBERS_TABLE));
			$query->where("name = $name");
			$db->setQuery((string)$query);
			$record = $db->loadObject();
			
			//throw exception on error
			if ($db->getErrorNum()) {
				throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
			}
			
			return $record;
	}
	public function findByEmail($email) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(SUBSCRIBERS_TABLE));
		$query->where("email = '$email'");
		$db->setQuery((string)$query);
		$record = $db->loadObject();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $record;
	}
	public function getAll() {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName(SUBSCRIBERS_TABLE));
			$db->setQuery((string)$query);
			$subscribers = $db->loadObjectList();
			
			//throw exception on error
			if ($db->getErrorNum()) {
				throw new JException($db->getErrorMsg());
			}
	
			if ($subcribers == null) {
				$subscribers = SubscribersModel::$EMPTY_ARRAY;
			}
	
			//throw exception on error
			if ($db->getErrorNum()) {
				throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
			}

			return $subscribers;
	}
	public function getBlockWise($offset, $length) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(SUBSCRIBERS_TABLE));
		$db->setQuery((string)$query, $offset, $length);
		$subscribers = $db->loadObjectList();

		if ($subscribers == null) {
			$subscribers = SubscribersModel::$EMPTY_ARRAY;
		}
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $subscribers;
	}
	public function count() {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$query->from($db->quoteName(SUBSCRIBERS_TABLE));
			$db->setQuery((string)$query);
			$result = $db->loadResult();
			
			//throw exception on error
			if ($db->getErrorNum()) {
				throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
			}
			
			return $result;
	}
	public function getSubscribedGroupIds($subscriberId = 0) {

		if (!$subscriberId > 0) {
			return SubscribersModel::$EMPTY_ARRAY;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('newsgroup_id');
		$query->from($db->quoteName(NEWSGROUP_SUBSCRIBERS_TABLE));
		$query->where("subscriber_id = " . (int) $subscriberId);
		$db->setQuery((string)$query);
		$subscribedGroupIds = $db->loadResultArray();

		if ($subscribedGroupIds == null) {
			$subscribedGroupIds = SubscribersModel::$EMPTY_ARRAY;
		}

		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $subscribedGroupIds;
	}
}
?>