<?php
/**
 * @package     Postman (Controler)
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

jimport("mvc3gepard.proximitycontroller");
jimport("mvc3gepard.utils");
jimport("joomla.html.pagination");
//jimport('joomla.log.log');
//jimport('joomla.application.component.controllerform');

define('COM_POSTMAN_EDIT_ONE_ERROR', 'You can edit one %s at the time only!');
define('COM_POSTMAN_SELECT_NONE_ERROR', 'Please select %s you would like to delete!');
define('COM_POSTMAN_EDIT_ERROR', 'Error editing %s!');
define('COM_POSTMAN_REMOVE_ERROR', 'Error removing %!');
define('COM_POSTMAN_SAVE_SUCCESS', '%s successfully saved!');
define('COM_POSTMAN_SAVE_ERROR', 'Error saving %s!');
define('COM_POSTMAN_RECORD_NOT_FOUND', '%s not found!');
define('COM_POSTMAN_EMPTY_LOG_SUCCESS', 'Log successfully emptied.');
define('COM_POSTMAN_EMPTY_LOGS_ERROR', 'Error removing logs!');
define('COM_POSTMAN_N_ITEMS_REMOVED', '%d %s(s) successfully removed');
define('COM_POSTMAN_SUBSCRIBER_REMOVED', 'Subscriber (%d) removed from group (%d)');
define('COM_POSTMAN_SUBSCRIBER_DELETED', 'Subscriber (%d) deleted');
define('COM_POSTMAN_SELECT_GROUP', "Select at least one group for delivering newsletters!");
define('COM_POSTMAN_NO_NEWSLETTER', "Newsletter %d has no content!");
define('COM_POSTMAN_NO_SUBSCRIBERS_GROUP', "Selected group '%s' has  no subscriber(s) assigned.");
define('COM_POSTMAN_SEND_FAIL', "Sending mail '%s' to group '%s' subscriber %s: FAIL!");
define('COM_POSTMAN_SEND_OK', "Mail '%s' sent to group '%s' subscriber '%s': OK");
/*
define('SUCCESSFULLY_CREATED', 1000);
define('SUCCESSFULLY_SAVED', 1001);
define('SUCCESSFULLY_UPDATED', 1002);

define('ERROR_SAVING', 2000);
define('ERROR_CREATING', 2001);
define('ERROR_UPDATING', 2002);
*/
/*
Reference: http://dev.mysql.com/doc/refman/5.0/en/error-messages-server.html#error_er_dup_entry
------------------------------------------------------------------------------------------------
define('ER_BAD_FIELD_ERROR', 1054);
define('ER_BAD_FIELD_ERROR_MSG', "Unknown column '%s' in '%s");
define('ER_WRONG_FIELD_WITH_GROUP', 1055);
define('ER_WRONG_FIELD_WITH_GROUP_MSG', "%s isn't in GROUP BY");
define('ER_WRONG_GROUP_FIELD', 1056);
define('ER_WRONG_GROUP_FIELD_MSG', "Can't group on '%s");
define('ER_WRONG_SUM_SELECT', 1057);
define('ER_WRONG_SUM_SELECT_MSG', 'Statement has sum functions and columns in same statement');
define('ER_WRONG_VALUE_COUNT', 1058);
define('ER_WRONG_VALUE_COUNT_MSG', "Column count doesn't match value count");
define('ER_TOO_LONG_IDENT',1059);
define('ER_TOO_LONG_IDENT_MSG', "Identifier name '%s' is too long");
define('ER_DUP_FIELDNAME', 1060);
define('ER_DUP_FIELDNAME_MSG', "Duplicate column name '%s'");
define('ER_DUP_KEYNAME', 1061);
define('ER_DUP_KEYNAME_MSG, "Duplicate key name '%s'");
define('ER_DUP_ENTRY', 1062);
define('ER_DUP_ENTRY_MSG', "Duplicate entry '%s' for key %d");
define('ER_WRONG_FIELD_SPEC', 1063);
define('ER_WRONG_FIELD_SPEC_MSG', "Incorrect column specifier for column '%s'");
define('ER_PARSE_ERROR', 1064);
define('ER_PARSE_ERROR_MSG', "%s near '%s' at line %d"):
define('ER_EMPTY_QUERY', 1065)
define('ER_EMPTY_QUERY_MSG','Query was empty');
define('ER_INVALID_DEFAULT', 1067);
define('ER_INVALID_DEFAULT_MSG', "Invalid default value for '%s");
define('ER_MULTIPLE_PRI_KEY', 1068);
define('ER_MULTIPLE_PRI_KEY_MSG', 'Multiple primary key defined');
*/

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."models".DS."ConfigModel.class.php");

