<?php
$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");
require_once($mwrootDir.'/deployment/settings.php');
$wgScriptPath=isset(DF_Config::$scriptPath) ? DF_Config::$scriptPath : "/mediawiki";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	session_start();

	$username = $_POST['username'];
	$passwort = $_POST['passwort'];

	$hostname = $_SERVER['HTTP_HOST'];
	$path = dirname($_SERVER['PHP_SELF']);
	$currentDir = dirname(__FILE__);

	if (file_exists("$currentDir/userloggedin")) {
		$lastMod = filemtime("$currentDir/userloggedin");
		$currenttime = time();

		if ($currenttime - $lastMod < 3600) {
			print "User already logged in. Try again later.";
			exit();
		}
	}

	// user name and password is checked
	if (DF_Config::$df_authorizeByWiki) {
		$result = authenticateUser($username, $passwort);
	} else{
		$result = $username == DF_Config::$df_webadmin_user
		&& $passwort == DF_Config::$df_webadmin_pass;
	}

	if ($result == true) {
		$_SESSION['angemeldet'] = true;
		touch("$currentDir/userloggedin");
		// Weiterleitung zur geschützten Startseite
		if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
			if (php_sapi_name() == 'cgi') {
				header('Status: 303 See Other');
			}
			else {
				header('HTTP/1.1 303 See Other');
			}
		}

		header('Location: http://'.$hostname.($path == '/' ? '' : $path).'/index.php');
		exit;
	}
}

function authenticateUser($username, $password) {
	$res = "";
	$header = "";
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

        // Execute
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $res = curl_exec($ch);
         
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
         
        $bodyBegin = strpos($res, "\r\n\r\n");
        list($header, $res) = $bodyBegin !== false ? array(substr($res, 0, $bodyBegin), substr($res, $bodyBegin+4)) : array($res, "");
        $res = trim($res);
        return $res == "true";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<title>Geschützter Bereich</title>
</head>
<body>
<form action="login.php" method="post">
Username:
<input type="text" name="username" />
<br />
Passwort:
<input type="password" name="passwort" />
<br />
<input type="submit" value="Anmelden" />
</form>
</body>
</html>
