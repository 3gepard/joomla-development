<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");
jimport("joomla.component.toolbarhelper");

final class MainMenuView implements IProximityView {

	private function quickiconButton( $link, $image, $text )
	{
		// TODO: Check out: administrator/modules/mod_quickicon
		global $mainframe;
		$app        = JFactory::getApplication();
		$lang		= JFactory::getLanguage();
		$template	= $app->getTemplate();
		?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $link; ?>">
					<?php echo JHTML::_('image.site',  $image, '/templates/'. $template .'/images/header/', NULL, NULL, $text ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
		<?php
	}

	public function display(array $data = null) {

		JToolBarHelper::title(JText::_("Postman"), "generic.png");
		//JToolBarHelper::preferences("com_postman", "400", "455", "Preferences", "");
		echo <<< HTML
	<table class="adminform">
	<tr>
		<td width="55%" valign="top">
		<div id="cpanel">
HTML;
		$link = 'index.php?option=com_postman&task=listNewsLetters';
		$this->quickiconButton( $link, 'icon-48-article-add.png', JText::_( 'Manage Newsletters' ) );

		$link = 'index.php?option=com_postman&task=listSubscribers';
		$this->quickiconButton( $link, 'icon-48-user.png', JText::_( 'Manage Subscribers' ) );

		$link = 'index.php?option=com_postman&task=listGroups';
		$this->quickiconButton( $link, 'icon-48-section.png', JText::_( 'Manage Groups' ) );

		$link = 'index.php?option=com_postman&task=editGroupSend';
		$this->quickiconButton( $link, 'icon-48-article.png', JText::_( 'Send Newsletter' ) );
		echo <<< HTML
		</div>
		</td>
		<td>
			<div id="content-pane" class="pane-sliders">
			<div class="panel">
			</div>
			</div>
		</td>
	</tr>
	</table>
HTML;
	}
}

?>