<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport("mvc3gepard.proximityview");

final class ListSubscribersView implements IProximityView {

	private $_subscribers;
	private $_page;

	public function display(array $data = null) {
		//JHtml::_('behavior.tooltip');
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
		//$tokenSpc = '&amp;'.JSession::getFormToken().'=1';
		

		echo <<< HTML
		<form name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
				<thead>
					<tr>
        				<th width="4%" class="title">#</th>
        				<th width="4%" class="title"><input type="checkbox" name="toggle" onclick="checkAll({$subscribersCount})"/></th>
						<th width="27%" class="title">Name</th>
						<th width="5%"  class="title">Active</th>
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
			$no = $i + 1;

			$link = "<a href=\"index.php?option=com_postman&task=editSubscriber&subscriberId={$subscriber->subscriber_id}\">{$subscriber->name}</a>";
			$checked = JHTML::_('grid.checkedout', $subscriber, $i, "subscriber_id");
			//$active = JHtml::_('jgrid.published', $subscriber->active, $i, '', '', 'cb', $subscriber->active);
			$active = JHtml::_('jgrid.published', $subscriber->active, $i, '', '', 'cb', $subscriber->active);
			echo <<< HTML
				<tr class="row{$row}">
					<td align="center">{$no}</td>
					<td align="center">{$checked}</td>
					<td>{$link}</td>
					<td><center>$active</center></td>
					<td>{$subscriber->email}</td>
					<td><center>{$subscriber->subscribe_date}</center></td>
				</tr>
HTML;
		}
		echo <<< HTML
        		</tbody>
				<tfoot><tr><td colspan="6">{$page->getListFooter()}</td></tr></tfoot>
			</table>
			<input type="hidden" name="task" value="listSubscribers" />
			<input type="hidden" name="boxchecked" value="0" />
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