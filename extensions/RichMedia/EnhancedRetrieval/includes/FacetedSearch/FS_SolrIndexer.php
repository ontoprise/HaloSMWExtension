<?php
/*  Copyright 2011, ontoprise GmbH
*  This file is part of the Faceted Search Module of the Enhanced Retrieval Extension.
*
*   The Enhanced Retrieval Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Enhanced Retrieval Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the class FSSolrIndexer. It encapsulates access to the
 * Apache Solr indexing service.
 * 
 * @author Thomas Schweitzer
 * Date: 22.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}

/**
 * This class offers methods for accessing an Apache Solr indexing server.
 * 
 * @author thsc
 *
 */
abstract class FSSolrIndexer implements IFSIndexer {
	
	//--- Constants ---
	const PING_CMD = 'solr/admin/ping';
	const CREATE_FULL_INDEX_CMD = 'solr/dataimport?command=full-import';	
	const FULL_INDEX_CLEAN_OPT = '&clean=true';
	const COMMIT_UPDATE_CMD = 'solr/update?commit=true';
	const DELETE_INDEX_QUERY = '<delete><query>*:*</query></delete>';
	const DELETE_DOCUMENT_BY_ID = '<delete><id>$1</id></delete>'; // $1 must be replaced by the actual ID
	const QUERY_PREFIX = 'solr/select/?';
		
	const HTTP_OK = 200; 

	//--- Private fields ---
	
	// string: Name or IP address of the host of the server
	private $mHost;
	
	// int: Server port of the Solr server
	private $mPort;
	
	// string: Base URL for all HTTP request to the SOLR server
	private $mBaseURL;
	
	
	//--- getter/setter ---
	public function getHost()	{ return $this->mHost; }
	public function getPort()	{ return $this->mPort; }
	
	//--- Public methods ---
	
	/**
	 * Creates a new Solr indexer object. This method can only be called from
	 * derived classes.
	 * 
	 * @param string $host
	 * 		Name or IP address of the host of the server
	 * @param int $port
	 * 		Server port of the Solr server
	 */
	protected function __construct($host, $port) {
		$this->mHost = $host;
		$this->mPort = $port;
		$this->mBaseURL = "http://$host:$port/";
	}
	
	
	/**
	 * Pings the server of the indexer and checks if it is responding.
	 * @return bool
	 * 	<true>, if the server is responding
	 * 	<false> otherwise
	 */
	public function ping() {
		$result = $this->sendCommand(self::PING_CMD, $resultCode);
		return $resultCode == self::HTTP_OK;
	}
	
	/**
	 * Creates a full index of all available semantic data.
	 * 
	 * @param bool $clean
	 * 		If <true> (default), the existing index is cleaned before the new
	 * 		index is created.
	 * @return bool success
	 * 		<true> if the operation was successful
	 * 		<false> otherwise
	 */
	public function createFullIndex($clean = true) {
		$cmd = self::CREATE_FULL_INDEX_CMD;
		if ($clean) {
			$cmd .= self::FULL_INDEX_CLEAN_OPT;
		}
		$result = $this->sendCommand($cmd, $resultCode);
		return $resultCode == self::HTTP_OK;
		
	}
	
	/**
	 * Deletes the complete index.
	 */
	public function deleteIndex() {
		$this->postCommand(self::COMMIT_UPDATE_CMD, self::DELETE_INDEX_QUERY, $rc);
		return $rc == self::HTTP_OK;
	}
	
	/**
	 * Sends a raw query to the SOLR server in the query format expected by SOLR.
	 * @param string $query
	 * 		Raw query string (without base URL)
	 * @return mixed bool/string
	 * 		Result of the query or <false> if request failed.
	 */
	public function sendRawQuery($query) {
		$result = $this->sendCommand(self::QUERY_PREFIX.$query, $resultCode);
		return ($resultCode == self::HTTP_OK) ? $result : false;
	}
	
	/**
	 * Updates the index on the SOLR server for the given document.
	 * 
	 * The given document specification is transformed to XML and then sent to 
	 * the SOLR server.
	 * 
	 * @param array $document
	 * 		This array contains key-value pairs. The key is a field of the
	 * 		SOLR document. The value may be a single string i.e. the value of
	 * 		the SOLR field or an array of string if the field is multi-valued.
	 * @return bool
	 * 		<true> if the update was sent successfully
	 */
	public function updateIndex(array $document) {
		// Create the XML for the document
		$xml = "<add>\n\t<doc>\n";
		foreach ($document as $field => $value) {
			if (is_array($value)) {
				foreach ($value as $v) {
					$xml .= "\t\t<field name=\"$field\"><![CDATA[$v]]></field>\n";
				}
			} else {
				$xml .= "\t\t<field name=\"$field\"><![CDATA[$value]]></field>\n";
			}
		}
		$xml .= "\t</doc>\n</add>";
		
		// Send the XML as update command to the SOLR server
		$this->postCommand(self::COMMIT_UPDATE_CMD, $xml, $rc);
		
		return $rc == self::HTTP_OK;
	}
	
