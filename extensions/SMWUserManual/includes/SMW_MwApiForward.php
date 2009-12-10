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