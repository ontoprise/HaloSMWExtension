<?php
/**
 * @file
 * @ingroup SMWHaloTriplestore
 * @author: Kai
 */

require_once 'SMW_RESTWebserviceConnector.php';
require_once( "stompclient/Stomp.php" );
require_once( "SMW_TS_Helper.php" );

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

	protected static $_instance;
	
	protected function __construct() {
		// Initialize namespaces
		new TSNamespaces();
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
	 * Sends query
	 *
	 * @param string $query text
	 * @param string query parameters
	 * @param string $graph
	 * 		The graph to query. If not set, the graph stored in the global variable
	 * 		$smwgTripleStoreGraph is queried.
	 * @return string SPARQL-XML result
	 */
	public abstract function query($query, $params = "", $graph = "");

	/**
	 * Calls the webservice which gives status information about the triple store connector.
	 *
	 * @param $graph Graph-URI
	 * @return String (HTML) or false on an error
	 */
	public abstract function getStatus($graph);

	public static function getConnector() {
		if (is_null(self::$_instance)) {
			global $smwgMessageBroker, $smwgWebserviceProtocol;

			if (isset($smwgMessageBroker)) {
				if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
					self::$_instance = new TSConnectorMessageBrokerAndRESTWebservice();
				} else {
					self::$_instance = new TSConnectorMessageBrokerAndSOAPWebservice();
				}
			} else if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
				self::$_instance = new TSConnectorRESTWebservice();

			} else {

				self::$_instance = new TSConnectorSOAPWebservice();
			}
		}
		return self::$_instance;
	}
}

/**
 * MessageBroker connector implementation for updates (SPARUL).
 * SOAP webservice for SPARQL queries.
 *
 */
class TSConnectorMessageBrokerAndSOAPWebservice extends TSConnectorSOAPWebservice {


	public function connect() {
		global $smwgMessageBroker;
		$this->updateClient = new StompConnection("tcp://$smwgMessageBroker:61613");
		$this->updateClient->connect();

		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion, $smwgUseLocalhostForWSDL;
		if (!isset($smwgDeployVersion) || !$smwgDeployVersion) ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
		if (isset($smwgUseLocalhostForWSDL) && $smwgUseLocalhostForWSDL === true) $host = "http://localhost"; else $host = $wgServer;
		$this->queryClient = new SoapClient("$host$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_sparql", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));

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
 * MessageBroker connector implementation for updates (SPARUL).
 * REST webservice for SPARQL queries.
 *
 */
class TSConnectorMessageBrokerAndRESTWebservice extends TSConnectorRESTWebservice {


	public function connect() {
		global $smwgMessageBroker;
		$this->updateClient = new StompConnection("tcp://$smwgMessageBroker:61613");
		$this->updateClient->connect();

		global $smwgWebserviceUser, $smwgWebservicePassword, $smwgWebserviceEndpoint;
		list($host, $port) = explode(":", $smwgWebserviceEndpoint);
		$credentials = isset($smwgWebserviceUser) ? $smwgWebserviceUser.":".$smwgWebservicePassword : "";
		$this->queryClient = new RESTWebserviceConnector($host, $port, "sparql", $credentials);
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
		global $smwgWebserviceUser, $smwgWebservicePassword, $smwgWebserviceEndpoint;
		list($host, $port) = explode(":", $smwgWebserviceEndpoint);
		$credentials = isset($smwgWebserviceUser) ? $smwgWebserviceUser.":".$smwgWebservicePassword : "";
		$this->updateClient = new RESTWebserviceConnector($host, $port, "sparul", $credentials);
		$this->queryClient = new RESTWebserviceConnector($host, $port, "sparql", $credentials);
		$this->manageClient = new RESTWebserviceConnector($host, $port, "management_rest/", $credentials);
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

	public function query($query, $params = "") {
		global $smwgTripleStoreGraph;
		if (stripos(trim($query), 'SELECT') === 0 || stripos(trim($query), 'PREFIX') === 0) {
			// SPARQL, attach common prefixes
			$query = TSNamespaces::getAllPrefixes().$query;
		}
		$queryRequest = "query=".urlencode($query);
		$queryRequest = "&default-graph-uri=".urlencode($smwgTripleStoreGraph);
		$queryRequest = "&params=".urlencode($params);

		list($header, $status, $result) = $this->queryClient->send($queryRequest);
		if ($status != 200) {
			throw new Exception(strip_tags($result), $status);
		}
		return $result;
	}

	public function getStatus($graph) {
		global $smwgTripleStoreGraph;

		$request = "graph=".urlencode($smwgTripleStoreGraph);
		
		list($header, $status, $result) = $this->manageClient->send($request, "getTripleStoreStatus");
		if ($status != 200) {
			throw new Exception(strip_tags($result), $status);
		}
		$xmlDoc = simplexml_load_string($result);
		$resultMap = array();
		$resultMap['tscversion'] = (string) $xmlDoc->tscversion;
		$resultMap['driverInfo'] = (string) $xmlDoc->driverInfo;
		$resultMap['isInitialized'] = ((string) $xmlDoc->isInitialized) == 'true';
		$resultMap['features'] = explode(",", (string) $xmlDoc->features);

		return $resultMap;

	}
}

/**
 * SOAP webservice connector implementation.
 *
 */
class TSConnectorSOAPWebservice extends TSConnection {

