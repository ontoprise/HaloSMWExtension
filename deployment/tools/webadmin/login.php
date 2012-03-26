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
 * @ingroup WebAdmin
 *
 * @defgroup WebAdmin Web-administration tool
 * @ingroup DeployFramework
 *
 * Script for handling login.
 *
 * @author: Kai Kühn
 *
 */
$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");
require_once($mwrootDir.'/deployment/settings.php');
$wgScriptPath=isset(DF_Config::$scriptPath) ? DF_Config::$scriptPath : "/mediawiki";

require_once($mwrootDir.'/deployment/languages/DF_Language.php');
dffInitLanguage();

// make an environment check before showing login
$envCheck = dffCheckEnvironment();
if ($envCheck !== true) {
	echo "<h1>Installation problem</h1>";
	echo "Some problems found with the webadmin console:";
	echo $envCheck;
	exit();
}

$loginHint = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	// make sure that no other user is currently logged in
	$currentDir = dirname(__FILE__);
	if (file_exists("$currentDir/sessiondata/userloggedin")) {
		$lastMod = filemtime("$currentDir/sessiondata/userloggedin");
		$currenttime = time();
        
		// timeout is 5 min
		if ($currenttime - $lastMod < 300) {
			print "User already logged in. Try again later.";
			exit();
		}
	}

	session_start();

	$username = $_POST['username'];
	$passwort = $_POST['passwort'];

	$hostname = $_SERVER['HTTP_HOST'];
	$path = dirname($_SERVER['PHP_SELF']);
	
	// user name and password is checked
	if (isset(DF_Config::$df_authorizeByWiki) && DF_Config::$df_authorizeByWiki == true) {
		$isAuthorized = authenticateUser($username, $passwort);
		if ($isAuthorized === 400) {
			echo "Authentication by Wiki sysop-users requires Wiki Administration Tool to be included in LocalSettings.php";
			echo "<br>please add: <pre>require_once('deployment/Deployment.php');</pre>";
			echo "<br>Another possible cause is that \$scriptPath is not correctly set in deployment/settings.php. It must be exactly the same as \$wgScriptPath in LocalSettings.php.";
			exit;
		} else if ($isAuthorized === 404) {
			$proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' ? "https" : "http";
			$hostname = $_SERVER['HTTP_HOST'];
			echo '<b>$scriptPath</b> is probably not correctly configured. Please check deployment/settings.php. <br>The current wiki URL is: <b>'.$proto."://".$hostname.$wgScriptPath."</b>";
			exit;
		}
	} else{
		$isAuthorized = $username == DF_Config::$df_webadmin_user
		&& $passwort == DF_Config::$df_webadmin_pass;
	}

	if ($isAuthorized == true) {
		$_SESSION['angemeldet'] = true;
		touch("$currentDir/sessiondata/userloggedin");

		if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
			if (php_sapi_name() == 'cgi') {
				header('Status: 303 See Other');
			}
			else {
				header('HTTP/1.1 303 See Other');
			}
		}
        $proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' ? "https" : "http";
		header('Location: '.$proto.'://'.$hostname.($path == '/' ? '' : $path).'/index.php');
		exit;
	} else {
		// login failed
		global $dfgLang;
		$loginHint = "<div id=\"df_login_failed\">".$dfgLang->getLanguageString('df_webadmin_login_failed')."</div>";
	}

}

/**
 * Checks if the given user is granted to use DF webadmin.
 *
 * @param string $username
 * @param string $password
 * @param string $acceptMIME
 *
 * @return boolean
 */
