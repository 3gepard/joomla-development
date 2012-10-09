<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");

//jimport("joomla.html.toolbar");
jimport("joomla.component.toolbarhelper");

final class ListNewsLettersView implements IProximityView {

	private $_letters;
	private $_page;

	public function display(array $data = null) {
		$listTitle = "Manage Newsletters";
		
		$newButton = "editNewsLetter";
		$deleteListCmd = "removeNewsLetter";

		// laded menu items from file
		require_once(dirname(__FILE__) .DS. 'ComponentMenuItemsWrapper.php');

		$letters = $this->getLetters();
		$lettersCount = count($letters);
		$page = $this->getPage();
		$filter_order = "";
		
		$token = JHtml::_('form.token');
		$tokenSpc = '&amp;'.JSession::getFormToken().'=1';
		
		echo <<< HTML
		<form name="adminForm" action="index.php?option=com_postman" method="post">
			<table class="adminlist">
        		<thead>
        			<tr>
        				<th width="4%" class="title">#</th>
        				<th width="4%" class="title"><input type="checkbox" name="toggle" onclick="checkAll({$lettersCount})"/></th>
						<th  class="title">Subject</th>
						<th width="5%" class="title">Send</th>
						<th width="5%" class="title">Published</th>
						<th width="15%" class="title">Created</th>
        			</tr>
        		</thead>
        		<tbody>

HTML;

		$k = 0;

		for($i = 0; $i < $lettersCount; $i++) {

			$letter = $letters[$i];
			$link = "<a href=\"index.php?option=com_postman&task=editNewsLetter&letterId={$letter->letter_id}${tokenSpc}\">{$letter->subject}</a>";

			$publishedIcon = null;

			$checked = JHTML::_('grid.checkedout', $letter, $i, "letter_id");
			$k ^= 1;

			$no = $i + 1;
			
			$published = JHtml::_('jgrid.published', $letter->published, $i, '', '', 'cb', $letter->published);

			echo <<< HTML
				<tr class="row{$k}">
					<td align="center">{$no}</td>
					<td align="center">{$checked}</td>
					<td>{$link}</td>
					<td align="center">
						<table class="toolbar">
							<tr>
								<td class="button" id="toolbar-publish">
									<a class="jgrid" class="jgrid" href="index.php?option=com_postman&task=editGroupSend&letterId={$letter->letter_id}${tokenSpc}" class="toolbar">
										<span class="state icon-16-banners-tracks"/>&nbsp</span>
									</a>
								</td>
							</tr>
						</table>
					</td>
					<td align="center">
						{$published}
					</td>
					<td align="center">{$letter->created}</td>
				</tr>
HTML;
		}

		echo <<< HTML
        	</tbody>
        	<tfoot><tr><td colspan="8">{$page->getListFooter()}</td></tr></tfoot>
		</table>
		<input type="hidden" name="option" value="com_postman" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="controller" value="newsletter" />
		<input type="hidden" name="filter_order" value="{$filter_order}>" />
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