<?php
/*
 * Created on 17.09.2007
 *
 * Author: kai
 */
 
 class HaloSQLTableFactory {
 	
 	function __construct() {
 		// do nothing
 	}
 	
 	function createOrUpdateTables($verbose) {
 		$this->setupGardening($verbose);
		
		$this->setupLogging($verbose);
		
		$this->createPreDefinedPages($verbose);
 	}
 	/**
	 * Creates some predefined pages
	 */
	protected function createPreDefinedPages($verbose) {
		global $smwgHaloContLang;
		$this->reportProgress("Creating predefined pages...\n",$verbose);
		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		foreach($ssp as $key => $value) {
			$t = Title::newFromText($value, SMW_NS_PROPERTY);
			if (!$t->exists()) {
				$article = new Article($t);
				$article->insertNewArticle(wfMsg('smw_predefined_props', $t->getText()), "", false, false);
			}
		}
		
		$scs = $smwgHaloContLang->getSpecialCategoryArray();
		foreach($scs as $key => $value) {
			$t = Title::newFromText($value, NS_CATEGORY);
			if (!$t->exists()) {
				$article = new Article($t);
				$article->insertNewArticle(wfMsg('smw_predefined_cats', $t->getText()), "", false, false);
			}
		}
		$this->reportProgress("Predefined pages created successfully.\n",$verbose);
	}
	/**
	 * Initializes the gardening component
	 */
	protected function setupGardening($verbose) {
			global $wgDBname;
			$db =& wfGetDB( DB_MASTER );

			// create gardening table
			$smw_gardening = $db->tableName('smw_gardening');
			$fname = 'SMW::initGardeningLog';

			// create relation table
			$this->setupTable($smw_gardening, array(
				  'id'				=>	'INT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY' ,
				  'user'      		=>  'VARCHAR(255) COLLATE latin1_bin NOT NULL' ,
				  'gardeningbot'	=>	'VARCHAR(255) COLLATE latin1_bin NOT NULL' ,
				  'starttime'  		=> 	'DATETIME NOT NULL',
				  'endtime'     	=> 	'DATETIME',
				  'timestamp_start'	=>	'VARCHAR(14) COLLATE latin1_bin NOT NULL',
				  'timestamp_end' 	=>	'VARCHAR(14) COLLATE latin1_bin',
				  'useremail'   	=>  'VARCHAR(255) COLLATE latin1_bin',
				  'log'				=>	'VARCHAR(255) COLLATE latin1_bin',
				  'progress'		=>	'DOUBLE'), $db, $verbose);


			// create GardeningLog category
			$this->reportProgress("Setting up GardeningLog category ...\n",$verbose);
			$gardeningLogCategoryTitle = Title::newFromText(wfMsg('smw_gardening_log'), NS_CATEGORY);
 			$gardeningLogCategory = new Article($gardeningLogCategoryTitle);
 			if (!$gardeningLogCategory->exists()) {
 				$gardeningLogCategory->insertNewArticle(wfMsg('smw_gardening_log_exp'), wfMsg('smw_gardening_log_exp'), false, false);
 			}
 			$this->reportProgress("   ... GardeningLog category created.\n",$verbose);


 			// fetch all user IDs and add group SMW_GARD_ALL_USERS
 			$this->reportProgress("Add exsiting users to gardening groups ...\n",$verbose);
			$res = $db->select( $db->tableName('user'),
		             array('user_id'),
		             array(),
		             "SMW::initGardeningLog",array());
		    if($db->numRows( $res ) > 0) {
				while ($row = $db->fetchObject($res)) {
					$user = User::newFromId($row->user_id);
					$user->addGroup(SMW_GARD_ALL_USERS);
				}
			}
			$db->freeResult($res);

			// fetch all sysop IDs and add group SMW_GARD_GARDENERS
			$res = $db->select( $db->tableName('user_groups'),
		             array('ug_user'),
		             array('ug_group' => 'sysop'),
		             "SMW::initGardeningLog",array());
		    if($db->numRows( $res ) > 0) {
				while ($row = $db->fetchObject($res)) {
					$user = User::newFromId($row->ug_user);
					$user->addGroup(SMW_GARD_GARDENERS);
				}
			}
			$db->freeResult($res);
			$this->reportProgress("   ... done!\n",$verbose);
	}
	
	/**
	 * Initializes the logging component
	 */
	protected function setupLogging($verbose) {
			global $smw_enableLogging;
			$this->reportProgress("Setting up logging ...\n",$verbose);
			if($smw_enableLogging !== true){
				$this->reportProgress("   ... logging not enabled. Doing nothing.  \n",$verbose);
				return;
			}
			
			$this->reportProgress("   ... Creating logging database \n",$verbose);
			global $wgDBname;
			$db =& wfGetDB( DB_MASTER );

			// create gardening table
			$smw_logging = $db->tableName('smw_logging');
			$fname = 'SMW::setupLogging';

			// create relation table
			$this->setupTable($smw_logging, array(
				  'id'				=>	'INT(10) UNSIGNED NOT NULL auto_increment PRIMARY KEY' ,
				  'timestamp'      	=>  'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' ,
				  'user'      		=>  'VARCHAR(255)' ,
				  'location'		=>	'VARCHAR(255)' ,
				  'type'			=>	'VARCHAR(255)' ,
				  'function'		=>	'VARCHAR(255)' ,
				  'remotetimestamp'	=>	'VARCHAR(255)' ,
				  'text'			=>  'LONGTEXT' 
				  ), $db, $verbose);

			$this->reportProgress("   ... done!\n",$verbose);
}
 	/**
	 * Make sure the table of the given name has the given fields, provided
	 * as an array with entries fieldname => typeparams. typeparams should be
	 * in a normalised form and order to match to existing values.
	 *
	 * The function returns an array that includes all columns that have been
	 * changed. For each such column, the array contains an entry 
	 * columnname => action, where action is one of 'up', 'new', or 'del'
	 * If the table was already fine or was created completely anew, an empty 
	 * array is returned (assuming that both cases require no action).
	 *
	 * NOTE: the function partly ignores the order in which fields are set up.
	 * Only if the type of some field changes will its order be adjusted explicitly.
	 */
	protected function setupTable($table, $fields, $db, $verbose) {
		global $wgDBname;
		$this->reportProgress("Setting up table $table ...\n",$verbose);
		if ($db->tableExists($table) === false) { // create new table
			$sql = 'CREATE TABLE ' . $wgDBname . '.' . $table . ' (';
			$first = true;
			foreach ($fields as $name => $type) {
				if ($first) {
					$first = false;
				} else {
					$sql .= ',';
				}
				$sql .= $name . '  ' . $type;
			}
			$sql .= ') TYPE=innodb';
			$db->query( $sql, 'SMWSQLStore::setupTable' );
			$this->reportProgress("   ... new table created\n",$verbose);
			return array();
		} else { // check table signature
			$this->reportProgress("   ... table exists already, checking structure ...\n",$verbose);
			$res = $db->query( 'DESCRIBE ' . $table, 'SMWSQLStore::setupTable' );
			$curfields = array();
			$result = array();
			while ($row = $db->fetchObject($res)) {
				$type = strtoupper($row->Type);
				if ($row->Null != 'YES') {
					$type .= ' NOT NULL';
				}
				$curfields[$row->Field] = $type;
			}
			$position = 'FIRST';
			foreach ($fields as $name => $type) {
				if ( !array_key_exists($name,$curfields) ) {
					$this->reportProgress("   ... creating column $name ... ",$verbose);
					$db->query("ALTER TABLE $table ADD `$name` $type $position", 'SMWSQLStore::setupTable');
					$result[$name] = 'new';
					$this->reportProgress("done \n",$verbose);
				} elseif ($curfields[$name] != $type && stripos("auto_increment", $type) == -1) {
					$this->reportProgress("   ... changing type of column $name from '$curfields[$name]' to '$type' ... ",$verbose);
					$db->query("ALTER TABLE $table CHANGE `$name` `$name` $type $position", 'SMWSQLStore::setupTable');
					$result[$name] = 'up';
					$curfields[$name] = false;
					$this->reportProgress("done.\n",$verbose);
				} else {
					$this->reportProgress("   ... column $name is fine\n",$verbose);
					$curfields[$name] = false;
				}
				$position = "AFTER $name";
			}
			foreach ($curfields as $name => $value) {
				if ($value !== false) { // not encountered yet --> delete
					$this->reportProgress("   ... deleting obsolete column $name ... ",$verbose);
					$db->query("ALTER TABLE $table DROP COLUMN `$name`", 'SMWSQLStore::setupTable');
					$result[$name] = 'del';
					$this->reportProgress("done.\n",$verbose);
				}
			}
			$this->reportProgress("   ... table $table set up successfully.\n",$verbose);
			return $result;
		}
	}
	
	/**
	 * Make sure that each of the column descriptions in the given array is indexed by *one* index
	 * in the given DB table.
	 */
	protected function setupIndex($table, $columns, $db) {
		$table = $db->tableName($table);
		$res = $db->query( 'SHOW INDEX FROM ' . $table , 'SMW::SetupIndex');
		if ( !$res ) {
			return false;
		}
		$indexes = array();
		while ( $row = $db->fetchObject( $res ) ) {
			if (!array_key_exists($row->Key_name, $indexes)) {
				$indexes[$row->Key_name] = array();
			}
			$indexes[$row->Key_name][$row->Seq_in_index] = $row->Column_name;
		}
		foreach ($indexes as $key => $index) { // clean up existing indexes
			$id = array_search(implode(',', $index), $columns );
			if ( $id !== false ) {
				$columns[$id] = false;
			} else { // duplicate or unrequired index
				$db->query( 'DROP INDEX ' . $key . ' ON ' . $table, 'SMW::SetupIndex');
			}
		}

		foreach ($columns as $column) { // add remaining indexes
			if ($column != false) {
				$db->query( "ALTER TABLE $table ADD INDEX ( $column )", 'SMW::SetupIndex');
			}
		}
		return true;
	}

	/**
	 * Print some output to indicate progress. The output message is given by
	 * $msg, while $verbose indicates whether or not output is desired at all.
	 */
	protected function reportProgress($msg, $verbose) {
		if (!$verbose) {
			return;
		}
		if (ob_get_level() == 0) { // be sure to have some buffer, otherwise some PHPs complain
			ob_start();
		}
		print $msg;
		ob_flush();
		flush();
	}
	
 }
?>
