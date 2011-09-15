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
 * This file defines the class TSCAdministrationStore.
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
class TSCAdministrationStore  {

	//--- Constants ---
	const TSC_SD_GRAPH = "DataSourceInformationGraph";

	//--- Private fields ---

	// TSCAdministrationStore
	// Points to the singleton instance of the TSCAdministration store.
	private static $mInstance;


	/**
	 * Constructor for  TSCAdministrationStore
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
	 * @return TSCAdministrationStore
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
     * Loads all definition of a linked data source from the triple store.
     *
     * @return hash array (datasourcID => TSCSourceDefinition)
     */
	public function loadAllSourceDefinitions() {
		$tsa = new TSCTripleStoreAccess();

		$result = $tsa->callLDImporter("getImportStatus");

		if (!$result) {
			return NULL;
		}
		
		$dom = simplexml_load_string($result);
		 
		if ($dom === FALSE) {
			return null;
		}

		// add variables to the query result
		$datasources = $dom->xpath('//datasource');

		$dsResults = array();
		// Convert results into an array of key-value pairs.
		foreach ($datasources as $d) {
			
			$id = (string) $d->attributes()->id;
			$sd = new TSCSourceDefinition($id);
			if (isset($d->attributes()->label)) {
				$sd->setLabel((string) $d->attributes()->label);
			}
			if (isset($d->attributes()->importSuccessful)) {
				$status = (string) $d->attributes()->importSuccessful;
				$status = $status == 'true';
				$sd->setImported($status);
			}
		    if (isset($d->lastImportDate[0])) {
                $sd->setLastImportDate((string) $d->lastImportDate[0]);
            }
            
			if (isset($d->errorMessages[0])) {
				$sd->setErrorMessagesFromLastImport((string) $d->errorMessages[0]);
			}

			if (isset($d->sparqlEndpointLocation[0])) {
				$sd->setSparqlEndpointLocation((string) $d->sparqlEndpointLocation[0]);
			}
			
			if (isset($d->dataDumpLocation[0])) {
				$sd->setDataDumpLocations(array((string) $d->dataDumpLocation[0]));
			}

			if (isset($d->lastMod[0])) {
				$sd->setLastMod((string) $d->lastMod[0]);
			}
			 
			if (isset($d->changeFreq[0])) {
				$sd->setChangeFreq((string) $d->changeFreq[0]);
			}
			
			//TODO: Thomas: get all other metadata
			
			$dsResults[$id] = $sd;
		}

		return $dsResults;
	}

