<div class="submenu-box">
	<div class="submenu-pad">
		<ul id="submenu" class="configuration">
			<li><a id="Newsletter"<?php echo ($task=="listNewsLetters") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listNewsLetters"><?php echo JText::_('Newsletters'); ?></a></li>
			<li><a id="Subscribers" <?php echo ($task=="listSubscribers") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listSubscribers"><?php echo JText::_('Subscribers'); ?></a></li>
			<li><a id="Groups" <?php echo ($task=="listGroups") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listGroups"><?php echo JText::_('Groups'); ?></a></li>
			<li><a id="Send" <?php echo ($task=="editGroupSend") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=editGroupSend"><?php echo JText::_('Send'); ?></a></li>
			<li><a id="Logs" <?php echo ($task=="listLogs") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listLogs"><?php echo JText::_('Logs'); ?></a></li>
		</ul>
	<div class="clr"></div>
	</div>
</div>