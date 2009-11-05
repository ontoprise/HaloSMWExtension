<?php

//use this flag to configure wheter to display talk namespaces
global $wgWebDAVDisplayTalkNamespaces;
$wgWebDAVDisplayTalkNamespaces = false;

//namespaces in the blacklist below will not be displayed
//in the WebDAV directory
global $wgWebDAVNamespaceBlackList;
$wgWebDAVNamespaceBlackList = array();
$wgWebDAVNamespaceBlackList["Concept"] = false;
$wgWebDAVNamespaceBlackList["E-mail"] = false;
$wgWebDAVNamespaceBlackList["Help"] = false;
$wgWebDAVNamespaceBlackList["Media"] = false;
$wgWebDAVNamespaceBlackList["MediaWiki"] = false;
$wgWebDAVNamespaceBlackList["Project"] = false;
$wgWebDAVNamespaceBlackList["Special"] = false;
$wgWebDAVNamespaceBlackList["Talk"] = false;
$wgWebDAVNamespaceBlackList["Type"] = false;