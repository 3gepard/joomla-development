<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");

final class NewsLetterPreview implements IProximityView {

	private $_content;

	public function display(array $data = null) {
		
		echo $this->_content;	
	}

	public function setContent($content) {
		$this->_content = $content;
	}
}
?>