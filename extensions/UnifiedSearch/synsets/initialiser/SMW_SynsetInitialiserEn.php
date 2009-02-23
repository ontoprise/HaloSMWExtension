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
class SynsetInitialiserEn implements ISynsetInitialiser {

	/**
	 * Reads the synsets from the original source and stores them in the database
	 *
	 */
	public function storeSynsetsFromSource(){
		global $IP;
		require_once($IP."/extensions/UnifiedSearch/synsets/storage/SMW_SynsetStorageSQL.php");
		$synsetStorage = new SynsetStorageSQL();
		
		$fr = fopen ($IP.'/extensions/UnifiedSearch/synsets/initialiser/wn_s_en.pl', 'r' );

		$count = 0;
		//$synonyms = array();
		while(!feof($fr)){
			$line = fgets($fr);
			$strpos = @strpos($line, ",");
			if(!$strpos){
				continue;
			}
			$synsetId = substr($line, 2, $strpos-2);
			//echo($synsetId."\r\n");
			$strpos = strpos($line,"'");
			$synonym = substr($line, $strpos+1, strpos($line,"'",$strpos+1) - $strpos - 1);
			//if(array_key_exists($synonym, $synonyms)){
			//	break;
			//}
			//$synonymy[$synonym] = 1;
			//echo($synonym."\r\n");

			$synsetStorage->addTerm($synonym, $synsetId);

			$count++;

			//if($count > 10){
			//	break;
			//}
		}
		echo("count: ".$count);

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

		$fr = fopen ($IP.'/extensions/UnifiedSearch/synsets/initialiser/smw_synsets_En.sql', 'r' );
		$synsetStorage->importSQLDump($fr);
		fclose($fr);
	}
		
}

?>