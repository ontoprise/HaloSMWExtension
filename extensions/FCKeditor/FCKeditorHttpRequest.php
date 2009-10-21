<?php
/* 
 * Two functions for doing a http request. This is needed for the mediawiki
 * plugin to load the Query Interface and the WebService Popup.
 */


/**
 * Do http request depending if curl is active or not
 *
 * @param string server i.e. www.domain.com
 * @param string file	i.e. /path/to/script.cgi or /some/file.html
 * @param string params i.e. param1=val1&param2=val2
 * @return array(int, string) with httpCode, page
 */
 function fckHttpRequest($server, $file, $params) {
    if (function_exists('curl_init')) {
	return fckHttpRequestWithCurl($server, $file, $params);
    }
    else {
        if (strtolower(substr($server, 0, 5)) == "https")
            return array(-1, "Error: for HTTPS connections please activate the Curl module in your PHP configuration");
        else
            return fckHttpRequestWithoutCurl($server, $file, $params);
    }
 }

/**
 * If no curl is available, the page must retrieved manually
 *
 * @param string server i.e. www.domain.com
 * @param string file	i.e. /path/to/script.cgi or /some/file.html
 * @param string params i.e. param1=val1&param2=val2
 * @return array(int, string) with httpCode, page
 */
function fckHttpRequestWithoutCurl($server, $file, $params = "") {
    if ($file{0} != "/") $file = "/".$file;
    $server = preg_replace('/^http(s)?:\/\//i', '', $server);
    $cont = "";
    $ip = gethostbyname($server);
    $fp = fsockopen($ip, $_SERVER['SERVER_PORT']);
    if (!$fp) return array(-1, false);
    $com = "GET $file".((strlen($params) > 0) ? '?'.$params : '')." HTTP/1.1\r\nAccept: */*\r\n".
           "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n".
           "Host: $server:$port\r\n".
           "Connection: Keep-Alive\r\n";
    if (isset($_SERVER['AUTH_TYPE']) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
        $com .= "Authorization: Basic ".base64_encode($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW'])."\r\n";
    $com .= "\r\n";
    fputs($fp, $com);
    while (!feof($fp))
        $cont .= fread($fp, 1024);
    fclose($fp);
    $httpHeaders= explode("\r\n", substr($cont, 0, strpos($cont, "\r\n\r\n")));
    list($protocol, $httpErr, $message) = explode(' ', $httpHeaders[0]);
    $offset = 8;
    $cont = substr($cont, strpos($cont, "\r\n\r\n") + $offset );
    return array($httpErr, $cont);
}

/**
 * retrieve a web page via curl
 *
 * @param string server i.e. http://www.domain.com (incl protocol prefix)
 * @param string file	i.e. /path/to/script.cgi or /some/file.html
 * @param string params i.e. param1=val1&param2=val2
 * @return array(int, string) with httpCode, page
 */
function fckHttpRequestWithCurl($server, $file, $params = "") {
    if ($file{0} != "/") $file = "/".$file;
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $server.$file.((strlen($params) > 0) ? '?'.$params : ''));
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    // needs authentication?
    if (isset($_SERVER['AUTH_TYPE']) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        curl_setopt($c, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    }
    // user agent (important i.e. for Popup in FCK Editor)
    if (isset($_SERVER['HTTP_USER_AGENT']))
        curl_setopt($c, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

    $page = curl_exec($c);
    $httpErr = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);
    return array($httpErr, $page);
}
?>