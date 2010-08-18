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
 * This file contains the implementation of comment creation for Collaboration.
 *
 * @author Benjamin Langguth
 * Date: 07.12.2009
 */

/**
 * @defgroup CEComment
 * @ingroup Collaboration
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Collaboration extension. It is not a valid entry point.\n" );
}


class CEComment {
	
	/* constants */
	const SUCCESS = 0;
	const COMMENT_ALREADY_EXISTS = 1;
	const PERMISSION_ERROR = 2;
	
	
	public static function createLocalComment($pageName, $pageContent) {
		global $wgUser, $cegEnableComment, $cegEnableCommentFor;
		
		$title = Title::newFromText($pageName);
		$titleNSfixed = Title::makeTitle(CE_COMMENT_NS, $title);
		
		$article = new Article($titleNSfixed);
		
		# check if comments are enabled #
		if ( !isset($cegEnableComment) || !$cegEnableComment )
			return CECommentUtils::createXMLResponse(wfMsg('ce_cf_disabled'), self::PERMISSION_ERROR, $pageName);
		
		# check authorization #
		if ( !isset($cegEnableCommentFor)
			|| ($cegEnableCommentFor == CE_COMMENT_NOBODY )
			|| ( ($cegEnableCommentFor == CE_COMMENT_AUTH_ONLY) && !($wgUser->isAnon()) ) )
		{
			return CECommentUtils::createXMLResponse(wfMsg('ce_cf_disabled'), self::PERMISSION_ERROR, $pageName);
		} else {
			//user is allowed
			if ($article->exists()) {
				return CECommentUtils::createXMLResponse(wfMsg('ce_comment_exists', $pageName), self::COMMENT_ALREADY_EXISTS, $pageName);
			}
			if(!$title->userCan('edit')) {
				return CECommentUtils::createXMLResponse(wfMsg('ce_com_cannot_create'), self::PERMISSION_ERROR, $pageName);
			} else {
				$summary = wfMsg('ce_com_edit_sum');
				$article->doEdit( $pageContent, $summary );

				if($article->exists()) {
					return CECommentUtils::createXMLResponse(wfMsg('ce_com_created'/*, $pageName*/), self::SUCCESS, $pageName);
				} else {
					return CECommentUtils::createXMLResponse(wfMsg('ce_com_cannot_create'), self::PERMISSION_ERROR, $pageName);
				}
			}
		}
	}
	
	public static function createRemoteComment($wikiurl, $wikiPath, $pagename, $pageContent, $userCredentials) {
		
		//we have to use cURL to get an edit token if wikiuser is given

		return CECommentUtils::createXMLResponse('everything ok', '1');
	}

	/**
	 * Delete a list of comment articles
	 * 
	 * @param $relCommentArticleList
	 * @return boolean
	 * 		false: one or more comment articles could not be deleted
	 * 		true: everythiny is fine
	 */
	function deleteRelatedCommentArticles ($relCommentArticleList) {
		
		return true;
	}
}

