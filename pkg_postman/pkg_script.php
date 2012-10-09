<?php
/**
* @version 			1.0 3Gepard $
* @package			
* @url				http://www.threegepard.com
* @editor			3Geaprd
* @copyright		Copyright (C) 2012 SEBLOD. All Rights Reserved.
* @license 			GNU General Public License version 2 or later; see _LICENSE.php
**/

// No Direct Access
defined( '_JEXEC' ) or die;

if (!defined('TABLE_BAK')) {
	define('TABLE_BAK', '_bak');
}

// Script
class pkg_postmanInstallerScript
{
	private $_db;
	private $tables = array('#__postman_log',
		'#__postman_tickets',
		'#__postman_newsletters',
		'#__postman_newsgroups',
		'#__postman_subscribers',
		'#__postman_newsgroups_subscribers'
	);
	function pkg_postmanInstallerScript() {
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
			}
		}
		return false;	
	}
	private function drop($table){
		if ($this->exist($table)) {
			$db = JFactory::getDbo();
			$table = $this->_db->quoteName($table);
			echo "... deleting table $table<br/>";
			$this->_db->dropTable($table);
		}
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
	private function updateTables() {
		echo "<h3>Updateing tables</h3>";
		$subscriber = str_replace('#__', $this->_db->getPrefix(), `#__subscribers`);
		$module = str_replace('#__', $this->_db->getPrefix(), `#__modules`);

		$sql1 = "UPDATE `$subscriber` SET active = 1";
		$sql2 = "UPDATE `$module` SET position = \"footer-right\",published = 1 WHERE title = 'Postman'";

		echo "* Subscribers";
		$this->_db->setQuery($sql1);
		$result = $this->_db->query();
	
		echo " ... " . count($result) . "<br/>";

		echo "* Module";
		$this->_db->setQuery($sql2);
		$result = $this->_db->query();
		echo " ... " . count($result) . "<br/>";

		echo "* Please update mod_postman settings<br/>";
		echo "* Make one group public<br/>";
	}
	// install
	function install( $parent )
	{
		echo "<h3>Thank you for installing Postman package!</h3>";	
	}
	// uninstall
	function uninstall( $parent )
	{
		echo "<h3>Thank you for using Postman!</h3>";
	}
	// update
	function update( $parent )
	{
		echo "<h3>Update!</h3>";
	}
	// preflight
	function preflight( $type, $parent )
	{
		$version	=	new JVersion;
		
		if ( version_compare( $version->getShortVersion(), '2.5.1', 'lt' ) ) {
			Jerror::raiseWarning( null, 'You should upgrade your site with Joomla 2.5.1 (or +) before installing Postman 2.5.1' );
			return false;
		}

		set_time_limit( 0 );

		echo "<h3>Package preinstalation ... (preflight)</h3>";	

		//remove old and rename current to bakup
		foreach($this->tables as $table) {
			$table = str_replace('#__', $this->_db->getPrefix(), $table);

			echo "* Checking table  $table ";

			$hasTable = $this->exist($table);
			$hasBakup = $this->exist($table . TABLE_BAK);
			
			$msg = ($hasTable) ?  "... table found" :  "... table not found";

			echo "$msg";

			if ($hasBakup) {
				echo " ... backup exists ";
			}else{
				if ($hasTable) 
				{
					$this->rename(
						$table, 
						$table . TABLE_BAK
					);
				}
			}
			echo "<br/>";
		}
	}
	// postflight
	function postflight( $type, $parent )
	{
		echo "<h3>Package post installation ... (postflight)</h3>";

		foreach($this->tables as $table) {
			$table = str_replace('#__', $this->_db->getPrefix(), $table);
		
			echo "* Checking table $table";
			$hasTable = $this->exist($table);
			$hasBakup = $this->exist($table . TABLE_BAK);

			if ($hasTable) {
				echo " ... table found " . $table;
				if ($hasBakup) {
					echo " ... backup found " . $table . TABLE_BAK;
					$this->copy($table . TABLE_BAK, $table);
				}
			}else{
				echo " ... table is missing!!!";
			}

			echo "<br/>";
		}

		$this->updateTables();
	}
}
?>