	/**
	 * Copies an indexed document with the ID $sourceID to a new document with the
	 * new ID $targetID.
	 * 
	 * @param int/string $sourceID
	 * 		The document ID of the source document
	 * @param int/string $targetID
	 * 		The target ID of the copied document.
	 * @param array(string => string) $ignoreCopyFields
	 * 		Some fields are copied automatically by the <copyfield> command in
	 * 		schema.xml. If a regular expression in a key of this array matches a
	 * 		field then the corresponding field that matches the value is removed.
	 * 		Example: "^(.*?)_t$" => "$1_s"
	 * 				This matches the field "someText_t", thus the field "someText_s"
	 * 				is removed. This corresponds to the copyfield command:
	 * 				<copyField source="*_t" dest="*_s"/>
	 * @return bool
	 * 		<true> if the copy was created successfully
	 * 		<false> otherwise
	 */
	public function copyDocument($sourceID, $targetID, $ignoreCopyFields = null) {
		$doc = array();
		
		// Get the document with the old id
		$r = $this->sendRawQuery("q=id:$sourceID&wt=json");
		if ($r === false) {
			return false;
		}

		$doc = json_decode($r, true);
		$doc = @$doc['response']['docs'][0];
			
		if (!isset($doc)) {
			// wrong structure of the document
			return false;
		}
		// Set the new ID
		$doc['id'] = $targetID;
		
		if (!is_null($ignoreCopyFields)) {
			foreach ($ignoreCopyFields as $srcPattern => $targetPattern) {
				foreach ($doc as $field => $val) {
					$f = preg_replace("/$srcPattern/", "$targetPattern", $field);
					if ($f !== $field) {
						// The source pattern matched => remove the target field
						unset($doc[$f]);
					}
				}
			}
		}
		// Create the copy
		return $this->updateIndex($doc);
	}
	
	/**
	 * Deletes the document with the ID $id from the index.
	 * 
	 * @param string/int $id
	 * 		ID of the document to delete.
	 * @return bool
	 * 		<true> if the document was deleted successfully
	 * 		<false> otherwise
	 * 		
	 */
	public function deleteDocument($id) {
		$cmd = self::DELETE_DOCUMENT_BY_ID;
		$cmd = str_replace('$1', $id, $cmd);
		$this->postCommand(self::COMMIT_UPDATE_CMD, $cmd, $rc);
		return $rc == self::HTTP_OK;
	}
	
	//--- Private methods ---
	
	/**
	 * Sends a command via curl to the SOLR server.
	 * @param string $command
	 * 		The command is appended to the base URI of the server and then sent
	 * 		as HTTP request.
	 * @param int $resultCode
	 * 		Returns the status of the HTTP request
	 * 			200 - HTTP_OK
	 * @return string
	 * 		Result of the request
	 */
	private function sendCommand($command, &$resultCode) {
		$url = $this->mBaseURL.$command;
		$fetch = curl_init( $url );
		if( defined( 'ERDEBUG' ) ) {
			curl_setopt( $fetch, CURLOPT_VERBOSE, 1 );
		}
		
		ob_start();
		$ok = curl_exec( $fetch );
		$result = ob_get_contents();
		ob_end_clean();
		
		$info = curl_getinfo( $fetch );
//		if( !$ok ) {
//			echo "Something went awry...\n";
//			var_dump( $info );
//			die();
//		}
		curl_close( $fetch );
		
		$resultCode = $info['http_code']; # ????
		return $result;
	}
	
	/**
	 * Sends a POST command to the SOLR server
	 * @param string $command
	 * 		The command is appended to the base URI of the server and then sent
	 * 		as HTTP request.
	 * @param string $data
	 * 		The data that will be posted.
	 * @param int $resultCode
	 * 		Returns the status of the HTTP request
	 * 			200 - HTTP_OK
	 */
	private function postCommand($command, $data, &$resultCode) {
		$url = $this->mBaseURL.$command;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($curl, CURLOPT_HEADER, 1);
//		curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);
//		if ($this->cookies == TRUE) curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file);
//		if ($this->cookies == TRUE) curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_file);
//		curl_setopt($curl, CURLOPT_ENCODING , $this->compression);
//		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
//		if ($this->proxy) curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		
		$result = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
		
		$resultCode = $info['http_code']; # ????
		return $result;
		
	}
	
}
