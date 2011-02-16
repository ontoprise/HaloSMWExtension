<?php
/**
 * @file
 * @ingroup ExportObjectLogicBot
 *
 * @defgroup ExportObjectLogicBot
 * @ingroup SemanticGardeningBots
 *
 * @author Kai Kühn
 *
 * Created on 16.02.2011
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

/**
 * Exports object logic from TSC.
 *
 * @author kuehn
 *
 */
class ExportObjectLogicBot extends GardeningBot {
	function __construct() {
		parent::GardeningBot("smw_exportobjectlogicbot");
	}

	public function getHelpText() {
		return wfMsg('smw_gard_exportobl_docu');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function isVisible() {
		return smwfIsTripleStoreConfigured();
	}


	/**
	 * Returns an array of GardeningParamObjects
	 */
	public function createParameters() {
		return array();
	}

	/**
	 * Export ontology
	 * DO NOT use echo when it is not running asynchronously.
	 */
	public function run($paramArray, $isAsync, $delay) {

		// do not allow to start synchronously.
		if (!$isAsync) {
			return "Export ontology bot should not be executed synchronously!";
		}
		$this->setNumberOfTasks(5);

		// request ontology export
		echo "\nRequest ontology export...";
		$tsConnector = TSConnection::getConnector();
		$tsConnector->connect();
		$this->addSubTask(1);
		$res = $tsConnector->manage("exportData", array("format"=>"obl"));
		echo "done.";
		$this->worked(1);


		// wait until finished
		echo "\nWait until finished...";
		$this->addSubTask(1);
		$files = explode(",", $res);
		$params = array('file' => $files);
		while(true) {
			sleep(5);
			$res = $tsConnector->manage("isExportReady", $params);
			if (trim($res) == 'true') break;
		}
		echo "done.";
		$this->worked(1);

		// download files
		$download = new SGA_HttpDownload();

		global $smwgWebserviceEndpoint;
		list($host, $port) = explode(":", $smwgWebserviceEndpoint);
		$port += 2;

		// create temp directory
		echo "\nDownload files...";
		$this->addSubTask(count($files));
		$tempdir = self::getTempDir()."/tscDownloads";
		self::mkpath($tempdir);

		foreach($files as $f) {
			$url = "http://$host:$port/$f";
			echo "\n\t$url";
			$download->downloadAsFileByURL($url, "$tempdir/$f", "", $this);
			$this->worked(1);
		}
		echo "done.";
		$tsConnector->disconnect();

		// upload files
		echo "\nUpload files...";
		$this->addSubTask(count($files));
		global $wgServer, $wgScriptPath;
		$downloadLink = '';
		foreach($files as $f) {
			$exportFileTitle = Title::newFromText(basename($tempdir."/".$f), NS_IMAGE);
			$im_file = wfLocalFile($exportFileTitle);
			$im_file->upload($tempdir."/".$f, "auto-inserted file", "noText");
			$downloadLink .= "\n*[[".$exportFileTitle->getPrefixedText()."|".wfMsg('smw_gard_export_here')."]]";
			echo "\n\t$tempdir."/".$f to page ".$exportFileTitle->getPrefixedText();
			$this->worked(1);
		}
		echo "done.";

		// remove temporary files
		echo "\Remove temporary files...";
		$this->addSubTask(1);
		foreach($files as $f) {
			unlink($tempdir."/".$f);
			echo "\n\t$tempdir."/".$f";
		}
		$this->worked(1);
		echo "done.";

		return "\n\n$downloadLink\n\n";

	}
	/**
	 * Creates the given directory.
	 *
	 * @param string $path
	 * @return unknown
	 */
	private static function mkpath($path) {
		if(@mkdir($path) || file_exists($path)) return true;
		return (self::mkpath(dirname($path)) && @mkdir($path));
	}
	/**
	 * Returns the home directory.
	 * (path with slashes only also on Windows)
	 *
	 * @return string
	 */
	private static function getTempDir() {
		if (self::isWindows()) {
			exec("echo %TEMP%", $out, $ret);
			return str_replace("\\", "/", reset($out));
		} else {
			exec('echo $TMPDIR', $out, $ret);
			$tmpdir = trim(reset($out));
			if (empty($tmpdir)) {
				$tmpdir = "/tmp"; // fallback
			}
			return $tmpdir;
		}
	}

	/**
	 * Checks if script runs on a Windows machine or not.
	 *
	 * @return boolean
	 */
	private static function isWindows() {
		static $thisBoxRunsWindows;

		if (! is_null($thisBoxRunsWindows)) return $thisBoxRunsWindows;

		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();
		//Get Systemstring
		preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
		//Check if it consists 'windows' as string
		preg_match('/[Ww]indows/',$ma[1],$os);
		$thisBoxRunsWindows= count($os) > 0;
		return $thisBoxRunsWindows;
	}

	/**
	 * Shows the download progress.
	 *
	 * @param float $per
	 */
	public function downloadProgress($length, $contentLength = 0) {

	}

	public function downloadStart($filename) {
		if (!is_null($filename)) echo "\nDownloading $filename...\n";
	}

	public function downloadFinished($filename) {
		if (!is_null($filename)) echo "\n$filename was downloaded.";
	}
}

/*
 * Note: This bot filter has no real functionality. It is just a dummy to
 * prevent error messages in the GardeningLog. There are no gardening issues
 * about exporting. Instead there's a textual log.
 * */
define('SMW_EXPORTOBECTLOGIC_BOT_BASE', 1200);
require_once ($sgagIP . '/includes/SGA_GardeningIssues.php');
class ExportObjectLogicBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_EXPORTOBECTLOGIC_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'));
	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {

	}

