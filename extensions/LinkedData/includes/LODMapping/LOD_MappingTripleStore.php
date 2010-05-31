<?php
/**
 * @file
 * @ingroup LinkedData
 */
global $smwgHaloIP;
require_once($smwgHaloIP."/includes/storage/SMW_RESTWebserviceConnector.php");

/**
 * This is the implementation for accessing the mapping endpoint at the TSC.
 *
 * Note: There is no SOAP implementation available, because the TSC does not
 * support SOAP any more.
 *
 * @author Kai
 * Date: 28.5.2010
 *
 */
class LODMappingTripleStore implements ILODMappingStore {

	private $_client;

	function __construct() {

		// create webservice client
		global $smwgWebserviceEndpoint, $smwgWebserviceUser, $smwgWebservicePassword, $smwgWebserviceProtocol;

		if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
			list($host, $port) = explode(":", $smwgWebserviceEndpoint);
			$credentials = isset($smwgWebserviceUser) ? $smwgWebserviceUser.":".$smwgWebservicePassword : "";
			$this->_client = new RESTWebserviceConnector($host, $port, "mapping", $credentials);
		} else {
			trigger_error("Mapping endpoint can not be requested by SOAP.");
			die();
		}

	}

	// start implement interfaces methods from ILODMappingStore
	public function existsMapping($source, $target) {
		$paramMap = array("sourceID" => $source, "targetID" => $target);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) = $this->_client->send($payload, "/existsMapping");
		if ($status != 200) {
			return false;
		}
		return trim($res) == 'true';
	}

	public function addMapping(LODMapping $mapping) {
		$paramMap = array("sourceID" => $mapping->getSource(), "targetID" => $mapping->getTarget(), "mappingText" => $mapping->getMappingText());
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/addMapping");
		if ($status != 200) {
			return false;
		}
		return true;
	}

	public function getAllMappings($source = null, $target = null) {
		$paramMap = array("sourceID" => $source, "targetID" => $target);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/getAllMappings");
		if ($status != 200) {
			return array();
		}
		return $this->makeMappingsFromXML($res);
	}

	public function removeAllMappings($source = null, $target = null) {
		$paramMap = array("sourceID" => $source, "targetID" => $target);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/removeAllMappings");

	}

	public function getAllSources() {
		$payload = "";
		list($header, $status, $res) =  $this->_client->send($payload, "/getAllSources");
		if ($status != 200) {
			return array();
		}
		return $this->makeIDs2Array($res);
	}

	public function getAllTargets() {
		$payload = "";
		list($header, $status, $res) =  $this->_client->send($payload, "/getAllTargets");
		if ($status != 200) {
			return array();
		}
		return $this->makeIDs2Array($res);
	}
	// end implement interfaces methods from ILODMappingStore

	/**
	 * Converts the list of IDs from XML to an array of string.
	 *
	 * @param string $xml
	 * @return array of IDs (string)
	 */
	private function makeIDs2Array($xml) {
		$dom = simplexml_load_string($xml);
		if($dom === FALSE) return array();
		$ids = $dom->xpath('//id');
		if($ids === FALSE) return array();

		$results = array();
		foreach($ids as $id) {
			$results[] = (string) $id;
		}
		return $results;

	}

	/**
	 * Converts the list of IDs from XML to an array of string.
	 *
	 * @param string $xml
	 * @return array of LODMappings
	 */
	private function makeMappingsFromXML($xml) {
		$dom = simplexml_load_string($xml);
		if($dom === FALSE) return array();
		$mappings = $dom->xpath('//mapping');
		if($mappings === FALSE) return array();

		$results = array();
		foreach($mappings as $m) {
			$results[] = new LODMapping((string) $m, (string) $m->attributes()->sourceID, (string) $m->attributes()->targetID);
		}
		
		return $results;

	}

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