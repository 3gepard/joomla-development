<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");

//jimport("joomla.html.toolbar");
jimport("joomla.component.toolbarhelper");

final class ListSubscribersView implements IProximityView {

	private $_subscribers;
	private $_page;

	public function display(array $data = null) {
		jimport("joomla.html.toolbar");
		jimport("joomla.html.editor");

		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');

		$listTitle = "Manage Subscribers";
		$newButton = "editSubscriber";
		$deleteListCmd = "removeSubscriber";

		// laded menu items from file
		require_once(dirname(__FILE__) .DS. 'ComponentMenuItemsWrapper.php');

		$subscribers = $this->getSubscribers();
		$subscribersCount = count($subscribers);
		$page = $this->getPage();
		
		$token = JHtml::_('form.token');

		echo <<< HTML
		<form name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
				<thead>
					<tr>
        				<th width="4%" class="title">#</th>
        				<th width="4%" class="title"><input type="checkbox" name="toggle" onclick="checkAll({$subscribersCount})"/></th>
						<th width="27%" class="title">Name</th>
						<th width="5%"  class="title">Confirmed</th>
						<th class="title">Email</th>
						<th width="15%" class="title">Subscribe date</th>
        			</tr>
        		</thead>
        		<tbody>
HTML;
		$row = 0;

		for($i = 0; $i < $subscribersCount; $i++) {

			$subscriber = $subscribers[$i];

			$row ^= 1;
			$link = "<a href=\"index.php?option=com_postman&task=editSubscriber&subscriberId={$subscriber->subscriber_id}\">{$subscriber->name}</a>";
			$checked = JHTML::_('grid.checkedout', $subscriber, $i, "subscriber_id");
			$no = $i + 1;
			
			$confirmed = JHtml::_('jgrid.published', $subscriber->confirmed, $i, '', '', 'cb', $subscriber->confirmed);

			echo <<< HTML
				<tr class="row{$row}">
					<td align="center">{$no}</td>
					<td align="center">{$checked}</td>
					<td>{$link}</td>
					<td><center>$confirmed</center></td>
					<td>{$subscriber->email}</td>
					<td><center>{$subscriber->subscribe_date}</center></td>
				</tr>
			</tbody>
HTML;
		}
		echo <<< HTML
			<tfoot>
				<tr><td colspan="7">{$page->getListFooter()}</td></tr>
			</tfoot>
        </table>
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" />
		{$token}
		</form>
HTML;
	}

	public function setSubscribers($subscribers) {
		$this->_subscribers = $subscribers;
	}

	public function getSubscribers() {
		return $this->_subscribers;
	}

	public function setPage($page) {
		$this->_page = $page;
	}

	public function getPage() {
		return $this->_page;
	}
}
?>