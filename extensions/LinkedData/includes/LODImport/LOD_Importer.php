<?php
/**
 * @file
 * @ingroup LinkedData
 */
global $smwgHaloIP;
require_once($smwgHaloIP."/includes/storage/SMW_RESTWebserviceConnector.php");

/**
 * This is the implementation for accessing the LDImporter endpoint at the TSC.
 *
 * @author Christian Becker, largely based on LOD_MappingTripleStore
 *
 */
class LODImporter {

	private $_client;

	function __construct() {
		// create webservice client
		global $smwgWebserviceEndpoint, $smwgWebserviceUser, $smwgWebservicePassword, $smwgWebserviceProtocol;

		if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
			list($host, $port) = explode(":", $smwgWebserviceEndpoint);
			$credentials = isset($smwgWebserviceUser) ? $smwgWebserviceUser.":".$smwgWebservicePassword : "";
			$this->_client = new RESTWebserviceConnector($host, $port, "ldimporter", $credentials);
		} else {
			trigger_error("LDImporter endpoint can not be requested by SOAP.");
			die();
		}
	}

	/**
	 * @return	bool	true, if successful
	 */
	public function checkUpdate() {
		list($header, $status, $res) = $this->_client->send("", "/checkUpdates");
		if ($status != 200) {
			return false;
		}
		return true;
	}

	/**
	 * @param	LODSourceDefinition	$dataSource
	 * @param	bool	$update
	 * @return	bool	true, if successful
	 */
	public function runImport(LODSourceDefinition $dataSource, $update) {
		$paramMap = array("dataSourceId" => $dataSource->getID(), "update" => $update);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/runImport");
		if ($status != 200) {
			return false;
		}
		return true;
	}

	/**
	 * @param	LODSourceDefinition	$dataSource
	 * @param	resource or string	$inputHandleOrString
	 * @param	bool	$update
	 * @return	string	Temporary graph URI; null on error
	 */
	public function loadData(LODSourceDefinition $dataSource, $inputHandleOrString, $inContentType, $update) {
		$paramMap = array("dataSourceId" => $dataSource->getID(), "update" => $update ? "true" : "false");
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->sendData($payload, "/loadData", $inputHandleOrString, $inContentType);
		if ($status != 200) {
			return null;
		}
		return $res;
	}
		
	/**
	 * @param LODSourceDefinition $dataSource
	 * @param	string	$dataDumpLocationURL
	 * @param	bool	$update
	 * @return	string	Temporary graph URI; null on error
	 */
	public function loadDataFromDump(LODSourceDefinition $dataSource, $dataDumpLocationURL, $update) {
		$paramMap = array("dataSourceId" => $dataSource->getID(), "dataDumpLocationURL" => $dataDumpLocationURL, "update" => $update);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/loadDataFromDump");
		if ($status != 200) {
			return null;
		}
		return $res;
	}
	
	/**
	 * @param	LODSourceDefinition	$dataSourceIn
	 * @param	LODSourceDefinition	$dataSourceOut
	 * @param	string	$temporaryGraphURI
	 * @param	bool	$dropTemporaryGraph
	 * @return	string	Import graph URI; null on error
	 */	
	public function translate(LODSourceDefinition $dataSourceIn, LODSourceDefinition $dataSourceOut, $temporaryGraphURI, $dropTemporaryGraph) {
		$paramMap = array("dataSourceInId" => $dataSourceIn->getID(), "dataSourceOutId" => $dataSourceOut->getID(), "temporaryGraphURI" => $temporaryGraphURI, "dropTemporaryGraph" => $dropTemporaryGraph);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/translate");
		if ($status != 200) {
			return null;
		}
		return $res;
	}	

	/**
	 * @param	LODSourceDefinition	$dataSourceIn
	 * @param	LODSourceDefinition	$dataSourceOut
	 * @param	string	$importGraphURI
	 */	
	public function resolve(LODSourceDefinition $dataSourceIn, LODSourceDefinition $dataSourceOut, $importGraphURI) {
		$paramMap = array("dataSourceInId" => $dataSourceIn->getID(), "dataSourceOutId" => $dataSourceOut->getID(), "importGraphURI" => $importGraphURI);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/resolve");
	}	

	/**
	 * @param unknown_type $paramMap
	 * @return string
	 */
	private function serializeParameters($paramMap) {
		$first = true;
		$result = "";
		foreach($paramMap as $param => $value) {
			if (is_null($value)) continue;
			if ($first) {
				$first = false;
				$result .= $param."=".urlencode($value);
			} else {
				$result .= "&".$param."=".urlencode($value);
			}
		}
		return $result;
	}
	
}