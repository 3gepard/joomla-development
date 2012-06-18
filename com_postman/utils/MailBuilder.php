<?php

class Tag {
	private $_attribs;
	private $_tagtext = "";
	private $_tagname = "";

	public function __construct($html) {
	
		$this->_tagtext = $html;
		$this->_attribs = array();
		preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $html, $list, PREG_OFFSET_CAPTURE);
		
		for($i=0;$i < count($list); $i++) {
			$atrib = $list[1][$i][0];
			$this->_attribs[$atrib] = $list[2][$i][0];
		}
	}

	public function getTagName() {
		
		preg_match_all('/([\w:-]+)/i', $this->_tagtext, $arr);
		return $arr[0][0];
	}
	
	public function count() {

		return count($this->_attribs);
	}
	
	public function isTagOk() {
		
		return true;
	}

	public function attrib($name, &$value) {

		if (array_key_exists($name, $this->_attribs)) {

			$value = $this->_attribs[$name];
			return true;
		}

		$value = "";
		return false;
	}
	
	public function change($name, $value) {

		$this->_attribs[$name] = $value;
	}
	
	public function toString() {

		$text = "";

		foreach($this->_attribs as $key => $value) {
			$text .= " {$key}=\"{$value}\"";
		}
		
		if (!count($text)>0) { 
			return $this->_tagtext;
		}

		$text = "<". $this->getTagName() . $text ." />";
		return $text;
	}
}

?>