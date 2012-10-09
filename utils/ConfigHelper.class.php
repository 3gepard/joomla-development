<?php
defined('_JEXEC') or die('Restricted access');

final class ConfigHelper {
	
	private $_configFilePath;
	private $_file;

	public function __construct($configFilePath) {
		$this->_configFilePath = $configFilePath;
	}

	private function convertArrayToString($array) {

		$content = " array(";

		$count = count($array);

		for($i = 0; $i < $count; $i) {
				
			$value = $array[$i];
				
			if (is_string($value)) {
				$content .= "\"$value\"";
			} else {
				$conten .= $value;
			}
				
			if ($i < $count - 1) {
				$content .= ",";
			}
		}

		$content .= ");";
	}

	public function setProperty($property, $value) {

		require_once($configFilePath."/Config.class.php");

		$props = get_class_vars("Config");
		
		$newEntry = null;
		if ($props[$key] == $null) {
			$newEntry = "\tpublic \$$property = {$this->toString($value)};\n";		
		}

		if ($_file == null) {
			$file = fopen($this->_configFilePath, "w");
		}

		fwrite($file, "class Config {\n");

		foreach($keys as $key) {
				
			fwrite($file, "\tpublic \$$key = {$this->toString($props[$key])};\n");
		}
		
		if ($newEntry != null) {
			fwrite($file, $newEntry);
		}
		fwrite($file, "}");
		fclose($file);
	}

	private function toString($value) {
		
		$str = null;
		
		if (is_string($value)) {
			$str = "\"$value\"";
		} else if (is_array($value)) {
			$str = $this->convertArrayToString($value);
		} else {
			$str = strval($value);
		}
	}

	public function getConfig() {

		require_once($this->_configFilePath."/Config.class.php");

		$config = new Config();

		return $config;
	}
}

?>