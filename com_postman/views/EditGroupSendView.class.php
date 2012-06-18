<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport("joomla.html.toolbar");

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");



final class EditGroupSendView implements IProximityView {

	private $_groups;
	private $_letterId;
	private $_letters;

	public function display(array $data = null) {

		echo <<< HTML
		<script language="JavaScript">
		<!--
		function getSelectedLetterId() {

			return document.forms['adminForm'].getElementById('letterId').value;
		}
		-->
		</script>
HTML;

		$groups = $this->getGroups();
		$groupsCount = count($groups);
		$letterId = $this->getSelectedLetterId();
		$letters = $this->getLetters();

		JToolBarHelper::title(JText::_("Postman - Send Group Email"), "generic.png");
		JToolBarHelper::preview("index.php?option=com_postman&type=letter&letterId=$letterId&", "Preview Mail");
		JToolBarHelper::apply("sendEmails","Send mail");
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
	
				document.forms['adminForm'].getElementById('task').value = 'editGroupSend';
				document.forms['adminForm'].submit();
			}

			Joomla.submitbutton = function(task) {
				if (task == 'listGroups' || document.formvalidator.isValid(document.id('send-form'))) {
					Joomla.submitform(task, document.getElementById('send-form'));
				}
			}
		</script>
		<form id="send-form" name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
        		<thead>
					<input id="task" type="hidden" name="task" />
					<input type="hidden" name="boxchecked" value="0" />
					<tr>
						<th width="10%" class="title">Newsletter:</th>
						<td width="40%">
							<select id="letterId" name="letterId" onchange="javascript:reloadGroupSendView()">
HTML;
								$lettersCount = count($letters);
								$selectedLetter = null;

								for ($i = 0; $i < $lettersCount; $i++) {

									$letter = $letters[$i];

									$selected = "";

									if ($letter->letter_id == $letterId) {
										$selected = "selected=\"selected\"";
										$selectedLetter = $letter;
									}

									echo "<option {$selected} value=\"{$letter->letter_id}\">{$letter->subject}</option>";
								}

								echo <<< HTML
							</select>
						</td>
					</tr>
					<tr>
						<th width="10%" class="title">Groups</th>
						<td width="40%>
							<div style="border : solid 1px; height: 200px; overflow: auto;">
								<table class="adminlist">
									<thead>
										<tr>
											<th width="5%" class="title">#</th>
											<th width="5%" class="title"><input type="checkbox" name="toggle" onclick="checkAll({$groupsCount})"/></th>
											<th width="20%" class="title">Group</th>
										</tr>
									</thead>
									<tbody>
HTML;
									$row = 0;

									for($i = 0; $i < $groupsCount; $i++) {

										$group = $groups[$i];

										$checkbox = "<input type=\"checkbox\" id=\"cb{$i}\" name=\"cid[]\" value=\"{$group->newsgroup_id}\" onclick=\"isChecked(this.checked);\" />";
										$no = $i + 1;
										$row ^= 1;

										echo "<tr class=\"row{$row}\">
											<td align=\"center\">{$no}</td>
											<td align=\"center\">{$checkbox}</td>
											<td>{$group->name}</td></tr>";
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

	public function setGroups($groups) {
		$this->_groups = $groups;
	}

	public function getGroups() {
		return $this->_groups;
	}

	public function setSelectedLetterId($letterId) {
		$this->_letterId = $letterId;
	}

	public function getSelectedLetterId() {
		return $this->_letterId;
	}

	public function setLetters($letters) {
		$this->_letters = $letters;
	}

	public function getLetters() {
		return $this->_letters;
	}

}
?>