	/**
	 * Stores the definition of a Linked Data source in the triple store. The
	 * new namespace with the prefix "smw-lde" is used.
	 *
	 * Data source definitions are stored in a dedicated graph defined by TSC_BASE_URI
	 * and TSC_SD_GRAPH.
	 *
	 * The fields of the data source definition are stored as follows:
	 * - The subjects of all triples for a data source are based on its ID.
	 * - The following properties are used (with given cardinality and type):
	 * smw-lde:ID 						^^xsd:string 	(1)
	 * smw-lde:description				^^xsd:string 	(0..1)
	 * smw-lde:label 					^^xsd:string 	(0..1)
	 * smw-lde:homepage 				^^owl:Thing 	(0..1)
	 * smw-lde:sampleURI 				^^owl:Thing 	(0..*)
	 * smw-lde:sparqlEndpointLocation 	^^owl:Thing 	(0..1)
	 * smw-lde:sparqlGraphName 			^^owl:Thing 	(0..1)
	 * smw-lde:sparqlGraphPattern		^^xsd:string 	(0..*)
	 * smw-lde:dataDumpLocation 		^^owl:Thing 	(0..*)
	 * smw-lde:lastmod 					^^xsd:dateTime	(0..1)
	 * smw-lde:changefreq 				^^xsd:string 	(0..1)
	 * smw-lde:vocabulary 				^^owl:Thing 	(0..*)
	 * smw-lde:predicateToCrawl			^^owl:Thing 	(0..*)
         * smw-lde:levelsToCrawl			^^xsd:integer 	(0..*)
	 *
	 * @param TSCSourceDefinition $sd
	 * 		This object defines a linked data source.
	 * @param mixed bool/string $persistenceID
	 * 		If <true> or an ID are given, the triples are stored in the
	 * 		persistency layer of the TS. In case of <true>, the ID of the LSD
	 * 		will be chosen as persistency ID.
	 *
	 * @return bool
	 * 		<true> if the source definition was stored successfully or
	 * 		<false> otherwise
	 */
	public function storeSourceDefinition(TSCSourceDefinition $sd, $persistenceID = false) {

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

		$triples[] = new TSCTriple($subject, "rdf:type", "smw-lde:Datasource", "__objectURI");
		$triples[] = new TSCTriple($subject, $propNS."ID", $sd->getID(), "xsd:string");
		$triples[] = new TSCTriple($subject, $propNS."description", $sd->getDescription(), "xsd:string");
		$triples[] = new TSCTriple($subject, $propNS."label", $sd->getLabel(), "xsd:string");
		$triples[] = new TSCTriple($subject, $propNS."homepage", $sd->getHomepage(), "__objectURI");

		if (is_array($sd->getSampleURIs())) {
			foreach ($sd->getSampleURIs() as $uri) {
				$triples[] = new TSCTriple($subject, $propNS."sampleURI", $uri, "__objectURI");
			}
		}

		$triples[] = new TSCTriple($subject, $propNS."sparqlEndpointLocation", $sd->getSparqlEndpointLocation(), "__objectURI");
		$triples[] = new TSCTriple($subject, $propNS."sparqlGraphName", $sd->getSparqlGraphName(), "__objectURI");
		if (is_array($sd->getSparqlGraphPatterns())) {
			foreach ($sd->getSparqlGraphPatterns() as $pattern) {
				$triples[] = new TSCTriple($subject, $propNS."sparqlGraphPattern", $pattern, "xsd:string");
			}
		}

		if (is_array($sd->getDataDumpLocations())) {
			foreach ($sd->getDataDumpLocations() as $ddl) {
				$triples[] = new TSCTriple($subject, $propNS."dataDumpLocation", $ddl, "__objectURI");
			}
		}

		$triples[] = new TSCTriple($subject, $propNS."lastmod", $sd->getLastMod(), "xsd:dateTime");
		$triples[] = new TSCTriple($subject, $propNS."changefreq", $sd->getChangeFreq(), "xsd:string");

		if (is_array($sd->getVocabularies())) {
			foreach ($sd->getVocabularies() as $voc) {
				$triples[] = new TSCTriple($subject, $propNS."vocabulary", $voc, "__objectURI");
			}
		}
		
		if (is_array($sd->getPredicatesToCrawl())) {
			foreach ($sd->getPredicatesToCrawl() as $uri) {
				$triples[] = new TSCTriple($subject, $propNS."predicateToCrawl", $uri, "__objectURI");
			}
		}
                
                if (is_array($sd->getLevelsToCrawl())) {
			foreach ($sd->getLevelsToCrawl() as $level) {
				$triples[] = new TSCTriple($subject, $propNS."levelsToCrawl", $level, "xsd:int");
			}
		}

		$graph = $this->getDataSourcesGraph();

		$persist = $persistenceID === true || is_string($persistenceID);
		$tsa = $persist
		? new TSCPersistentTripleStoreAccess(true)
		: new TSCTripleStoreAccess();
		$tsa->addPrefixes(self::getSourceDefinitionPrefixes());
		$tsa->createGraph($graph);
		$tsa->deleteTriples($graph, "$subject ?p ?o", "$subject ?p ?o");
		$tsa->insertTriples($graph, $triples);
		if ($persist) {
			if ($persistenceID === true) {
				$persistenceID = $sd->getID();
			}
			$tsa->flushCommands("TSCSourceDefinition", $persistenceID);
		} else {
			$tsa->flushCommands();
		}

		return true;
	}

