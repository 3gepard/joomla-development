<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");

jimport("joomla.component.toolbarhelper");

final class EditGroupView implements IProximityView {
	
	private $_group;
	private $_groupSubscribers;
	private $_page;
	
	function display(array $data = null) { 
		jimport("joomla.html.toolbar");
		jimport("joomla.html.editor");
		
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');

		JToolBarHelper::title(JText::_("Postman - Edit Group"), "generic.png");

		JToolBarHelper::apply('applyGroup');
		JToolBarHelper::save("saveAndCloseGroup");
		JToolBarHelper::cancel("listGroups","Close");
		JToolBarHelper::help("screen.postman");

		$group = $this->getGroup();
		
		$groupId = -1;
		$name = "";
		$description = "";
		$public = 0;
		$creationDate = date("d.m.Y", time());
		
		if ($group != null) {
			$groupId = $group->newsgroup_id;
			$name = $group->name;
			$description = $group->description;
			$public = ($group->public) ? "checked" : "";
			$creationDate = $group->creation_date;
		}
		
		$subscribers = $this->getSubscribers();
		$subscribersCount = count($subscribers);
		$page = $this->getPage();
		
		echo <<< HTML

		<script type="text/javascript">
			Joomla.submitbutton = function(task) {
				if (task == 'listGroups' || document.formvalidator.isValid(document.id('group-form'))) {
					Joomla.submitform(task, document.getElementById('group-form'));
				}
			}
		</script>
		<form id="group-form"  name="adminForm" action="index.php?option=com_postman" method="post">
			<input type="hidden" name="task" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="groupId" value="{$groupId}" />
			<div class="width-60 fieldset">
				<fieldset class="adminform">
					<ul class="adminlist adminformlist">
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip required" for="jform_name" id="jform_name-lbl">Name<span class="star">&nbsp;*</span></label>
							<input type="text" size="40" class="inputbox required" value="{$name}" id="jform_name" name="name" aria-required="true" required="required" />
						</li>
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip" for="jform_description" id="jform_description-lbl">Description</label>
							<input type="text" size="40" class="inputbox required" value="{$description}" id="jform_description" name="description" aria-required="true" required="required" />
						</li>
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip" for="jform_public" id="jform_creationdate-lbl">Public</label>
							<input type="checkbox" $public checked="{$public}" name="public" id="jform_public">
						</li>
						
						</li>
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip" for="jform_creationdate" id="jform_creationdate-lbl">Creation Date</label>
							<span title="" cass="" >{$creationDate}</span>
						</li>
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip" for="jform_subsribers" id="jform_subsribers-lbl">Subsribers</label>
							<table class="adminlist" style="width: 300px;">
							<thead>
								<tr>
									<th width="10%" class="title">#</th>
									<th width="5%" class="title">
										<input type="checkbox" name="toggle" onclick="checkAll({$subscribersCount})" />
									</th>
									<th width="15%" class="title">Name</th>
									<th width="15%" class="title">Email</th>
								</tr>
							</thead>
							<tbody>
HTML;
		
						$row = 0;
						
						for ($i = 0; $i < $subscribersCount; $i++) {
							
							$row ^= 1;
							$subscriber = $subscribers[$i];
							$checked = "checked";
							$checkbox = "<input type=\"checkbox\" checked id=\"cb{$i}\" name=\"cid[]\" value=\"{$subscriber->subscriber_id}\" onclick=\"isChecked(this.checked);\" />";
							
							$no =$i + 1;
	
							echo "<tr class=\"row{$row}\">
								<td>{$no}</td>
								<td>{$checkbox}</td>
								<td>{$subscriber->name}</td>
								<td>{$subscriber->email}</td></tr>";

						}
						
			echo <<< HTML
							</tbody>
							<tfoot>
								<tr><td colspan="5">
								{$page->getListFooter()}
								<tr></td>
							</tfoot>
					</table>
					</td></tr>
        		</tbody>
			</table>
		</form>
HTML;
	}
	
	public function setGroup($group) {
		$this->_group = $group;
	}
	
	public function getGroup() {
		return $this->_group;
	}
	
	public function getSubscribers() {
		return $this->_groupSubscribers;
	}
	
	public function setSubscribers($groupSubscribers) {
		$this->_groupSubscribers = $groupSubscribers;
	}
	
	public function setPage($page) {
		$this->_page = $page;
	}
	
	public function getPage() {
		return $this->_page;
	}
}

?>