<?php
/*
 * Created on 18.02.2009
 *
 * Author: ingo
 */

global $IP;
require_once($IP."/extensions/UnifiedSearch/synsets/SMW_SynsetParserFunction.php");

/*
 * This class provides access to the synset functionality
 */
class Synsets {

	/*
	 * Creates the database tables and fills it with synsets.
	 * (The source is a sql-dump)
	 */
	public function setup(){
		global $IP;
		require_once($IP."/extensions/UnifiedSearch/synsets/storage/SMW_SynsetStorageSQL.php");
		$st = new SynsetStorageSQL();
		$st->setup(false);

		global $wgLanguageCode;
		if (!empty($wgLanguageCode)) {
			$lng = ucfirst($wgLanguageCode);
			$fileName = $IP."/extensions/UnifiedSearch/synsets/initialiser/SMW_SynsetInitialiser".$lng.".php";
			if (file_exists($fileName)){
				require_once($fileName);
				$cName = "SynsetInitialiser".$lng;
				$si = new $cName();
			}
		}

		if ( !class_exists($cName)) {
			require_once($IP."/extensions/UnifiedSearch/synsets/initialiser/SMW_SynsetInitialiserEn.php");
			$si = new SynsetInitialiserEn();
		}

		$si->storeSynsets();
	}
	
	/*
	 * Creates the database tables and fills it with synsets.
	 * (The source is the original data from WordNet, openthesaurus....)
	 */
	public function setupFromSource(){
		global $IP;
		require_once($IP."/extensions/UnifiedSearch/synsets/storage/SMW_SynsetStorageSQL.php");
		$st = new SynsetStorageSQL();
		$st->setup(false);

		global $wgLanguageCode;
		if (!empty($wgLanguageCode)) {
			$lng = ucfirst($wgLanguageCode);
			$fileName = $IP."/extensions/UnifiedSearch/synsets/initialiser/SMW_SynsetInitialiser".$lng.".php";
			if (file_exists($fileName)){
				require_once($fileName);
				$cName = "SynsetInitialiser".$lng;
				$si = new $cName();
			}
		}

		if ( !class_exists($cName)) {
			require_once($IP."/extensions/UnifiedSearch/synsets/initialiser/SMW_SynsetInitialiserEn.php");
			$si = new SynsetInitialiserEn();
		}

		$si->storeSynsetsFromSource();
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
	public function getSynsets($term){
		global $IP;
		require_once($IP."/extensions/UnifiedSearch/synsets/storage/SMW_SynsetStorageSQL.php");
		$st = new SynsetStorageSQL();
		return $st->getSynsets($term);


	}


}


?>