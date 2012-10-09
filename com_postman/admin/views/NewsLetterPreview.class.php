<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport("mvc3gepard.proximityview");

final class NewsletterPreview implements IProximityView {

	private $_content;

	public function display(array $data = null) {
		
		echo $this->_content;	
	}

	public function setContent($content) {
		$this->_content = $content;
	}
}
?>