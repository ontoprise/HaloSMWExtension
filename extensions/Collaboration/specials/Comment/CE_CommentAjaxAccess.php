<?php
/*
 * Created on 11.11.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/Collaboration/includes/CE_Initialize.php" );
EOT;
        exit( 1 );
}

global $wgAjaxExportList;

$wgAjaxExportList[] = 'cef_comment_createNewPage';


class CECommentAjax {
	function _construct() {
		#do nothing special here
	}
}

#AJAX functions

/**
 * @param wikurl etc...
 * @return xml
 * 
 */
function cef_comment_createNewPage( $wikiurl, $wikiPath, $pageName, $pageContent, 
	$userName, $userPassword, $domain ) {

	
	$pageName = CECommentUtils::unescape( $pageName );
	$pageContent = CECommentUtils::unescape( $pageContent );	
		
	if ( !$wikiurl || $wikiurl == '' )
		// no wikipath given -> must be local!
		return CEComment::createLocalComment($pageName, $pageContent);
	else
		//remote!
		if (!$wikiPath || $wikiPath = '' )
			return '<xml>wikiurl entered but no wikipath!</xml>';
		else {
			// create and check(?) credentials
			
			return CEComment::createRemoteComment( $wikiurl, $wikiPath, $pageName, $pageContent, $userCredentials);
		}

	return CECommentUtils::createXMLResponse('sth went wrong here','0');
}

/**
 * 
 * @param $wikiurl
 * @param $wikiPath
 * @param $pagename
 * @param $revision
 * @param $section
 * @return unknown_type
 */
function cef_comment_getPageContent( $wikiurl, $wikiPath, $pagename, $revision, $section ) {
	
	if ( !$wikiurl || $wikiurl == '' )
		// no wikipath given -> must be local!
		return CEComment::createLocalComment($pagexml);
	else
		if (!$wikiPath || $wikiPath = '' )
			return 'wikiurl entered but no wikipath!';
	
	return "xml";
}
