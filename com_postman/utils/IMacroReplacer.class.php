<?php
defined('_JEXEC') or die('Restricted access');

interface IMacroReplacer {
	
	public function replace($macro, $value);
	public function getReplacedContent();
}
?>