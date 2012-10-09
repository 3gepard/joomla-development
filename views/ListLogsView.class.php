<?php
/**
 * @package		Postman
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");
jimport("joomla.html.toolbar");

final class ListLogsView implements IProximityView {

	private $_logs;
	private $_filter;
	private $_fromDate;
	private $_toDate;

	public function display(array $data = null) {

		$logs = $this->getLogs();
		$logCount = count($logs);

		JToolBarHelper::title(JText::_("Postman - Logs"), "generic.png");
		JToolBarHelper::custom("emptyLog","trash", " ", "Empty Log", false);
		JToolBarHelper::cancel("listGroups", "Close");
		JToolBarHelper::spacer();
		JToolBarHelper::preferences("com_postman");
		JToolBarHelper::help("screen.postman");

		ob_start();
		$task = JRequest::getCmd("task", "");
		$contents = ob_get_contents();
		ob_end_clean();

		// Set document data
		$document = JFactory::getDocument();
		$document->setBuffer($contents, 'modules', 'submenu');

		echo <<< HTML

		<script language="JavaScript">
		<!--
			function reloadGroupSendView() {
	
				document.forms['adminForm'].getElementById('task').value = 'listLogs';
				document.forms['adminForm'].submit();
			}
		
			Joomla.submitbutton = function(task) {
				if (task == 'listGroups' || document.formvalidator.isValid(document.id('logs-form'))) {
					Joomla.submitform(task, document.getElementById('logs-form'));
				}elseif(task == 'emptyLog' || document.formvalidator.isValid(document.id('logs-form'))) {
					Joomla.submitform(task, document.getElementById('logs-form'));
				};
			}
		-->
		</script>
		<form id="logs-form" name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
        		<thead>
					<input id="task" type="hidden" name="task" />
					<input type="hidden" name="boxchecked" value="0" />
					<tr>
						<th width="5%" class="title">#</th>
						<th width="30%" class="title">Date</th>
						<th width="65%" class="title">Description</th>
					</tr>
					<tr>
HTML;
						$row = 0;

						for($i = 0; $i < $logCount; $i++) {

							$log = $logs[$i];

							$no = $i + 1;
							$row ^= 1;

							echo "<tr class=\"row{$row}\">
								<td align=\"center\">{$no}</td>
								<td align=\"center\">{$log->time_stamp}</td>
								<td>{$log->message}</td></tr>";
						}
						echo <<< HTML

									</tbody>
								</table>
							</div>
						</td>
						<td>
						</td>
					</tr>
				</thead>
			</table>
		</form>
HTML;
	}

	public function setLogs($logs) {
		$this->_logs = $logs;
	}

	public function getLogs() {
		return $this->_logs;
	}

	public function getSelectedLetterId() {
		return $this->_letterId;
	}
}
?>