<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
  * @ingroup DIWSMaterialization
  * 
  * @author Ingo Steinbauer
 */

global $smwgDIIP;
require_once("$smwgDIIP/specials/Materialization/SMW_IMaterializationStorage.php");

/*
 * This class is responsible for database access
 */
class SMWMaterializationStorageSQL implements IMaterializationStorage{

	private $db;
	private $smw_ws_materialization_hashes;
	
	public function __construct(){
		$this->db =& wfGetDB( DB_MASTER );
		$this->smw_ws_materialization_hashes = $this->db->tableName('smw_ws_materialization_hashes');
	}
	
	/**
	 * Setups database for Materialization
	 *
	 * @param boolean $verbose
	 */
	public function setup($verbose) {
		if ($verbose) print ("Creating tables for Materialization...\n");
		DBHelper::setupTable($this->smw_ws_materialization_hashes, array(
				  'page_id'		 =>  'INT(8) UNSIGNED NOT NULL',
				  'call_hash'  	             =>  'VARCHAR(33) NOT NULL' ,
				  'materialization_hash'             =>  'VARCHAR(33) NOT NULL' ),
				  $this->db, $verbose);
		if ($verbose) print("..done\n");
	}

	public function deleteDatabaseTables() {
		global $wgDBtype;
		
		$db =& wfGetDB( DB_MASTER );
		$verbose = true;
		DBHelper::reportProgress("Dropping materialization tables ...\n",$verbose);

		$db->query('DROP TABLE' . ($wgDBtype=='postgres'?'':' IF EXISTS'). $this->smw_ws_materialization_hashes, 'WSStorageSQL::drop');
		DBHelper::reportProgress(" ... dropped table $this->smw_ws_materialization_hashes.\n", $verbose);
		
		DBHelper::reportProgress("   ... done!\n",$verbose);
	}
	
	/**
	 * Add the data of a new materialization
	 *
	 * @param string $pageId : id of the page where the materialization takes place
	 * @param string $callHash : hash value of the call of which the result gets materialized
	 * @param string $materializationHash : hash value of the materialized call result
	 */
	public function addMaterializationHash($pageId, $callHash, $materializationHash) {
		$res = $this->db->selectRow($this->smw_ws_materialization_hashes,
			 array('page_id'), 
			 array('page_id' => $pageId, 
			 	'call_hash' => $callHash, 
			 	'materialization_hash' => $materializationHash));
		
			 if (!$res !== false) {
			$this->db->query('INSERT INTO '.$this->smw_ws_materialization_hashes
				.' VALUES ('.$this->db->addQuotes($pageId)
				.','.$this->db->addQuotes($callHash)
				.','.$this->db->addQuotes($materializationHash).')');
		}
	}

	/**
	 * get the data of a materialization
	 *
	 * @param string $pageId : id of the page where the materialization takes place
	 * @param string $callHash : hash value of the call which gets materialized
	 *
	 * @return string : hash value of the materialized call result or null
	 */
	public function getMaterializationHash($pageId, $callHash) {
		$query = "SELECT materialization_hash FROM ".$this->smw_ws_materialization_hashes
			." WHERE page_id=".$this->db->addQuotes($pageId)
			." AND call_hash=".$this->db->addQuotes($callHash);
		$res = $this->db->query($query );
		
		
		$row = $this->db->fetchObject($res);
		if($row){
			return $row->materialization_hash;
		}
		return null;
	}
	
	/**
	 * delete the data of a materialization
	 *
	 * @param string $pageId : id of the page where the materialization takes place
	 * @param string $callHash : hash value of the call which gets materialized
	 */
	public function deleteMaterializationHash($pageId, $callHash){
		$this->db->delete($this->smw_ws_materialization_hashes, 
			array('page_id' => $pageId,
				'call_hash'   => $callHash));
	}
	
	/**
	 * get all call hashes for a given pageId
	 *
	 * @param string $pageId : id of the page where the materialization takes place
	 *
	 * @return array<string> : hash values of the calls which gets materialized
	 */
	public function getCallHashes($pageId){
		$query = "SELECT call_hash FROM ".$this->smw_ws_materialization_hashes
			." WHERE page_id=".$this->db->addQuotes($pageId);
			
		$res = $this->db->query($query );
		
		$result = array();
		while($row = $this->db->fetchObject($res)){
			$result[$row->call_hash] = null;
		}
		return $result;
	}
	
	
}