<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."ProximityController.class.php");
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."utils".DS."IMacroReplacer.class.php");

jimport("joomla.html.pagination");
jimport('joomla.log.log');

//@TODO: Move constants in a language file.
define('COM_POSTMAN_EDIT_ONE_ERROR', 'You can edit one %s at the time only!');
define('COM_POSTMAN_EDIT_NONE_ERROR', 'Please select %s you would like to delete!');
define('COM_POSTMAN_EDIT_ERROR', 'Error editing %s!');
define('COM_POSTMAN_REMOVE_ERROR', 'Error removing %!');
define('COM_POSTMAN_SAVE_SUCCESS', '%s successfully saved!');
define('COM_POSTMAN_SAVE_ERROR', 'Error saving %s!');
define('COM_POSTMAN_RECORD_NOT_FOUND', '%s not found');
define('COM_POSTMAN_EMPTY_LOG_SUCCESS', 'Log successfully emptied');
define('COM_POSTMAN_ENTRY_SAVED_SUCCESS', '%s successfully saved');
define('COM_POSTMAN_ENTRIES_REMOVED', '%d %s(s) successfully removed');

define('COM_POSTMAN_NEWSLETTER_SAVE_SUCCESS', 'Newsletter successfully saved');
define('COM_POSTMAN_NEWSLETTER_N_ITEMS_REMOVED', '%d newsletter(s) successfully removed');
define('COM_POSTMAN_SUBSCRIBER_SAVE_SUCCESS', 'Subscriber successfully saved');
define('COM_POSTMAN_SUBSCRIBER_REMOVED', 'Subscriber (%d) removed from group (%d)');
define('COM_POSTMAN_SUBSCRIBER_DELETED', 'Subscriber (%d) deleted');
define('COM_POSTMAN_SUBSCRIBER_N_ITEMS_REMOVED', '%d subscriber(s) successfully removed');
define('COM_POSTMAN_GROUP_SAVE_SUCCESS', 'Group successfully saved');
define('COM_POSTMAN_GROUP_N_ITEMS_REMOVED', '%d group(s) successfully removed');

final class AdminController extends ProximityController {
	private $_macroReplacer;
	private $_params;
	private $_log;

