<?php
/**
 * @package		Postman (Component)
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."mvc".DS."IProximityView.class.php");

final class EditNewsLetterView implements IProximityView {
	
	private $_letter;
	
	function display(array $data = null) { 
		jimport("joomla.html.toolbar");
		jimport("joomla.html.editor");

		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');

		JToolBarHelper::title( JText::_("Postman - Newsletter"), "generic.png");
		JToolBarHelper::apply('applyNewsletter');
		JToolBarHelper::save("saveAndCloseNewsletter");
		JToolBarHelper::cancel("listNewsLetters","Close");
		JToolBarHelper::help("screen.postman");

		$letter = $this->getLetter();
		$subject = '';
		$letter_id = -1;
		$message = '';
		$published = 0;

		if ($letter != null) {
			$subject = $letter->subject;
			$letter_id = $letter->letter_id;
			$message = $letter->message;
			$published = ($letter->published) ? 1 : 0;
		}

		$editor = JFactory::getEditor();
		//$action = JRoute::_('index.php?option=com_postman&layout=edit&id=' . (int) $this->_letter->letter_id);
		$token = JHtml::_('form.token');
		
		echo <<< HTML

		<script type="text/javascript">
			Joomla.submitbutton = function(task) {
				if (task == 'listNewsLetters' || document.formvalidator.isValid(document.id('newsletter-form'))) {
					Joomla.submitform(task, document.getElementById('newsletter-form'));
				}
			}
		</script>
		<form action="index.php?option=com_postman" method="post" name="adminForm" id="newsletter-form" class="form-validate">
			<div class="width-60 fieldset">
				<fieldset class="adminform">
					<ul class="adminlist adminformlist">
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip required" for="jform_subject" id="jform_subject-lbl">Subject<span class="star">&nbsp;*</span></label>
							<input type="text" size="40" class="inputbox required" value="{$subject}" id="jform_title" name="subject" aria-required="true" required="required">
						</li>
						<li class="width-60" style="clear: both">
							<label title="" class="hasTip" for="jform_published" id="jform_published-lbl">Published</label>
							<input id="cb0" name="published" type="checkbox" checked="{$published}" />
						</li>
						<li class="width-60" style="clear: both">
HTML;

						echo $editor->display("editor1", $message, "600px", "400px", 60, 40, true);
						echo <<< HTML

						</li>
					</ul>
				</fiedset>
			</div>
			<input type="hidden" name="letterId" value="{$letter_id}" />
			<input type="hidden" name="task" />
			{$token}
		</form>
HTML;
	}
	
	public function setLetter($letter) {
		$this->_letter = $letter;
	}
	
	public function getLetter() {
		return $this->_letter;
	}
}

?>