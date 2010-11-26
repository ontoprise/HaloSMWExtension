<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file defines the class LODRatingAccess.
 * 
 * @author Thomas Schweitzer
 * Date: 11.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This is the main class for creating, retrieving and deleting ratings for 
 * triples.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODRatingAccess  {
	
	//--- Constants ---
	// The (local) name of the rating graph
	const LOD_RATING_GRAPH = "RatingsGraph";
	
	const LOD_TYPE_RATING  = "smw-lde:Rating";
	const LOD_PROP_RATES   = "smw-lde:rates";
	const LOD_PROP_RATED_INFORMATION = "smw-lde:ratedInformation";
	const LOD_PROP_CREATED = "smw-lde:created";
	const LOD_PROP_VALUE   = "smw-lde:value";
	const LOD_PROP_COMMENT = "smw-lde:comment";
	
	//--- Private fields ---
	
	// LODPersistentTripleStoreAccess
	// Used to store the ratings in the triple store.
	private $mTSA;
	
	// array(string => bool)
	// All prefixes that are needed to store the triples in the triple store.
	// The prefixes are stored as key. The value is of no interest.
	private $mNeededPrefixes = array();
	
	/**
	 * Constructor for  LODRatingAccess
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
		$this->mTSA = new LODPersistentTripleStoreAccess();
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	
	/**
	 * Adds a rating for the given triple and stores it in the triple store.
	 *
	 * @param LODTriple $triple
	 * 		The triple that is rated.
	 * @param LODRating $rating
	 * 		The rating for the triple
	 * 
	 */
	public function addRating(LODTriple $triple, LODRating $rating) {
		$hash = $this->makeHashForTriple($triple);
		$graphName = $this->addGraphWithTriple($triple, $hash);
		$this->addRatingToRatingGraph($graphName, $rating);
		$this->storeRating($hash);
	}
	
	/**
	 * Returns all ratings for $triple.
	 * 
	 * @param LODTriple $triple
	 * 		The ratings are retrieved for this triple.
	 * @return array<LODRating>
	 * 		An array of ratings. The array is empty, if there are no ratings
	 * 		for the triple.
	 */
	public function getRatings(LODTriple $triple) {
		$hash = $this->makeHashForTriple($triple);
		
		$pm = LODPrefixManager::getInstance();
		
		$graphName = "smwGraphs:RatingGraph_$hash";
		$graphName = $pm->makeAbsoluteURI($graphName);

		$ratingGraphName = "smwGraphs:".self::LOD_RATING_GRAPH;
		$ratingGraphName = $pm->makeAbsoluteURI($ratingGraphName);
		$query = $pm->getSPARQLPrefixes(array("smw-lde"));
		
		$query .= <<<SPARQL

SELECT *
WHERE {
  GRAPH $ratingGraphName {
  	?user smw-lde:rates ?bn .
  	?bn smw-lde:ratedInformation $graphName .
  	?bn smw-lde:created ?created .
  	?bn smw-lde:value ?value .
  	?bn smw-lde:comment ?comment .
  }
}		
SPARQL;
		
		$r = $this->mTSA->queryTripleStore($query);

		$userNS = $pm->getNamespaceURI("smwUsers");
		$userNSLen = strlen($userNS);
		
		$ratings = array();
		
		// Create instances of LODRating for the query results
		
		$rows = $r->getRows();
		foreach ($rows as $row) {
			$user = $row->getResult("user")->getValue();
			if (strpos($user, $userNS) === 0) {
				$user = substr($user, $userNSLen);
			}
			$ct = $row->getResult("created")->getValue();
			$value = $row->getResult("value")->getValue();
			$comment = $row->getResult("comment")->getValue();
			$ratings[] = new LODRating($value, $comment, $user, $ct);
		}
		
		return $ratings;
		
	}
	
	/**
	 * Deletes all ratings of the triple $triple.
	 * 
	 * @param LODTriple $triple
	 * 		The triple whose ratings are deleted.
	 * 
	 */
	public function deleteAllRatingsForTriple(LODTriple $triple) {
		$hash = $this->makeHashForTriple($triple);
		$this->mTSA->deletePersistentTriples("LODRating", $hash);
	}
	
	/**
	 * Returns all triples for the given rating key. The rating key is a combination
	 * of a query ID, a result row and a variable in the query.
	 * 
	 * @param string $ratingKey
	 * 		The rating key is needed for retrieving information about the query
	 * 		and its result from the database. Its format is 
	 * 		"query-ID|row index|variable name" e.g. "42|2|x".
	 * @return array(array(array(LODTriple), array(LODTriple)))
	 * 		The resulting array consists of two arrays of triples. The first
	 * 		array contains "primary" triples, the second "secondary" triples.
	 * 		Primary triples are those that contain the variable given in
	 * 		the rating key (e.g. x). Secondary triples are all other triples of
	 * 		the query that led to the actual result but that do not contain the
	 * 		variable.
	 * 		As there may be several solutions that lead to the requested primary
	 * 		triples, the whole structure of primary and secondary triples may
	 * 		appear several times.
	 * 
	 * @throws LODRatingException
	 * 		LODRatingException::WRONG_RATING_KEY
	 * 			If the rating key has an invalid format.
	 * 		LODRatingException::INVALID_QUERY_ID
	 * 			The query ID is invalid.
	 * 		LODRatingException::INVALID_ROW
	 * 			Results of an invalid row are requested.
	 * 
	 */
	public static function getTriplesForRatingKey($ratingKey) {
		list($queryID, $row, $var) = self::parseRatingKey($ratingKey);
		
		list($query, $queryParams) = self::getQueryForRatingKey($ratingKey);
		
		// Retrieve the complete row of results
		$db = LODStorage::getDatabase();
		$rowContent = $db->readQueryResultRow($queryID, $row);
		if (is_null($rowContent)) {
			throw new LODRatingException(LODRatingException::INVALID_ROW, $row);
		}
		
		// Create bindings for the query analyzer
		$bindings = array();
		foreach ($rowContent as $variable => $binding) {
			
			if (empty($binding[1])) {
				// binding has no data type => default is URI
				$bindings[] = new LODSparqlResultURI($variable, $binding[0]);
			} else {
				// binding is a literal with a type
				$bindings[] = new LODSparqlResultLiteral($variable, $binding[0],
														 $binding[1]);
			}
		}

		// Start the query analyzer
		$qa = new LODQueryAnalyzer($query, $queryParams, $bindings);
		$resultSets = $qa->bindAndGetAllTriples();

		// There may be several solutions with different variable bindings
		$solutions = array();
		foreach ($resultSets as $result) {
			// Get the primary triples
			$ptis = $result[$var];
			$primaryTripleInfo = array();
			$allTriples = array();
			foreach ($ptis as $pti) {
				if (!$pti->hasUnboundVarInTriple()) {
					$primaryTripleInfo[] = $pti->getTriple();
					$allTriples[] = $pti->getTriple();
				}
			}
			
			// Get the secondary triples
			$secondaryTripleInfo = array();
			foreach ($result as $variable => $tripleInfos) {
				if ($variable !== $var) {
					foreach ($tripleInfos as $ti) {
						$t = $ti->getTriple();
						if (!in_array($t, $allTriples)
						    && !$ti->hasUnboundVarInTriple()) {
							$secondaryTripleInfo[] = $t;
							$allTriples[] = $t;
						}
					}
				}
			}
			$solutions[] = array($primaryTripleInfo, $secondaryTripleInfo); 
		}
		return $solutions;
	}
	
	/**
	 * Retrieves the query that belongs to the given rating key.
	 * 
	 * @param string $ratingKey
	 * 		The rating key is needed for retrieving information about the query
	 * 		and its result from the database. Its format is 
	 * 		"query-ID|row index|variable name" e.g. "42|2|x".
	 * @return array(string query, array queryParams)
	 * 		The query as string and its parameters as array.
	 * 
	 * @throws LODRatingException
	 * 		LODRatingException::WRONG_RATING_KEY
	 * 			If the rating key has an invalid format.
	 * 		LODRatingException::INVALID_QUERY_ID
	 * 			The query ID is invalid.
	 */
	public static function getQueryForRatingKey($ratingKey) {
		list($queryID, $row, $var) = self::parseRatingKey($ratingKey);
		
		$db = LODStorage::getDatabase();

		// Retrieve the query
		$query = $db->getQueryByID($queryID);
		if (is_null($query)) {
			throw new LODRatingException(LODRatingException::INVALID_QUERY_ID, $queryID);
		}
		$params = $db->getQueryParamsByID($queryID);
		return array($query, $params);
	}
		
	/**
	 * This is a callback function for the hook "ProcessSPARQLXMLResults" which
	 * is called from the triples store after the results of a query were retrieved
	 * in form of SPARQL-XML.
	 * 
	 * This function adds meta-data to each query result i.e. it augments each 
	 * value with rating key which consists of
	 * - the query ID
	 * - the index of the row of a result
	 * - the variable a result value is bound to 
	 * 
	 * @param SMWQuery $query
	 * 		The original query whose result can now be processed.
	 * @param string $sparqlXML
	 * 		The result of the query in form of SPARQL-XML (with possible meta-data).
	 * 		This result is augmented.
	 * 
	 * @return bool
	 * 		Returns always true
	 * 
	 */
	public static function onProcessSPARQLXMLResults(SMWQuery $query, &$sparqlXML) {
		// The result is only processed if the parameter "enableRating" is set "true".
		if (!(isset($query->params) 
			  && array_key_exists('enablerating', $query->params)
			  && $query->params['enablerating'] == "true")) {
			return true;
		}
		
		global $wgRequest;
		
		$articleName = $wgRequest->getText("title");
		
		$dom = simplexml_load_string($sparqlXML);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		if ($dom === FALSE) {
			return true;
		}

		// Store the query and its results in the database
		$db = LODStorage::getDatabase();
		$params = @$query->params;
		if (!isset($params)) {
			$params = array();
		}
		$queryID = $db->addQuery($query->getQueryString(), $params, $articleName);
		
		$results = $dom->xpath('//sparqlxml:result');
		$row = 0;
		foreach ($results as $r) {
			// Each result has a binding for each variable
			$bindings = $r->binding;
			foreach ($bindings as $b) {
				// A binding is bound to a variable. Its value can be a URI or
				// a literal. (We do not support blank nodes.)
				$attrib = $b->attributes();
				$variableName = (string) $attrib['name'];
				
				$insertNode = null;
				$dataType = null;
				$value = 0;
				if (isset($b->uri)) {
					// The value is an URI
					$insertNode = $b->uri;
					$value = (string) $b->uri;
				} else if (isset($b->literal)) {
					// The value is a literal
					$insertNode = $b->literal;
					$attrib = $b->literal->attributes();
					$dataType = isset($attrib['datatype'])
									? (string) $attrib['datatype'] 
									: null;
					$value = (string) $b->literal;
				}
				
				if (!is_null($insertNode)) {
					// Store the binding in the rating result table
					$db->storeQueryResultRow($queryID, $row, $variableName, $value, $dataType);
					
					// Add the rating key the query result
					$meta = $insertNode->addChild("metadata");
					$meta->addAttribute("name", "rating-key");
					$v = $meta->addChild("value", "$queryID|$row|$variableName");
					
				}
			}
			++$row;
		}
		$sparqlXML = $dom->asXML();

		return true;
		
	}
	
	/**
	 * This method is called, when an article is deleted. All stored queries
	 * that belong to the article and their results are deleted.
	 *
	 * @param Article $article
	 * 		The article that will be deleted.
	 * @param User $user
	 * 		The user who deletes the article.
	 * @param string $reason
	 * 		The reason, why the article is deleted.
	 */
	public static function onArticleDelete(&$article, &$user, &$reason) {
		$name = $article->getTitle()->getFullText();
		$db = LODStorage::getDatabase();
    	$db->deleteQueries($name);
    	return true;
	}
	
	/**
	 * 
	 * Occurs whenever the software receives a request to save an article.
	 * The article may contain queries whose content and results may be stored.
	 * This data will be deleted.
	 * @param Article $article
	 * 		The article that will be saved.
	 */
	public static function onArticleSave(&$article) {
		$name = $article->getTitle()->getFullText();
		$db = LODStorage::getDatabase();
    	$db->deleteQueries($name);
		return true;
	}
	
	//--- Private methods ---
	
	/**
	 * Flushes all commands for triples to the triple store.
	 *  
	 * @param string $hash
	 * 		This hash value is used as ID for the persistence layer of the 
	 * 		triple store.
	 */
	private function storeRating($hash) {
	
		$this->mTSA->flushCommands("LODRating", $hash);
	}
	
	/**
	 * Creates a hash value for the given $triple.
	 * 
	 * @param LODTriple $triple
	 * 		The triple for which a hash value will be created.
	 * @return string
	 * 		The hash value.
	 */
	private function makeHashForTriple(LODTriple $triple) {
		$pm = LODPrefixManager::getInstance();		
		// get the absolute URIs of the triple's elements
		$s = $triple->getSubject();
		$s = $pm->makeAbsoluteURI($s);

		$p = $triple->getPredicate();
		$p = $pm->makeAbsoluteURI($p);
		
		$o = $triple->getObject();
		if (!$triple->isObjectLiteral()) {
			$o = $pm->makeAbsoluteURI($p);
		}
		
		$hash = hash("md5", "$s||$p||$o");
		return $hash;
	}
	
	/**
	 * Each triple which is rated is duplicated in its own rating graph. The
	 * name of this graph must be unique and can be recreated from the triple
	 * it contains.
	 * 
	 * This method generates commands for the triple store that
	 * - create a rating graph whose name contains the $hash value
	 * - add the triple to the new graph
	 * 
	 * @param LODTriple $triple
	 * 		The triple which is added to the new rating graph
	 * @param string $hash
	 * 		The hash value for the given triple
	 * @return string
	 * 		The name of the rating graph for the triple.
	 */
	private function addGraphWithTriple(LODTriple $triple, $hash) {
		
		$graphName = "smwGraphs:RatingGraph_$hash";
		$graphName = LODPrefixManager::getInstance()->makeAbsoluteURI($graphName, false);
		
		// Collect all required prefixes
		$this->mNeededPrefixes["smwGraphs"] = true;
		$triplePrefixes = $triple->getPrefixes();
		foreach ($triplePrefixes as $tp) {
			if (!empty($tp)) {
				$this->mNeededPrefixes[$tp] = true;
			}
		}
		
		// Create the graph
		$this->addPrefixesToTSA();
		$this->mTSA->createGraph($graphName, false);
		
		// Insert the triple
		$this->mTSA->insertTriples($graphName, array($triple));
		
		return $graphName;
	}
	
	/**
	 * Adds the $rating for the graph with the name $graphName to the rating 
	 * graph.
	 * 
	 * @param string $graphName
	 * @param LODRating $rating
	 */
	private function addRatingToRatingGraph($graphName, $rating) {
		$ratingGraphName = "smwGraphs:".self::LOD_RATING_GRAPH;
		$ratingGraphName = LODPrefixManager::getInstance()->makeAbsoluteURI($ratingGraphName, false);
		
		// Collect all required prefixes
		$this->mNeededPrefixes["smwGraphs"] = true;
		$this->mNeededPrefixes["smwUsers"] = true;
		$this->mNeededPrefixes["smw-lde"] = true;
		$this->mNeededPrefixes["xsd"] = true;
		$this->mNeededPrefixes["rdf"] = true;
		
		$this->addPrefixesToTSA();
		
		$this->mTSA->createGraph($ratingGraphName, false);
		
		$user = urlencode($rating->getAuthor());
		
		$triples = array();
		$bn = "_:1";
		$triples[] = new LODTriple("smwUsers:$user", self::LOD_PROP_RATES, $bn, "__blankNode");
		$triples[] = new LODTriple($bn, "rdf:type", self::LOD_TYPE_RATING, "__objectURI");
		$triples[] = new LODTriple($bn, self::LOD_PROP_RATED_INFORMATION, $graphName, "__objectURI");
		$triples[] = new LODTriple($bn, self::LOD_PROP_CREATED, $rating->getCreationTime(), "xsd:dateTime");
		$triples[] = new LODTriple($bn, self::LOD_PROP_VALUE, $rating->getValue(), "xsd:string");
		$triples[] = new LODTriple($bn, self::LOD_PROP_COMMENT, $rating->getComment(), "xsd:string");

		$this->mTSA->insertTriples($ratingGraphName, $triples);
		
	}
	
	/**
	 * Adds the collected prefixes to the TSA and resets the array of needed 
	 * prefixes.
	 */
	private function addPrefixesToTSA() {
		$pm = LODPrefixManager::getInstance();
		$prefixes = array_keys($this->mNeededPrefixes);
		$prefixSPARQL = $pm->getSPARQLPrefixes($prefixes);
		$this->mTSA->addPrefixes($prefixSPARQL);
		$this->mNeededPrefixes = array();
	}
	
	/**
	 * Parses a rating key and returns an array with queryID, row and variable.
	 * 
	 * @param string $ratingKey
	 * 		The rating key is needed for retrieving information about the query
	 * 		and its result from the database. Its format is 
	 * 		"query-ID|row index|variable name" e.g. "42|2|x".
	 * @return array(string, string, string)
	 * 		The resulting array consists the queryID, row and variable
	 * 
	 * @throws LODRatingException
	 * 		LODRatingException::WRONG_RATING_KEY
	 * 			If the rating key has an invalid format.
	 * 
	 */
	private static function parseRatingKey($ratingKey) {
		// parse the rating key
		$rk = null;
		if (preg_match("/^(\d+)\|(\d+)\|(\S+)$/", $ratingKey, $rk) == 0) {
			throw new LODRatingException(LODRatingException::WRONG_RATING_KEY, $ratingKey);
		}
		array_shift($rk);
		return $rk;
		
	}
	
}