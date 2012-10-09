<div class="submenu-box">
	<div class="submenu-pad">
		<ul id="submenu" class="configuration">
			<li><a id="Newsletter"<?php echo ($task=="listNewsLetters") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listNewsLetters"><?php echo JText::_('Newsletters'); ?></a></li>
			<li><a id="Subscribers" <?php echo ($task=="listSubscribers") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listSubscribers"><?php echo JText::_('Subscribers'); ?></a></li>
			<li><a id="Groups" <?php echo ($task=="listGroups") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listGroups"><?php echo JText::_('Groups'); ?></a></li>
			<li><a id="Send" <?php echo ($task=="sendGroupNewsletterView") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=sendGroupNewsletter"><?php echo JText::_('Send'); ?></a></li>
			<li><a id="Logs" <?php echo ($task=="listLogs") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listLogs"><?php echo JText::_('Logs'); ?></a></li>
			<li><a id="Tickets" <?php echo ($task=="listTickets") ? " class=\"active\"": "";?> href="index.php?option=com_postman&task=listTickets"><?php echo JText::_('Tickets'); ?></a></li>
		</ul>
	<div class="clr"></div>
	</div>
</div>