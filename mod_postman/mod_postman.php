<?php
/**
 * @package		Postman
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."models".DS."NewsGroupsModel.class.php");

$divstyle = $params->get("divstyle");
$labelstyle	= $params->get("labelstyle");
$inputstyle	= $params->get("inputstyle");
$selectstyle = $params->get("selectstyle");
$buttonstyle = $params->get("buttonstyle");

$model = new NewsGroupsModel();
$groups = $model->getPublicGroups();

echo <<< HTML
	<div id="postman" style="{$divstyle}">
		<form name="inputForm" action="administrator/index`.php?option=com_postman&task=subscribeToNewsletter" method="post">
			<label id="lbEmail" style="{$labelstyle}">Email&nbsp;:</label><input id="email" type="text" name="email" style="{$inputstyle}" /><br/>
HTML;
			echo <<< HTML

			<label id="groups" style="{$labelstyle}">Newsletter&nbsp;:</label>
			<select name="group" style="{$selectstyle}">
HTML;
			$i = 0;
			foreach ($groups as $group) {
				$selected = ($i > 0) ? '' : 'selected';
				echo <<< HTML

				<option "{$selected} value="{$group->newsgroup_id}">{$group->name}</option>
HTML;
				$i++;
			}
			echo <<< HTML
			</select>
HTML;

			$token = JHtml::_('form.token');

echo <<< HTML
			<input id="submit" type="submit" value="Subscribe" style="{$buttonstyle}" /></td>
			{$token} 
		</form>
	</div>
HTML;

?>