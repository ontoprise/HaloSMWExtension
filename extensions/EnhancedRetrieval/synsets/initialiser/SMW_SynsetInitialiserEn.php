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
 * @ingroup EnhancedRetrievalSynsetLanguage
 * 
 * Created on 18.02.2009
 *
 * @author Ingo Steinbauer
 */

global $IP;
require_once($IP."/extensions/EnhancedRetrieval/synsets/SMW_ISynsetInitialiser.php");

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
		require_once($IP."/extensions/EnhancedRetrieval/synsets/storage/SMW_SynsetStorageSQL.php");
		$synsetStorage = new SynsetStorageSQL();
		
		$fr = fopen ($IP.'/extensions/EnhancedRetrieval/synsets/initialiser/wn_s_en.pl', 'r' );

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
		require_once($IP."/extensions/EnhancedRetrieval/synsets/storage/SMW_SynsetStorageSQL.php");
		$synsetStorage = new SynsetStorageSQL();

		$fr = fopen ($IP.'/extensions/EnhancedRetrieval/synsets/initialiser/smw_synsets_En.sql', 'r' );
		$synsetStorage->importSQLDump($fr);
		fclose($fr);
	}
		
}

