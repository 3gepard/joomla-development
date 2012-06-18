<?php
/**
 * @package		Postman
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");

//jimport("joomla.html.toolbar");
jimport("joomla.component.toolbarhelper");

final class ListGroupsView implements IProximityView {

	private $_groups;
	private $_page;

	public function __construct() {

	}

	public function display(array $data = null) {

		$listTitle = "Manage Groups";

		// buttons controller methods
		$newButton = "editGroup";
		$deleteListCmd = "removeGroup";
		
		// laded menu items from file
		require_once(dirname(__FILE__) .DS. 'ComponentMenuItemsWrapper.php');

		$groups = $this->getGroups();
		$groupsCount = count($groups);
		$page = $this->getPage();

		echo <<< HTML
		<form name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
        		<thead>
        			<tr>
        				<th width="4%" class="title">#</th>
        				<th width="4%" class="title"><input type="checkbox" name="toggle" onclick="checkAll({$groupsCount})"/></th>
						<th width="27%" class="title">Group</th>
						<th class="title">Description</th>
						<th width="15%" class="title">Created</th>
        			</tr>
        		</thead>
        		<tbody>
HTML;
		$row = 0;
		for($i = 0; $i < $groupsCount; $i++) {

			$group = $groups[$i];
			$row ^= 1;
			$no = $i + 1;
			$link = "<a href=\"index.php?option=com_postman&task=editGroup&groupId={$group->newsgroup_id}\">{$group->name}</a>";
			$checked = JHTML::_('grid.checkedout', $group, $i, "newsgroup_id");

// 			$published = JHtml::_('jgrid.published', $group->published, $i, '', '', 'cb', $group->published);
			echo <<< HTML
				<tr class="row{$row}">
					<td align="center">{$no}</td>
					<td align="center">{$checked}</td>
					<td>{$link}</td>
					<td>{$group->description}</td>
					<td align="center">{$group->creation_date}</td>
				</tr>
			</tbody>
HTML;
		}
		echo <<< HTML
			<tfoot>
				<tr><td colspan="6">{$page->getListFooter()}</td></tr>
			</tfoot>
        </table>
		<input type="hidden" name="task" value="listGroups"/>
		<input type="hidden" name="boxchecked" value="0" />
		</form>
HTML;
	}

	public function setGroups($groups) {
		$this->_groups = $groups;
	}

	public function getGroups() {
		return $this->_groups;
	}

	public function getPage() {
		return $this->_page;
	}

	public function setPage($page) {
		$this->_page = $page;
	}
}
?>
