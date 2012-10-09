<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport("mvc3gepard.proximityview");

final class ListGroupsView implements IProximityView {

	private $_groups;
	private $_page;

	public function display(array $data = null) {
		//JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');

		$listTitle = "Manage Groups";
		$newButton = "editGroup";
		$deleteListCmd = "removeGroup";
		
		// laded menu items from file
		require_once(dirname(__FILE__) .DS. 'ComponentMenuItemsWrapper.php');

		$groups = $this->getGroups();
		$groupsCount = count($groups);
		$page = $this->getPage();

		$token = JHtml::_('form.token');
		//$tokenSpc = '&amp;'.JSession::getFormToken().'=1';

		echo <<< HTML
		<form name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
        		<thead>
        			<tr>
        				<th width="4%" class="title">#</th>
        				<th width="4%" class="title"><input type="checkbox" name="toggle" onclick="checkAll({$groupsCount})"/></th>
						<th width="27%" class="title">Group</th>
						<th width="5%"  class="title">Public</th>
						<th class="title">Description</th>
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
 			$state = ($group->public) ? 'publish' : 'unpublish';
 			$public = ($group->public) ? 'Public (listed on subscription form)' : 'Private (not shown on subscription form)';

 			echo <<< HTML
				<tr class="row{$row}">
					<td align="center">{$no}</td>
					<td align="center">{$checked}</td>
					<td>{$link}</td>
					<td><center><a class="jgrid" title="{$public}"><span class="state {$state}"><span class="text">{$public}</span></span></a></center></td>
					<td>{$group->description}</td>
				</tr>
HTML;
		}
		echo <<< HTML
        		</tbody>
			<tfoot>
				<tr><td colspan="5">{$page->getListFooter()}</td></tr>
			</tfoot>
        </table>
		<input type="hidden" name="task" value="listGroups" />
		<input type="hidden" name="boxchecked" value="0" />
		{$token}
		</form>
HTML;
	}

	public function setGroups($groups) {
		$this->_groups = $groups;
	}

	public function getGroups() {
		return $this->_groups;
	}

	public function setPage($page) {
		$this->_page = $page;
	}

	public function getPage() {
		return $this->_page;
	}
}
?>