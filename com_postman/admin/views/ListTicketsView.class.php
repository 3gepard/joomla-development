<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport("mvc3gepard.proximityview");

final class ListTicketsView implements IProximityView {

	private $_tickets;
	private $_page;

	public function display(array $data = null) {
		//JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');

		$listTitle = "Manage Tickets";
		$newButton = "editTicket";
		$deleteListCmd = "removeTicket";

		// laded menu items from file
		require_once(dirname(__FILE__) .DS. 'ComponentMenuItemsWrapper.php');

		$tickets = $this->getTickets();
		$ticketCount = count($tickets);
		$page = $this->getPage();

		$token = JHtml::_('form.token');
		//$tokenSpc = '&amp;'.JSession::getFormToken().'=1';

		echo <<< HTML
		<form name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
				<thead>
					<tr>
        				<th width="4%" class="title">#</th>
        				<th width="4%" class="title"><input type="checkbox" name="toggle" onclick="checkAll({$ticketCount})"/></th>
						<th width="15%" class="title">Ticket</th>
						<th width="5%"  class="title">Type</th>
						<th class="title">Email</th>
						<th width="10%" class="title">Date</th>
        			</tr>
        		</thead>
        		<tbody>
HTML;
		$row = 0;
		for($i = 0; $i < $ticketCount; $i++) {
			$ticket = $tickets[$i];
			$row ^= 1;
			$no = $i + 1;

			$checked = JHTML::_('grid.checkedout', $ticket, $i, "ticketid");
			$link = "<a href=\"index.php?option=com_postman&task=editTicket&subscriberId={$ticket->ticketid}\">{$ticket->email}</a>";
			switch ($ticket->type) {
				case TICKET_CONFIRMED:
						$type = 'Confirmed';
						break;
				case TICKET_UNSUBSCRIBE:
						$type = 'Unsubscribe';
						break; 
				case TICKET_SUBSCRIBE:
						$type = 'Subscribe';
						break;
				default:
						$type = 'Subscribe';
						break;
			}

			echo <<< HTML
				<tr class="row{$row}">
					<td align="center">{$no}</td>
					<td align="center">{$checked}</td>
					<td align="center">{$ticket->ticketid}</td>
					<td><center>$type</center></td>
					<td>{$ticket->email}</td>
					<td><center>{$ticket->date}</center></td>
				</tr>
HTML;
		}
		echo <<< HTML
        		</tbody>
				<tfoot><tr><td colspan="6">{$page->getListFooter()}</td></tr></tfoot>
			</table>
			<input type="hidden" name="task" value="listTickets" />
			<input type="hidden" name="boxchecked" value="0" />
			{$token}
		</form>
HTML;
	}

	public function setTickets($tickets) {
		$this->_tickets = $tickets;
	}

	public function getTickets() {
		return $this->_tickets;
	}

	public function setPage($page) {
		$this->_page = $page;
	}

	public function getPage() {
		return $this->_page;
	}
}

/*
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticketid` varchar(100) NOT NULL DEFAULT '',
    `newsgroup_id` int(11) unsigned NOT NULL, 
    `email` varchar(100) NOT NULL DEFAULT '',
	`date` timestamp NOT NULL default CURRENT_TIMESTAMP,
	`type` tinyint(1) NOT NULL default '1',
 */

?>