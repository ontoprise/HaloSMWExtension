<?php
/*
 * Created on 11.11.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


class CEComment {
	
	/* constants */
	const SUCCESS = 0;
	const COMMENT_ALREADY_EXISTS = 1;
	const PERMISSION_ERROR = 2;
	
	
	public static function createLocalComment($pageName, $pageContent) {
		global $wgUser;
		
		$title = Title::newFromText($pageName);
		$titleNSfixed = Title::makeTitle(CE_COMMENT_NS, $title);
		
		$article = new Article($titleNSfixed);
		
		if ($article->exists())
			return CECommentUtils::createXMLResponse(wfMsg('ce_comment_exists', $pageName), self::COMMENT_ALREADY_EXISTS);
		
		if(!$title->userCan('edit'))
			return CECommentUtils::createXMLResponse(wfMsg('ce_com_cannot_create'), self::PERMISSION_ERROR);
		else {
			$summary = wfMsg('ce_com_edit_sum');
			//$pageContent = str_replace('|}}','|CommentDateTime='+ '' +'}}', $pageContent);
			$article->doEdit($pageContent, $summary );
				
			if($article->exists())
				return CECommentUtils::createXMLResponse(wfMsg('ce_com_created'/*, $pageName*/), self::SUCCESS);
			else
				return CECommentUtils::createXMLResponse(wfMsg('ce_com_cannot_create'), self::PERMISSION_ERROR);
		}
	}
	
	public static function createRemoteComment($wikiurl, $wikiPath, $pagename, $pageContent, $userCredentials) {
		
		//we have to use cURL to get an edit token if wikiuser is given

		return CECommentUtils::createXMLResponse('everything ok', '1');
	}
	

	

	//TODO: think about return types!
	
	/**
	 * Update a list of comment articles
	 * 
	 * @param $oldName
	 * @param $newName
	 * @return boolean
	 * 		false: one or more comment articles could not updated
	 * 		true: everythiny is fine
	 */
	function updateRelatedCommentArticles($relCommentArticleList, $oldName, $newName) {
		
		//get a list with all related comments
		
		//change value of property "is related to"
		
		return true;
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

