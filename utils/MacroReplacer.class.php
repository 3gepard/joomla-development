<?php

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_postman".DS."utils".DS."IMacroReplacer.class.php");

final class MacroReplacer implements IMacroReplacer {
	
	private $_content;
	private $_replacedContent;
	private $_macroList = array();

	
	public function __construct($content = "") {
		$this->_content = $content;
	}
	
	public function getReplacedContent() {
		
		if (!strlen($this->_content)>0) {
			return false; 
		}
		
		$keys = array_keys($this->_macroList);

		$this->_replacedContent = $this->_content;
		
		foreach ($keys as $key) {
			$this->_replacedContent = 
				str_replace($key, $this->_macroList[$key], $this->_replacedContent);
		}

		return $this->_replacedContent;
	}
	
	public function replace($macro, $value) {
		
		$this->_macroList[$macro] = $value;
	}
	
	public function setContent($content) {
		$this->_content = $content;
	}
	
	public function getContent() {
		return $this->_content;
	}
}
?>