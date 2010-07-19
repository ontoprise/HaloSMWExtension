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
	const LOD_SD_GRAPH = "DataSourceInformationGraph";
	const LOD_BASE_URI = "http://www.example.org/smw-lde/";
	const LOD_SMW_LDE = "smw-lde.owl#";
	const LOD_SMW_GRAPHS = "smwGraphs/";
	const LOD_SMW_DATASOURCES = "smwDatasources/";
	
	const LOD_SWP = "http://www.w3.org/2004/03/trix/swp-2/";
	
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
//		TSConnection::getConnector(); 
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
	 * Stores the definition of a Linked Data source in the triple store. The
	 * new namespace "smw-lde" is used.
	 * Is is a concatenation of the constants LOD_BASE_URI and LOD_SMW_LDE.
	 * 
	 * Data source definitions are stored in a dedicated graph defined by LOD_BASE_URI
	 * and LOD_SD_GRAPH.
	 * 
	 * The fields of the data source definition are stored as follows: 
	 * - The subjects of all triples for a data source are based on its ID.
	 * - The following properties are used (with given cardinality and type):
	 * smw-lde:ID 						^^xsd:string 	(1) 
	 * smw-lde:description				^^xsd:string 	(0..1) 
	 * smw-lde:label 					^^xsd:string 	(0..1) 
	 * smw-lde:linkedDataPrefix 		^^xsd:string 	(0..1)
	 * smw-lde:uriRegexPattern 			^^xsd:string 	(0..1)
	 * smw-lde:homepage 				^^owl:Thing 	(0..1)
	 * smw-lde:sampleURI 				^^owl:Thing 	(0..*)
	 * smw-lde:sparqlEndpointLocation 	^^owl:Thing 	(0..1)
	 * smw-lde:sparqlGraphName 			^^owl:Thing 	(0..1)
	 * smw-lde:dataDumpLocation 		^^owl:Thing 	(0..*)
	 * smw-lde:lastmod 					^^xsd:dateTime	(0..1)
	 * smw-lde:changefreq 				^^xsd:string 	(0..1)
	 * smw-lde:vocabulary 				^^owl:Thing 	(0..*)
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
		$subjectNS = "smwDatasources:";
		$subject = $sd->getID();
		if (!isset($subject)) {
			// The ID of a data source definition is mandatory
			return false;
		}
		$subject = $subjectNS.$subject;

		$propNS = "smw-lde:";
		$triples = array();
		
		$triples[] = new LODTriple($subject, "rdf:type", "smw-lde:Datasource", "owl:Thing");
		$triples[] = new LODTriple($subject, $propNS."ID", $sd->getID(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."description", $sd->getDescription(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."label", $sd->getLabel(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."linkedDataPrefix", $sd->getLinkedDataPrefix(), "xsd:string");
		$triples[] = new LODTriple($subject, $propNS."uriRegexPattern", $sd->getUriRegexPattern());
		$triples[] = new LODTriple($subject, $propNS."homepage", $sd->getHomepage(), "owl:Thing");

		if (is_array($sd->getSampleURIs())) {
			foreach ($sd->getSampleURIs() as $uri) {
				$triples[] = new LODTriple($subject, $propNS."sampleURI", $uri, "owl:Thing");
		 	}
		}

	 	$triples[] = new LODTriple($subject, $propNS."sparqlEndpointLocation", $sd->getSparqlEndpointLocation(), "owl:Thing");
	 	$triples[] = new LODTriple($subject, $propNS."sparqlGraphName", $sd->getSparqlGraphName(), "owl:Thing");
		if (is_array($sd->getDataDumpLocations())) {
		 	foreach ($sd->getDataDumpLocations() as $ddl) {
		 		$triples[] = new LODTriple($subject, $propNS."dataDumpLocation", $ddl, "owl:Thing");
		 	}
		}

	 	$triples[] = new LODTriple($subject, $propNS."lastmod", $sd->getLastMod(), "xsd:dateTime");
	 	$triples[] = new LODTriple($subject, $propNS."changefreq", $sd->getChangeFreq(), "xsd:string");

		if (is_array($sd->getVocabularies())) {
		 	foreach ($sd->getVocabularies() as $voc) {
		 		$triples[] = new LODTriple($subject, $propNS."vocabulary", $voc, "owl:Thing");
		 	}
		}

		$graph = self::LOD_BASE_URI.self::LOD_SMW_GRAPHS.self::LOD_SD_GRAPH;
		
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
		$graph = self::LOD_BASE_URI.self::LOD_SMW_GRAPHS.self::LOD_SD_GRAPH;
		$subjectNS = "smwDatasources:";
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
		$propNS = self::LOD_BASE_URI.self::LOD_SMW_LDE;
		$sd = new LODSourceDefinition($properties["{$propNS}ID"][0]);
		if (array_key_exists("{$propNS}description", $properties)) {
			$sd->setDescription($properties["{$propNS}description"][0]);
		}
		if (array_key_exists("{$propNS}label", $properties)) {
			$sd->setLabel($properties["{$propNS}label"][0]);
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
		if (array_key_exists("{$propNS}lastmod", $properties)) {
			$sd->setLastMod($properties["{$propNS}lastmod"][0]);
		}
		if (array_key_exists("{$propNS}changefreq", $properties)) {
			$sd->setChangeFreq($properties["{$propNS}changefreq"][0]);
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
		$subjectNS = "smwDatasources";
		$subject   = $subjectNS.":".$sourceID;
		
		$tsa = new LODTripleStoreAccess();
		$tsa->addPrefixes(TSNamespaces::getW3CPrefixes()
			              .self::getSourceDefinitionPrefixes());
		$tsa->deleteTriples(self::LOD_BASE_URI.self::LOD_SMW_GRAPHS.self::LOD_SD_GRAPH, 
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
		$tsa->dropGraph(self::LOD_BASE_URI.self::LOD_SMW_GRAPHS.self::LOD_SD_GRAPH);
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
		$graph = self::LOD_BASE_URI.self::LOD_SMW_GRAPHS.self::LOD_SD_GRAPH;
		$id    = "smw-lde:ID";
		$prefixes = self::getSourceDefinitionPrefixes();
		
		$query = $prefixes."SELECT ?s ?id FROM <$graph> WHERE { ?s $id  ?id . }";
		
		$tsa = new LODTripleStoreAccess();
		$qr = $tsa->queryTripleStore($query, $graph);
		
		$result = array();
		if (is_null($qr)) return $result;
		
		foreach ($qr->getRows() as $row) {
			$result[] = $row->getResult("id")->getValue();
		}
		return $result;
	}
	
	/**
	 * Returns the prefixes for all namespaces that are used in triples for
	 * storing LOD source definitions.
	 * 
	 * @return string
	 * 		Namespace prefixes
	 *
	 */
	public function getSourceDefinitionPrefixes() {
		
		return
			 "PREFIX smw-lde: <".self::LOD_BASE_URI.self::LOD_SMW_LDE."> \n"
			."PREFIX smwGraphs: <".self::LOD_BASE_URI.self::LOD_SMW_GRAPHS."> \n"
			."PREFIX smwDatasources: <".self::LOD_BASE_URI.self::LOD_SMW_DATASOURCES."> \n"
			."PREFIX owl: <http://www.w3.org/2002/07/owl#> \n\n";
	}
	
	public function getProvenanceGraphPrefixes() {
		return
			 "PREFIX swp: <".self::LOD_SWP."> \n";
	}
	
	public function getSMWGraphsURI(){
		return self::LOD_BASE_URI.self::LOD_SMW_GRAPHS;
	}
	
	public function getDataSourcesURI(){
		return self::LOD_BASE_URI.self::LOD_SMW_DATASOURCES;
	}
	
	//--- Private methods ---
	
}

