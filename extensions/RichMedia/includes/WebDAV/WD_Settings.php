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
$wgWebDAVNamespaceBlackList["IAI"] = false;


//Define the mapping between the template parameters
//used by the RichMedia extension and the WebDAV properties
///extracted from files. The WebDAV extension will use the 
//template call below for each uploaded file. Leave the mapping
//empty if you don't want to use the RichMedia template.
//Every WebDAV parameter (embedded in two "#" symbols
//will be replaced by the WebDAV extension with a certain 
//value. The WebDAV extension knows the following
//attributes: ##filename## todo:add the others
global $wgWebDAVRichMediaTemplateMapping;
$wgWebDAVRichMediaTemplateMapping = "{{RMMedia
|Filename = ##filename##
|RelatedArticles = ##relatedArticles##
}}

[[Category:Media]]"; 









