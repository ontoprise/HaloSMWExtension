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
 * @ingroup SMWHaloTriplestore
 * @author: Kai
 */

require_once( "TSC_RESTWebserviceConnector.php");
require_once( "stompclient/Stomp.php" );
require_once( "TSC_Helper.php" );

/**
 * Provides an abstraction for the connection to the triple store.
 * Currently, 4 connector types are supported:
 *
 *  1. MessageBroker for SPARUL
 *      *with SOAP SPARQL webservice
 *      *with REST SPARQL webservice
 *  2. REST webservice for SPARUL/SPARQL
 *  3. SOAP webservice for SPARUL/SPARQL
 *
 */
abstract class TSConnection {
	protected $updateClient;
	protected $queryClient;
	protected $manageClient;
	protected $ldImportClient;

	protected static $_instance;

	protected function __construct() {
		// Initialize namespaces
		TSNamespaces::getInstance();
	}

	/**
	 * Connects to the triplestore
	 *
	 */
	public abstract function connect();

	/**
	 * Disconnects from triplestore
	 *
	 */
	public abstract function disconnect();

	/**
	 * Sends SPARUL commands
	 *
	 * @param string $topic only relevant for a messagebroker.
	 * @param string or array of strings $commands
	 */
	public abstract function update($topic, $commands);

	/**
	 * Sends query which returns SPARQL/XML. (SELECT or ASK)
	 *
	 * @param string $query text
	 * @param string query parameters
	 * @param string $graph
	 * 		The graph to query. If not set, the graph stored in the global variable
	 * 		$smwgHaloTripleStoreGraph is queried.
	 * @return string SPARQL-XML result
	 */
	public abstract function query($query, $params = "", $graph = "");
	
	/**
     * Sends query which returns RDF/XML (CONSTRUCT OR DESRIBE)
     *
     * @param string $query text
     * @param string query parameters
     * @param string $graph
     *      The graph to query. If not set, the graph stored in the global variable
     *      $smwgHaloTripleStoreGraph is queried.
     * @return string SPARQL-XML result
     */
	public abstract function queryRDF($query, $params = "", $graph = "");

	/**
	 * Calls the webservice which gives status information about the triple store connector.
	 *
	 * @param $graph Graph-URI
	 * @return String (HTML) or false on an error
	 */
	public abstract function getStatus($graph);
    
	/**
	 * Run a management command.
	 * 
	 * @param string $command
	 * @param array $params
	 * 
	 * @return string
	 */
	public abstract function manage($command, $params = array());
		
	/**
	 * Translates an ASK query into SPARQL.
	 *
	 * @param string $query The ASK query
	 * @param string $params ASK parameters (limit, merge, offset, ....)
	 * @param string $baseURI Base URI from which the SPARQL IRIs are created.
	 */
	public abstract function translateASK($query, $params = "", $baseURI = "");


	/**
     * Calls a method from the LDImport REST interface
     *
     * @param string $method
     * @param string $payload (application/x-www-form-urlencoded)
     */
    public abstract function callLDImporter($method, $payload = "");
    
	/**
	 * Trigger datasource import/update of LDImporter. Convenience method.
	 *
	 * @param string $datasource ID
	 * @param boolean $update true for update, false for initial import
	 */
	public abstract function runImport($datasource, $update = false, $synchronous = false , $runSchemaTranslation = true, $runIdentityResolution = true);

	public static function getConnector() {
		if (is_null(self::$_instance)) {
			global $smwgMessageBroker, $smwgWebserviceProtocol;

			if (isset($smwgMessageBroker)) {
				if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
					self::$_instance = new TSConnectorMessageBrokerAndRESTWebservice();
				} else {
					trigger_error("SOAP requests to TSC are not supported anymore.");
				}
			} else if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
				self::$_instance = new TSConnectorRESTWebservice();

			} else {

				trigger_error("SOAP requests to TSC are not supported anymore.");
			}
		}
		return self::$_instance;
	}
}


/**
 * MessageBroker connector implementation for updates (SPARUL).
 * REST webservice for SPARQL queries.
 *
 */
class TSConnectorMessageBrokerAndRESTWebservice extends TSConnectorRESTWebservice {


