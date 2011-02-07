<?php

/**
 * @file
 * @ingroup LinkedDataAdministration
 */
/*  Copyright 2011, MES GmbH
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
 * This file defines the class LODTrustStore.
 *
 * @author Magnus Niemann
 * Date: 06.01.2011
 *
 */
if (!defined('MEDIAWIKI')) {
    die("This file is part of the LinkedData extension. It is not a valid entry point.\n");
}


//--- Includes ---
global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class handles the storage of a Linked Data source description in the
 * triple store. Source descriptions can be saved, loaded and delete from the
 * store.
 *
 * @author Magnus Niemann
 *
 */
class LODPolicyStore {
    //--- Constants ---
    const LOD_SD_GRAPH = "DataSourceInformationGraph";
    const LOD_TRUST_GRAPH = "TrustGraph";
    const LOD_TRUSTPOLICIES = "TrustPolicies";

    //--- Private fields ---
    private $_client;
    // LODPolicyStore
    // Points to the singleton instance of the LODPolicy store.
    private static $mInstance;

    /**
     * Constructor for LODPolicyStore
     */
    protected function __construct() {
        self::$mInstance = $this;
    }

    //--- getter/setter ---
    //--- Public methods ---

    /**
     * Returns the singleton instance of this class.
     *
     * @return LODPolicyStore
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
     * Loads a policy from the triple store.
     *
     * @param string $sourceID
     * 		ID of the linked data source
     *
     * @return LODPolicy
     * 		The policy or <NULL>, if there is no such policy
     * 		with the given ID.
     */
    public function loadPolicy($policyID) {
        $graph = $this->getTrustGraph();
        $prefixes = self::getPolicyPrefixes();

        $query = <<<QUERY
		$prefixes
SELECT ?s ?p ?o ?op ?oo
FROM <$graph>
WHERE {
	?s ?p ?o .
	?s smw-lde:ID "$policyID"^^xsd:string .
    OPTIONAL {
        ?o ?op ?oo .
    }
}
QUERY;

        $tsa = new LODTripleStoreAccess();

        $result = $tsa->queryTripleStore($query, $graph);

        if (!$result || count($result->getRows()) == 0) {
            return NULL;
        }
        $rows = $result->getRows();

        $URI = NULL;
        $description = NULL;
        $heuristic = NULL;
        $pattern = NULL;
        $uri = NULL;
        $parameters = array();
        $pm = LODPrefixManager::getInstance();
        $propNS = $pm->getNamespaceURI("smw-lde");
        $uriNotSet = true;
        foreach ($rows as $row) {
            if ($uriNotSet) {
                $uriNotSet = false;
                $URI = $this->getVarVal($row, 's');
            }
            $prop = "{$propNS}description";
            $rowProp = $this->getVarVal($row, 'p');
            $value = $this->getVarVal($row, 'o');
            if ($rowProp == $prop) {
                $description = $value;
            }
            $prop = "{$propNS}pattern";
            if ($rowProp == $prop) {
                $pattern = $value;
            }
            $prop = "{$propNS}heuristic";
            if ($rowProp == $prop) {
                $heuristic = $this->getHeuristic($value, $rows);
            }
            $prop = "{$propNS}parameter";
            if ($rowProp == $prop) {
                $uri = $this->getVarVal($row, 'o');
                if (!array_key_exists($uri, $parameters)) {
                    $parameter = $this->getParameter($value, $rows);
                    $parameters[$uri] = $parameter;
                }
            }
        }

        // Create a policy object.
        $policy = new LODPolicy($policyID, $URI);
        if ($description) {
            $policy->setDescription($description);
        }
        if ($pattern) {
            $policy->setPattern($pattern);
        }
        if ($heuristic) {
            $policy->setHeuristic($heuristic);
        }
        $policy->setParameters($parameters);

        return $policy;
    }

