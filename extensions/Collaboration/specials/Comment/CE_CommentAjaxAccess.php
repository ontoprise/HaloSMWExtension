<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Collaboration-Extension.
 *
 *   The Collaboration-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Collaboration-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup CEComment
 * 
 * This file contains the ajax functions of comment component for Collaboration extension.
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