final class AdminController extends ProximityController {
	private $_macroReplacer;
	private $_log;
	private $_limit =  20;
	public function __construct() {

		parent::__construct(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."models",
			JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."views");

		//$this->_params = JComponentHelper::getParams("com_postman");
		$this->_log = $this->getModel('LogsModel');
		$this->_limit = ConfigModel::get("listLimit", $this->_limit);
	}
	//LOG & DEBUG methods ------------------------------------------------
	public function LOG($txt, $args = null) {
		$args = '';
		if ($args != null) {
			//print out all arguments
			$args = implode("\n", $arg);
			if ($args != null) $this->_log->log("$txt:args: $args");
		}else{
			$this->_log->log($txt);
		}
	}
	public function DEBUG($txt, $arg = null) {
		$args = '';
		if ($arg != null) {
			//print out all arguments
			$args = implode("\n", $arg);
			$this->_log->debug("$txt:args: $args");
		}else{
			$this->_log->debug($txt);
		}
	}
	private function defaultTask() {
		$this->listNewsLetters();
	}
	private function getErrorMsg($error) {
		$description = split('SQL=', $error);
		return (count($description)>0) ? $description[0] : $error;
	}
	private function handleError($e, $text = "", $code = 100) {
		
		if (!ConfigModel::get('debug', 0)) {
			JError::raiseWarning($code, sprintf("%s (%d)", $text, $e->getCode()));
			$this->LOG(sprintf("%s (%d)", $text, $e->getCode()));
		}else{
			$msg = "Error " .
				"<br/> Number: %d - Message: %s" .
				"<br/>File: %s - Line:%s";
			JError::raiseWarning($code, sprintf($msg, 
				$e->getCode(),
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()));
		}
	}
	// Helper functions --------------------------------------------------
	public function setMacroReplacer(IMacroReplacer $replacer) {

		$this->_macroReplacer = $replacer;
	}
	public function getMacroReplacer() {
		return $this->_macroReplacer;
	}
	public function showMainMenu() {
		$view = $this->getView("MainMenuView");
		$view->display();
	}
	// NEWSLETTERS -------------------------------------------------------
	public function listNewsLetters() {
		//Validate access rights - form.token must be added in form
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_limit);

		$model = $this->getModel("NewslettersModel");
		$view = $this->getView("ListNewslettersView");
	
		$total = $model->count();
	
