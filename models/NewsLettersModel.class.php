<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."ProximityModel.class.php");

define('NEWSLETTERS_TALBE', '#__postman_newsletters');

final class NewsLettersModel extends ProximityModel {

	static $EMPTY_ARRAY = array();
	private $_dbPrefix;

	public function __construct() {
		parent::__construct();

		$config = JFactory::getConfig();
		$this->_dbPrefix = $config->getValue("config.dbprefix");
	}
	public function create($subject, $message, $published) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert(NEWSLETTERS_TALBE);
		$query->columns(array($db->quoteName('subject'),
			$db->quoteName('message'),
			$db->quoteName('published'),
			$db->quoteName('created'),
			$db->quoteName('checked_out'),
			$db->quoteName('checked_out_time')));

		$query->values(
			$db->quote($subject) .",".
			$db->quote($message) .",".
			$published .",".
			'NOW()' .",".
			'1' .",".
			'NOW()');
			
		// If the insert failed, exit the application.
		$db->setQuery((string) $query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}

		return $result;
	}
	public function update($letterId, $subject, $message, $published, $checkout = 0) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName(NEWSLETTERS_TALBE));
		$query->set($db->quoteName('subject').' = '.$db->quote($subject));
		$query->set($db->quoteName('message').' = '.$db->quote($message));
		$query->set($db->quoteName('published').' = '.$db->quote($published));
		$query->set($db->quoteName('checked_out').' = '.$db->quote($checkout));
		$query->where("letter_id = " .(int) $letterId);
		$db->setQuery((string)$query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function markAsSent($letterId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__postman_newsletters'));
		$query->set($db->quoteName('sent') . '=NOW()');
		$query->where("letter_id = " .(int) $letterId);
		$db->setQuery((string)$query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		return $result;
	}
	public function checkedOut($letterId, $checkout = 0) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName(NEWSLETTERS_TALBE));
		$query->set($db->quoteName('checked_out').' = '.$db->quote($checkout));
		$query->where("letter_id = " .(int) $letterId);
		$db->setQuery((string)$query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function delete($letterId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName(NEWSLETTERS_TALBE));
		$query->where("letter_id = " .(int) $letterId);
		$db->setQuery((string)$query);
		$result = $db->query();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new JException($db->getErrorMsg());
		}
		
		return $result;
	}
	public function findById($letterId) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(NEWSLETTERS_TALBE));
		$query->where("letter_id = " .(int) $letterId);
		$db->setQuery((string)$query);
		$record = $db->loadObject();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $record;
	}
	public function findBySubjectLike($subject) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(NEWSLETTERS_TALBE));
		$query->where("subject LIKE '$subject%'");
		$db->setQuery((string)$query);
		$record = $db->loadObject();
		
		//throw exception on error
		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $record;
	}
	public function findBySubject($subject) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(NEWSLETTERS_TALBE));
		$query->where("subject LIKE '$subject%'");
		$db->setQuery((string)$query);
		$records = $db->loadObjectList();

		if ($records == null) {
			$records = NewsLettersModel::$EMPTY_ARRAY;
		}
		
		if ($db->getErrorNum() > 0) {
			throw new JException($db->getErrorMsg());
		}

		return $records;
	}
	public function getAll() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(NEWSLETTERS_TALBE));
		$db->setQuery((string)$query);
		$records = $db->loadObjectList();
		
		if ($records == null) {
			$records = NewsLettersModel::$EMPTY_ARRAY;
		}

		if ($db->getErrorNum()) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
	
		return $records;
	}
	public function count() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName(NEWSLETTERS_TALBE));
		$db->setQuery((string)$query);
		//return $db->loadObject();
		$result = $db->loadResult();
		
		if ($db->getErrorNum() > 0) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $result;
	}
	public function getBlockwise($offset, $length) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName(NEWSLETTERS_TALBE));
		$db->setQuery((string) $query, $offset, $length);
		$records = $db->loadObjectList();

		if ($records == null) {
			$records = NewsLettersModel::$EMPTY_ARRAY;
		}
		
		if ($db->getErrorNum() > 0) {
			throw new ErrorException($db->getErrorMsg(), $db->getErrorNum(), 1, __FILE__, __LINE__, null);
		}
		
		return $records;
	}
	public function isEmailHtml($emailBody) {
		//TODO: newsletter are saved as html, this will be different only if WYIWYG editor is not used.
		return true;
	}
	public function getPreviewEmail($letterId = null) {

		if ($letterId == 0) {
			throw new ErrorException("Newsletter id must be assigned to preview email!", 1, 1, __FILE__, __LINE__, null);
		}

		$letter = $this->findById($letterId);
		
		if ($letter == null) {
			throw new ErrorException("Newsletter not found!!", 1, 1, __FILE__, __LINE__, null);
		}

		$emailbody = $letter->message;

		if (trim($emailbody) == "") {
			throw new ErrorException("Newsletter has no text!", 1, 1, __FILE__, __LINE__, null);
		}

		$patterns = array();
		$replacements = array();
		$i = 0;
		$src_exp = "/src=\"(.*?)\"/";
		$link_exp =  "[^http:\/\/www\.|^www\.]";

		if (!$this->isEmailHtml($emailbody)) {
			return $emailbody;
		}
/*
		preg_match_all($src_exp, $emailbody, $images, PREG_SET_ORDER);

		foreach ($images as $img)
		{
			$links = preg_match($link_exp, $img[1], $match, PREG_OFFSET_CAPTURE);
			if(!$links)
			{
				$patterns[$i] = $img[1];
				$replacements[$i] = JURI::root() .DS. $img[1];
			}
			$i++;
		}

		if (count($replacements) > 0) {

			return str_replace($patterns,$replacements,$emailbody);
		}
*/
		return $emailbody;
	}
	// functon to covert image tags from relative to absolute paths
	// TODO: POSTMAN: Fails if image is already an aboslute path add "checkIsApsolutePath"
	public function embedImages(&$emailbody, $mailer)
	{
		$mod_html_content=null;
		$patterns = array();
		$replacements = array();
		$i = 0;
		$src_exp = "/src=\"(.*?)\"/";
		$link_exp =  "[^http:\/\/.|^www\.]";

		if (!$this->isEmailHtml($emailbody)) return false;

		preg_match_all($src_exp, $emailbody, $images, PREG_SET_ORDER);

		$linkSrc = "";
		foreach ($images as $img)
		{
			$links = preg_match($link_exp, $img[1], $match, PREG_OFFSET_CAPTURE);

			if(!$links && $fullLink)
			{
				$path = JPATH_SITE .DS. trim($img[1]);
				$cid  = md5($path);

				// Append file  with $path and $cid
				if ($mailer->AddEmbeddedImage($path, $cid)) {
					$patterns[$i] = $linkSrc;
					$replacements[$i] = "cid:{$cid}";
					//JError::raiseNotice(100, "path: {$path}");
				}else{
					$name = basename($img[1]);
					JError::raiseNotice(100, "Can't embed file \"{$img[1]}\".");
					JError::raiseNotice(100, "Error: {$mailer->ErrorInfo}");
				}

			}else{
				$linkSrc = $img[1];
			}
			$i++;
		}

		if (count($replacements) > 0) {
			$emailbody = str_replace($patterns,$replacements,$emailbody);
			return true;
		}

		return false;
	}

}

?>