	public function connect() {
		global $smwgWebserviceUser, $smwgWebservicePassword, $wgServer, $wgScript, $smwgUseLocalhostForWSDL;
		if (!isset($smwgDeployVersion) || !$smwgDeployVersion) ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
		if (isset($smwgUseLocalhostForWSDL) && $smwgUseLocalhostForWSDL === true) $host = "http://localhost"; else $host = $wgServer;
		$this->updateClient = new SoapClient("$host$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_sparul", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));

		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion, $smwgUseLocalhostForWSDL;
		if (!isset($smwgDeployVersion) || !$smwgDeployVersion) ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
		if (isset($smwgUseLocalhostForWSDL) && $smwgUseLocalhostForWSDL === true) $host = "http://localhost"; else $host = $wgServer;
		$this->queryClient = new SoapClient("$host$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_sparql", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));
		$this->manageClient = new SoapClient("$host$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_manage", array('connection_timeout' => 4, 'login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));
	}

	public function disconnect() {
		// do nothing. webservice calls use stateless HTTP protocol.
	}

	public function update($topic, $commands) {
		if (!is_array($commands)) {
			$enc_commands = isset($smwgSPARULUpdateEncoding) && $smwgSPARULUpdateEncoding === "UTF-8" ? utf8_encode($commands) : $commands;
			$this->updateClient->update($enc_commands);
			return;
		}
		$commandStr = implode("|||",$commands);
		$enc_commands = isset($smwgSPARULUpdateEncoding) && $smwgSPARULUpdateEncoding === "UTF-8" ? utf8_encode($commandStr) : $commandStr;
		$this->updateClient->update($enc_commands);
	}

public function query($query, $params = "", $graph = "") {


		global $smwgTripleStoreGraph;
		if (empty($graph)) {
			$graph = $smwgTripleStoreGraph;
		}
		
		if (stripos(trim($query), 'SELECT') === 0 || stripos(trim($query), 'PREFIX') === 0) {
			// SPARQL, attach common prefixes
			$response = $this->queryClient->query(TSNamespaces::getAllPrefixes().$query, $graph, $params);
		} else {

			// do not attach anything
			$response = $this->queryClient->query($query, $graph, $params);

		}
		return $response;
	}

	public function getStatus($graph) {
		// getTripleStoreStatus throws a SOAP exception if necessary
		$result = $this->manageClient->getTripleStoreStatus($graph);
		$xmlDoc = simplexml_load_string($result);
		$resultMap = array();
		$resultMap['tscversion'] = (string) $xmlDoc->tscversion;
		$resultMap['driverInfo'] = (string) $xmlDoc->driverInfo;
		$resultMap['isInitialized'] = ((string) $xmlDoc->isInitialized) == 'true';
		$resultMap['features'] = explode(",", (string) $xmlDoc->features);
		return $resultMap;
	}
}
