<?php
/*  Copyright 2011, ontoprise GmbH
*  This file is part of the Faceted Search Module of the Enhanced Retrieval Extension.
*
*   The Enhanced Retrieval Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Enhanced Retrieval Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the class FSSolrSMWDB. It creates the index from the database
 * tables of SMW.
 * 
 * @author Thomas Schweitzer
 * Date: 22.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}

/**
 * This class is the indexer for the SMW database tables.
 * 
 * @author thsc
 *
 */
class FSSolrSMWDB extends FSSolrIndexer {

	//--- Private fields ---
	
	
	//--- getter/setter ---
	
	//--- Public methods ---

	
	/**
	 * Creates a new FSSolrSMWDB indexer object.
	 * @param string $host
	 * 		Name or IP address of the host of the server
	 * @param int $port
	 * 		Server port of the Solr server
	 */
	public function __construct($host, $port) {
		parent::__construct($host, $port);
	}
	
	/**
	 * Updates the index for the given $article.
	 * It retrieves all semantic data of the new version and adds it to the index.
	 * 
	 * @param Article $article
	 * 		The article that changed.
	 */
	public function updateIndexForArticle(Article $article, $user, $text) {
		$doc = array();
		
		$db =& wfGetDB( DB_SLAVE );
		
		// Get the page ID of the article
		$t = $article->getTitle();
		$pid = $t->getArticleID();
		$pns = $t->getNamespace();
		$pt  = $t->getDBkey();
		
		$doc['id'] = $pid;
		$doc['smwh_namespace_id'] = $pns;
		$doc['smwh_title'] = $pt;
		$doc['smwh_full_text'] = $text;
		
		// Get the categories of the article
		$this->retrieveCategories($db, $pid, $doc);
		if ($this->retrieveSMWID($db, $pns, $pt, $doc)) {
			$smwID = $doc['smwh_smw_id'];
			$this->retrieveRelations($db, $smwID, $doc);
			$this->retrieveAttributes($db, $smwID, $doc);
			$this->retrieveTextAttributes($db, $smwID, $doc);
		}
		// Let the super class update the index
		$this->updateIndex($doc);
	}
	/**
	 * Updates the index for a moved article.
	 * 
	 * @param int $oldid
	 * 		Old page ID of the article
	 * @param $newid
	 * 		New page ID of the article
	 * @return bool
	 * 		<true> if the document in the index for the article was moved
	 * 				successfully
	 * 		<false> otherwise
	 */
	public function updateIndexForMovedArticle($oldid, $newid) {
		if ($this->deleteDocument($oldid)) {
			global $wgUser;
			// The article with the new name has the same page id as before
			$article = Article::newFromID($oldid);
			$text = $article->getContent();
			return $this->updateIndexForArticle($article, $wgUser, $text);
		}
		return false;
	}
	
	//--- Private methods ---
	
	/**
	 * Retrieves the categories of the article with the page ID $pid and adds
	 * them to the document description $doc.
	 * 
	 * @param Database $db
	 * 		The database object
	 * @param int $pid
	 * 		The page ID.
	 * @param array $doc
	 * 		The document description. If the page belongs to categories, an array
	 * 		of names is added with the key 'smwh_categories'.
	 */
	private function retrieveCategories($db, $pid, array &$doc) {
		$categorylinks = $db->tableName('categorylinks');

		$sql = <<<SQL
			SELECT CAST(c.cl_to AS CHAR) cat
			FROM $categorylinks c
			WHERE cl_from=$pid
SQL;
		$res = $db->query($sql);
		if ($db->numRows($res) > 0) {
			$doc['smwh_categories'] = array();
			while ($row = $db->fetchObject($res)) {
				$cat = $row->cat;
				$doc['smwh_categories'][] = $cat;
			}
		}
		$db->freeResult($res);
		
	}

	/**
	 * Retrieves the SMW-ID of the article with the $namespaceID and the $title
	 * and adds them to the document description $doc.
	 * 
	 * @param Database $db
	 * 		The database object
	 * @param int $namespaceID
	 * 		Namespace ID of the article
	 * @param string $title
	 * 		The DB key of the title of the article
	 * @param array $doc
	 * 		The document description. If there is a SMW ID for the article, it is 
	 * 		added with the key 'smwh_smw_id'.
	 * @return bool
	 * 		<true> if an SMW-ID was found
	 * 		<false> otherwise
	 */
	private function retrieveSMWID($db, $namespaceID, $title, array &$doc) {
		// Get the SMW ID for the page
//        $title = str_replace("'", "\'", $title);
        $title = mysql_real_escape_string($title);
		$smw_ids = $db->tableName('smw_ids');
		$sql = <<<SQL
			SELECT s.smw_id as smwID
			FROM $smw_ids s
			WHERE s.smw_namespace=$namespaceID AND 
			      s.smw_title='$title'
SQL;
		$found = false;
		$res = $db->query($sql);
		if ($db->numRows($res) > 0) {
			$row = $db->fetchObject($res);
			$smwID = $row->smwID;
			$doc['smwh_smw_id'] = $smwID;
			$found = true;
		}
		$db->freeResult($res);
		
		return $found;		
				
	}
	
