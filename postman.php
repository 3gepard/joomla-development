<?php
/**
 * @package		Postman
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_postman')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."controllers".DS."AdminController.class.php");
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."utils".DS."MacroReplacer.class.php");

$macroReplacer = new MacroReplacer();
$controller = new AdminController();
$controller->setMacroReplacer($macroReplacer);
$task = JRequest::getCmd("task", "listNewsletters");
$controller->handle($task);
?>