	/**
	 * Loads the definition of a linked data source from the triple store.
	 *
	 * @param string $sourceID
	 * 		ID of the linked data source
	 *
	 * @return TSCSourceDefinition
	 * 		The definition of the source or <NULL>, if there is no such source
	 * 		with the given ID.
	 */
	public function loadSourceDefinition($sourceID) {
		$graph = $this->getDataSourcesGraph();
		$prefixes = self::getSourceDefinitionPrefixes();

		$query = <<<QUERY
		$prefixes
SELECT ?p ?o 
FROM <$graph> 
WHERE { 
	?s ?p ?o . 
	?s smw-lde:ID "$sourceID"^^xsd:string .
}
QUERY;

		$tsa = new TSCTripleStoreAccess();

		$result = $tsa->queryTripleStore($query, $graph);

		if (!$result || count($result->getRows()) == 0) {
			return NULL;
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
		$pm = TSCPrefixManager::getInstance();
		$propNS = $pm->getNamespaceURI("smw-lde");
		$sd = new TSCSourceDefinition($properties["{$propNS}ID"][0]);
		if (array_key_exists("{$propNS}description", $properties)) {
			$sd->setDescription($properties["{$propNS}description"][0]);
		}
		if (array_key_exists("{$propNS}label", $properties)) {
			$sd->setLabel($properties["{$propNS}label"][0]);
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
		if (array_key_exists("{$propNS}sparqlGraphPattern", $properties)) {
			$sd->setSparqlGraphPatterns($properties["{$propNS}sparqlGraphPattern"]);
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
		if (array_key_exists("{$propNS}imported", $properties)) {
			$sd->setImported($properties["{$propNS}imported"]);
		}
		if (array_key_exists("{$propNS}predicateToCrawl", $properties)) {
			$sd->setPredicatesToCrawl($properties["{$propNS}predicateToCrawl"]);
		}
                if (array_key_exists("{$propNS}levelsToCrawl", $properties)) {
			$sd->setLevelsToCrawl($properties["{$propNS}levelsToCrawl"]);
		}

		return $sd;
	}

	/**
	 * Deletes the source definition with the ID $sourceID from the triple store.
	 *
	 * @param string $sourceID
	 * 		ID of the source definition. It may be <NULL> if the $persistencyID
	 * 		is set. In this case all triples that belong to this ID will be deleted.
	 * @param string $persistencyID
	 * 		If not <NULL>, all LSDs with the given persistency ID will be deleted
	 * 		from the persistency layer of the triple store and the triple store.
	 * 		Otherwise the $sourceID	will be used as persistency ID.
	 */
	public function deleteSourceDefinition($sourceID, $persistencyID = NULL) {
		$tsa = new TSCPersistentTripleStoreAccess(true);
		if (!is_null($sourceID)) {
				
			$tsa->addPrefixes(self::getSourceDefinitionPrefixes());
			$wherePattern = <<<QUERY
	?s ?p ?o . 
	?s smw-lde:ID "$sourceID"^^xsd:string .
QUERY;
				
			$tsa->deleteTriples($this->getDataSourcesGraph(),
			$wherePattern, "?s ?p ?o");
			$tsa->flushCommands();
		}

		// The source definition may be stored in the persistency layer
		// => delete this data too
		if ($persistencyID === NULL) {
			$persistencyID = $sourceID;
		}
		$tsa->deletePersistentTriples("TSCSourceDefinition", $persistencyID);
	}

	/**
	 * Deletes the complete graph for source definitions with all its content.
	 * This method should only be called for maintenance purposes.
	 *
	 */
	public function deleteAllSourceDefinitions() {
		$tsa = new TSCPersistentTripleStoreAccess();
		$tsa->dropGraph($this->getDataSourcesGraph());
		$tsa->flushCommands();
		$tsa->deletePersistentTriples("TSCSourceDefinition");
	}

	/**
	 * Every TSC source definition has an ID. This method returns all IDs of
	 * definitions that are stored in the triple store.
	 *
	 * @return array<string>
	 * 		An array of all IDs. If no ID is available, the array is empty.
	 */
	public function getAllSourceDefinitionIDs() {
		$graph = $this->getDataSourcesGraph();
		$id    = "smw-lde:ID";
		$prefixes = self::getSourceDefinitionPrefixes();

		$query = $prefixes."SELECT ?s ?id FROM <$graph> WHERE { ?s $id  ?id . }";

		$tsa = new TSCTripleStoreAccess();
		$qr = $tsa->queryTripleStore($query, $graph);

		$result = array();
		if (is_null($qr)) return $result;

		foreach ($qr->getRows() as $row) {
			$result[] = $row->getResult("id")->getValue();
		}
		return $result;
	}
	
    /**
     * Every TSC source definition has an ID. This method returns all IDs of
     * definitions and their labels that are stored in the triple store.
     *
     * @return array<id, label>
     *      An array of all IDs and according labels. If no ID is available, the array is empty.
     */
    public function getAllSourceDefinitionIDsAndLabels() {
        $graph = $this->getDataSourcesGraph();
        $id    = "smw-lde:ID";
        $label    = "smw-lde:label";
        $prefixes = self::getSourceDefinitionPrefixes();

        $query = $prefixes."SELECT ?s ?id ?label FROM <$graph> WHERE { ?s $id  ?id . ?s $label ?label . }";

        $tsa = new TSCTripleStoreAccess();
        $qr = $tsa->queryTripleStore($query, $graph);

        $result = array();
        if (is_null($qr)) return $result;

        foreach ($qr->getRows() as $row) {
            $result[] = array($row->getResult("id")->getValue(), $row->getResult("label")->getValue());
        }
        return $result;
    }

	/**
	 * Returns the prefixes for all namespaces that are used in triples for
	 * storing TSC source definitions.
	 *
	 * @return string
	 * 		Namespace prefixes
	 *
	 */
	public function getSourceDefinitionPrefixes() {
		$pm = TSCPrefixManager::getInstance();
		return $pm->getSPARQLPrefixes(array("xsd", "owl", "rdf", "rdfs",
											"smw-lde", "smwGraphs", 
											"smwDatasources"));
	}

	public function getProvenanceGraphPrefixes() {
		$pm = TSCPrefixManager::getInstance();
		return $pm->getSPARQLPrefixes(array("swp"));
	}

	public function getSMWGraphsURI(){
		$pm = TSCPrefixManager::getInstance();
		return $pm->getNamespaceURI('smwGraphs');
	}

	public function getDataSourcesURI(){
		$pm = TSCPrefixManager::getInstance();
		return $pm->getNamespaceURI('smwDatasources');
	}

	/**
	 * @return string
	 * 		Returns the name of the graph in which data source definitions are
	 * 		stored.
	 */
	public static function getDataSourcesGraph() {
		$pm = TSCPrefixManager::getInstance();
		return $pm->getNamespaceURI('smwGraphs').self::TSC_SD_GRAPH;
	}
	//--- Private methods ---

}

