<?php
/**
 * @package     Postman
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
jimport('mvc3gepard.proximitycontroller');

define('COM_POSTMAN_MISSING_EMAIL',           'Please enter the email address!');
define('COM_POSTMAN_MISSING_GROUP',           'Please choose the group');
define('COM_POSTMAN_SUBSCRIPTION_OK',         'Thank you for subscribing to our newsletter!');
define('COM_POSTMAN_UNSUBSCRIPTION_OK',       'You have successfully unsubscribed from our newsletter!');
define('COM_POSTMAN_SEND_EMAIL',              'Email sent to %s');
define('COM_POSTMAN_SEND_EMAIL_FAIL',         'Fail to send the email to (%s, %s) with error: %s');
define('COM_POSTMAN_SEND_SUBSCRIPTION_EMAIL', 'Sending subscription request to %s with ticket [%s]');
define('COM_POSTMAN_SUBSCRIPTION_ERROR',      'Error creating subscription request!');
define('COM_POSTMAN_UNSUBSCRIPTION_ERROR',    'Error creating unsubscription request!');
define('COM_POSTMAN_TICKET_CREATED',          'Ticket created [%s]');
define('COM_POSTMAN_TICKET_EXISTS',           'Ticket [%s] exist in a system, created on date %s');
define('COM_POSTMAN_VALIDATE_EMAIL_ERROR',    'Email address is invalid!');

final class Controller extends ProximityController {
	private $groups = null;
	private $subscribers = null;
	private $tickets = null;
	private $_log = null;

	public function __construct() {
		parent::__construct(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."models", 
			JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."views");

		$this->_log = $this->getModel("LogsModel");
		$this->subscribers = $this->getModel("SubscribersModel");
		$this->groups = $this->getModel("NewsletterGroupsModel");
		$this->tickets = $this->getModel("TicketsModel");
		$this->tickets->setLogger($this->_log);
	}
	//LOG & DEBUG methods ------------------------------------------------
	private function LOG($txt, $args = null) {
		$args = '';
		if ($args != null) {
			//print out all arguments
			$args = implode("\n", $args);
			$this->_log->append("$txt: args: $args");
		}else{
			$this->_log->append($txt);
		}
	}
	private function DEBUG($txt, $args = null) {
		$args = '';
		if ($args != null) {
			//print out all arguments
			$args = implode("\n", $args);
			$this->_log->debug("$txt:args: $args");
		}else{
			$this->_log->debug($txt);
		}
	}
	private function getTag() {
		return md5("postman");
	}
	private function SendEmail($email, $subject, $body) {
		try {
			$mailer = JFactory::getMailer();
			$mailer->addRecipient($email);
			//$mailer->AddAddress($subscriber->email, $subscriber->name);
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->IsHTML(true);

			if (!$mailer->Send()) {
				$this->LOG(printf(COM_POSTMAN_SEND_EMAIL_FAIL, $email));
				return false;
			}

			$this->LOG(JText::sprintf(COM_POSTMAN_SEND_EMAIL, $email));
			return true;
		}catch(JException $e) {
			$this->LOG(sprintf("Controller::SendEmail::Error sending the email (%s, %s) with error: %s", 
				$email,
				$subject,
				$e->getMessage()));
		}
		return false;
	}
	private function redirect($txt = ""){
		$app = JFactory::getApplication();
		if ($txt == "") {
			$app->redirect(JURI::root());
		}else{
			$app->redirect(JURI::root(),"$txt");
		}
	}
	private function validate($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	private function sendEmailReminder($email, $ticketid, $onDate, $type = TICKET_SUBSCRIBE){
		$this->DEBUG(sprintf("Sending email reminder to: %s for ticket %s", $email, $ticketid));

		$subscriber     = $this->subscribers->findByEmail($email);
		$subscriberName = ($subscriber != null)? $subscriber->name : "";
		$signature      = ConfigModel::get("signature", "Web Team");

		if ($type == TICKET_SUBSCRIBE) {
			$url     = JURI::root() . "component/index.php?option=com_postman&task=confirms&email=$email&ticketid=$ticketid" .
				"&type=" . TICKET_SUBSCRIBE .
				"&tag="  . $this->getTag();
			$subject = ConfigModel::get("subjectsubscribe", "Reminder: Your subscription confirmation to $groupName");
			$body = "Dear subscriber $subscriberName,<br/>" . 
				"This is reminder that you requested subscription to a newsletter. <br/>".
				"In order to complete your subscription please click on the following <a href=\"$url\">link</a>.<br/><br/>" .
				"Sincerely yours, <br/>" .
				$signature;
		}else{
			$url     = JURI::root() . "component/index.php?option=com_postman&task=confirms&email=$email&ticketid=$ticketid".
				"&type=" . TICKET_UNSUBSCRIBE .
				"&tag="  . $this->getTag();
			$subject = ConfigModel::get("subjectunsubscribe", "Reminder: Your newsletter cancellation to $groupName");
			$body = "Dear subscriber $subscriberName,<br/>" . 
				"This is reminder that you requested cancellation from the newsletter. <br/>".
				"In order to complete your cancellation please click on the following <a href=\"$url\">link</a>.<br/><br/>" .
				"Sincerely yours, <br/>" .
				$signature;
		}

		return ($this->SendEmail($email, $subject, $body));
	}
	private function sendEmailRequest($ticketid, $email, $groupName, $type = TICKET_SUBSCRIBE) {

		$this->LOG(sprintf(COM_POSTMAN_SEND_SUBSCRIPTION_EMAIL, $email, $ticketid));

		$url            = JURI::root() . "component/index.php?option=com_postman&task=confirms&ticketid=$ticketid&tag=" . $this->getTag();
		$subject        = ConfigModel::get("subjectsubscribe", "Your subscription confirmation to $groupName");
		$subscriber     = $this->subscribers->findByEmail($email);
		$subscriberName = ($subscriber != null)? $subscriber->name : "";
		$signature      = ConfigModel::get("signature", "Web Team");

		$body = "Dear subscriber $subscriberName,<br/><br/>" . 
			"Recently, on $onDate you requested a subscription to the $groupName newsletter.<br/>".
			"Please click on the following <a href=\"$url\">link</a> to confirm your subscription!<br/><br/>" .
			"Sincerely yours, <br/><br/>" .
			$signature;

		return $this->SendEmail($email, $subject, $body);
	}
	public function subscribe() {
		//JSession::checkToken();
		//JSession::getFormToken();
		$this->LOG("Subscribe request ...");

		// validate session
		if (!JSession::checkToken()) {
			$this->redirect();
		}

		$groupId = JRequest::getInt("postman-group", -1);
		$email   = JRequest::getString("postman-email");
		$type    = JRequest::getString("postman-type");
		$tag     = JRequest::getString("postman-tag");

		if ($email == "")  return $this->redirect(COM_POSTMAN_MISSING_EMAIL);
		if ($groupId == 0) return $this->redirect(COM_POSTMAN_MISSING_GROUP);

		$email = mb_strtolower($email);
		$group = $this->groups->findById($groupId);

		$this->LOG(sprintf("Request for subscribing  %s on %s group", $email, $group->name));

		// validate email
		if (!$this->validate($email)) {
			$this->redirect(COM_POSTMAN_VALIDATE_EMAIL_ERROR);
		}

		// validate tag 
		$tag2 = $this->getTag();

		if ($tag !== $tag2) {
			$tagError = "Error subscribing: #7u87u";
			$this->LOG(sprintf("Tag element %s not expected %s, loging error %s", $tag, $tag2, $tagError));
			$this->redirect($tagError);
		}

		// validate existance of subscription
		$subscriber = $this->subscribers->findByGroupAndEmailName($groupId,$email);
		if ($subscriber != null) {
			if ($group) $this->LOG(sprintf("Subscriber %s exists in a group %s", $email, $group->name));
			//TODO: send the email to the subscriber saying that it's already added to the group.			
			$this->redirect();
		}

		// manage ticket
		if ($record = $this->tickets->findByGroupAndEmail($groupId, $email, TICKET_SUBSCRIBE)) {
			$this->LOG(sprintf(COM_POSTMAN_TICKET_EXISTS, $record->ticketid, $record->date));
			$this->sendEmailReminder($email, $record->ticketid, $record->date, TICKET_SUBSCRIBE);
		}else{
			$this->DEBUG("this->tickets->createTicket($groupId, $email, ". TICKET_SUBSCRIBE .")");
			if ($this->tickets->createTicket($groupId, $email, TICKET_SUBSCRIBE)) {
				$this->DEBUG(sprintf(COM_POSTMAN_TICKET_CREATED, $email));
				
				//find the ticket, send email, redirect
				$ticket = $this->tickets->findByGroupAndEmail($groupId, $email, TICKET_SUBSCRIBE);
				$this->sendEmailRequest($ticket->ticketid, $email, $group->name);
				$this->redirect(COM_POSTMAN_SUBSCRIPTION_OK);
			}else{
				$this->redirect(COM_POSTMAN_SUBSCRIPTION_ERROR);
			}
		}

		$this->redirect();
		// $view->display();
	}
	public function unsubscribe() {
		//JSession::checkToken();
		//JSession::getFormToken();
		$this->LOG("Unsubscribe request ...");

		//validate session
		if (!JSession::checkToken()) {
			$this->redirect();
		}

		$groupId = JRequest::getInt("postman-group", -1);
		$email   = JRequest::getString("postman-email");
		$type    = JRequest::getString("postman-type");
		$tag     = JRequest::getString("postman-tag");

		if ($email == "")  return $this->redirect(COM_POSTMAN_MISSING_EMAIL);
		if ($groupId == 0) return $this->redirect(COM_POSTMAN_MISSING_GROUP);

		$email = mb_strtolower($email);
		$group = $this->groups->findById($groupId);

		$this->LOG(sprintf("Request to unsubscribe  %s on %s", $email, $group->name));

		//validate email
		if (!$this->validate($email)) {
			$this->redirect(COM_POSTMAN_VALIDATE_EMAIL_ERROR);
		}

		//validate tag
		$tag2 = md5("postman");

		if ($tag !== $tag2) {
			$tagError = "Error unsubscribing: #7u87U.";
			$this->LOG(sprintf("Tag element %s not expected %s, loging error %s", $tag, $tag2, $tagError));
			$this->redirect($tagError);
		}

		// manage ticket
		if ($record = $this->tickets->findByGroupAndEmail($groupId, $email, TICKET_UNSUBSCRIBE)) {
			$this->LOG(sprintf(COM_POSTMAN_TICKET_EXISTS, $record->ticketid, $record->date));
			$this->sendEmailReminder($email, $record->ticketid, $record->date, TICKET_UNSUBSCRIBE);
		}else{
			$this->DEBUG("this->tickets->createTicket($groupId, $email, ". TICKET_UNSUBSCRIBE .")");
			if ($this->tickets->createTicket($groupId, $email, TICKET_SUBSCRIBE)) {
				$this->LOG(sprintf(COM_POSTMAN_TICKET_CREATED, $email));

				//find the ticket, send email, redirect
				$ticket = $this->tickets->findByGroupAndEmail($groupId, $email, TICKET_UNSUBSCRIBE);
				$this->sendEmailRequest($ticket->ticketid, $email, $group->name);
				$this->redirect(COM_POSTMAN_UNSUBSCRIPTION_OK);
			}else{
				$this->redirect(COM_POSTMAN_UNSUBSCRIPTION_ERROR);
			}
		}

		$this->redirect();
	}
	public function confirmSubscription() {
		$ticketId = JRequest::getString('ticketid', null);
		$tag      = JRequest::getString('tag', null);
		$type     = JRequest::getString("postman-type");
		//$tag     = JRequest::getString("postman-tag");

		$this->DEBUG("confirmSubscription($ticketId,$tag, $type)");

		if ($ticketId == null || $tag == null) {
			$this->redirect();
		}

		$this->LOG(sprintf("Subscription confirmation for the ticket %s", $ticketId));

		//create new subscriber if doesn't exists
		$ticket = $this->tickets->findTicket($ticketId);

		if ($ticket != null) {
			$this->DEBUG(sprintf("Looking for existing subscriber %s",$ticket->email));

			//find subscriber
			$subscriber = $this->subscribers->findByEmail($ticket->email);
			$group      = $this->groups->findById($ticket->newsgroup_id);

			if ($subscriber == null) {
				$this->subscribers->create($ticket->email, $ticket->email, true, 0);
				$subscriber = $this->subscribers->findByEmail($ticket->email);
				if ($subscriber == null) {
					$this->LOG("Subscriber not added, canceling confirmation process");
					$this->redirect();
				}				
				$this->LOG("Subscriber created ...");
			}else{
				$this->LOG("Subscriber alredy exists in the list");
			}

			if ($subscriber == null) {
				$this->LOG("Error mapping subscriber while confirming subscription");
				$this->redirect();
			}

			if ($group == null)     {
				$this->LOG("Error mapping group while confirming subscription");
				$this->redirect();
			}
			
			//subscribe to the group
			$this->groups->addSubscriber($ticket->newsgroup_id, $subscriber->subscriber_id);
			$this->LOG(sprintf("Adding subscriber %s to the group %s", $subscriber->email, $group->name));

			$this->tickets->delete($ticketId);
			$this->LOG("Removing ticket from the system");
		}else{
			$this->LOG("Ticket not found in the system $ticketId");
		}
		$this->redirect();
	}
	public function confirmCancelation() {
		echo "<h3>Thank you for using newsletter!</h3>";
	}
	public function unsubscribeEmailResponse() {
		echo "<h3>Unsubscribe</h3>";
	}
	public function confirms(){ 
		$this->confirmSubscription();
	}
	public function confirmc(){ 
		$this->confirmCancelation();
	}
}
?>