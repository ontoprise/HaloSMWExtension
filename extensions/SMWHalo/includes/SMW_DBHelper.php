<?php
/*
 * Created on 19.10.2007
 *
 * Author: kai
 */
 
 class DBHelper {
 	
 	
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
	public static function setupTable($table, $fields, $db, $verbose) {
		global $wgDBname;
		DBHelper::reportProgress("Setting up table $table ...\n",$verbose);
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
			$db->query( $sql, 'DBHelper::setupTable' );
			DBHelper::reportProgress("   ... new table created\n",$verbose);
			return array();
		} else { // check table signature
			DBHelper::reportProgress("   ... table exists already, checking structure ...\n",$verbose);
			$res = $db->query( 'DESCRIBE ' . $table, 'DBHelper::setupTable' );
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
					DBHelper::reportProgress("   ... creating column $name ... ",$verbose);
					$db->query("ALTER TABLE $table ADD `$name` $type $position", 'DBHelper::setupTable');
					$result[$name] = 'new';
					DBHelper::reportProgress("done \n",$verbose);
				} elseif ($curfields[$name] != $type && stripos("auto_increment", $type) == -1) {
					DBHelper::reportProgress("   ... changing type of column $name from '$curfields[$name]' to '$type' ... ",$verbose);
					$db->query("ALTER TABLE $table CHANGE `$name` `$name` $type $position", 'DBHelper::setupTable');
					$result[$name] = 'up';
					$curfields[$name] = false;
					DBHelper::reportProgress("done.\n",$verbose);
				} else {
					DBHelper::reportProgress("   ... column $name is fine\n",$verbose);
					$curfields[$name] = false;
				}
				$position = "AFTER $name";
			}
			foreach ($curfields as $name => $value) {
				if ($value !== false) { // not encountered yet --> delete
					DBHelper::reportProgress("   ... deleting obsolete column $name ... ",$verbose);
					$db->query("ALTER TABLE $table DROP COLUMN `$name`", 'DBHelper::setupTable');
					$result[$name] = 'del';
					DBHelper::reportProgress("done.\n",$verbose);
				}
			}
			DBHelper::reportProgress("   ... table $table set up successfully.\n",$verbose);
			return $result;
		}
	}
	
	/**
	 * Make sure that each of the column descriptions in the given array is indexed by *one* index
	 * in the given DB table.
	 */
	public static function setupIndex($table, $columns, $db) {
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
	public static function reportProgress($msg, $verbose) {
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