	public function __construct() {

		parent::__construct(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."models",
			JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."views");

		$this->_params = JComponentHelper::getParams("com_postman");
		$this->_log = $this->getModel('LogsModel');
	}
	//LOG & DEBUG methods ------------------------------------------------
	public function LOG($txt, $arg = null) {
		$args = '';
		if ($arg != null) {
			//print out all arguments
			$args = implode("\n", $arg);
			$this->_log->log("$txt:args: $args");
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
	private function handleError($e, $text = "", $code = 100) {
		if (!$this->_params->get('debug', 0)) {
			JError::raiseWarning($code, sprintf("%s (%d)", $text, $e->getCode()));
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
	private function saveNewsLetter() {
		//Validate access rights
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			$letterId = JRequest::getInt("letterId");
			$subject = JRequest::getVar("subject", "");
			$message = JRequest::getVar("editor1", null, "default", "none", JREQUEST_ALLOWHTML);
			$published = JRequest::getVar("published");
			$isPublished = ($published = 'on')? 1:0;
			
			//get the model instance and save the record
			$model = $this->getModel("NewsLettersModel");
	
			// create or update
			if ($letterId == -1) {
				$model->create($subject, $message, $isPublished);
	
				//find the newsletter number
				$newsletter = $model->findBySubject($subject);
				$letterId = $newsletter[0]->letter_id;
	
				JRequest::setVar("letterId", $letterId);
			}else{
				$model->update($letterId, $subject, $message, $isPublished);
			}
	
			JFactory::getApplication()->enqueueMessage(COM_POSTMAN_NEWSLETTER_SAVE_SUCCESS);
	
			JRequest::setVar("limitstart", null);
			JRequest::setVar("limit", null);
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_SAVE_ERROR, 'newsletter'));
			return false;
		}
	
		return true;
	}
	public function editNewsLetter() {
		// Validate access rights - form.token must be added in form
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try 
			{
			$letterId = JRequest::getInt('letterId', -1);
			$lettersSelected = JRequest::getVar('letter_id', array(), '', 'array');

			// if more than one newsletter selected throw error
			if (count($lettersSelected) > 1) {
				throw new ErrorException(sprintf(COM_POSTMAN_EDIT_ONE_ERROR, 'newsletter'), 0, 1, __FILE__, __NO__, null);
			}

			// if newsletter is checked, not clicked
			if ($letterId == -1) {
				if (count($lettersSelected) > 0) $letterId = $lettersSelected[0];
			}

			$view = $this->getView("EditNewsLetterView");
			$model = $this->getModel("NewsLettersModel");

			$newsLetter = null;
			if ($letterId > -1){
				// find the newsletter record
				$newsLetter = $model->findById($letterId);
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

		try
		{
			$letterIds = JRequest::getVar("letter_id", array());
		
			
			if (!count($letterIds) > 0) {
				throw new ErrorException(sprintf(COM_POSTMAN_EDIT_NONE_ERROR, 'newsletter'), 1, 1, __FILE__, __NO__, null);
			}

			$model = $this->getModel("NewsLettersModel");
			$view = $this->getView("ListNewsLettersView");
		
			$nDeleted = 0;
		
			foreach($letterIds as $letterId) {
				$model->delete($letterId);
				$nDeleted++;
			}
		
			if ($nDeleted) {
				$msg = sprintf(COM_POSTMAN_NEWSLETTER_N_ITEMS_REMOVED, $nDeleted);
				JFactory::getApplication()->enqueueMessage($msg);
			}else{
				$msg = sprintf(COM_POSTMAN_RECORD_NOT_FOUND, "Newsletter(s)");
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
	public function listNewsLetters() {
		// Validate access rights - form.token must be added in form
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_params->get("listLimit"));
		$model = $this->getModel("NewsLettersModel");
		$view = $this->getView("ListNewsLettersView");
		
		$total = $model->count();
		
		$letters = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);
		
		$view->setPage($pagination);
		$view->setLetters($letters);
		
		$view->display();
		return true;
	}
	// SUBSCRIBERS -------------------------------------------------------
	private function saveSubscriber() {
		//Validate access rights
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	
		try
		{
			//set variables
			$subscriberId = JRequest::getInt("subscriberId", -1);
			$email = JRequest::getString("email");
			$name = JRequest::getString("name");
			$confirmed = (JRequest::getWord("confirmed") == "on") ? 1:0;
			$grpsSelected = JRequest::getVar("cid", array());
			
			//Validation has been done in jscript on button click (see more in EditNewsletterView class)

			//get the subscriber, group model and user groups
			$subscribersModel	= $this->getModel("SubscribersModel");
			$groupModel 		= $this->getModel("NewsGroupsModel");
			$subscribedGroups	= $subscribersModel->getSubscribedGroupIds($subscriberId);

			// create it or update it
			if ($subscriberId == -1) {
				//create subscriber
				$subscribersModel->create($name, $email, $confirmed);

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
				$subscribersModel->update($subscriberId, $name, $email, $confirmed);
			}

			//remove saved groups and add selected groups
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
	public function editSubscriber() {
		//Validate access rights - form.token must be added in form
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		try
		{
			$subscriberId = JRequest::getInt('subscriberId',-1);
			$sbscrbSelected = JRequest::getVar('subscriber_id', array(), '', 'array');
			
			if (count($sbscrbSelected) > 1) {
				throw new ErrorException(sprintf(COM_POSTMAN_EDIT_ONE_ERROR, 'subscriber'), 0, 1, __FILE__, __NO__, null);
			}
			
			// if subscriber is checked, not clicked
			if ($subscriberId == -1) {
				if (count($sbscrbSelected) > 0) $subscriberId = $sbscrbSelected[0];
			}
		
			//models, view
			$subscriberModel = $this->getModel("SubscribersModel");
			$groupModel = $this->getModel("NewsGroupsModel");
			$view = $this->getView("EditSubscriberView");
		
			//find the subscriber record
			$subscriber = null;
			if ($subscriberId > -1) {
				$subscriber = $subscriberModel->findById($subscriberId);
				if ($subscriber==null) {
					throw new JException();
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

		try
		{
			$subscribers = JRequest::getVar("subscriber_id", array());
			$model = $this->getModel("SubscribersModel");
			$modelGroup = $this->getModel("NewsGroupsModel");
			
			if (!count($subscribers) > 0) {
				throw new ErrorException(sprintf(COM_POSTMAN_EDIT_NONE_ERROR, 'subscriber'), 1, 1, __FILE__, __NO__, null);
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
				$msg = sprintf(COM_POSTMAN_SUBSCRIBER_N_ITEMS_REMOVED, $nDeleted);
				JFactory::getApplication()->enqueueMessage($msg);
			}
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_REMOVE_ERROR, 'subscriber'));
		}
		$this->listSubscribers();
	}
	public function listSubscribers() {

		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_params->get("listLimit"));
	
		$model = $this->getModel("SubscribersModel");
		$view = $this->getView("ListSubscribersView");

		$total = $model->count();

		$subscribers = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);

		$view->setPage($pagination);
		$view->setSubscribers($subscribers);

		$view->display();
	}
	// GROUPS ------------------------------------------------------------
	private function saveGroup() {
		//Validate access rights
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		try
		{
			$groupId = JRequest::getVar("groupId", -1);
			$name = JRequest::getString("name");
			$description = JRequest::getString("description");
			$grpsSelected = JRequest::getVar("cid", array());
			$usrsSelected = JRequest::getVar("cid", array());

			//get group model and user groups
			$groupModel = $this->getModel("NewsGroupsModel");
	
			// create it or update it
			if ($groupId == -1) {
				//create subscriber
				$groupModel->create($name, $description);
	
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
				//update subscriber group
				$groupModel->update($groupId, $name, $description);
			}
	
			//update group subscribers
			$groupModel->updateGroupSubscribers($groupId, $usrsSelected);

			JFactory::getApplication()->enqueueMessage(COM_POSTMAN_GROUP_SAVE_SUCCESS);

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
			$limitstart = JRequest::getVar("limitstart", 0);
			$limit = JRequest::getVar("limit", $this->_params->get("listLimit"));

			// if more than one newsletter selected throw error
			if (count($grpsSelected) > 1) {
				throw new ErrorException(sprintf(COM_POSTMAN_EDIT_ONE_ERROR, 'group'), 0, 1, __FILE__, __NO__, null);
			}

			// if subscriber checked over clicked
			if ($groupId == -1) {
				if (count($grpsSelected) > 0) $groupId = $grpsSelected[0];
			}

			$view = $this->getView("EditGroupView");
			$model = $this->getModel("NewsGroupsModel");
	
			$subscribers = array();
			$group = null;
			if ($groupId > -1) {
				$group = $model->findById($groupId);
				if ($group == null) {
					$this->raiseNotice("Cannot edit group [$groupId]!");
					$this->listGroups();
					return false;
				}
	
				$subscribers = $model->getSubscribersByGroupIdBlockWise($groupId);//, $limitstart, $limit);
			}
			$pagination = new JPagination(count($subscribers), $limitstart, $limit, 'filter');
			$view->setPage($pagination);
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
		try 
		{
			$groupIds = JRequest::getVar('newsgroup_id', array());
			
			if (!count($groupIds) > 0) {
				throw new ErrorException(sprintf(COM_POSTMAN_EDIT_NONE_ERROR, 'group'), 1, 1, __FILE__, __NO__, null);
			}

			$nDeleted = 0;
			$model = $this->getModel("NewsGroupsModel");
			foreach($groupIds as $groupId) {
				$model->delete($groupId);
				$nDeleted++;
			}

			if ($nDeleted) {
				$msg = sprintf(COM_POSTMAN_GROUP_N_ITEMS_REMOVED, $nDeleted);
				JFactory::getApplication()->enqueueMessage($msg);
			}

		}catch(JException $e){
			$adds = (count($groupIds) > 1) ? "'s": '';
			$msg = $e->getMessage();
			JError::raiseNotice(100, "Removing group$adds failed!<br/> Error: {$msg}");
		}

		$this->listGroups();
	}
	public function listGroups() {
	
		$limitstart = JRequest::getInt("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_params->get("listLimit"));
	
		$model = $this->getModel("NewsGroupsModel");
		$view = $this->getView("ListGroupsView");
		$groups = $model->getBlockWise($limitstart, $limit);
		$total = $model->count();
	
		$pagination = new JPagination($total, $limitstart, $limit);
		$view->setPage($pagination);
		$view->setGroups($groups);
	
		$view->display();
	}
	// SUBSCRPITON METHODS ------------------------------------------------
	private function validateToken() {
		
	}
	private function sendEmailToUser($email) {
		
	}
	public function subscribeToNewsletter() {
		// Validate access rights - form.token must be added in form
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$groupId	= JRequest::getVar('group_id');
		$nam		= JRequest::getVar('name');
		$email		= JRequest::getVar('email');
		//TODO: subscribeToNewsletter - consider message que for request to prevent flooding
		// check if this function is disabled
		// log to database attempt of subscription
		// validate email
		// check if email exist in db
		// send subscribtion email to the user
	}
	public function confirmSubscription() {
		$token	= JRequest::getVar('token');
		// log to database confirmation
		// validate token
		// add user to newsgroup
		// send email notification
		//TODO: vidi 
	}
	public function unsubscribeFromNewsletter() {
		// validate token
		// validate email
		// send unsubscribe mail
		// 
	}
	// SENDING NEWSLETTER ------------------------------------------------
	public function  editGroupSend() {

		$letterId = JRequest::getInt("letterId", -1);

		$groupsModel = $this->getModel("NewsGroupsModel");
		$letterModel = $this->getModel("NewsLettersModel");
		$view = $this->getView("EditGroupSendView");

		$letters = $letterModel->getAll();
		$groups = $groupsModel->getAll();

		if ($letterId == -1) {

			$letterId = ($letters) ? $letters[0]->letter_id : -1;
		}

		$view->setSelectedLetterId($letterId);
		$view->setGroups($groups);
		$view->setLetters($letters);

		$view->display();

		$application = JFactory::getApplication();
	}
	public function preview() {

		$docType = JRequest::getString("type");

		if ($docType == "letter") {
			$letterId = JRequest::getInt("letterId");
			$application = JFactory::getApplication();
			$application->redirect("index.php?option=com_postman&task=previewEmail&letterId={$letterId}");
		}
	}
	public function  previewEmail() {

		$letterId = JRequest::getInt("letterId");

		$model = $this->getModel("NewsLettersModel");
		$view = $this->getView("NewsLetterPreview");

		$content = $model->getPreviewEmail($letterId);

		if (trim($content) == "") {
			$this->editGroupSend();
			return false;
		}
		$view->setContent($content);
		$view->display();
	}
	public function  sendEmails() {

		jimport("joomla.error.error");
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."utils".DS."MailBuilder.php");

		$groupIds = JRequest::getVar("cid", array());
		$letterId = JRequest::getVar("letterId");

		$groupsModel = $this->getModel("NewsGroupsModel");
		$lettersModel = $this->getModel("NewsLettersModel");
		
		$this->_log->debug("BEGIN: Send emails.");

		if (!count($groupIds) > 0) {

			JError::raiseWarning(100, "Please select at least one group for delivering newsletters!");
			$logModel->log("Please select at least one group for delivering newsletters!");
			$this->editGroupSend();
			return;
		}

		$letter = $lettersModel->findById($letterId);
		$mailbody = $letter->message;

		if (trim($mailbody) == "") {
			$msg = "Newsletter {$letterId} has no content!";
			JError::raiseWarning(100, $msg);
			$this->editGroupSend();
			$this->_log->log($msg);
			return;
		}

		$mailer = JFactory::getMailer();

		// embed images and flash objects
		if ($lettersModel->isEmailHtml($mailbody)) {
			$lettersModel->embedImages($mailbody,$mailer);
		}

		$this->_macroReplacer->setContent($mailbody);

		$startlimit = 0;
		$hasSubscribers = false;

		$limit = $this->_params->get("emailPerBatch",0);
		$this->_log->debug("-->emailPerBatch: {$limit}");

		$app = JFactory::getApplication();

		foreach($groupIds as $groupId) {

			$total = $groupsModel->countSubscribersOfGroup($groupId);
			$this->_log->debug("-->total: {$limit}");
			while($startlimit < $total) {

				$subscribers = $groupsModel->getSubscribersByGroupIdBlockWise($groupId, $startlimit, $limit);

				// Check if there are subscirbers in the list.
				if (!$hasSubscribers) $hasSubscribers = count($subscribers) > 0;

				foreach($subscribers as $subscriber) {

					$key = strval($subscriber->subscriber_id) 
						.strval($groupId)
						.$subscriber->name;

					$msg = "Preparing mail for: {$key}";
					$this->_log->debug($msg);
					$hash = md5($key);
					$this->_macroReplacer->replace("[NAME]", $subscriber->name);
					$this->_macroReplacer->replace("[CURRENT_DATE]", date($this->_params->get('dateformat'),time()));
					$this->_macroReplacer->replace("[UNREGISTER_LINK]", "<a href=\"" .JURI::root(). "index.php?option=com_postman&task=removeSubscriber&cid=" .$subscriber->subscriber_id. "&gid={$groupId}&tag={$hash}>Unsubscribe</a>");
					$mailer->addRecipient($subscriber->email);
					//$mailer->AddAddress($subscriber->email, $subscriber->name);
					$mailer->setSubject($letter->subject);
					$mailer->setBody($this->_macroReplacer->getReplacedContent());
					$mailer->IsHTML(true);

					if ($mailer->Send()) {
						//$msg = JText::sprintf("-->OK: mail sent");
						$msg = "Mail sent to {$subscriber->email}: OK";
						$this->_log->log($msg);
						$app->enqueueMessage($msg);
						$mailer->ClearAllRecipients();
					}else{
						$msg = "Sending mail to {$subscriber->email}: FAIL";
						JError::raiseNotice(100, $msg);
						$this->_log->log($msg);
						return;
					}
					
					$startlimit += $limit;
				}
			}
		}

		if (!$hasSubscribers) {
			$msg = "No subscirbers. Check selected group(s) has subscriber(s) assigned.";
			$logModel->debug($msg);
			JError::raiseWarning(100, $msg);
			$this->editGroupSend();
			return;
		}

		$this->_log->debug("END: Send emails.");

		$lettersModel->markAsSent($letterId);
		$this->editGroupSend();
	}
	// LOGS --------------------------------------------------------------
	public function listLogs(){
		$limitstart = JRequest::getVar("limitstart", 0);
		$limit = JRequest::getVar("limit", $this->_params->get("listLimit"));
		
		$model = $this->getModel("LogsModel");
		$view = $this->getView("ListLogsView");
		
		$total = $model->count();
		
		$logs = $model->getBlockWise($limitstart, $limit);
		$pagination = new JPagination($total, $limitstart, $limit);
		
// 		$view->setPage($pagination);
		$view->setLogs($logs);
		
		$view->display();
	}
	public function emptyLog(){
		try 
		{
			$limitstart = JRequest::getVar("limitstart", 0);
			$limit = JRequest::getVar("limit", $this->_params->get("listLimit"));
			
			$model = $this->getModel("LogsModel");
			$model->emptyLog();
			JFactory::getApplication()->enqueueMessage(sprintf(COM_POSTMAN_EMPTY_LOG_SUCCESS, $total));
			$view = $this->getView("ListLogsView");
			$view->display();	
		}catch(ErrorException $e){
			$this->handleError($e, sprintf(COM_POSTMAN_SAVE_ERROR, 'group'));
			return false;
		}
		
		return true;
	}
}
?>