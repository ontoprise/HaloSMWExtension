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


// Define the mapping between the template used by the 
// RichMedia extension and the WebDAV properties extracted  
// from files and other contexts.
global $wgWebDAVRichMediaMapping;
$wgWebDAVRichMediaMapping = array();
$wgWebDAVRichMediaMappingParameters = array(); 
$wgWebDAVRichMediaMappingParameters["Filename"] = "Filename";
$wgWebDAVRichMediaMappingParameters["RelatedArticles"] = "RelatedArticles";
$wgWebDAVRichMediaMappingParameters["UploadDate"] = "UploadDate";
$wgWebDAVRichMediaMappingParameters["Media subcategory"] = "Media subcategory";
$wgWebDAVRichMediaMappingParameters["Uploader"] = "Uploader";
$wgWebDAVRichMediaMappingParameters["Delimiter"] = ",";

$wgWebDAVRichMediaMappingParameters["TemplateName"] = "RMAudio";
$wgWebDAVRichMediaMapping[NS_AUDIO] = $wgWebDAVRichMediaMappingParameters;

$wgWebDAVRichMediaMappingParameters["TemplateName"] = "RMDocument";
$wgWebDAVRichMediaMapping[NS_DOCUMENT] = $wgWebDAVRichMediaMappingParameters;

$wgWebDAVRichMediaMappingParameters["TemplateName"] = "RMICalendar";
$wgWebDAVRichMediaMapping[NS_ICAL] = $wgWebDAVRichMediaMappingParameters;

$wgWebDAVRichMediaMappingParameters["TemplateName"] = "RMImage";
$wgWebDAVRichMediaMapping[NS_IMAGE] = $wgWebDAVRichMediaMappingParameters;

$wgWebDAVRichMediaMappingParameters["TemplateName"] = "RMPdf";
$wgWebDAVRichMediaMapping[NS_PDF] = $wgWebDAVRichMediaMappingParameters;

$wgWebDAVRichMediaMappingParameters["TemplateName"] = "RMVCard";
$wgWebDAVRichMediaMapping[NS_VCARD] = $wgWebDAVRichMediaMappingParameters;

$wgWebDAVRichMediaMappingParameters["TemplateName"] = "RMVideo";
$wgWebDAVRichMediaMapping[NS_VIDEO] = $wgWebDAVRichMediaMappingParameters;