	public function getData($options, $request) {
		parent::getData($options, $request);
	}
}

// create instance
new ExportObjectLogicBot();

/**
 * @file
 * @ingroup DFIO
 *
 * HTTP Downloader implementation.
 *
 * @author Kai Kühn / Ontoprise / 2009
 *
 */
class SGA_HttpDownload {
	private $header;



	public function __construct() {
			
	}

	/**
	 * Downloads a resource via HTTP protocol and stores it into a file.
	 *
	 * @param URL $url
	 * @param string $filename
	 * @param object $callback: An object with 2 methods:
	 *                     downloadProgress($percentage).
	 *                     downloadFinished($filename)
	 */
	public function downloadAsFileByURL($url, $filename, $credentials = "", $callback = NULL) {
		$partsOfURL = parse_url($url);

		$path = $partsOfURL['path'];
		$host = $partsOfURL['host'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$this->downloadAsFile($path, $port, $host, $filename, $credentials, $callback);
	}

	/**
	 * Downloads a resource via HTTP protocol and stores it into a file.
	 *
	 * @param string $path
	 * @param int $port
	 * @param string $host
	 * @param string $filename (may contain path)
	 * @param object $callback: An object with 2 methods:
	 *                     downloadProgress($percentage).
	 *                     downloadFinished($filename)
	 *      If null, an internal rendering method uses the console to show a progres bar and a finish message.
	 */
	public function downloadAsFile($path, $port, $host, $filename, $credentials = "", $callback = NULL) {

		$credentials = trim($credentials);
		if ($credentials == ':') $credentials = ''; // make sure the credentials are not empty by accident
		$address = gethostbyname($host);
		$handle = fopen($filename, "wb");
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		socket_connect($socket, $address, $port);
		$in = "GET $path HTTP/1.0\r\n";
		$in .= "Host: $host\r\n";
		if ($credentials != '') $in .= "Authorization: Basic ".base64_encode(trim($credentials))."\r\n";
		$in .= "\r\n";
		socket_write($socket, $in, strlen($in));
		$this->headerFound = false;
		$this->header = "";
		$length = 0;
		$cb = is_null($callback) ? $this : $callback;
		call_user_func(array($cb,"downloadStart"), basename(dirname($path))."/".basename($path));
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

			call_user_func(array($cb,"downloadProgress"), $length,$contentLength);

		}  while ($out = socket_read($socket, 2048));

		call_user_func(array($cb,"downloadProgress"), 100,100);

		call_user_func(array($cb,"downloadFinished"), $filename);
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
	public function downloadAsString($path, $port, $host, $credentials = "", $callback = NULL) {

		$credentials = trim($credentials);
		if ($credentials == ':') $credentials = ''; // make sure the credentials are not empty by accident
		$address = gethostbyname($host);
		$res = "";
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			
		socket_connect($socket, $address, $port);
		$in = "GET $path HTTP/1.0\r\n";
		$in .= "Host: $host\r\n";
		if ($credentials != '') $in .= "Authorization: Basic ".base64_encode(trim($credentials))."\r\n";
		$in .= "\r\n";
		socket_write($socket, $in, strlen($in));
		$this->headerFound = false;
		$this->header = "";
		$length = 0;
		$cb = is_null($callback) ? $this : $callback;
		call_user_func(array($cb,"downloadStart"), basename(dirname($path))."/".basename($path));
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
			call_user_func(array($cb,'downloadProgress'), $length, $contentLength);

		} while ($out = socket_read($socket, 2048));


		call_user_func(array($cb,"downloadProgress"), 100,100);

		call_user_func(array($cb,"downloadFinished"), NULL);

		socket_close($socket);
		return $res;
	}


	private function checkHeader($path) {
		preg_match('/HTTP\/\d.\d\s+(\d+)/', $this->header, $matches);
		if (!isset($matches[1])) throw new SGA_HttpError("Invalid HTTP header");
		switch($matches[1]) {
			case 200: break; // OK
			case 400: throw new SGA_HttpError("Bad request: $path", 400, $this->header);
			case 401: throw new SGA_HttpError("Authorization required: $path", 401, $this->header);
			case 403: throw new SGA_HttpError("Access denied: $path", 403, $this->header);
			case 404: throw new SGA_HttpError("File not found: $path", 404, $this->header);
			default: throw new SGA_HttpError("Unknown HTTP error", $matches[1], $this->header);
		}


	}






	/**
	 * Parser content length from HTTP header
	 *
	 * @return int
	 */
	private function getContentLength() {
		preg_match("/Content-Length:\\s*(\\d+)/", $this->header, $matches);
		//if (!isset($matches[1])) throw new SGA_HttpError("Content-Length not set", 0, $this->header);
		if (!isset($matches[1])) return 0; //Return 0 if contentlength is not set, e.g. filtert by a proxy
		if (!is_numeric($matches[1])) throw new SGA_HttpError("Content-Length not numeric", 0, $this->header);
		return intval($matches[1]);
	}


}

class SGA_HttpError extends Exception {
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



