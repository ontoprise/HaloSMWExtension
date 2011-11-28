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
		global $smwgHaloWebserviceEndpoint, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgWebserviceProtocol;

		if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
			list($host, $port) = explode(":", $smwgHaloWebserviceEndpoint);
			$credentials = isset($smwgHaloWebserviceUser) ? $smwgHaloWebserviceUser.":".$smwgHaloWebservicePassword : "";
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
	 * @param	TSCSourceDefinition	$dataSource
	 * @param	bool	$update
	 * @return	bool	true, if successful
	 */
	public function runImport(TSCSourceDefinition $dataSource, $update, $synchronous, $runSchemaTranslation ,$runIdentityResolution) {
		$paramMap = array("dataSourceId" => $dataSource->getID(), "update" => $update,  "synchronous" =>$synchronous,
                    "runSchemaTranslation" => $runSchemaTranslation, "runIdentityResolution" => $runIdentityResolution);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/runImport");
		if ($status != 200) {
			return false;
		}
		return true;
	}

	/**
	 * @param	TSCSourceDefinition	$dataSource
	 * @param	resource or string	$inputHandleOrString
	 * @param	bool	$update
	 * @return	string	Temporary graph URI; null on error
	 */
	public function loadData(TSCSourceDefinition $dataSource, $inputHandleOrString, $inContentType, $update) {
		$paramMap = array("dataSourceId" => $dataSource->getID(), "update" => $update ? "true" : "false");
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->sendData($payload, "/loadData", $inputHandleOrString, $inContentType);
		if ($status != 200) {
			return null;
		}
		return $res;
	}
		
	/**
	 * @param TSCSourceDefinition $dataSource
	 * @param	string	$dataDumpLocationURL
	 * @param	bool	$update
	 * @return	string	Temporary graph URI; null on error
	 */
	public function loadDataFromDump(TSCSourceDefinition $dataSource, $dataDumpLocationURL, $update) {
		$paramMap = array("dataSourceId" => $dataSource->getID(), "dataDumpLocationURL" => $dataDumpLocationURL, "update" => $update);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/loadDataFromDump");
		if ($status != 200) {
			return null;
		}
		return $res;
	}
	
	/**
	 * @param	TSCSourceDefinition	$dataSourceIn
	 * @param	TSCSourceDefinition	$dataSourceOut
	 * @param	string	$temporaryGraphURI
	 * @param	bool	$dropTemporaryGraph
	 * @return	string	Import graph URI; null on error
	 */	
	public function translate(TSCSourceDefinition $dataSourceIn, TSCSourceDefinition $dataSourceOut, $temporaryGraphURI, $dropTemporaryGraph) {
		$paramMap = array("dataSourceInId" => $dataSourceIn->getID(), "dataSourceOutId" => $dataSourceOut->getID(), "temporaryGraphURI" => $temporaryGraphURI, "dropTemporaryGraph" => $dropTemporaryGraph);
		$payload = $this->serializeParameters($paramMap);
		list($header, $status, $res) =  $this->_client->send($payload, "/translate");
		if ($status != 200) {
			return null;
		}
		return $res;
	}	

	/**
	 * @param	TSCSourceDefinition	$dataSourceIn
	 * @param	TSCSourceDefinition	$dataSourceOut
	 * @param	string	$importGraphURI
	 */	
	public function resolve(TSCSourceDefinition $dataSourceIn, TSCSourceDefinition $dataSourceOut, $importGraphURI) {
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
