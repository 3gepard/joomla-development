<?php
/**
 * @package     Postman
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport('mvc3gepard.proximitymodel');

define('TICKETS_TABLE', '#__postman_ticket');

define("TICKET_CANCEL", 0);
define("TICKET_SUBSCRIBE", 1);
define("TICKET_UNKNOWN", 2);

define("SECRETKEY", "s_e32tGDX");
// define('NEWSGROUP_SUBSCRIBERS_TABLE', '#__postman_newsgroups_subscribers');


final class TicketModel extends ProximityModel {

	static $EMPTY_ARRAY = array();
	private $_log;
	public function __construct($logger = null) {
		parent::__construct();
		$this->_log = $this->getModel('LogsModel');
	}
	private function getID($group, $email, $type) {
		$p = JComponentHelper::getParams("com_postman");
		$subscriptionKey = $p->getString("subscriptionKey", SECRETKEY);
		$cancelationKey  = $p->getString("cancelationKey", SECRETKEY);
		$secretkey = ($type == TICKET_SUBSCRIBE) ? $subscriptionKey : $cancelationKey; 
		return md5($group . $email . $secretkey);
	}
	public function findByGroupAndEmail($group, $email, $secretkey=SECRETKEY) {
		return findTicket(getID($group,$email, $secretkey));
	}
	public function findTicket($ticketid) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName(TICKETS_TABLE));
		$query->where("ticketid = " .(int) $ticketid);
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
		$query->from($db->quoteName(TICKETS_TABLE));
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
		$query->from($db->quoteName(TICKETS_TABLE));
		$db->setQuery((string)$query);
		$records = $db->loadObjectList();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new JException($db->getErrorMsg());
		}

		if ($records == null) {
			$records = TicketModel::$EMPTY_ARRAY;
		}

		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $records;
	}
	public function subscribe($groupid, $email) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$ticketid = $this->getID($groupid,$email, TICKET_SUBSCRIBE);
		$query->insert(TICKETS_TABLE);
		$query->columns(array(
			$db->quoteName('date'),
			$db->quoteName('email'),
			$db->quoteName('ticketid'),
			$db->quoteName('type'),
			$db->quoteName('status')));
		$query->values(
			'NOW() ,'.
			$db->quote($email) .",".
			$db->quote($ticketid) .",".
			$db->quote($type) .",".
			$db->quote($status) .",");
		$db->setQuery((string) $query);
	 	$db->query();
	 	//throw exception on error
	 	if ($db->getErrorNum()) {
	 		throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
	 	}
	}
	public function unsubscriber($email, $groupid) {
		$this->_log->log("Unsubscribe for: $email, $groupid, $type");
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert(TICKETS_TABLE);
		$query->columns(array(
			$db->quoteName('date'),
			$db->quoteName('ticketid'),
			$db->quoteName('email'),
			$db->quoteName('type'),
			$db->quoteName('status')
		));
		$query->values(
			'NOW() ,'.
			$db->quote($ticketid) .",".
			$db->quote($email)    .",".
			$db->quote($type)     .",".
			$db->quote($status)   .","
		);
		$db->setQuery((string) $query);
	 	$db->query();
	}
}