	/**
	 * Retrieves the relations of the article with the SMW ID $smwID and adds
	 * them to the document description $doc.
	 * 
	 * @param Database $db
	 * 		The database object
	 * @param int $smwID
	 * 		The SMW ID.
	 * @param array $doc
	 * 		The document description. If the page has relations, all relations
	 * 		and their values are added to $doc. The key 'smwh_properties' will
	 * 		be an array of relation names and a key will be added for each 
	 * 		relation with the value of the relation.
	 */
	private function retrieveRelations($db, $smwID, array &$doc) {
		$smw_rels2 = $db->tableName('smw_rels2');
		$smw_ids   = $db->tableName('smw_ids');

		$sql = <<<SQL
			SELECT CAST(pids.smw_title AS CHAR) as prop, 
                   CAST(oids.smw_title AS CHAR) as obj
	        FROM $smw_rels2 AS r
	        LEFT JOIN ($smw_ids as pids) ON (pids.smw_id = r.p_id)
	        LEFT JOIN ($smw_ids as oids) ON (oids.smw_id = r.o_id)
	        WHERE r.s_id=$smwID
SQL;
		$res = $db->query($sql);
		if ($db->numRows($res) > 0) {
			$properties = array();
			while ($row = $db->fetchObject($res)) {
				$prop = $row->prop;
				$obj  = $row->obj;
				
				// The values of all properties are stored as string.
        		$prop = "smwh_{$prop}_t";
				if (!array_key_exists($prop, $doc)) {
					$doc[$prop] = array();
				}
        		$doc[$prop][] = $obj;
        		// Store the names of all properties in the article
				$properties[] = $prop;
			}
			$doc['smwh_properties'] = array_unique($properties);
		}
		$db->freeResult($res);
		
	}
	
