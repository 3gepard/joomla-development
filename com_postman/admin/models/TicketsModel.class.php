<?php
/**
 * @package     Postman
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport('mvc3gepard.proximitymodel');

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."models".DS."ConfigModel.class.php");

define('TICKETS_TABLE', '#__postman_tickets');
define("TICKET_UNSUBSCRIBE", 0);
define("TICKET_SUBSCRIBE", 1);
define("TICKET_CONFIRMED", 2);

define("SECRETKEY", ConfigModel::get("secret"));

final class TicketsModel extends ProximityModel {

	static $EMPTY_ARRAY = array();
	private $modelLog   = null;

	public function __construct() {
		parent::__construct();
	}
	private function LOG($text) {
		if ($this->modelLog != null) {
			$this->modelLog->log($text);
		}
	}
	private function DEBUG($text) {
		if ($this->modelLog != null) {
			$this->modelLog->debug($text);
		}
	}
	private function getID($group, $email, $type) {
		//$this->DEBUG("getID:($group, $email, $type)");
		//$secretkey = ConfigModel::get("secret");
		$subscriptionKey = ConfigModel::get("subscriptionKey", SECRETKEY);
		$cancelationKey  = ConfigModel::get("cancelationKey",  SECRETKEY);
		$secretkey = ($type == TICKET_SUBSCRIBE) ? $subscriptionKey : $cancelationKey;

		$this->DEBUG("getID:$subscriptionKey, $cancelationKey, $secretkey");

		return md5($group . $email . $secretkey);
	}
	public function setLogger($modellog){
		$this->modelLog = $modellog;
	}
	public function createTicket($groupid, $email, $type) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$ticketid = $this->getID($groupid,$email, $type);

		$this->DEBUG("createTicket:($groupid, $email, $type)");

		$query->insert(TICKETS_TABLE);
		$query->columns(array(
			$db->quoteName('ticketid'),
			$db->quoteName('newsgroup_id'),
			$db->quoteName('email'),
			$db->quoteName('type'),
		));

		$query->values(
			$db->quote($ticketid) .",".
			$db->quote($groupid) .",".
			$db->quote($email) .",".
			$db->quote($type)
		);

		$db->setQuery((string) $query);
	 	$db->query();

	 	//throw exception on error
	 	if ($db->getErrorNum()) {
	 		$this->LOG("Error subscribing:($groupid, $email): ". $db->getErrorMsg());
	 		//throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
	 		return false;
	 	}

	 	return true;	
	}
	public function delete($ticketid) {
		$this->LOG(sprintf("Deleting ticket %s", $ticketid));
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName(TICKETS_TABLE));
		$query->where("ticketid = " .(int) $ticketid);
		$db->setQuery((string)$query);
		$db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
	}
	public function find($groupid, $email, $type) {
		return $this->findTicket($this->getID($groupid, $email, $type));
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
			//throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
			$record = TicketModel::$EMPTY_ARRAY;
		}

		return $record;
	}
	public function findByGroupAndEmail($group, $email, $secretkey=SECRETKEY) {
		return $this->findTicket($this->getID($group,$email, $secretkey));
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
	public function count() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName(TICKETS_TABLE));
		$db->setQuery((string)$query);
		$result = $db->loadResult();
		
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
		$query->from($db->quoteName(TICKETS_TABLE));
		$db->setQuery((string)$query, $offset, $length);
		$tickets = $db->loadObjectList();

		if ($tickets == null) {
			$tickets = TicketsModel::$EMPTY_ARRAY;
		}
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $tickets;
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
		$this->DEBUG("Unsubscribe for: $email, $groupid, $type");
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
