<?php

/**
 * @file
 * @ingroup SMWHaloTriplestore
 * 
 * REST webservice connector.
 *
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
class RESTWebserviceConnector {

	private $host;
	private $port;
	private $path;
	private $credentials;

	public function __construct($host, $port, $path, $credentials = '') {
		$this->host = $host;
		$this->port = $port;
		$this->path = $path;
		$this->credentials = $credentials;
	}

	public function send($payload, $service) {


		$res = "";
		$header = "";

		// Create a curl handle to a non-existing location
		$ch = curl_init("http://".$this->host.":".$this->port."/$service");
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array (
        "Content-Type: text/xml; charset=utf-8",
        "Expect: "
        ));
        if ($this->credentials != '') curl_setopt($ch,CURLOPT_USERPWD,trim($this->credentials));
        // Execute
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $res = curl_exec($ch);
       
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
       
        list($header, $res) = explode("\r\n\r\n", $res);
        return array($header, $res);
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