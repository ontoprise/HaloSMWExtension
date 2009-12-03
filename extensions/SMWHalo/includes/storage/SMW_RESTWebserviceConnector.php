<?php

/**
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

	public function update($payload) {

		$address = gethostbyname($this->host);
		$res = "";
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($socket, $address, $this->port);

		$in =   "POST $this->path HTTP/1.0\r\n".
                "Host: $this->host\r\n".
                "Content-Type: text/xml\r\n".
                "Content-Length: ".strlen($payload)."\r\n";

		if ($this->credentials != '') $in .= "Authorization: Basic ".base64_encode(trim($this->credentials))."\r\n";
		$in .= "\r\n";
		$in .= $payload;

		socket_write($socket, $in, strlen($in));
		$headerFound = false;
		$header = "";

		$out = socket_read($socket, 2048, PHP_BINARY_READ);
		$payload = false;
		do {
			$read = strlen($out);

			if (! $headerFound) {
				$header .= $out;
				$index = strpos($header, "\r\n\r\n");

				if ($index !== false) {

					$out = substr($header, $index+4);

					$header = substr($header, 0, $index);
					$contentLength = $this->getContentLength($header);

					$headerFound = true;


				} else {
					if ($read == 0) throw new RESTHttpError("No header found", 0, $header);
					continue;
				}
			} else {
				$payload = true;
			}

			$res .= $out;

			if ($read < 2048 && $payload) break;


		} while ($out = socket_read($socket, 2048));

		socket_close($socket);
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