    /**
     * Loads all policies from the TPEE web service.
     *
     * @return hash array (policyID => LODPolicy)
     */
    public function loadAllPolicies() {
        $graph = $this->getTrustGraph();
        $prefixes = self::getPolicyPrefixes();

        $query = <<<QUERY
		$prefixes
SELECT ?id
FROM <$graph>
WHERE {
	?s smw-lde:ID ?id .
}
QUERY;

        $tsa = new LODTripleStoreAccess();

        $result = $tsa->queryTripleStore($query, $graph);
        $policies = array();
        if (!$result || count($result->getRows()) == 0) {
            return $policies;
        }

        foreach ($result->getRows() as $row) {
            $id = $row->getResult('id')->getValue();
            $policy = $this->loadPolicy($id);
            $policies[$id] = $policy;
        }
        return $policies;
    }

    /**
     * Stores the definition of a Linked Data source in the triple store. The
     * new namespace with the prefix "smw-lde" is used.
     *
     * Data source definitions are stored in a dedicated graph defined by LOD_BASE_URI
     * and LOD_SD_GRAPH.
     *
     * @return bool
     * 		<true> if the policy was stored successfully or
     * 		<false> otherwise
     */
    public function storePolicy($uri, $id, $description, $pattern, $heuristic, $parameters, $persistenceID = false) {
        // create the triples for the policy
        $propNS = "smw-lde:";
        $rdfsNS = "rdfs:";
        $triples = array();
        $heuristicURI = "smwTrustPolicies:" . $heuristic;

        $triples[] = new LODTriple($uri, "rdf:type", "smw-lde:Policy", "__objectURI");
        $triples[] = new LODTriple($uri, $propNS . "ID", $id, "xsd:string");
        $triples[] = new LODTriple($uri, $propNS . "description", $description, "xsd:string");
        $triples[] = new LODTriple($uri, $propNS . "pattern", $pattern, "xsd:string");
        $triples[] = new LODTriple($uri, $propNS . "heuristic", $heuristicURI, "__objectURI");
        $triples[] = new LODTriple($heuristicURI, $rdfsNS . "label", $heuristic, "xsd:string");

        if (is_array($parameters)) {
            foreach ($parameters as $parameter) {
                $parUri = $parameter["uri"];
                $triples[] = new LODTriple($uri, $propNS . "parameter", $parUri, "__objectURI");
                $triples[] = new LODTriple($parUri, $propNS . "paramName", $parameter["name"], "xsd:string");
                $triples[] = new LODTriple($parUri, $rdfsNS . "label", $parameter["label"], "xsd:string");
                $triples[] = new LODTriple($parUri, $propNS . "description", $parameter["description"], "xsd:string");
            }
        }

        $graph = $this->getTrustGraph();

        $persist = $persistenceID === true || is_string($persistenceID);
        $tsa = $persist ? new LODPersistentTripleStoreAccess(true) : new LODTripleStoreAccess();
        $tsa->addPrefixes(self::getPolicyPrefixes());
        $tsa->createGraph($graph);
        $tsa->deleteTriples($graph, "<$uri> ?p ?o", "<$uri> ?p ?o");
        if (is_array($parameters)) {
            foreach ($parameters as $parameter) {
                $tsa->deleteTriples($graph, "<$parUri> ?p ?o", "<$parUri> ?p ?o");
            }
        }
        $tsa->insertTriples($graph, $triples);
        if ($persist) {
            if ($persistenceID === true) {
                $persistenceID = $id;
            }
            $tsa->flushCommands("LODPolicy", $persistenceID);
        } else {
            $tsa->flushCommands();
        }
        return true;
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
    public function deletePolicy($policyID, $persistencyID = NULL) {
        $tsa = new LODPersistentTripleStoreAccess(true);
        if (!is_null($policyID)) {

            $tsa->addPrefixes(self::getPolicyPrefixes());
            $wherePattern = <<<QUERY
	?s ?p ?o . 
	?s smw-lde:ID "$policyID"^^xsd:string .
QUERY;

            $tsa->deleteTriples($this->getTrustGraph(),
                    $wherePattern, "?s ?p ?o");
            $tsa->flushCommands();
        }

        // The policy definition may be stored in the persistency layer
        // => delete this data too
        if ($persistencyID === NULL) {
            $persistencyID = $policyID;
        }
        $tsa->deletePersistentTriples("LODPolicy", $persistencyID);
        // unfortunately there is no information in the TSA about success or failure
        return true;
    }

    /**
     * Deletes the complete graph for source definitions with all its content.
     * This method should only be called for maintenance purposes.
     *
     */
    public function deleteAllPolicies() {
        $tsa = new LODPersistentTripleStoreAccess();
        $tsa->dropGraph($this->getTrustGraph());
        $tsa->flushCommands();
        $tsa->deletePersistentTriples("LODPolicy");
    }

    /**
     * Every TPEE policy has an ID. This method returns all IDs of
     * policies that are stored in the triple store.
     *
     * @return array<string>
     * 		An array of all IDs. If no ID is available, the array is empty.
     */
    public function getAllPolicyIDs() {
        $graph = $this->getTrustGraph();
        $prefixes = self::getPolicyPrefixes();

        $query = <<<QUERY
		$prefixes
SELECT ?id
FROM <$graph>
WHERE {
	?s smw-lde:ID ?id .
}
QUERY;

        $tsa = new LODTripleStoreAccess();
        $qr = $tsa->queryTripleStore($query, $graph);

        $result = array();
        if (is_null($qr))
            return $result;

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
    public function getPolicyPrefixes() {
        $pm = LODPrefixManager::getInstance();
        return $pm->getSPARQLPrefixes(array("xsd", "owl", "rdf", "rdfs",
            "smw-lde", "smwGraphs",
            "smwTrustPolicies"));
    }

    public function getProvenanceGraphPrefixes() {
        $pm = LODPrefixManager::getInstance();
        return $pm->getSPARQLPrefixes(array("swp"));
    }

    public function getSMWGraphsURI() {
        $pm = LODPrefixManager::getInstance();
        return $pm->getNamespaceURI('smwGraphs');
    }

    public function getDataSourcesURI() {
        $pm = LODPrefixManager::getInstance();
        return $pm->getNamespaceURI('smwDatasources');
    }

    /**
     * @return string
     * 		Returns the name of the graph in which data source definitions are
     * 		stored.
     */
    public static function getDataSourcesGraph() {
        $pm = LODPrefixManager::getInstance();
        return $pm->getNamespaceURI('smwGraphs') . self::LOD_SD_GRAPH;
    }

    /**
     * @return string
     * 		Returns the name of the graph in which trust policies are
     * 		stored.
     */
    public static function getTrustGraph() {
        $pm = LODPrefixManager::getInstance();
        return $pm->getNamespaceURI('smwGraphs') . self::LOD_TRUST_GRAPH;
    }

    /**
     * @return string
     * 		Returns the namespace of the trust policies.
     */
    public static function getTrustPolicyNS() {
        $pm = LODPrefixManager::getInstance();
        return $pm->getNamespaceURI('smwTrustPolicies');
    }

    //--- Private methods ---

    private static function getVarVal($row, $var) {
        $result = $row->getResult($var);
        if (!$result) {
            return NULL;
        }
        return $result->getValue();
    }

    private static function getHeuristic($uri, $rows) {
        $pm = LODPrefixManager::getInstance();
        $propNS = $pm->getNamespaceURI("rdfs");
        $heuristic = new LODHeuristic($uri);
        foreach ($rows as $row) {
            if (self::getVarVal($row, 'o') == $uri) {
                $prop = self::getVarVal($row, 'op');
                $value = self::getVarVal($row, 'oo');
                if ($prop == "{$propNS}label") {
                    $heuristic->setLabel($value);
                }
            }
        }
        return $heuristic;
    }

    private static function getParameter($uri, $rows) {
        $pm = LODPrefixManager::getInstance();
        $propNS = $pm->getNamespaceURI("smw-lde");
        $rdfsNS = $pm->getNamespaceURI("rdfs");
        $parameter = new LODParameter($uri);
        foreach ($rows as $row) {
            if (self::getVarVal($row, 'o') == $uri) {
                $prop = self::getVarVal($row, 'op');
                $value = self::getVarVal($row, 'oo');
                if ($prop == "{$propNS}paramName") {
                    $parameter->setName($value);
                }
                if ($prop == "{$rdfsNS}label") {
                    $parameter->setLabel($value);
                }
                if ($prop == "{$propNS}description") {
                    $parameter->setDescription($value);
                }
            }
        }
        return $parameter;
    }

}