function authenticateUser($username, $password, $acceptMIME=NULL) {
	$res = "";
	$header = "";
	$payload="";
	global $wgScriptPath;
	$proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' ? "https" : "http";
	$hostname = $_SERVER['HTTP_HOST'];

	// Create a curl handle to a non-existing location
	$ch = curl_init("$proto://$hostname$wgScriptPath/index.php?action=ajax&rs=dff_authUser&rsargs[]=".urlencode($username)."&rsargs[]=".urlencode($password));
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
	$httpHeader = array (
        "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
        "Expect: "
        );
        if (!is_null($acceptMIME)) $httpHeader[] = "Accept: $acceptMIME";
        curl_setopt($ch,CURLOPT_HTTPHEADER, $httpHeader);

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        	curl_setopt($ch,CURLOPT_USERPWD,trim($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']));
        }

        if ($proto == "https") {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // don't verify ssl
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Execute
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $res = curl_exec($ch);
         
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status != 200) {
        	return $status;
        }
         
        $bodyBegin = strpos($res, "\r\n\r\n");
        list($header, $res) = $bodyBegin !== false ? array(substr($res, 0, $bodyBegin), substr($res, $bodyBegin+4)) : array($res, "");
        $res = trim($res);
        return (strpos($res, "wikiadmintool_authorized") !== false);
}




/**
 * Checks if webadmin tool has appropriate rights to work correctly.
 *
 * @return mixed True if so, otherwise a string containing the problems.
 */
function dffCheckEnvironment() {
	global $mwrootDir;
	require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Tools.php');
	$result = "";

	// check if LocalSettings can be written.
	@$res = fopen("$mwrootDir/LocalSettings.php", "a");
	if ($res === false) {
		$result .= "<li>Could not open LocalSettings.php for writing. Make sure it is writeable for the webserver. On Linux you use the chmod/chown commands for this purpose.</li>";
	} else {
		fclose($res);
	}

	// check if the sessiondata folder can be written
	@$res = fopen("$mwrootDir/deployment/tools/webadmin/sessiondata/test_file_for_webadmin", "a");
	if ($res === false) {
		$result .= "<li>Could not write into the 'deployment/tools/webadmin/sessiondata' subfolder. Make sure it is writeable for the webserver. On Linux you use the chmod/chown commands for this purpose.</li>";
	} else {
		fclose($res);
	}

	// check if extensions folder is writeable
	@touch("$mwrootDir/extensions/test_file_for_webadmin");
	if (!file_exists("$mwrootDir/extensions/test_file_for_webadmin")) {
		$result .= "<li>Could not write into the 'extensions' subfolder</li>";
	} else {
		unlink("$mwrootDir/extensions/test_file_for_webadmin");
	}
	checkWritePriviledgesOnExtensions("$mwrootDir/extensions", $result);


	// check if external processes can be run
	$phpExe = 'php';
	if (array_key_exists('df_php_executable', DF_Config::$settings) && !empty(DF_Config::$settings['df_php_executable'])) {
		$phpExe = DF_Config::$settings['df_php_executable'];
	}
	@exec("\"$phpExe\" --version", $out, $ret);
	if ($ret != 0 || stripos($out[0], "PHP") === false) {
		$result .= "<li>Could not run external processes: <pre>".implode("\n",$out)."</pre>";
		$result .= "<br>Check the setting 'df_php_executable' in deployment/settings.php. It must point to the PHP cli-executable, e.g. /usr/bin/php. You can use the command 'whereis php' to find out the location.";
		$autodetect = Tools::whereis("php");
		$result .= "<br>Try to auto-detect location of PHP: ".($autodetect == '' ? "failed" : $autodetect)."</li>";
	} else if ($ret == 0 && preg_match("/5\\.\\d+\\.\\d+/", $out[0]) === 0) {
		$result .= "<li>Wrong PHP version: ".$out[0]." (PHP 5.x.x required, except 5.3.1)</li>";
	}

	// check for PHP5 if 'df_php_executable' is not explicitly set.
	if (!array_key_exists('df_php_executable', DF_Config::$settings) || DF_Config::$settings['df_php_executable'] == 'php') {
		@exec("php5 --version", $out, $ret);
		if ($ret == 0) {
			$result .= "<li>PHP5 executable is available. Please configure in deployment/settings.php to make sure PHP5 is used: <code>'df_php_executable' => 'php5'</code></li>";
		}
	}

	// check if temp folder can be written
	$tempFolder = Tools::getTempDir();
	@touch("$tempFolder/test_file_for_webadmin");
	if (!file_exists("$tempFolder/test_file_for_webadmin")) {
		$result .= "<li>Could not write into the temp folder at $tempFolder.<br>Check if the env-variable \$TMPDIR or \$TEMP is set. If not, make sure that at least /tmp or c:\\temp is writebale.</li>";
	} else {
		unlink("$tempFolder/test_file_for_webadmin");
	}

	// check if deployment/config/repositories is writeable
	$repositoryFileWritable = is_writable("$mwrootDir/deployment/config/repositories");
	if ($repositoryFileWritable === false) {
		$result .= "<li>Could not open deployment/config/repositories for writing. Make sure it is writeable for the webserver. On Linux you use the chmod/chown commands for this purpose.</li>";
	}

	// check homedir/tempdir
	require_once($mwrootDir.'/deployment/io/DF_Log.php');
	require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Rollback.php');
	try {
		Logger::getInstance();
		Rollback::getInstance($mwrootDir);

	} catch(DF_SettingError $e) {
		$msg = $e->getMsg();
		$result .= "<li>$msg<br>Check the setting df_homedir in deployment/settings.php. It must point to a directory which is writeable by the webserver. On Linux you use the chmod/chown commands for this purpose.</li>";
	}

	// check for curl (needed for wiki auth)
	if (isset(DF_Config::$df_authorizeByWiki) && DF_Config::$df_authorizeByWiki == true) {
		if (!extension_loaded("curl")) {
			$result .= "<li>Could not find 'curl'-PHP extension. Install it or deactivate authentication by wiki. To do this set in deployment/settings.php: (\$df_authorizeByWiki=false;)";
			$result .= "<br>In this case the WAT credentials are defined in DF_Config::\$df_webadmin_user and DF_Config::\$df_webadmin_pass</li>";
		}
	}

	// check HTTP download methods (some webhosters provide crappy PHP installations which lacks socket functions)
	// if sockets are not available, at least curl must be there. Otherwise it won't work.

	if (!array_key_exists('df_http_impl', DF_Config::$settings)) {
		if (!function_exists("socket_create") && !extension_loaded('curl')) {
			$result .= "<li>Could neither find socket functions nor 'php_curl' module. At least one is required to run this tool.</li>";
			$result .= "<br>You can activate modules in php.ini file. Look for 'extension=php_curl.dll/so' and remove the semi-colon in front of it.</li>";
		}
	}

	return empty($result) ? true : "<ul style=\"list-style-position:inside\">$result</ul>";
}

/**
 * Checks if the extensions in the extension folder are writeable. The used heuristic
 * is to check if the deploy descriptors (if any) are writable.
 *
 * @param string $ext_dir Extensions folder
 * @param & $text (out) Messages
 *
 * @return boolean True if everything is writable, false otherwise.
 */
function checkWritePriviledgesOnExtensions($ext_dir, & $text) {
	$result = true;
	if (substr($ext_dir,-1)!='/'){
		$ext_dir .= '/';
	}
	$handle = @opendir($ext_dir);
	if (!$handle) {
		return;
	}

	while ($entry = readdir($handle) ){
		if ($entry[0] == '.'){
			continue;
		}

		if (is_dir($ext_dir.$entry)) {
			// check if there is a init$.ext
			if (file_exists($ext_dir.$entry.'/deploy.xml') && !is_writable($ext_dir.$entry.'/deploy.xml')) {
				$text .= "<li>Make ".$ext_dir.$entry." writeable for the webserver.</li>";
				$result = false;

			}
		}

	}
	@closedir($handle);
	return $result;
}

// HTML output

$heading = $dfgLang->getLanguageString('df_webadmin');
$username = $dfgLang->getLanguageString('df_username');
$password = $dfgLang->getLanguageString('df_password');
$login = $dfgLang->getLanguageString('df_login');
if (!isset($isAuthorized)) {
	$isAuthorizedText = "notset";
} else $isAuthorizedText = $isAuthorized ? "true" : "false";

$html = <<<ENDS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<link type="text/css" href="skins/login.css" rel="stylesheet" />
<script type="text/javascript">

            dfIsAuthorized="$isAuthorizedText";
         
</script>
<script type="text/javascript" src="scripts/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="scripts/login.js"></script>
<title>$heading</title>
</head>
<body>
<div id="df_login" style="display:none;">
<h1>$heading</h1>
<form action="login.php" method="post">
<table align="center">
<tr>
<td>$username:</td>
<td><input type="text" name="username" /></td>
</tr>
<tr>
<td>$password:</td>
<td><input type="password" name="passwort" /></td>
</tr>
</table>
<input type="submit" value="$login" id="df_login_button"/>
</form>
</div>
</body>
</html>
ENDS
;

echo $html . $loginHint;