	public function connect() {
		global $smwgMessageBroker;
		$this->updateClient = new StompConnection("tcp://$smwgMessageBroker:61613");
		$this->updateClient->connect();

		global $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgHaloWebserviceEndpoint;
		list($host, $port) = explode(":", $smwgHaloWebserviceEndpoint);
		$credentials = isset($smwgHaloWebserviceUser) ? $smwgHaloWebserviceUser.":".$smwgHaloWebservicePassword : "";
		$this->queryClient = new RESTWebserviceConnector($host, $port, "sparql", $credentials);
		$this->manageClient = new RESTWebserviceConnector($host, $port, "management", $credentials);
		$this->ldImportClient = new RESTWebserviceConnector($host, $port, "ldimporter", $credentials);
	}


	public function disconnect() {
		$this->updateClient->disconnect();
	}


	public function update($topic, $commands) {
		global $smwgSPARULUpdateEncoding;
		if (!is_array($commands)) {
			$enc_commands = isset($smwgSPARULUpdateEncoding) && $smwgSPARULUpdateEncoding === "UTF-8" ? utf8_encode($commands) : $commands;
			$this->updateClient->send($topic, $enc_commands);
			return;
		}
		$commandStr = implode("|||",$commands);
		$enc_commands = isset($smwgSPARULUpdateEncoding) && $smwgSPARULUpdateEncoding === "UTF-8" ? utf8_encode($commandStr) : $commandStr;
		$this->updateClient->send($topic, $enc_commands);
	}




}

/**
 * REST webservice connector implementation.
 *
 */
class TSConnectorRESTWebservice extends TSConnection {

	public function connect() {
		global $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgHaloWebserviceEndpoint;
	    if (empty($smwgHaloWebserviceEndpoint)) {
            throw new Exception('Variable $smwgHaloWebserviceEndpoint is not defined for TSC');
        }
		list($host, $port) = explode(":", $smwgHaloWebserviceEndpoint);
		$credentials = isset($smwgHaloWebserviceUser) ? $smwgHaloWebserviceUser.":".$smwgHaloWebservicePassword : "";
		$this->updateClient = new RESTWebserviceConnector($host, $port, "sparul", $credentials);
		$this->queryClient = new RESTWebserviceConnector($host, $port, "sparql", $credentials);
		$this->manageClient = new RESTWebserviceConnector($host, $port, "management", $credentials);
		$this->ldImportClient = new RESTWebserviceConnector($host, $port, "ldimporter", $credentials);
	}

	public function disconnect() {
		// do nothing. webservice calls use stateless HTTP protocol.
	}

	public function update($topic, $commands) {
		if (!is_array($commands)) {
			$enc_commands = isset($smwgSPARULUpdateEncoding) && $smwgSPARULUpdateEncoding === "UTF-8" ? utf8_encode($commands) : $commands;
			$enc_commands = 'command='.urlencode($enc_commands);
			$this->updateClient->update($enc_commands);
			return;
		}
		$enc_commands = "";
		$first = true;
		foreach($commands as $c) {
			$enc_command = isset($smwgSPARULUpdateEncoding) && $smwgSPARULUpdateEncoding === "UTF-8" ? utf8_encode($c) : $c;
			if ($first) {
				$enc_commands .= "command=".urlencode($enc_command);
				$first=false;
			} else {
				$enc_commands .= "&command=".urlencode($enc_command);
			}
		}

		$this->updateClient->send($enc_commands);

	}

	public function query($query, $params = "", $graph = "") {
		global $smwgHaloTripleStoreGraph;
		
		if (stripos(trim($query), 'SELECT') === 0 || stripos(trim($query), 'PREFIX') === 0) {
			// SPARQL, attach common prefixes
			$query = TSNamespaces::getAllPrefixes().$query;
		}
		$queryRequest = "query=".urlencode($query);
		$queryRequest .= "&default-graph-uri=".urlencode($graph);
		$queryRequest .= "&params=".urlencode($params);

		list($header, $status, $result) = $this->queryClient->send($queryRequest);
		if ($status != 200) {
			throw new Exception(strip_tags($result), $status);
		}
		return $result;
	}
	
	public function queryRDF($query, $params = "", $graph = "") {
		global $smwgHaloTripleStoreGraph;
        
         // SPARQL, attach common prefixes
        $query = TSNamespaces::getAllPrefixes().$query;
        
        $queryRequest = "query=".urlencode($query);
        $queryRequest .= "&default-graph-uri=".urlencode($graph);
        $queryRequest .= "&params=".urlencode($params);

        list($header, $status, $result) = $this->queryClient->send($queryRequest, '', 'application/rdf+xml');
        if ($status != 200) {
            throw new Exception(strip_tags($result), $status);
        }
        return $result;
	}
	
