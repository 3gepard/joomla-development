<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport("mvc3gepard.proximityview");
/*
jimport("joomla.component.toolbarhelper");
jimport('joomla.application.component.view');
jimport("joomla.html.toolbar");
jimport("joomla.html.editor");
*/
final class ListNewslettersView implements IProximityView {

	private $_letters;
	private $_page;

	public function display(array $data = null) {
		//JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');

		$listTitle = "Manage Newsletters";
		$newButton = "editNewsLetter";
		$deleteListCmd = "removeNewsLetter";

		// laded menu items from file
		require_once(dirname(__FILE__) .DS. 'ComponentMenuItemsWrapper.php');

		$letters = $this->getLetters();
		$lettersCount = count($letters);
		$page = $this->getPage();

		$token = JHtml::_('form.token');
		$tokenSpc = '&amp;'.JSession::getFormToken().'=1';
		
		echo <<< HTML
		<form name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
        		<thead>
        			<tr>
        				<th width="4%" class="title">#</th>
        				<th width="4%" class="title"><input type="checkbox" name="toggle" onclick="checkAll({$lettersCount})"/></th>
						<th width="27%" class="title">Subject</th>
						<th width="5%" class="title">Published</th>
						<th width="5%" class="title">Send</th>
						<th class="title"> </th>
        			</tr>
        		</thead>
        		<tbody>

HTML;
		$row = 0;
		for($i = 0; $i < $lettersCount; $i++) {
			$letter = $letters[$i];
			$row ^= 1;
			$no = $i + 1;

			$link = "<a href=\"index.php?option=com_postman&task=editNewsLetter&letterId={$letter->letter_id}${tokenSpc}\">{$letter->subject}</a>";
			$checked = JHTML::_('grid.checkedout', $letter, $i, "letter_id");
			$published = JHtml::_('jgrid.published', $letter->published, $i, '', '', 'cb', $letter->published);

			echo <<< HTML
				<tr class="row{$row}">
					<td align="center">{$no}</td>
					<td align="center">{$checked}</td>
					<td>{$link}</td>
					<td align="center">{$published}</td>
					<td align="center">
						<table class="toolbar">
							<tr>
								<td class="button" id="toolbar-publish">
									<a class="jgrid" class="jgrid" href="index.php?option=com_postman&task=sendGroupNewsletter&letterId={$letter->letter_id}${tokenSpc}" class="toolbar">
										<span class="state icon-16-banners-tracks"/>&nbsp</span>
									</a>
								</td>
							</tr>
						</table>
					</td>
					<td></td>
				</tr>
HTML;
		}

		echo <<< HTML
        	</tbody>
        	<tfoot><tr><td colspan="6">{$page->getListFooter()}</td></tr></tfoot>
		</table>
		<input type="hidden" name="task" value="listNewsletters" />
		<input type="hidden" name="boxchecked" value="0" />
		{$token}
		</form>
HTML;
	}

	public function setLetters($letters) {
		$this->_letters = $letters;
	}

	public function getLetters() {
		return $this->_letters;
	}

	public function setPage($page) {
		$this->_page = $page;
	}

	public function getPage() {
		return $this->_page;
	}
}
?>