		$letters = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);
	
		$view->setPage($pagination);
		$view->setLetters($letters);

		JRequest::setVar("limitstart", $limitstart);
		JRequest::setVar("limit", $limit);

		$view->display();
	}
	public function editNewsLetter() {
		//Validate access rights - form.token must be added in form
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$letterId = JRequest::getInt('letterId', -1);
		$lettersSelected = JRequest::getVar('letter_id', array(), '', 'array');

		// if more than one newsletter selected throw error
		if (count($lettersSelected) > 1) {
			$this->raiseNotice(COM_POSTMAN_EDIT_ONE_ERROR, "newsletter");
			$this->listNewsLetters();
			return false;
		}

		try 
		{
			// if newsletter is checked, not clicked
			if ($letterId == -1) {
				if (count($lettersSelected) > 0) $letterId = $lettersSelected[0];
			}

			$view = $this->getView("EditNewsletterView");
			$model = $this->getModel("NewslettersModel");

			$newsLetter = null;
			//find the subscriber record or raise a notice
			if ($letterId > -1) {
				$newsLetter = $model->findById($letterId);;
				if ($newsLetter==null) {
					$this->raiseNotice(COM_POSTMAN_RECORD_NOT_FOUND, "Newsletter");
					$this->listSubscribers();
					return false;
				}
			}

			// for known newsletter set the data to be edited
			if ($newsLetter != null) {
				$view->setLetter($newsLetter);
			}

			$view->display();
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_EDIT_ERROR, 'newsletter'));
			return false;
		}

		return true;
	}
	private function saveNewsLetter() {
		//Validate access rights
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$letterId = JRequest::getInt("letterId");
		$subject = JRequest::getString("subject", "");
		$message = JRequest::getVar("editor1", null, "default", "none", JREQUEST_ALLOWHTML);
		$published = (JRequest::getString("published") == "on") ? 1:0;

		try
		{
			//get the model instance and save the record
			$model = $this->getModel("NewslettersModel");
			if ($letterId == -1) {
				try {
					$model->create($subject, $message, $published);
				}catch(ErrorException $e) {
					JError::raiseWarning(100, $this->getErrorMsg($e->getMessage()));
					return true;
				}
				//find the newsletter number
				$newsletter = $model->findBySubject($subject);
				$letterId = $newsletter[0]->letter_id;

				//set letterId to be used with edit
				JRequest::setVar("letterId", $letterId);
			}else{
				try {
					$model->update($letterId, $subject, $message, $published);
				}catch(ErrorException $e) {
					JError::raiseWarning(100, $this->getErrorMsg($e->getMessage()));
					return true;
				}
			}

			JFactory::getApplication()->enqueueMessage(sprintf(COM_POSTMAN_SAVE_SUCCESS,'Newsletter'));

			JRequest::setVar("limitstart", null);
			JRequest::setVar("limit", null);
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_EDIT_ERROR, 'newsletter'));
			return false;
		}
	
		return true;
	}
	public function applyNewsLetter() {
		if ($this->saveNewsLetter()) {
			$this->editNewsLetter();
		}else{
			$this->listNewsLetters();
		}
	}
	public function saveAndCloseNewsletter() {
		$this->saveNewsLetter();
		$this->listNewsLetters();
	}
	public function removeNewsLetter() {
		// Validate access rights - form.token must be added in form
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$letterIds = JRequest::getVar("letter_id", array());
		
		if (!count($letterIds) > 0) {
			throw new ErrorException(sprintf(COM_POSTMAN_SELECT_NONE_ERROR, 'newsletter'), 1, 1, __FILE__, __NO__, null);
		}

		try
		{
			$model = $this->getModel("NewslettersModel");
			$view = $this->getView("ListNewslettersView");
		
			$nDeleted = 0;
		
			foreach($letterIds as $letterId) {
				$model->delete($letterId);
				$nDeleted++;
			}
		
			if ($nDeleted) {
				$msg = sprintf(COM_POSTMAN_N_ITEMS_REMOVED, $nDeleted, 'newsletter');
				JFactory::getApplication()->enqueueMessage($msg);
			}

			JRequest::setVar("limitstart", null);
			JRequest::setVar("limit", null);
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(sprintf(COM_POSTMAN_REMOVE_ERROR, 'newsletter')));
		}
		// go back to list view
		$this->listNewsLetters();
	}
	// SUBSCRIBERS -------------------------------------------------------
	public function listSubscribers() {

		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_limit);

		$model = $this->getModel("SubscribersModel");
		$view = $this->getView("ListSubscribersView");

		$total = $model->count();

		$subscribers = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);

		$view->setPage($pagination);
		$view->setSubscribers($subscribers);

		$view->display();
	}
	public function editSubscriber() {
		//Validate access rights - form.token must be added in form
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		try
		{
			$subscriberId = JRequest::getInt('subscriberId',-1);
			$sbscrbSelected = JRequest::getVar('subscriber_id', array(), '', 'array');
			
			if (count($sbscrbSelected) > 1) {
				$this->raiseNotice(COM_POSTMAN_EDIT_ONE_ERROR, "subscribers");
				$this->listSubscribers();
				return false;
			}
			
			// if subscriber is checked, not clicked
			if ($subscriberId == -1) {
				if (count($sbscrbSelected) > 0) $subscriberId = $sbscrbSelected[0];
			}
		
			//models, view
			$subscriberModel = $this->getModel("SubscribersModel");
			$groupModel = $this->getModel("NewsletterGroupsModel");
			$view = $this->getView("EditSubscriberView");
		
			//find the subscriber record or raise a notice
			$subscriber = null;
			if ($subscriberId > -1) {
				$subscriber = $subscriberModel->findById($subscriberId);
				if ($subscriber==null) {
					$this->raiseNotice(COM_POSTMAN_RECORD_NOT_FOUND, "Subscriber");
					$this->listSubscribers();
					return false;
				}
			}

			//get all subscriber groups
			$allGroups = $groupModel->getAll();
			$subscribedGroupIds = $subscriberModel->getSubscribedGroupIds($subscriberId);
		
			// for given subscriber set the data to be edited
			$view->setSubscriber($subscriber);
			$view->setSubscribedGroupIds($subscribedGroupIds);
			$view->setAllGroups($allGroups);
			$view->display();
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_EDIT_ERROR, 'subscriber', $e->getMessage()));
			return false;
		}
		return true;
	}
	private function saveSubscriber() {
		//Validate access rights
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		//set variables
		$subscriberId = JRequest::getInt("subscriberId", -1);
		$email = JRequest::getString("email");
		$name = JRequest::getString("name");
		$active = (JRequest::getString("active") == "on") ? 1:0;
		$grpsSelected = JRequest::getVar("cid", array());

		try
		{
			//Validation has been done in jscript on button click (see more in EditNewsletterView class)

			//get the subscriber, group model and user groups
			$subscribersModel	= $this->getModel("SubscribersModel");
			$groupModel 		= $this->getModel("NewsletterGroupsModel");
			$subscribedGroups	= $subscribersModel->getSubscribedGroupIds($subscriberId);

			// add new or save subscriber to database
			if ($subscriberId == -1) 
			{
				//create subscriber
				try {
					$subscribersModel->create($name, $email, $active);
				}catch(ErrorException $e) {
					JError::raiseWarning(100, $this->getErrorMsg($e->getMessage()));
					return true;
				}

				//find the saved subscriber and set
				$subscriber = null;
				$subscriber = $subscribersModel->findByEmail($email);

				if ($subscriber != null) {
					$subscriberId = $subscriber->subscriber_id;
					JRequest::setVar("subscriberId", $subscriberId);
				}else{
					throw new Exception(sprintf(COM_POSTMAN_RECORD_NOT_FOUND, 'Subscriber'));
				}
			}else{
				try {
					$subscribersModel->update($subscriberId, $name, $email, $active);
				}catch(ErrorException $e) {
					JError::raiseWarning(100, $this->getErrorMsg($e->getMessage()));
					return true;
				}
			}
			// assign subscriber to selected groups
			if (count($grpsSelected) > 0) {
				$groupModel->removeSubscriberFromGroup($subscriberId);
				foreach($grpsSelected as $groupId) {
					$groupModel->addSubscriber($groupId, $subscriberId);
				}
			}

			JFactory::getApplication()->enqueueMessage(sprintf(COM_POSTMAN_SAVE_SUCCESS,'Subscriber'));

			JRequest::setVar("limitstart", null);
			JRequest::setVar("limit", null);
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_SAVE_ERROR, 'subscriber'));
			return false;
		}
		return true;
	}
	public function applySubscriber() {
		if ($this->saveSubscriber()) {
			$this->editSubscriber();
		}else{
			$this->listSubscribers();
		}
	}
	public function saveAndCloseSubscriber() {
		$this->saveSubscriber();
		$this->listSubscribers();
	}
	public function removeSubscriber() {
		// Validate access rights - form.token must be added in form
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$subscribers = JRequest::getVar("subscriber_id", array());
		try
		{
			$model = $this->getModel("SubscribersModel");
			$modelGroup = $this->getModel("NewsletterGroupsModel");
			
			if (!count($subscribers) > 0) {
				throw new ErrorException(sprintf(COM_POSTMAN_SELECT_NONE_ERROR, 'subscriber'), 1, 1, __FILE__, __NO__, null);
			}

			$nDeleted = 0;			
			foreach($subscribers as $subscriberId) 
			{
				$subscribedGroups = $model->getSubscribedGroupIds($subscriberId);

				foreach($subscribedGroups as $groupId) {
					$modelGroup->removeSubscriberFromGroup($subscriberId, $groupId);
					$this->LOG(sprintf(COM_POSTMAN_SUBSCRIBER_REMOVED, $subscriberId, $groupId));
				}
	
				$model->delete($subscriberId);
				$this->LOG(sprintf(COM_POSTMAN_SUBSCRIBER_DELETED, $subscriberId));

				$nDeleted++;
			}

			if ($nDeleted) {
				$msg = sprintf(COM_POSTMAN_N_ITEMS_REMOVED, $nDeleted, 'subscriber');
				JFactory::getApplication()->enqueueMessage($msg);
			}
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_REMOVE_ERROR, 'subscriber'));
		}
		$this->listSubscribers();
	}
	// GROUPS ------------------------------------------------------------
	public function listGroups() {
	
		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_limit);
	
		$model = $this->getModel("NewsletterGroupsModel");
		$view = $this->getView("ListGroupsView");
		
		$total = $model->count();

		$groups = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);

		$view->setPage($pagination);
		$view->setGroups($groups);
	
		$view->display();
	}
	private function saveGroup() {
		//Validate access rights
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$groupId      = JRequest::getVar("groupId", -1);
		$name         = JRequest::getString("name");
		$description  = JRequest::getString("description");
		$grpsSelected = JRequest::getVar("cid", array());
		$usrsSelected = JRequest::getVar("cid", array());
		$public       = (JRequest::getString("public") == "on") ? 1:0;

		try
		{
			//get group model and user groups
			$groupModel = $this->getModel("NewsletterGroupsModel");

			//create or update group
			if ($groupId == -1) {

				// create
				try {
					$groupModel->create($name, $description, $public);
				}catch(ErrorException $e) {
					JError::raiseWarning(100, $this->getErrorMsg($e->getMessage()));
					return true;
				}

				//get the saved subscriber record
				$group = null;
				$group = $groupModel->findByName($name);

				if ($group != null) {
					$groupId = $group->newsgroup_id;
					JRequest::setVar("groupId", $groupId);
				}else{
					throw new Exception("Group not found!");
				}
			}else{
				//update
				try {
					$groupModel->update($groupId, $name, $description, $public);
				}catch(ErrorException $e) {
					JError::raiseWarning(100, $this->getErrorMsg($e->getMessage()));
					return true;
				}
			}

			//update group subscribers
			$groupModel->updateGroupSubscribers($groupId, $usrsSelected);

			JFactory::getApplication()->enqueueMessage(sprintf(COM_POSTMAN_SAVE_SUCCESS,'Groups'));

			JRequest::setVar("limitstart", null);
			JRequest::setVar("limit", null);
	
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_SAVE_ERROR, 'group'));
			return false;
		}

		return true;
	}
	public function editGroup() {
		try {
			$groupId = JRequest::getVar("groupId", -1);
			$grpsSelected = JRequest::getVar('newsgroup_id', array());
			//$limitstart = JRequest::getVar("limitstart", 0);

			// if more than one newsletter selected throw error
			if (count($grpsSelected) > 1) {
				$this->raiseNotice(COM_POSTMAN_EDIT_ONE_ERROR, "group");
				$this->listGroups();
				return false;
			}

			// if subscriber checked over clicked
			if ($groupId == -1) {
				if (count($grpsSelected) > 0) $groupId = $grpsSelected[0];
			}

			$view = $this->getView("EditGroupView");
			$model = $this->getModel("NewsletterGroupsModel");
	
			$subscribers = array();
			$group = null;
			if ($groupId > -1) {
				$group = $model->findById($groupId);
				if ($group == null) {
					$this->raiseNotice(COM_POSTMAN_RECORD_NOT_FOUND, "Group");
					$this->listGroups();
					return false;
				}
				$subscribers = $model->getSubscribersByGroupIdBlockWise($groupId);
			}
			$view->setGroup($group);
			$view->setSubscribers($subscribers);
			$view->display();
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_EDIT_ERROR, 'group'));
			return false;
		}

		return true;
	}
	public function applyGroup() {
		if ($this->saveGroup()) {
			$this->editGroup();
		}else{
			$this->listGroups();
		}
	}
	public function saveAndCloseGroup(){
		$this->saveGroup();
		$this->listGroups();	
	}
	public function removeGroup() {
		$groupIds = JRequest::getVar('newsgroup_id', array());
		try 
		{
			
			if (!count($groupIds) > 0) {
				throw new ErrorException(sprintf(COM_POSTMAN_SELECT_NONE_ERROR, 'group'), 1, 1, __FILE__, __NO__, null);
			}

			$nDeleted = 0;
			$model = $this->getModel("NewsletterGroupsModel");
			foreach($groupIds as $groupId) {
				$model->delete($groupId);
				$nDeleted++;
			}

			if ($nDeleted) {
				$msg = sprintf(COM_POSTMAN_N_ITEMS_REMOVED, $nDeleted, 'group');				
				JFactory::getApplication()->enqueueMessage($msg);
			}

		}catch(JException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_REMOVE_ERROR, 'group'));
		}

		$this->listGroups();
	}
	// TICKETS -----------------------------------------------------------
	public function listTickets() {
		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_limit);

		$model = $this->getModel("TicketsModel");
		$view = $this->getView("ListTicketsView");
		$model->setLogger($this->_log);

		$total = $model->count();

		$tickets = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);

		$view->setPage($pagination);
		$view->setTickets($tickets);

		$view->display();
	}
	public function removeTicket() {
		// Validate access rights - form.token must be added in form
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ticketIds = JRequest::getVar("ticketid", array());

		if (!count($ticketIds) > 0) {
			throw new ErrorException(sprintf(COM_POSTMAN_SELECT_NONE_ERROR, 'ticket'), 1, 1, __FILE__, __NO__, null);
		}

		try
		{
			$model = $this->getModel("TicketsModel");
			$view = $this->getView("ListTicketsView");
			$model->setLogger($this->_log);

			$nDeleted = 0;

			foreach($ticketIds as $ticketId) {
				$model->delete($ticketId);
				$nDeleted++;
			}
		
			if ($nDeleted) {
				$msg = sprintf(COM_POSTMAN_N_ITEMS_REMOVED, $nDeleted, 'ticket');
				JFactory::getApplication()->enqueueMessage($msg);
			}

			JRequest::setVar("limitstart", null);
			JRequest::setVar("limit", null);
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(sprintf(COM_POSTMAN_REMOVE_ERROR, 'ticket')));
		}
		// go back to list view
		$this->listTickets();		
	}
	// SENDING NEWSLETTER ------------------------------------------------
	public function  sendGroupNewsletter() {
		$letterId = JRequest::getInt("letterId", -1);
		$groupsModel = $this->getModel("NewsletterGroupsModel");
		$letterModel = $this->getModel("NewslettersModel");
		$view = $this->getView("SendGroupNewsletterView");

		$letters = $letterModel->getAll();
		$groups = $groupsModel->getAll();

		if ($letterId == -1) {
			$letterId = ($letters) ? $letters[0]->letter_id : -1;
		}

		$view->setSelectedLetterId($letterId);
		$view->setGroups($groups);
		$view->setLetters($letters);
		$view->display();
	}
	public function  preview() {

		$docType = JRequest::getString("type");

		if ($docType == "letter") {
			$letterId = JRequest::getInt("letterId");
			$application = JFactory::getApplication();
			$application->redirect("index.php?option=com_postman&task=previewEmail&letterId={$letterId}");
		}
	}
	public function  previewEmail() {

		$letterId = JRequest::getInt("letterId");

		$model = $this->getModel("NewslettersModel");
		$view = $this->getView("NewsLetterPreview");

		$content = $model->getPreviewEmail($letterId);

		// send newsletter
		if (trim($content) == "") {
			$this->sendGroupNewsletterView();
			return false;
		}

		$view->setContent($content);
		$view->display();
	}
	public function sendEmails() {
		$this->LOG("sendEmails ...");

		jimport("joomla.error.error");
		jimport("mvc3gepard.utils.mailbuilder");

		$groupIds = JRequest::getVar("cid", array());
		$letterId = JRequest::getVar("letterId", 0);

		$this->DEBUG(sprintf("GroupId: %d LetterId: %d", $groupIds, $letterId));

		if (!count($groupIds) > 0) {
			JError::raiseWarning(100, COM_POSTMAN_SELECT_GROUP);
			$this->sendGroupNewsletter();
			return;
		}

		$groupsModel = $this->getModel("NewsletterGroupsModel");
		$lettersModel = $this->getModel("NewslettersModel");

		//debug #2
		$this->DEBUG("sendEmails::groupIds=(".implode(",", $groupIds).  "), letterId=$letterId, subject=" .$letter->subject. ", mailbody=$mailbody");

		$letter = $lettersModel->findById($letterId);
		$mailbody = $letter->message;

		if (trim($mailbody) == "") {
			JError::raiseWarning(100, sprintf(COM_POSTMAN_NO_NEWSLETTER, $letterId));
			$this->sendGroupNewsletter();
			$this->LOG($msg);
			return;
		}

		$mailer = JFactory::getMailer();

		// embed images and flash objects
		if ($lettersModel->isEmailHtml($mailbody)) {
			$lettersModel->embedImages($mailbody,$mailer);
		}

		$this->_macroReplacer->setContent($mailbody);

		$subscriberCount = 0;
		$hasSubscribers = false;
		$app = JFactory::getApplication();

		foreach($groupIds as $groupId) {

			$group = $groupsModel->findById($groupId);
			$subscribers = $groupsModel->getSubscribersByGroupIdBlockWise($groupId);

			if (!count($subscribers) > 0) {
				JError::raiseWarning(100, sprintf(COM_POSTMAN_NO_SUBSCRIBERS_GROUP, $group->name));
				$this->sendGroupNewsletter();
				return;
			}

			foreach($subscribers as $subscriber) {

				$subscriberCount ++;

				$key = strval($subscriber->subscriber_id) 
					.strval($groupId)
					.$subscriber->name;

				$hash = md5($key);

				//debug #3
				$this->DEBUG("Preparing mail for: {$key}:{$hash}");

				$this->_macroReplacer->replace("[NAME]", $subscriber->name);
				$this->_macroReplacer->replace("[USER_NAME]", $subscriber->name);
				$this->_macroReplacer->replace("[CURRENT_DATE]", date(ConfigModel::get('dateformat'),time()));
				$this->_macroReplacer->replace("[UNREGISTER_LINK]", "<a href=\"" .JURI::root() 
					."index.php?option=com_postman&task=removeSubscriber&cid=" 
					.$subscriber->subscriber_id 
					."&gid={$groupId}&tag={$hash}>Unsubscribe</a>");

				$mailer->addRecipient($subscriber->email);
				//$mailer->AddAddress($subscriber->email, $subscriber->name);
				$mailer->setSubject($letter->subject);
				$mailer->setBody($this->_macroReplacer->getReplacedContent());
				$mailer->IsHTML(true);

				try
				{
					if ($sent = $mailer->Send() == 1) {
						$okMsg = sprintf(COM_POSTMAN_SEND_OK, $letter->subject, $group->name, $subscriber->email);
						$this->LOG($okMsg);
						$app->enqueueMessage($okMsg);
						$mailer->ClearAllRecipients();
					}else{
						$errmsg = sprintf(COM_POSTMAN_SEND_FAIL, $letter->subject, $group->name, $subscriber->email) . " Error:" . $sent;
						JError::raiseWarning(100, $errmsg);
						$this->LOG($errmsg);
						return false;
					}
				}catch(Exception $e){
					$this->handleError($e, sprintf(COM_POSTMAN_SEND_FAIL, 'newsletter'));
					$this->sendGroupNewsletter();
					return false;
				}
			}
		}

		//debug #end
		$this->DEBUG("END:sendEmails");

		$lettersModel->markAsSent($letterId);
		$this->sendGroupNewsletter();
	}
	// LOGS --------------------------------------------------------------
	public function listLogs(){
		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_limit);

		$model = $this->getModel("LogsModel");
		$view = $this->getView("ListLogsView");

		$total = $model->count();

		$logs = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);

		$view->setPage($pagination);
		$view->setLogs($logs);

		$view->display();
	}
	public function emptyLog() {
		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = ConfigModel::get("listLimit", 20);
		
		$model = $this->getModel("LogsModel");
		$view = $this->getView("ListLogsView");

		$model->emptyLog();
		$total = $model->count();

		JFactory::getApplication()->enqueueMessage(COM_POSTMAN_EMPTY_LOG_SUCCESS);

		$logs = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);

		$view->setPage($pagination);
		$view->setLogs($logs);

		$view->display();			
		return true;
	}
}

?>
