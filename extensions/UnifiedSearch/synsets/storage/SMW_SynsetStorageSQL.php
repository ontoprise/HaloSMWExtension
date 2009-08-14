<?php
/*
 * Created on 18.02.2009
 *
 * Author: ingo
 */

global $IP;
require_once($IP."/extensions/UnifiedSearch/synsets/SMW_ISynsetStorage.php");

/*
 * This class is responsible for database access
 */
class SynsetStorageSQL implements ISynsetStorage{

	private $db;
	private $smw_synsets;

	public function __construct(){
		$this->db =& wfGetDB( DB_MASTER );
		$this->smw_synsets = $this->db->tableName('smw_synsets');
	}

	/**
	 * Setups database for Synsets
	 *
	 * @param boolean $verbose
	 */
	public function setup($verbose) {
		if ($verbose) print ("Creating tables for Synsets...\n");
		$this->db->query('DROP TABLE IF EXISTS '.$this->smw_synsets);
		$this->db->query('CREATE TABLE '.$this->smw_synsets.' (term VARCHAR(255), synset_id INTEGER)');
		if ($verbose) print("..done\n");
	}

	public function drop($verbose) {
		if ($verbose) print ("Dropping tables for Synsets...\n");
		$this->db->query('DROP TABLE IF EXISTS '.$this->smw_synsets);
		if ($verbose) print (" ... dropped table ".$this->smw_synsets.".\n");
	}

	/**
	 * Adds a new term together with its corresponding synset id.
	 *
	 * @param string $term
	 * @param int $synsetId
	 */
	public function addTerm($term, $synsetId) {
		$res = $this->db->selectRow($this->smw_synsets, array('term'), array('term'=>$term, 'synset_id'=>$synsetId));
		if (!$res !== false) {
			$this->db->query('INSERT INTO '.$this->smw_synsets.' VALUES ('.$this->db->addQuotes($term).','.$synsetId.')');
		}
	}

	/**
	 * Get all synonyms of a term. A term can in theory belong to
	 * several synsets. This method returns therefore an array of
	 * arrays, which each contain the synonyms belonging to one
	 * of the synsets.
	 *
	 * @param string $term
	 *
	 * @return Array<Array<String>>
	 */
	public function getSynsets($term) {
		$term = utf8_decode($term);

		$query = "SELECT synset_id FROM ".$this->smw_synsets." WHERE term=".$this->db->addQuotes($term);
		$res = $this->db->query($query );

		$result = array();
		while($row = $this->db->fetchObject($res)) {
			$synsetId = $row->synset_id;
			$query = "SELECT term FROM ".$this->smw_synsets." WHERE synset_id=".$synsetId;
				
			$res2 = $this->db->query($query );
			$resultTerms = array();
			while($row2 = $this->db->fetchObject($res2)) {
				if(utf8_encode($term) != utf8_encode($row2->term)){
					$resultTerms[] = utf8_encode($row2->term);
				}
			}
			$result[] = $resultTerms;
		}
		return $result;
	}

	/**
	 * Fills the database with all synonyms contained in a sql dump
	 *
	 * @param $file: a file handler
	 */
	public function importSQLDump($file){
		$res = $this->db->sourceStream($file);
	}

}

