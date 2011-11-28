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
 * 
 * REST webservice connector.
 *
 * @author: Kai K�hn
 *
 */
class RESTWebserviceConnector {

	private $host;
	private $port;
	private $path;
	private $credentials;

	/**
	 * Creates a connection to http://$host:$port/path
	 * 
	 * @param string $host
	 * @param int $port
	 * @param string $path
	 * @param string $credentials (format user:pass)
	 */
	public function __construct($host, $port, $path, $credentials = '') {
		$this->host = $host;
		$this->port = $port;
		$this->path = $path;
		$this->credentials = $credentials;
	}
    
	/**
	 * Sends a HTTP request with the given payload.
	 * 
	 * @param $payload
	 * 
	 * @returns array(HTTP header, HTTP status code, Message body)
	 */
	public function send($payload, $path = '', $acceptMIME = NULL) {


		$res = "";
		$header = "";

		// Create a curl handle to a non-existing location
		$ch = curl_init("http://".$this->host.":".$this->port."/".$this->path.$path);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
		$httpHeader = array (
        "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
        "Expect: "
        );
        if (!is_null($acceptMIME)) $httpHeader[] = "Accept: $acceptMIME";
		curl_setopt($ch,CURLOPT_HTTPHEADER, $httpHeader);
        if ($this->credentials != '') curl_setopt($ch,CURLOPT_USERPWD,trim($this->credentials));
        // Execute
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $res = curl_exec($ch);
       
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
       
        $bodyBegin = strpos($res, "\r\n\r\n");
        list($header, $res) = $bodyBegin !== false ? array(substr($res, 0, $bodyBegin), substr($res, $bodyBegin+4)) : array($res, "");
        return array($header, $status, str_replace("%0A%0D%0A%0D", "\r\n\r\n", $res));
	}
	
	
	/**
	 * Sends an input stream or string data via HTTP POST
	 * 
	 * @param string	$payload
	 * @param string	$path
	 * @param resource or string	$inputHandleOrString
	 * @param string	$contentType
	 * @param string	$charset (optional; defaults to utf-8)
	 * 
	 * @returns array(HTTP header, HTTP status code, Message body)
	 */
	public function sendData($payload, $path, $inputHandleOrString, $contentType, $charset="utf-8") {


		$res = "";
		$header = "";

		// Create a curl handle to a non-existing location
		$ch = curl_init("http://".$this->host.":".$this->port."/".$this->path.$path."?".$payload);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array (
        "Content-Type: $contentType; charset=$charset",
        "Expect: "
        ));
        if (is_resource($inputHandleOrString)) {
			curl_setopt($ch,CURLOPT_INFILE,$inputHandleOrString);
			curl_setopt($ch,CURLOPT_PUT,true);
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");
        } else {
			curl_setopt($ch,CURLOPT_POST,true);
        	curl_setopt($ch,CURLOPT_POSTFIELDS,$inputHandleOrString);
        }
        
        if ($this->credentials != '') curl_setopt($ch,CURLOPT_USERPWD,trim($this->credentials));
        // Execute
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $res = curl_exec($ch);
       
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
       
        list($header, $res) = strpos($res, "\r\n\r\n") !== false ? explode("\r\n\r\n", $res) : array($res, "");
        return array($header, $status, str_replace("%0A%0D%0A%0D", "\r\n\r\n", $res));
	}

	private function getContentLength($header) {
		preg_match("/Content-Length:\\s*(\\d+)/i", $header, $matches);
		if (!isset($matches[1])) throw new RESTHttpError("Content-Length not set", 0, $header);
		if (!is_numeric($matches[1])) throw new RESTHttpError("Content-Length not numeric", 0, $header);
		return intval($matches[1]);
	}


}

class RESTHttpError extends Exception {
	var $errcode;
	var $msg;
	var $httpHeader;

	public function __construct($msg, $errcode, $httpHeader) {
		$this->msg = $msg;
		$this->errcode = $errcode;
		$this->httpHeader = $httpHeader;
	}

	public function getMsg() {
		return $this->msg;
	}

	public function getErrorCode() {
		return $this->errcode;
	}

	public function getHeader() {
		return $this->httpHeader;
	}
}
