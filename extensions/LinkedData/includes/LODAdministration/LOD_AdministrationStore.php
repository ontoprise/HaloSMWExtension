<?php
/**
 * @file
 * @ingroup LinkedDataAdministration
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
 * This file defines the class LODAdministrationStore.
 * 
 * @author Thomas Schweitzer
 * Date: 28.04.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}


 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class handles the storage of a Linked Data source description in the
 * triple store. Source descriptions can be saved, loaded and delete from the
 * store.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  LODAdministrationStore  {
	
	//--- Constants ---
	const LOD_SOURCE_DEFINITION_GRAPH = "DataSourceInformationGraph";
	const LOD_BASE_URI = "http://smw/";
	const LOD_SOURCE_DEFINITION_URI_SUFFIX = "sd#";
	const LOD_SOURCE_DEFINITION_PROPERTY_SUFFIX = "sdprop#";
	
	//--- Private fields ---
	
	// LODAdministrationStore
	// Points to the singleton instance of the LODAdministration store.
	private static $mInstance;

	
	/**
	 * Constructor for  LODAdministrationStore
	 */		
	protected function __construct() {
		self::$mInstance = $this;
		// Initialize Triple store connection (Needed for the initialization of
		// TSNamespaces.)
		TSConnection::getConnector(); 
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	/**
	 * Returns the singleton instance of this class.
	 * 
	 * @return LODAdministrationStore
	 * 		The only instance of this class.
	 *
	 */
	public static function getInstance() {
		if (!self::$mInstance) {
			$c = __CLASS__;
			new $c;
		}
		return self::$mInstance;
	}
	
	/**
	 * Stores the definition of a Linked Data source in the triple store. Two
	 * new namespaces are used:
	 * -sd (source definition)
	 * -sdprop (source definition property)
	 * The namespaces are a concatenation of the constants LOD_BASE_URI and
	 * LOD_SOURCE_DEFINITION_URI_SUFFIX or LOD_SOURCE_DEFINITION_PROPERTY_SUFFIX,
	 * repectively.
	 * 
	 * Data source definitions are stored in a dedicated graph defined by LOD_BASE_URI
	 * and LOD_SOURCE_DEFINITION_GRAPH.
	 * 
	 * The fields of the data source definition are stored as follows: 
	 * - The subjects of all triples for a data source are based on its ID.
	 * - The following properties are used (with given cardinality and type):
	 * sdprop:id 						^^xsd:string 	(1) 
	 * sdprop:importanceIndex			^^xsd:int 		(1)
	 * sdprop:description				^^xsd:string 	(0..1) 
	 * rdfs:label 						^^xsd:string 	(0..1) 
	 * sdprop:mappingID 				^^xsd:string 	(1) 
	 * sdprop:linkedDataPrefix 			^^xsd:string 	(0..1)
	 * sdprop:uriRegexPattern 			^^xsd:string 	(0..1)
	 * sdprop:homepage 					^^xsd:anyURI 	(0..1)
	 * sdprop:sampleURI 				^^xsd:anyURI 	(0..*)
	 * sdprop:sparqlEndpointLocation 	^^xsd:anyURI 	(0..1)
	 * sdprop:sparqlGraphName 			^^xsd:anyURI 	(0..1)
	 * sdprop:dataDumpLocation 			^^xsd:anyURI 	(0..*)
	 * sdprop:lastMod 					^^xsd:dateTime	(0..1)
	 * sdprop:changeFreq 				^^xsd:string 	(0..1)
	 * sdprop:vocabulary 				^^xsd:anyURI 	(0..*)
	 * 
	 * @param LODSourceDefinition $sd
	 * 		This object defines a linked data source.
	 * 
	 * @return bool 
	 * 		<true> if the source definition was stored successfully or
	 * 		<false> otherwise
	 */
	public function storeSourceDefinition(LODSourceDefinition $sd) {
		
		// create the triples for the source definition
		$subjectNS = "sd:";
		$subject = $sd->getID();
		if (!isset($subject)) {
			// The ID of a data source definition is mandatory
			return false;
		}
		$subject = $subjectNS.$subject;

		$propNS = "sdprop:";
		$triples = array();
		
		$triples[] = new LODTriple($subject, $propNS."id", $sd->getID(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."importanceIndex", $sd->getImportanceIndex(), "xsd:int");
		$triples[] = new LODTriple($subject, $propNS."description", $sd->getDescription(), "xsd:string");
		$triples[] = new LODTriple($subject, "rdfs:label", $sd->getLabel(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."mappingID", $sd->getMappingID(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."linkedDataPrefix", $sd->getLinkedDataPrefix(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."uriRegexPattern", $sd->getUriRegexPattern(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."homepage", $sd->getHomepage(), "xsd:anyURI");

		if (is_array($sd->getSampleURIs())) {
			foreach ($sd->getSampleURIs() as $uri) {
				$triples[] = new LODTriple($subject, $propNS."sampleURI", $uri, "xsd:anyURI");
		 	}
		}

	 	$triples[] = new LODTriple($subject, $propNS."sparqlEndpointLocation", $sd->getSparqlEndpointLocation(), "xsd:anyURI");
	 	$triples[] = new LODTriple($subject, $propNS."sparqlGraphName", $sd->getSparqlGraphName(), "xsd:anyURI");
		if (is_array($sd->getDataDumpLocations())) {
		 	foreach ($sd->getDataDumpLocations() as $ddl) {
		 		$triples[] = new LODTriple($subject, $propNS."dataDumpLocation", $ddl, "xsd:anyURI");
		 	}
		}

	 	$triples[] = new LODTriple($subject, $propNS."lastMod", $sd->getLastMod(), "xsd:dateTime");
	 	$triples[] = new LODTriple($subject, $propNS."changeFreq", $sd->getChangeFreq(), "xsd:string");

		if (is_array($sd->getVocabularies())) {
		 	foreach ($sd->getVocabularies() as $voc) {
		 		$triples[] = new LODTriple($subject, $propNS."vocabulary", $voc, "xsd:anyURI");
		 	}
		}

		$graph = self::LOD_BASE_URI.self::LOD_SOURCE_DEFINITION_GRAPH;
		
		$tsa = new LODTripleStoreAccess();
		$tsa->addPrefixes(TSNamespaces::getW3CPrefixes()
			              .self::getSourceDefinitionPrefixes());
		$tsa->createGraph($graph);
		$tsa->deleteTriples($graph, "$subject ?p ?o", "$subject ?p ?o");
		$tsa->insertTriples($graph, $triples);
		$tsa->flushCommands();
		
		return true;
	}
	
	/**
	 * Loads the definition of a linked data source from the triple store.
	 *
	 * @param string $sourceID
	 * 		ID of the linked data source
	 * 
	 * @return LODSourceDefinition
	 * 		The definition of the source or <null>, if there is no such source
	 * 		with the given ID.
	 */
	public function loadSourceDefinition($sourceID) {
		$graph = self::LOD_BASE_URI.self::LOD_SOURCE_DEFINITION_GRAPH;
		$subjectNS = "sd:";
		$subject = $subjectNS.$sourceID;
		$prefixes = self::getSourceDefinitionPrefixes();
		
		$query = $prefixes."SELECT ?p ?o FROM <$graph> WHERE { $subject ?p ?o . }";
		
		$tsa = new LODTripleStoreAccess();
		
		$result = $tsa->queryTripleStore($query, $graph);
		
		if (!$result || count($result->getRows()) == 0) {
			return null;
		}
		$result = $result->toTable();
		
		// Convert results into an array of key-value pairs.
		$properties = array();
		foreach ($result as $keyValue) {
			if (!array_key_exists($keyValue[0], $properties)) {
				$properties[$keyValue[0]] = array();
			}
			$properties[$keyValue[0]][] = $keyValue[1];
		}
		
		// Create a source definition object.
		$propNS = self::LOD_BASE_URI.self::LOD_SOURCE_DEFINITION_PROPERTY_SUFFIX;
		$rdfs = "http://www.w3.org/2000/01/rdf-schema#";
		$sd = new LODSourceDefinition($properties["{$propNS}id"][0]);
		if (array_key_exists("{$propNS}importanceIndex", $properties)) {
			$sd->setImportanceIndex($properties["{$propNS}importanceIndex"][0]+0);
		}
		if (array_key_exists("{$propNS}description", $properties)) {
			$sd->setDescription($properties["{$propNS}description"][0]);
		}
		if (array_key_exists("{$rdfs}label", $properties)) {
			$sd->setLabel($properties["{$rdfs}label"][0]);
		}
		if (array_key_exists("{$propNS}mappingID", $properties)) {
			$sd->setMappingID($properties["{$propNS}mappingID"][0]);
		}
		if (array_key_exists("{$propNS}linkedDataPrefix", $properties)) {
			$sd->setLinkedDataPrefix($properties["{$propNS}linkedDataPrefix"][0]);
		}
		if (array_key_exists("{$propNS}uriRegexPattern", $properties)) {
			$sd->setUriRegexPattern($properties["{$propNS}uriRegexPattern"][0]);
		}
		if (array_key_exists("{$propNS}homepage", $properties)) {
			$sd->setHomepage($properties["{$propNS}homepage"][0]);
		}
		if (array_key_exists("{$propNS}sampleURI", $properties)) {
			$sd->setSampleURIs($properties["{$propNS}sampleURI"]);
		}
		if (array_key_exists("{$propNS}sparqlEndpointLocation", $properties)) {
			$sd->setSparqlEndpointLocation($properties["{$propNS}sparqlEndpointLocation"][0]);
		}
		if (array_key_exists("{$propNS}sparqlGraphName", $properties)) {
			$sd->setSparqlGraphName($properties["{$propNS}sparqlGraphName"][0]);
		}
		if (array_key_exists("{$propNS}dataDumpLocation", $properties)) {
			$sd->setDataDumpLocations($properties["{$propNS}dataDumpLocation"]);
		}
		if (array_key_exists("{$propNS}lastMod", $properties)) {
			$sd->setLastMod($properties["{$propNS}lastMod"][0]);
		}
		if (array_key_exists("{$propNS}changeFreq", $properties)) {
			$sd->setChangeFreq($properties["{$propNS}changeFreq"][0]);
		}
		if (array_key_exists("{$propNS}vocabulary", $properties)) {
			$sd->setVocabularies($properties["{$propNS}vocabulary"]);
		}
		
		return $sd;
	}
	
	/**
	 * Deletes the source definition with the ID $sourceID from the triple store.
	 *
	 * @param string $sourceID
	 * 		ID of the source definition.
	 */
	public function deleteSourceDefinition($sourceID) {
		$subjectNS = "sd";
		$subject   = $subjectNS.":".$sourceID;
		
		$tsa = new LODTripleStoreAccess();
		$tsa->addPrefixes(TSNamespaces::getW3CPrefixes()
			              .self::getSourceDefinitionPrefixes());
		$tsa->deleteTriples(self::LOD_BASE_URI.self::LOD_SOURCE_DEFINITION_GRAPH, 
							"$subject ?p ?o", "$subject ?p ?o");
		$tsa->flushCommands();
	}
	
	/**
	 * Deletes the complete graph for source definitions with all its content.
	 * This method should only be called for maintenance purposes. 
	 *
	 */
	public function deleteAllSourceDefinitions() {
		$tsa = new LODTripleStoreAccess();
		$tsa->dropGraph(self::LOD_BASE_URI.self::LOD_SOURCE_DEFINITION_GRAPH);
		$tsa->flushCommands();
	}
	
	/**
	 * Every LOD source definition has an ID. This method returns all IDs of 
	 * definitions that are stored in the triple store.
	 *
	 * @return array<string>
	 * 		An array of all IDs. If no ID is available, the array is empty.
	 */
	public function getAllSourceDefinitionIDs() {
		$graph = self::LOD_BASE_URI.self::LOD_SOURCE_DEFINITION_GRAPH;
		$id    = "sdprop:id";
		$prefixes = self::getSourceDefinitionPrefixes();
		
		$query = $prefixes."SELECT ?s ?id FROM <$graph> WHERE { ?s $id  ?id . }";
		
		$tsa = new LODTripleStoreAccess();
		$qr = $tsa->queryTripleStore($query, $graph);
		
		$result = array();
		foreach ($qr->getRows() as $row) {
			$result[] = $row->getResult("id")->getValue();
		}
		return $result;
	}
	
	//--- Private methods ---
	
	/**
	 * Returns the prefixes for all namespaces that are used in triples for
	 * storing LOD source definitions.
	 * 
	 * @return string
	 * 		Namespace prefixes
	 *
	 */
	private function getSourceDefinitionPrefixes() {
		return
			 "PREFIX sd:<".self::LOD_BASE_URI.self::LOD_SOURCE_DEFINITION_URI_SUFFIX."> \n"
			."PREFIX sdprop:<".self::LOD_BASE_URI.self::LOD_SOURCE_DEFINITION_PROPERTY_SUFFIX."> \n\n";
	}
}