	/**
	 * Retrieves the attributes of the article with the SMW ID $smwID and adds
	 * them to the document description $doc.
	 * 
	 * @param Database $db
	 * 		The database object
	 * @param int $smwID
	 * 		The SMW ID.
	 * @param array $doc
	 * 		The document description. If the page has attributes, all attributes
	 * 		and their values are added to $doc. The key 'smwh_attributes' will
	 * 		be an array of attribute names and a key will be added for each 
	 * 		attribute with the value of the attribute.
	 */
	private function retrieveAttributes($db, $smwID, array &$doc) {
		$smw_atts2 = $db->tableName('smw_atts2');
		$smw_ids   = $db->tableName('smw_ids');
		$smw_spec2 = $db->tableName('smw_spec2');

		$sql = <<<SQL
			SELECT CAST(pids.smw_title AS CHAR) as prop,
                   CAST(a.value_xsd AS CHAR) as valueXSD,
                   a.value_num as valueNum,
                   a.value_unit as valueUnit,
                   CAST(spec.value_string AS CHAR) as type
            FROM $smw_atts2 AS a
            LEFT JOIN ($smw_ids as pids) ON (pids.smw_id = a.p_id)
            LEFT JOIN ($smw_spec2 as spec) ON (a.p_id = spec.s_id)
            WHERE a.s_id=$smwID AND 
                  (LEFT(spec.value_string,1) ='_' OR spec.s_id IS NULL)
SQL;
		$res = $db->query($sql);
		if ($db->numRows($res) > 0) {
			$attributes = array();
			while ($row = $db->fetchObject($res)) {
				$prop = $row->prop;
				$valueXSD  = $row->valueXSD;
				$valueNum  = $row->valueNum;
				$valueUnit = $row->valueUnit;
				$type  = $row->type;
				
				// The values of all attributes are stored according to their type.
        		$typeSuffix = 't';
        		$isNumeric = false;
        		if ($type == '_dat' ||
        			$prop == 'Modification_date' ||
        			$prop == 'Creation_date') {
        			// Given format of a date: 1995/12/31T23:59:59
        			// Required format: 1995-12-31T23:59:59Z
        			$dateTime = explode("T", $valueXSD);
        			$date = $dateTime[0];
        			$date = str_replace('/', '-', $date);
        			$time = count($dateTime) > 1 && !empty($dateTime[1])
        					? $dateTime[1] : '00:00:00';
        			$valueXSD = "{$date}T{$time}Z";
        			$typeSuffix = 'dt';
					// Store a date/time also as long e.g. 19951231235959
					// This is needed for querying statistics for dates
					// Normalize month and day e.g. 1995-1-1 => 1995-01-01 
					$ymd = explode('-', $date);
					$m = (strlen($ymd[1]) == 1) ? '0'.$ymd[1] : $ymd[1];
					$d = (strlen($ymd[2]) == 1) ? '0'.$ymd[2] : $ymd[2];
					$dateTime = $ymd[0] . $m . $d . str_replace(':', '', $time);
					$propDate = 'smwh_' . $prop . '_datevalue_l';
					if (!array_key_exists($propDate, $doc)) {
						$doc[$propDate] = array();
					}
					$doc[$propDate][] = $dateTime;
        		} else if (
	        		$type == '_txt' ||
	        		$type == '_cod' ||
	        		$type == '_str' ||
	        		$type == '_ema' ||
	        		$type == '_uri' ||
	        		$type == '_anu' ||
	        		$type == '_tel' ||
	        		$type == '_tem' ||
	        		$type == '_rec') {
        			$typeSuffix = 't';
        		} else if ($type == '_num') {
        			$typeSuffix = 'd';
        			$isNumeric = true;
        		} else if ($type == '_boo') {
        			$typeSuffix = 'b';
        		}

        		$propXSD = "smwh_{$prop}_xsdvalue_$typeSuffix";
				if (!array_key_exists($propXSD, $doc)) {
					$doc[$propXSD] = array();
				}
        		$doc[$propXSD][] = $valueXSD;
        		// Store the names of all attributes in the article
				$attributes[] = $propXSD;
        		
        		if ($isNumeric) {
        			$propNum = "smwh_{$prop}_numvalue_d";
        			if (!array_key_exists($propNum, $doc)) {
        				$doc[$propNum] = array();
        			}
        			$doc[$propNum][] = $valueNum;
        			if (!empty($valueUnit)) {
        				$doc["smwh_{$prop}_unit_s"] = $valueUnit;
        			}
        		}
			}
			$doc['smwh_attributes'] = array_unique($attributes);
		}
		$db->freeResult($res);
		
	}
	
	/**
	 * Retrieves the attributes with type text of the article with the SMW ID 
	 * $smwID and adds them to the document description $doc.
	 * 
	 * @param Database $db
	 * 		The database object
	 * @param int $smwID
	 * 		The SMW ID.
	 * @param array $doc
	 * 		The document description. If the page has text attributes, all 
	 *		the attributes and their values are added to $doc. The key 
	 *		'smwh_attributes' will be an array of attribute names and a key will 
	 *		be added for each attribute with the value of the attribute.
	 */
	private function retrieveTextAttributes($db, $smwID, array &$doc) {
		$smw_text2 = $db->tableName('smw_text2');
		$smw_ids   = $db->tableName('smw_ids');

		$sql = <<<SQL
			SELECT CAST(pids.smw_title AS CHAR) as attr,
  				   CAST(t.value_blob AS CHAR) as text
			FROM $smw_text2 AS t
  			LEFT JOIN ($smw_ids as pids) ON (pids.smw_id = t.p_id)
  			WHERE t.s_id=$smwID
SQL;
		$res = $db->query($sql);
		if ($db->numRows($res) > 0) {
			$attributes = array();
			while ($row = $db->fetchObject($res)) {
				$attr = $row->attr;
				$text  = $row->text;
				
				// The values of all text attributes are stored as text.
        		$attr = "smwh_{$attr}_xsdvalue_t";
				if (!array_key_exists($attr, $doc)) {
					$doc[$attr] = array();
				}
        		$doc[$attr][] = $text;
        		// Store the names of all attributes in the article
				$attributes[] = $attr;
			}
			$attributes = array_unique($attributes);
			if (array_key_exists('smwh_attributes', $doc)) {
				// add new attributes
				$doc['smwh_attributes'] = array_merge($doc['smwh_attributes'], 
				                                      $attributes);
			} else {
				$doc['smwh_attributes'] = $attributes;
			}
		}
		$db->freeResult($res);
		
	}
	
	
	
}

