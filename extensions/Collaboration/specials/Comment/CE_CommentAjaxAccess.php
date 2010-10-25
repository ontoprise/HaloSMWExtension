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
$wgAjaxExportList[] = 'cef_comment_editPage';
$wgAjaxExportList[] = 'cef_comment_deleteComment';


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
function cef_comment_createNewPage( $pageName, $pageContent) {

	$pageName = CECommentUtils::unescape( $pageName );
	$pageContent = CECommentUtils::unescape( $pageContent );	
	return CEComment::createComment($pageName, $pageContent);
}

function cef_comment_editPage( $pageName, $pageContent) {

	$pageName = CECommentUtils::unescape( $pageName );
	$pageContent = CECommentUtils::unescape( $pageContent );	
	return CEComment::createComment($pageName, $pageContent, true);
}


function cef_comment_deleteComment($pageName) {
	$pageName = CECommentUtils::unescape($pageName);
	$result = wfMsg("ce_nothing_deleted");
	$success = true;
	if ($pageName != null) {
		try {
			$title = Title::newFromText($pageName);
			if($title->getNamespace() != CE_COMMENT_NS) {
				$title = Title::makeTitle(CE_COMMENT_NS, $title);
			}
			$article = new Article($title);
			$article->doDelete(wfMsg('ce_comment_delete_reason'));
			$result = wfMsg('ce_comment_deletion_successful');
			return CECommentUtils::createXMLResponse($result, '0', $pageName);
		} catch(Exception $e ) {
			$result .= wfMsg('ce_comment_deletion_error');
			$success = false;
			return CECommentUtils::createXMLResponse($result, '1', $pageName);
		}
	}
	return CECommentUtils::createXMLResponse('sth went wrong here', '1', $pageName);
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
function cef_comment_getPageContent($pagename, $revision, $section ) {
//	return CEComment::createComment($pagexml);
	return "xml";
}
