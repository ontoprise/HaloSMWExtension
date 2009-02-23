<?php
/*
 * Created on 18.02.2009
 *
 * Author: ingo
 */

global $IP;
require_once($IP."/extensions/UnifiedSearch/synsets/SMW_ISynsetInitialiser.php");

/*
 * This class is responsible for filling the data base
 * with synonyms from a file
 */
class SynsetInitialiserDe implements ISynsetInitialiser {

	/**
	 * Reads the synsets from the original source and stores them in the database
	 *
	 */
	public function storeSynsetsFromSource(){
		global $IP;
		require_once($IP."/extensions/UnifiedSearch/synsets/storage/SMW_SynsetStorageSQL.php");
		$synsetStorage = new SynsetStorageSQL();
		
		$fr = fopen ($IP.'/extensions/UnifiedSearch/synsets/initialiser/thesaurus.txt', 'r' );

		$synsetId = 0;
		while(!feof($fr)){
			$line = fgets($fr);
			$strpos = @strpos($line, "(");
			while($strpos !== false){
				$subString = substr($line, $strpos, strpos($line, ")") - $strpos +1);
				$line = str_replace($subString, "", $line);
				$strpos = @strpos($line, "(");
			}
				
			$syns = explode(";", $line);
			foreach($syns as $synonym){
				$synonym = trim($synonym);
				if(strlen($synonym) > 0){
					$synsetStorage->addTerm($synonym, $synsetId);
				}
			}
				
			$synsetId++;
		}

		fclose($fr);
	}

	/**
	 * Reads the synsets from an sql dump and stores them in the database
	 *
	 */
	public function storeSynsets(){
		global $IP;
		require_once($IP."/extensions/UnifiedSearch/synsets/storage/SMW_SynsetStorageSQL.php");
		$synsetStorage = new SynsetStorageSQL();

		$fr = fopen ($IP.'/extensions/UnifiedSearch/synsets/initialiser/smw_synsets_De.sql', 'r' );
		$synsetStorage->importSQLDump($fr);
		fclose($fr);
	}
}

?>