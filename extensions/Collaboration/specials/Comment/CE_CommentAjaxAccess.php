<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
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
$wgAjaxExportList[] = 'cef_comment_fullDeleteComments';


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
function cef_comment_createNewPage( $pageName, $pageContent ) {

	$pageName = CECommentUtils::unescape( $pageName );
	$pageContent = CECommentUtils::unescape( $pageContent );	
	return CEComment::createComment( $pageName, $pageContent );
}

function cef_comment_editPage( $pageName, $pageContent) {

	$pageName = CECommentUtils::unescape( $pageName );
	$pageContent = CECommentUtils::unescape( $pageContent );	
	return CEComment::createComment( $pageName, $pageContent, true );
}


function cef_comment_deleteComment( $pageName ) {
	wfProfileIn( __METHOD__ . ' [Collaboration]' );
	global $wgUser;
	$pageName = CECommentUtils::unescape( $pageName );
	$result = wfMsg( 'ce_nothing_deleted' );
	$success = true;
	if ( $pageName != null ) {
		try {
			$title = Title::newFromText( $pageName );
			if ( $title->getNamespace() != CE_COMMENT_NS ) {
				$title = Title::makeTitle( CE_COMMENT_NS, $title );
			}
			$article = new Article( $title );
			$articleContent = $article->getContent();
			$date = new Datetime( null, new DateTimeZone( 'UTC' ) );
			$articleContent = preg_replace( '/\|CommentContent.*}}/',
				'|CommentContent=' . $wgUser->getName() . ' ' .
				wfMsg( 'ce_comment_has_deleted' ) . ' ' .
				$date->format( 'r' ) . '|CommentWasDeleted=true|}}',
				$articleContent
			);
			$article->doEdit( $articleContent, wfMsg( 'ce_comment_delete_reason' ) );
			CEComment::updateRelatedArticle( $articleContent );
			$result = wfMsg( 'ce_comment_deletion_successful' );
			wfProfileOut( __METHOD__ . ' [Collaboration]' );
			return CECommentUtils::createXMLResponse( $result, '0', $pageName );
		} catch( Exception $e ) {
			$result .= wfMsg( 'ce_comment_deletion_error' );
			$success = false;
			wfProfileOut( __METHOD__ . ' [Collaboration]' );
			return CECommentUtils::createXMLResponse( $result, '1', $pageName );
		}
	}

	wfProfileOut( __METHOD__ . ' [Collaboration]' );
	return CECommentUtils::createXMLResponse( 'sth went wrong here', '1', $pageName );
}

function cef_comment_fullDeleteComments( $pageNames ) {
	wfProfileIn( __METHOD__ . ' [Collaboration]' );
	global $wgUser;
	$pageNames = CECommentUtils::unescape( $pageNames );
	$pageNames = explode( ',', $pageNames );
	$result = wfMsg( 'ce_nothing_deleted' );
	$success = false;
	foreach ( $pageNames as $pageName) {
		try {
			$title = Title::newFromText( $pageName );
			if ( $title->getNamespace() != CE_COMMENT_NS ) {
				$title = Title::makeTitle( CE_COMMENT_NS, $title );
			}
			$article = new Article( $title );
			$articleContent = $article->getContent();
			$articleDel = $article->doDelete( wfMsg( 'ce_comment_delete_reason' ) );
			$success = true;
		} catch( Exception $e ) {
			$result .= wfMsg( 'ce_comment_deletion_error' );
			$success = false;
			wfProfileOut( __METHOD__ . ' [Collaboration]' );
			return CECommentUtils::createXMLResponse( $result, '1', $pageName);
		}
	}
	if( $success ) {
		$result = wfMsg( 'ce_comment_massdeletion_successful' );
		wfProfileOut( __METHOD__ . ' [Collaboration]' );
		return CECommentUtils::createXMLResponse( $result, '0', $pageNames[0] );
	} else {
		$pageNames = json_encode($pageNames);
		wfProfileOut( __METHOD__ . ' [Collaboration]' );
		return CECommentUtils::createXMLResponse( 'sth went wrong here', '1', $pageNames );
	}
}
