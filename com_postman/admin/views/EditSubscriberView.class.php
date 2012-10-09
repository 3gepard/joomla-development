<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport("mvc3gepard.proximityview");
jimport("joomla.component.toolbarhelper");

final class EditSubscriberView implements IProximityView {

	private $_subscriber;
	private $_allGroups;
	private $_subscribedGroupIds;

	function display(array $data = null) {
		jimport("joomla.html.toolbar");
		jimport("joomla.html.editor");
		
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');

		JToolBarHelper::title(JText::_("Postman - Subscriber"), "generic.png");
		JToolBarHelper::apply("applySubscriber");
		JToolBarHelper::save("saveAndCloseSubscriber");
		JToolBarHelper::cancel("listSubscribers","Close");
		JToolBarHelper::help("screen.postman");

		$subscriber = $this->getSubscriber();
		$allGroups = $this->getAllGroups();
		$subscribedIds = $this->getSubscribedGroupIds();
		$allGroupsCount = count($allGroups);

		$subscriber_id    = "-1";
		$subscriber_name  = '';
		$subscriber_email = '';
		$subscriber_date  = date("d.m.Y", time());
		$checked = "";

		if ($subscriber != null) {
			$subscriber_id    = $subscriber->subscriber_id;
			$subscriber_name  = $subscriber->name;
			$subscriber_email = $subscriber->email;
			$subscriber_date  = $subscriber->subscribe_date;
			$checked = ($subscriber->active) ? "checked":"";
			$action = JRoute::_('index.php?option=com_postman&layout=edit&id=' . (int) $subscriber->subscriber_id);
		}
		
		$token = JHtml::_('form.token');

		echo <<< HTML

		<script type="text/javascript">
			Joomla.submitbutton = function(task) {
				if (task == 'listSubscribers' || document.formvalidator.isValid(document.id('subscriber-form'))) {
					Joomla.submitform(task, document.getElementById('subscriber-form'));
				}
			}
		</script>
		<form action="index.php?option=com_postman" method="post" name="adminForm" id="subscriber-form" class="form-validate">
		    <input type="hidden" name="subscriberId" value="{$subscriber_id}">
			<input type="hidden" name="task" />
			<input type="hidden" name="boxchecked" value="0" />
			{$token}
			<div class="width-60 fieldset">
				<fieldset class="adminform">
					<ul class="adminlist adminformlist">
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip required" for="jform_name" id="jform_name-lbl">Name<span class="star">&nbsp;*</span></label>
							<input type="text" size="40" class="inputbox required" value="{$subscriber_name}" id="jform_name" name="name" aria-required="true" required="required">
						</li>
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip required" for="jform_email" id="jform_email-lbl">Email<span class="star">&nbsp;*</span></label>
							<input type="text" size="40" class="inputbox validate-email required" value="{$subscriber_email}" id="jform_email" name="email" aria-required="true" required="required">
						</li>
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip" for="jform_active" id="jform_active-lbl">Active</label>
							<input id="cb0" name="active" type="checkbox" {$checked} />
						</li>
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip" for="jform_groups" id="jform_groups-lbl">Subscribers</label>
							<table class="adminlist" style="width:auto;">
							<tbody>
HTML;

					$row = 0;

					for($i = 0; $i < $allGroupsCount; $i++) {

						$group = $allGroups[$i];

						$checked = "";

						if ($this->isMemberOfGroup($group)) {
							$checked = "checked";
						}

						$checkbox = "<input type=\"checkbox\" $checked id=\"cb{$i}\" name=\"cid[]\" value=\"{$group->newsgroup_id}\" onclick=\"isChecked(this.checked);\" />";

						$rowNo = $i + 1;
						$row ^= 1;

						echo "<tr class=\"row{$row}\">
							<td width=\"10%\" align=\"center\">{$rowNo}</td>
							<td width=\"10%\" align=\"center\">{$checkbox}</td>
							<td width=\"25%\">{$group->name}</td></tr>";
					}
		echo <<< HTML
							</tbody>
						</table></div>
						</li>
					</ul>
				</fiedset>
			</div>
		</form>
HTML;

	}

	private function isMemberOfGroup($group) {

		$subscribedGroupIds = $this->getSubscribedGroupIds();

		$groupIdsCount = count($subscribedGroupIds);

		for($i = 0; $i < $groupIdsCount; $i++) {

			$subscribedGroupId = $subscribedGroupIds[$i];

			if ($group->newsgroup_id == $subscribedGroupId) {
				return true;
			}
		}

		return false;
	}

	public function setSubscriber($subscriber) {
		$this->_subscriber = $subscriber;
	}

	public function getSubscriber(){
		return $this->_subscriber;
	}

	public function getSubscribedGroupIds() {
		return $this->_subscribedGroupIds;
	}

	public function setSubscribedGroupIds($subscribedGroups) {
		$this->_subscribedGroupIds = $subscribedGroups;
	}

	public function getAllGroups() {
		return $this->_allGroups;
	}

	public function setAllGroups($allGroups) {
		return $this->_allGroups = $allGroups;
	}
}
?>