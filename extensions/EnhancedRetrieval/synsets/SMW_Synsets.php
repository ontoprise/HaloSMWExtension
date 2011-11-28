<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup EnhancedRetrievalSynsets
 * 
 * @defgroup EnhancedRetrievalSynsets EnhancedRetrieval Synonyms 
 * @ingroup EnhancedRetrieval
 * Created on 18.02.2009
 *
 * @author Ingo Steinbauer
 */

global $IP;
require_once($IP."/extensions/EnhancedRetrieval/synsets/SMW_SynsetParserFunction.php");

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
		require_once($IP."/extensions/EnhancedRetrieval/synsets/storage/SMW_SynsetStorageSQL.php");
		$st = new SynsetStorageSQL();
		$st->setup(false);

		global $wgLanguageCode;
		if (!empty($wgLanguageCode)) {
			$lng = ucfirst($wgLanguageCode);
			$fileName = $IP."/extensions/EnhancedRetrieval/synsets/initialiser/SMW_SynsetInitialiser".$lng.".php";
			if (file_exists($fileName)){
				require_once($fileName);
				$cName = "SynsetInitialiser".$lng;
				$si = new $cName();
			}
		}

		if ( !class_exists($cName)) {
			require_once($IP."/extensions/EnhancedRetrieval/synsets/initialiser/SMW_SynsetInitialiserEn.php");
			$si = new SynsetInitialiserEn();
		}

		$si->storeSynsets();
	}


	public function drop(){
		global $IP;
		require_once($IP."/extensions/EnhancedRetrieval/synsets/storage/SMW_SynsetStorageSQL.php");
		$st = new SynsetStorageSQL();
		$st->drop(true);

	}

	/*
	 * Creates the database tables and fills it with synsets.
	 * (The source is the original data from WordNet, openthesaurus....)
	 */
	public function setupFromSource(){
		global $IP;
		require_once($IP."/extensions/EnhancedRetrieval/synsets/storage/SMW_SynsetStorageSQL.php");
		$st = new SynsetStorageSQL();
		$st->setup(false);

		global $wgLanguageCode;
		if (!empty($wgLanguageCode)) {
			$lng = ucfirst($wgLanguageCode);
			$fileName = $IP."/extensions/EnhancedRetrieval/synsets/initialiser/SMW_SynsetInitialiser".$lng.".php";
			if (file_exists($fileName)){
				require_once($fileName);
				$cName = "SynsetInitialiser".$lng;
				$si = new $cName();
			}
		}

		if ( !class_exists($cName)) {
			require_once($IP."/extensions/EnhancedRetrieval/synsets/initialiser/SMW_SynsetInitialiserEn.php");
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
		require_once($IP."/extensions/EnhancedRetrieval/synsets/storage/SMW_SynsetStorageSQL.php");
		$st = new SynsetStorageSQL();
		return $st->getSynsets($term);


	}

	/**
	 * This method creates a string for query expansion based on synsets
	 *
	 * @param $terms array<string> : the terms
	 *
	 * @return string : additions to the query
	 */
	public static function expandQuery($terms){
		$synSets = new Synsets();
		$results = array();
		foreach($terms as $t){
			$syns = $synSets->getSynsets($t);
				
			$result = array();
			foreach($syns as $key => $value){
				foreach($value as $synonym){
					$result[$synonym] = true;
				}
			}
			$results[] = QueryExpander::opTerms(array_keys($result), "OR");
		}

		return $results;
	}


}


