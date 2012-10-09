<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if (!defined('TABLE_BAK')) {
	define('TABLE_BAK', '_bak');
}

// Script
class ScriptInstaller
{
	private $_db;
	private $tables = array('#__postman_log',
		'#__postman_tickets',
		'#__postman_newsletters',
		'#__postman_newsgroups',
		'#__postman_subscribers',
		'#__postman_newsgroups_subscribers'
	);
	function ScriptInstaller() {
		$this->_db = JFactory::getDbo();
	}
	private function exist($table){
		//$db = JFactory::getDbo();
		$query = $this->_db->getQuery(true);
		$query->select("COUNT(*)");
		$query->from($this->_db->quoteName($table));
		$this->_db->setQuery((string)$query);
		$record = $this->_db->loadObject();

		if (!$record) {
			return false;
		}

		return true;		
	}
	private function rename($oldname, $newname) {
		echo "... renaming table:" . $oldname . " ---> $newname<br/>";
		if ($this->exist($oldname)) {
			try {
				$r =  $this->_db->renameTable($this->_db->quoteName($oldname), $this->_db->quoteName($newname));
			}catch(JException $e) {
				echo "<br/>Error renaming table $oldname to $newname<br/>";
				return false;
			}
		}else{
			return false;
		}
		return true;	
	}
	private function copy($tableA, $tableB) {
		echo "...copy data: $tableA to $tableB ";

		$columnsA = $this->_db->getTableColumns($tableA);
		$columnsB = $this->_db->getTableColumns($tableB);

		$sql = "INSERT INTO `$tableB`";
		$flds = '';
		$m = 0;

		if ($columnsA != null && $columnsB != null) {
			foreach ($columnsA as $field => $val) {
				foreach($columnsB as $field2 => $val2) {
					if ($field == $field2) {
						if ($flds != '') $flds .= ',';
						$flds .= $field;
						$m++;
					}
				}
			}
		}

		if ($m>0) {
			$db = JFactory::getDbo();
			$sql .= "($flds)";
			$sql .= " SELECT $flds FROM `$tableA`";
			$flds = '';
			$this->_db->setQuery($sql);
			$result = $this->_db->query();
		
			//throw exception on error
			if ($this->_db->getErrorNum()) {
				echo "<br/>&nbsp;&nbsp;Error code: " . $this->_db->getErrorNum() . " : Error message: " .  $this->_db->getErrorMsg();
			}else{
				echo "---> (". count($result) . ")";
			}
		}
	}
	public  function backup() {
		echo "<h3>Postman preinstalation ... </h3>";
		//create bakup tables
		foreach($this->tables as $table) {

			$table = str_replace('#__', $this->_db->getPrefix(), $table);

			echo "* Checking table  $table ";

			$hasTable = $this->exist($table);
			$hasBakup = $this->exist($table . TABLE_BAK);

			$msg = ($hasTable) ?  "... table found" :  "... table not found";

			echo "$msg";

			$result = false;

			if ($hasBakup) {
				echo " ... backup exists ";
			}else{
				if ($hasTable) 
				{
					$result = $this->rename(
						$table, 
						$table . TABLE_BAK
					);
				}
			}
			echo "<br/>";
		}
		return $result;
	}
	public  function copydata() {
		echo "<h3>Postman post installation ... </h3>";

		$result = false;

		foreach($this->tables as $table) {
			$table = str_replace('#__', $this->_db->getPrefix(), $table);
		
			echo "* Checking table $table";
			$hasTable = $this->exist($table);
			$hasBakup = $this->exist($table . TABLE_BAK);

			if ($hasTable) {
				echo " ... table found " . $table;
				if ($hasBakup) {
					echo " ... backup found " . $table . TABLE_BAK;
					$result = $this->copy($table . TABLE_BAK, $table);
				}
			}else{
				echo " ... table is missing!!!";
			}

			echo "<br/>";
		}
		return $result;
	}
}
function com_install()
{
	echo "<h3>Thank you for installing com_postman.</h3>";
	$scr = new ScriptInstaller();
	if ($scr->backup()) {
		echo "<h3>Postman tables backedup.</h3>";
	}

	if ($scr->copydata()) {
		echo "<h3>Postman backup data copied.</h3>";
	}	
}
function com_uninstall() {
	echo "<h3>Thank you for using com_postman.</h3>";
}
