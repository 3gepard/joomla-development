<?php
/**
 * @package		Postman
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT.DS."controllers".DS."Controller.class.php");

$controller = new Controller();
$task = JRequest::getCmd("task", "default");
$controller->handle($task);
?>
