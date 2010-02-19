<?php

global $wgUseAjax, $wgAjaxExportList;
// register Ajax functions (these are below in this file)
$wgUseAjax = true;
$wgAjaxExportList[] = 'wfUprForwardApiCall';

function wfUprForwardApiCall(){
    $params = func_get_args();
    if (count($params) != 2) return;
    if (!preg_match('/^https?:\/\//i', $params[0])) return;
    $host= $params[0];
    $data=str_replace('&amp;', '&', $params[1]);
    if (function_exists('curl_init'))
         return wfUprSendApiCallViaCurl($host, $data);
    return wfUprSendApiCallViaFsock($host, $data); 
}

function wfUprSendApiCallViaFsock($server, $data) {
    // remove the "http://" protocol from host name
    $host = substr($server, strpos($server, ':') + 3);
    // split server and path at the first / after the "http://"
    $p = strpos($host, '/');
    $path = substr($host, $p);
    $host = substr($host, 0, $p);
    // if the server has a port, a : is in the string
    $p = strpos($host, ':');
    if ( $p !== false) {
        $port = substr($host, $p);
        $host = substr($host, 0, $p);
    }
    // standard http(s) ports
    else if (strtolower(substr($server, 0, 5)) == 'https')
        $port = 443;
    else
        $port = 80;
    // open socket now
    $fp = fsockopen($host, $port, $errno, $errstr);
    if (!$fp) return;
    // formulate a POST request and send it to the server
    $com = "POST $path HTTP/1.1\r\n".
       "Accept: */*\r\n".
       "Content-Type: application/x-www-form-urlencoded\r\n".
       "Content-Length: ".strlen($data)."\r\n".
       "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n".
       "Host: $host:$port\r\n".
       "\r\n".
       "$data\r\n";
    fputs($fp, $com);
    $cont = '';
    while (!feof($fp)) {
        $cont .= fgets($fp, 4096);
    }
    fclose($fp);
    $httpHeaders= explode("\r\n", substr($cont, 0, strpos($cont, "\r\n\r\n")));
    list($protocol, $httpErr, $message) = explode(' ', $httpHeaders[0]);
    $cont = substr($cont, strpos($cont, "\r\n\r\n") );
    if ($httpErr == '200') return $cont;
    return '';
}

function wfUprSendApiCallViaCurl($host, $data) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $host);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 120); // 2 min wait for connect
    curl_setopt($c, CURLOPT_TIMEOUT, 300); // 5 min wait for response
    if (isset($_SERVER['HTTP_USER_AGENT']))
        curl_setopt($c, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    $res = curl_exec($c);
    $httpErr = curl_getinfo($c, CURLINFO_HTTP_CODE);
    $curlErr = curl_errno($c);
    curl_close($c);
    /* uncomment the following lines for debug */
    /*
    $fp = fopen(dirname(__FILE__).'/debug.log', "a+");
    if ($fp) {
        fputs($fp, "Host: $host\n");
        fputs($fp, "new request: http result $httpErr, curl errno $curlErr\n");
        fputs($fp, print_r($data."\n", true));
        fputs($fp, print_r($res."\n", true));
        fclose($fp);
    }
    */
    if ($curlErr != 0 && $httpErr != 200) $res='';
    return $res;
}
?>