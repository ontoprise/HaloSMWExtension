<?php

        $res = "";
        $header = "";

        // Create a curl handle to a non-existing location
        $ch = curl_init("http://localhost/mediawiki/index.php?action=ajax&rs=dff_authUser&rsargs[]=WikiSysop&rsargs[]=root");
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
        $httpHeader = array (
        "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
        "Expect: "
        );
        if (!is_null($acceptMIME)) $httpHeader[] = "Accept: $acceptMIME";
        curl_setopt($ch,CURLOPT_HTTPHEADER, $httpHeader);
      
        // Execute
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $res = curl_exec($ch);
       
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
       
        $bodyBegin = strpos($res, "\r\n\r\n");
        list($header, $res) = $bodyBegin !== false ? array(substr($res, 0, $bodyBegin), substr($res, $bodyBegin+4)) : array($res, "");

print $header;print "\n";print $res;