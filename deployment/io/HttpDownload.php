<?php

define('PROGRESS_BAR_LENGTH', 40);

/**
 * @author Kai Kühn / Ontoprise / 2009
 *
 */
class HttpDownload {
	private $header;

	/**
	 * Downloads a resource via HTTP protocol and stores it into a file.
	 *
	 * @param URL $url
	 * @param string $filename
	 * @param object $callback: An object with 2 methods:
	 *                     downloadProgres($percentage).
	 *                     downloadFinished($filename)
	 */
	public function downloadAsFileByURL($url, $filename, $callback = NULL) {
		$partsOfURL = parse_url($url);

		$path = $partsOfURL['path'];
		$host = $partsOfURL['host'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$this->downloadAsFile($path, $port, $host, $filename, $callback);
	}

	/**
	 * Downloads a resource via HTTP protocol and stores it into a file.
	 *
	 * @param string $path
	 * @param int $port
	 * @param string $host
	 * @param string $filename (may contain path)
	 * @param object $callback: An object with 2 methods:
	 *                     downloadProgres($percentage).
	 *                     downloadFinished($filename)
	 *      If null, an internal rendering method uses the console to show a progres bar and a finish message.
	 */
	public function downloadAsFile($path, $port, $host, $filename, $callback = NULL) {

		$address = gethostbyname($host);
		$handle = fopen($filename, "wb");
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($socket, $address, $port);
		$in = "GET $path HTTP/1.0\r\n\r\n";
		socket_write($socket, $in, strlen($in));
		$this->headerFound = false;
		$this->header = "";
		$length = 0;
		$cb = is_null($callback) ? $this : $callback;
		call_user_func(array(&$cb,"downloadStart"), basename(dirname($path))."/".basename($path));
		$out = socket_read($socket, 2048);

		do {

			if (! $this->headerFound) {
				$this->header .= $out;
				$index = strpos($this->header, "\r\n\r\n");

				if ($index !== false) {
						
					$out = substr($this->header, $index+4);
					$length = strlen($out);
					$this->header = substr($this->header, 0, $index);
					$this->checkHeader($path);
					$contentLength = $this->getContentLength();
					$this->headerFound = true;
				} else {

					continue;
				}
			}
			$length += strlen($out);
			fwrite($handle, $out);
			$percentage = $length / $contentLength;

			call_user_func(array(&$cb,"downloadProgres"), $percentage > 1 ? 1 : $percentage);

		}  while ($out = socket_read($socket, 2048));
		if ($percentage < 1) call_user_func(array(&$callback,"progress"), 1);
		call_user_func(array(&$cb,"downloadFinished"), $filename);
		fclose($handle);
		socket_close($socket);
	}

	/**
	 * Downloads a resource via HTTP protocol returns the content as string.
	 *
	 * @param string $path
	 * @param int $port
	 * @param string $host
	 * @param object $callback: An object with 2 methods:
	 *                     downloadProgres($percentage).
	 *                     downloadFinished($filename)
	 *      If null, an internal rendering method uses the console to show a progres bar and a finish message.
	 * @return string
	 */
	public function downloadAsString($path, $port, $host, $callback = NULL) {

		$address = gethostbyname($host);
		$res = "";
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($socket, $address, $port);
		$in = "GET $path HTTP/1.0\r\n\r\n";
		socket_write($socket, $in, strlen($in));
		$this->headerFound = false;
		$this->header = "";
		$length = 0;
		$cb = is_null($callback) ? $this : $callback;
		call_user_func(array(&$cb,"downloadStart"), basename(dirname($path))."/".basename($path));
		$out = socket_read($socket, 2048);

		do {
			if (! $this->headerFound) {
				$this->header .= $out;
				$index = strpos($this->header, "\r\n\r\n");

				if ($index !== false) {

					$out = substr($this->header, $index+4);
					$length = strlen($out);
					$this->header = substr($this->header, 0, $index);
					$this->checkHeader($path);
					$contentLength = $this->getContentLength();

					$this->headerFound = true;

						
				} else {


					continue;
				}
			}
			$length += strlen($out);
			$res .= $out;
			$percentage = $length / $contentLength;

			call_user_func(array(&$cb,"downloadProgres"), $percentage > 1 ? 1 : $percentage);

		} while ($out = socket_read($socket, 2048));

		if ($percentage < 1) call_user_func(array(&$callback,"progress"), 1);
		call_user_func(array(&$cb,"downloadFinished"), NULL);

		socket_close($socket);
		return $res;
	}


	private function checkHeader($path) {
		preg_match('/HTTP\/\d.\d\s+(\d+)/', $this->header, $matches);
		if (!isset($matches[1])) throw new HttpError("Invalid HTTP header");
		switch($matches[1]) {
			case 200: break;
			case 404: throw new HttpError("File not found: $path", 404);
			default: throw new HttpError("Error", $matches[1]);
		}
			

	}

	/**
	 * Parser content length from HTTP header
	 *
	 * @return int
	 */
	private function getContentLength() {
		preg_match("/Content-Length:\\s*(\\d+)/", $this->header, $matches);
		if (!isset($matches[1])) throw new HttpError();
		if (!is_numeric($matches[1])) throw new HttpError();
		return intval($matches[1]);
	}

	/**
	 * Shows a progres bar on the console
	 *
	 * @param float $per
	 */
	public function downloadProgres($per) {
		static $first = true;
		static $lastLength = 0;
		if (!$first) for($i = 0; $i < $lastLength; $i++) echo chr(8);
		$first = false;
		$prg = intval(round($per,2)*PROGRESS_BAR_LENGTH);
		$done = "";
		$left = "";
		for($i = 0; $i < $prg; $i++) $done .= "=";
		for($i = $prg; $i < PROGRESS_BAR_LENGTH; $i++) $left .= " ";
		$per100 = intval(round($per,2)*100);
		$show = "[$done$left] $per100%";
		echo $show;
		$lastLength = strlen($show);
	}

	public function downloadStart($filename) {
		if (!is_null($filename)) echo "\n\nDownloading $filename...\n";
	}

	public function downloadFinished($filename) {
		echo "\n";
		//if (!is_null($filename)) echo "\n$filename was downloaded.";
	}
}

class HttpError extends Exception {
	var $errcode;
	var $msg;

	public function __construct($msg, $errcode) {
		$this->msg = $msg;
		$this->errcode = $errcode;
	}

	public function getMsg() {
		return $this->msg;
	}

	public function getErrorCode() {
		return $this->errcode;
	}
}

// testcode
/*$path = "/job/smwhalo/lastSuccessfulBuild/artifact/SMWHaloTrunk/extensions/SemanticNotifications/deploy/bin/smwhalo-semnot-1.0.zip";
 $port = 8080;
 $host = "dailywikibuilds.ontoprise.com";
 $d = new HttpDownload();
 $d->downloadAsFile($path, $port, $host, "result.zip");
 */
?>
