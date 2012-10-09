<?php
/**
 * @package     Postman
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport('mvc3gepard.proximitymodel');

define('SUBSCRIBERS_TABLE', '#__postman_subscribers');
define('SUBSCRIBERS_TABLE_NEWSGROUPS', '#__postman_newsgroups');
define('SUBSCRIBERS_TABLE_NEWSGROUPS_SUBSCRIBERS', '#__postman_newsgroups_subscribers');

define('NOTACTIVE', 0);
define('ACTIVE', 1);

// define('NEWSGROUP_SUBSCRIBERS_TABLE', '#__postman_newsgroups_subscribers');
final class SubscribersModel extends ProximityModel {

	static $EMPTY_ARRAY = array();

	public function __construct($logger = null) {
		parent::__construct();

		$this->_logger = $logger;
		$config = JFactory::getConfig();
		$this->_dbPrefix = $config->getValue("config.dbprefix");
	}
	public function create($name, $email, $activated, $checked_out = 1) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert(SUBSCRIBERS_TABLE);
		$query->columns(array(
			$db->quoteName('name'),
			$db->quoteName('email'),
			$db->quoteName('active'),
			$db->quoteName('subscribe_date'),
			$db->quoteName('checked_out'),
			$db->quoteName('checked_out_time')
		));

		$query->values($db->quote($name) .",".
			$db->quote($email) .",".
			$activated .",".
			'NOW() ,'.
			$checked_out .",".
			'NOW()'
		);
		
		$db->setQuery((string) $query);
	 	$db->query();
	 	
	 	//throw exception on error
	 	if ($db->getErrorNum()) {
	 		throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
	 	}
	}
	public function update($subscriberId, $name, $email, $activated) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName(SUBSCRIBERS_TABLE));
		$query->set($db->quoteName('name').' = '.$db->quote($name));
		$query->set($db->quoteName('email').' = '.$db->quote($email));
		$query->set($db->quoteName('active').' = '.$db->quote($activated));
		$query->where("subscriber_id = " .(int) $subscriberId);
		$db->setQuery((string)$query);
		$db->query();

		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
	}
	public function checkedinandout($subscriberId,$checkout) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName(SUBSCRIBERS_TABLE));
		$query->set($db->quoteName('checked_out').' = '.$checkout);
		if ($checkout == ACTIVE) $query->set($db->quoteName('checked_out_time').' = NOW()');
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
	public function findByGroupAndEmailName($groupid, $email) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(SUBSCRIBERS_TABLE_NEWSGROUPS_SUBSCRIBERS) .' AS gs');
		$query->join('LEFT', $db->quoteName('#__postman_subscribers') . ' AS s ON s.subscriber_id = gs.subscriber_id');
		$query->join('LEFT', $db->quoteName('#__postman_newsgroups')  . ' AS g ON g.newsgroup_id = gs.newsgroup_id');
		$query->where("gs.newsgroup_id = $groupid");
		$query->where("s.email = \"$email\"");
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