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
 * @ingroup DIWebServices
  * Pages in the namespace WebService are enhanced with additional information
 *  - A link to the GUI for editing a WS
 *  - An alphabetical list of all articles that use the web service.
 *
 * Some code based is on CategoryPage.php and SMW_PropertyPage.php
 *
 * @author: Thomas Schweitzer
 */

if( !defined( 'MEDIAWIKI' ) )   die( 1 );

/**
 * Implementation of MediaWiki's Article that shows additional information on
 * WebService pages.
 */
class DIWebServicePage extends SMWOrderedListPage {

	private $mArticles = array();
	
	private $mFromArticle;
	private $mUntilArticle;

	/**
	 * Initialize the limits
	 */
	protected function initParameters() {
		global $wgRequest, $diwsgArticleLimit;
		$this->limit = $diwsgArticleLimit;

		$this->mFromArticle = $wgRequest->getVal( 'fromarticle', 0);
		$this->mUntilArticle = $wgRequest->getVal( 'untilarticle' );
		return true;
	}

	/**
	 * Fill the internal arrays with the set of articles to be displayed (possibly plus one additional
	 * article that indicates further results).
	 */
	protected function getHTML() {
		global $wgContLang;

		// ask for the list of articles that use the web service
		$options = new SMWRequestOptions();
		$options->limit = $this->limit + 1;
		$options->sort = true;

		$reverse = false;
		if ($this->mFromArticle != '') {
			$options->boundary = $this->mFromArticle;
			$options->ascending = true;
			$options->include_boundary = true;
		} elseif ($this->mUntilArticle != '') {
			$options->boundary = $this->mUntilArticle;
			$options->ascending = false;
			$options->include_boundary = false;
			$reverse = true;
		}
		$articleIDs = difGetWSStore()
			->getWSArticles($this->getTitle()->getArticleID(), $options);

		foreach($articleIDs as $articleId){
			$this->mArticles[] = SMWDIWikiPage::newFromTitle(Title::newFromID($articleId));
		}

		if ($reverse) {
			$this->mArticles = array_reverse($this->mArticles);
		}

		$html = '';

		global $wgArticlePath;
		$ws = DIWebService::newFromID($this->getTitle()->getArticleID());
		if($ws != null){
			if(strpos($wgArticlePath, "?") > 0){
				$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL()."&wwsdId=".$this->getTitle()->getArticleID();
			} else {
				$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL()."?wwsdId=".$this->getTitle()->getArticleID();
			}
			$html .= '<a href="'.$url.'">'.wfMsg('smw_wws_edit_in_gui').'</a><br/><br/><br/>';
		}
		
		$html .= wfMsg('smw_wws_articlecount', min($this->limit, count($this->mArticles)));
		
		if(count($this->mArticles) > 0){
			if (min($this->limit, count($this->mArticles)) < 6 ) {
				$html .= SMWPageLister::getShortList($this->mFromArticle, min($this->limit, count($this->mArticles)), $this->mArticles, null);
			} else {
				$html .= SMWPageLister::getColumnList($this->mFromArticle, min($this->limit, count($this->mArticles)), $this->mArticles, null);
			}
			
			if($this->mFromArticle != 0 || count($this->mArticles) > $this->limit){
				$pageLister = new SMWPageLister( $this->mArticles, null, $this->limit, $this->mFromArticle, $this->mUntilArticle );
				$navigation = $pageLister->getNavigationLinks($this->mTitle);
				$html .= $navigation;
			}
		}
		
		$html .= '<br/>';
		
		return $html;
	}
}
