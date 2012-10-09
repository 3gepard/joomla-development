<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
define('NEWSGROUP_TABLE', '#__postman_newsgroups');
define('NEWSGROUP_SUBSCRIBERS_TABLE', '#__postman_newsgroups_subscribers');
// define('SUBSCRIBERS_TABLE1', '#__postman_subscribers');

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."ProximityModel.class.php");

final class NewsGroupsModel extends ProximityModel {

	static $EMPTY_ARRAY = array();
	public function __construct() {
		parent::__construct();
		$config = JFactory::getConfig();
	}
	public function create($name, $description) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert(NEWSGROUP_TABLE);
		$query->columns(array($db->quoteName('name'),
				$db->quoteName('description'),
				$db->quoteName('creation_date'),
				$db->quoteName('checked_out'),
				$db->quoteName('checked_out_time')));
		$query->values($db->quote($name) .",".
				$db->quote($description) .",".
				'NOW() ,'.
				'1, '.
				'NOW()');
		$db->setQuery((string) $query);
		$result = $db->query();
	
		if (!$result) {
			throw new JException("Create failed.");
		}

		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function update($newsgroupId, $name, $description, $checkedout=0) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName(NEWSGROUP_TABLE));
		$query->set($db->quoteName('name').' = '.$db->quote($name));
		$query->set($db->quoteName('description').' = '.$db->quote($description));
		$query->where("newsgroup_id = " .(int) $newsgroupId);
		$db->setQuery((string)$query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $result;
	}
	public function delete($newsgroupId) {
		$db = JFactory::getDbo();
		$q1 = $db->getQuery(true);
		$q2 = $db->getQuery(true);

		//deleter cascading/related records
		$q1->delete($db->quoteName(NEWSGROUP_SUBSCRIBERS_TABLE));
		$q1->where("newsgroup_id = " .(int) $newsgroupId);
		$db->setQuery((string)$q1);
		$result1 = $db->query();
		
		if ($db->getErrorNum() > 0) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		//remove  newsgroup
		$q2->delete($db->quoteName(NEWSGROUP_TABLE));
		$q2->where("newsgroup_id = " .(int) $newsgroupId);
		$db->setQuery((string)$q2);
		$result2 = $db->query();
			
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return array($result1,$result2);
	}
	public function findById($newsgroupId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(NEWSGROUP_TABLE));
		$query->where("newsgroup_id = " .(int) $newsgroupId);
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
		$query->select("*");
		$query->from($db->quoteName(NEWSGROUP_TABLE));
		$query->where("name = '$name'");
		$db->setQuery((string)$query);
		$record = $db->loadObject();

		if ($record == null) {
			$record = NewsGroupsModel::$EMPTY_ARRAY;
		}

		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $record;
	}
	public function addSubscriber($newsgroupId, $subscriberId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert(NEWSGROUP_SUBSCRIBERS_TABLE);
		$query->columns(array($db->quoteName('newsgroup_id'), $db->quoteName('subscriber_id')));
		$query->values((int) $newsgroupId .",". (int) $subscriberId);
		$db->setQuery((string) $query);
		$record = $db->query();

		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $record;
	}
	public function removeSubscriberFromGroup($subscriberId, $newsgroupId = -1) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete(NEWSGROUP_SUBSCRIBERS_TABLE);
		
		// if newsgroup not given remove all subscribers links
		if ($newsgroupId == -1) {
			$query->where("subscriber_id = " .(int) $subscriberId);
		}else{
			$query->where("subscriber_id = " .(int) $subscriberId . " AND newsgroup_id = " .$newsgroupId);
		}
		
		$db->setQuery((string) $query);
		$result = $db->query();

		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $result;
	}
	public function removeSubscribersFromGroup($newsgroupId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete(NEWSGROUP_SUBSCRIBERS_TABLE);
	
		//if newsgroup not given remove all subscribers links
		if ($newsgroupId == -1) {
			$query->where("newsgroup_id = " .(int) $newsgroupId);
		}
	
		$db->setQuery((string) $query);
		$result = $db->query();
	
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function getSubscribersByGroupIdBlockWise($groupId, $offset = null, $limit = null) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("s.subscriber_id, s.name AS name, s.email AS email");
		$query->from($db->quoteName('#__postman_subscribers') . ' AS s');
		$query->join('LEFT', $db->quoteName(NEWSGROUP_SUBSCRIBERS_TABLE) . ' AS ns ON s.subscriber_id = ns.subscriber_id');
		$query->where("ns.newsgroup_id = $groupId");
		$query->where("s.confirmed = 1" );

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
			$records = NewsGroupsModel::$EMPTY_ARRAY;
		}
		
		return $records;
	}
	public function getSubscribersByGroupNameBlockWise($name, $offset = null, $limit = null) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("s.subscriber_id AS subscriber_id, s.name AS name, s.email AS email");
		$query->from('#-_postman_subscribers AS ss');
		$query->join('LEFT', NEWSGROUP_SUBSCRIBERS_TABLE . ' ns ON ns.subscriber_id = ss.subscriber_id');
		$query->join('LEFT', NEWSGROUP_TABLE_TABLE . ' ng ');
		$query->where("ng.name = '$name'");
		
		//if offsetis provided
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
			$records = NewsGroupsModel::$EMPTY_ARRAY;
		}

		return $records;
	}
	public function getAll() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(NEWSGROUP_TABLE));
		$db->setQuery((string)$query);
		$records = $db->loadObjectList();
			
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		if ($records == 0) {
			$records = NewsGroupsModel::$EMPTY_ARRAY;
		}

		return $records;
	}
	public function getPublicGroups() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(NEWSGROUP_TABLE));
		$query->where('public = 1');
		$db->setQuery((string)$query);
		$records = $db->loadObjectList();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		if ($records == 0) {
			$records = NewsGroupsModel::$EMPTY_ARRAY;
		}
		
		return $records;		
	}
	public function getBlockwise($offset, $limit) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(NEWSGROUP_TABLE));
		$db->setQuery((string)$query, $offset, $limit);
		$records = $db->loadObjectList();
	
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		if ($records == 0) {
			$records = NewsGroupsModel::$EMPTY_ARRAY;
		}
	
		return $records;
	}
	public function count() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(NEWSGROUP_TABLE));
		$db->setQuery((string)$query);
		$result = $db->loadResult();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $result;
	}
	public function countSubscribersOfGroup($groupId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("COUNT(*)");
		$query->from($db->quoteName(NEWSGROUP_SUBSCRIBERS_TABLE));
		$query->where("newsgroup_id = ". (int) $groupId);
		$db->setQuery((string)$query);
		$records = $db->loadObjectList();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		if ($records == 0) {
			$records = NewsGroupsModel::$EMPTY_ARRAY;
		}
	
		return $records;
	}
	public function isSubscriberOfGroup($groupId, $subscriberId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("COUNT(id) AS count");
		$query->from($db->quoteName(NEWSGROUP_SUBSCRIBERS_TABLE));
		$query->where("newsgroup_id = ". (int) $groupId . " AND subscriber_id = " . (int)$subscriberId);
		$db->setQuery($sql, $offset, $limit);
		$result = $db->loadResult();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $result > 0;
	}
	public function updateGroupSubscribers($groupId, $usrsSelected) {

		$removalLst = array();
		$missingLst = array();
		
		//if no users selected make sure they are removed from group and exit
		if (count($usrsSelected) == 0) {
			$this->removeSubscribersFromGroup($groupId);
			return;
		}

		//get existing users
		$usrsExisting = $this->getSubscribersByGroupIdBlockWise($groupId);

		//add users for removal
		foreach($usrsExisting as $usr) {
			if (!in_array($usr->subscriber_id, $usrsSelected)) {
				array_push($removalLst, $usr->subscriber_id);
			}
		}

		if (!count($removalLst) > 0) return;

		//execute query
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName(NEWSGROUP_SUBSCRIBERS_TABLE));
		$query->where("newsgroup_id = ". (int) $groupId 
				. " AND subscriber_id IN (" . join(',', $removalLst).")");
		$db->setQuery((string)$query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
}
?>