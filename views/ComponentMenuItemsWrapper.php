<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
// customised for com_postman, copied from view com_config/views/application/view.php
	JToolBarHelper::title(JText::_("Postman - $listTitle"), "generic.png");

	JToolBarHelper::addNewX($newButton);
	JToolBarHelper::editList($newButton);
	JToolBarHelper::deleteList("Delete the selected item(s)?", "$deleteListCmd");
	//JToolBarHelper::custom('showMainMenu', 'preview.png', 'preview_f2.png', 'Main menu', false, false);
	JToolBarHelper::spacer();
	JToolBarHelper::preferences("com_postman");
	JToolBarHelper::help("screen.postman");

	ob_start();
	$task = JRequest::getCmd("task", "");
	
	// laded menu items from file
	require_once(dirname(__FILE__) .DS. 'ComponentMenuItems.php');

	$contents = ob_get_contents();
	ob_end_clean();

	// Set document data
	$document = JFactory::getDocument();
	$document->setBuffer($contents, 'modules', 'submenu');
?>