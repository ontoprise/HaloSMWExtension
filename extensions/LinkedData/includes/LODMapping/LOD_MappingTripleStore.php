<?php

global $smwgHaloIP;
require_once($smwgHaloIP."/includes/storage/SMW_RESTWebserviceConnector.php");

class LODMappingTripleStore implements ILODMappingStore {

	private $_client;

	function __construct() {
		
		// create 
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

	public function existsMapping($source, $target) {
		$payload = "sourceID=".urlencode($source)."&targetID=".urlencode($target);
		list($header, $status, $res) = self::$_client->send($payload, "/existsMapping");
		if ($status != 200) {
			return false;
		}
		return trim($res) == 'true';
	}

	public function addMapping(LODMapping $mapping) {
		$payload = "mappingText=".urlencode($mapping->getMappingText());
		list($header, $status, $res) = self::$_client->send($payload, "/addMapping");
		if ($status != 200) {
			return false;
		}
		return true;
	}

	public function getAllMappings($source = null, $target = null) {
		$payload = "sourceID=".urlencode($mapping->getSourceID())."&targetID=".urlencode($mapping->getTargetID());
		list($header, $status, $res) = self::$_client->send($payload, "/getAllMappings");
		if ($status != 200) {
			return array();
		}
		return array(new LODMapping($res, $source, $target));
	}

	public function removeAllMappings($source = null, $target = null) {
		$payload = "sourceID=".urlencode($mapping->getSourceID())."&targetID=".urlencode($mapping->getTargetID());
		list($header, $status, $res) = self::$_client->send($payload, "/removeAllMappings");

	}

	public function getAllSources() {
		$payload = "";
		list($header, $status, $res) = self::$_client->send($payload, "/getAllSources");
		if ($status != 200) {
			return array();
		}
	}

	public function getAllTargets() {
		$payload = "";
		list($header, $status, $res) = self::$_client->send($payload, "/getAllTargets");
		if ($status != 200) {
			return array();
		}
	}

}