	public function manage($command, $params = array()) {
       
        $request = wfArrayToCGI($params);

        list($header, $status, $result) = $this->manageClient->send($request, "/$command");
        if ($status != 200) {
            throw new Exception(strip_tags($result), $status);
        }
        return $result;
	}

	public function getStatus($graph) {
		global $smwgHaloTripleStoreGraph;

		$request = "graph=".urlencode($smwgHaloTripleStoreGraph);

		list($header, $status, $result) = $this->manageClient->send($request, "/getTripleStoreStatus");
		if ($status != 200) {
			throw new Exception(strip_tags($result), $status);
		}
		$xmlDoc = simplexml_load_string($result);
		$resultMap = array();
		$resultMap['tscversion'] = (string) $xmlDoc->tscversion;
		$resultMap['licenseState'] = (string) $xmlDoc->licenseState;
		$resultMap['driverInfo'] = (string) $xmlDoc->driverInfo;
		$resultMap['isInitialized'] = ((string) $xmlDoc->isInitialized) == 'true';
		$resultMap['features'] = explode(",", (string) $xmlDoc->features);
		
		$resultMap['loadedGraphs'] = array();
		$graphsLoaded = $xmlDoc->loadedGraphs[0];
		foreach($graphsLoaded->graph as $gl) {
		      $resultMap['loadedGraphs'][] = $gl->attributes()->uri;
		}
        $resultMap['autoloadFolder'] = (string) $xmlDoc->autoloadFolder[0];
        
	    $resultMap['startParameters'] = array();
        $startParameters = $xmlDoc->startParameters[0];
        foreach($startParameters->param as $p) {
              $resultMap['startParameters'][] = array($p->attributes()->name, (string) $p);
        }
        
	    $resultMap['syncCommands'] = array();
        $syncCommands = $xmlDoc->syncCommands[0];
        foreach($syncCommands->command as $c) {
              $resultMap['syncCommands'][] = (string) $c;
        }
		return $resultMap;

	}

	public function translateASK($query, $params = "", $baseURI = "") {
		global $smwgHaloTripleStoreGraph;
		if (empty($baseURI)) {
			$baseURI = $smwgHaloTripleStoreGraph;
		}

		$queryRequest = "query=".urlencode($query);
		$queryRequest .= "&baseuri=".urlencode($baseURI);
		$queryRequest .= "&parameters=".urlencode($params);

		list($header, $status, $result) = $this->queryClient->send($queryRequest, "/translateASK");
		if ($status != 200) {
			throw new Exception(strip_tags($result), $status);
		}
		return $result;
	}
	
    public function refactorOblOntology($prefix, $ontologyID, $ontology, $wikiGraph, $lang) {
        
        $queryRequest = "prefix=".urlencode($prefix);
        $queryRequest .= "&ontology=".urlencode($ontology);
        $queryRequest .= "&ontologyID=".urlencode($ontologyID);
        $queryRequest .= "&wikiGraph=".urlencode($wikiGraph);
        $queryRequest .= "&lang=".urlencode($lang);

        list($header, $status, $result) = $this->manageClient->send($queryRequest, "/refactorOblOntology");
        if ($status != 200) {
            throw new Exception(strip_tags($result), $status);
        }
        return $result;
    }

	public function runImport($datasourceID, $update = false, $synchronous = false, $runSchemaTranslation = true ,$runIdentityResolution = true) {
		$payload = "dataSourceId=".urlencode($datasourceID)."&update=".urlencode($update)."&synchronous=".urlencode($synchronous)
                        ."&runSchemaTranslation=".urlencode($runSchemaTranslation)."&runIdentityResolution=".urlencode($runIdentityResolution);
		list($header, $status, $result) = $this->ldImportClient->send($payload, "/runImport");
		if ($status != 200) {
			throw new Exception(strip_tags($result), $status);
		}
		return true;
	}
	
    public function callLDImporter($method, $payload = "") {
        list($header, $status, $result) = $this->ldImportClient->send($payload, "/$method");
        if ($status != 200) {
            throw new Exception(strip_tags($result), $status);
        }
        return $result;
    }
}


