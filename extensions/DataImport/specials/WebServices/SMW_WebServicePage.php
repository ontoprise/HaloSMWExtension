<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DIWebServices
  * Pages in the namespace WebService are enhanced with additional lists:
 *  - An alphabetical list of all articles that use the web service.
 *  - An alphabetical list of all semantic properties that get their value from
 *    the web service.
 *  - Services that have the update policy once show a Refresh button that
 *    starts an update bot when the user clicks it. The time of the last update
 *    is shown as well.
 *
 * Some code based is on CategoryPage.php and SMW_PropertyPage.php
 *
 * @author: Thomas Schweitzer
 */

if( !defined( 'MEDIAWIKI' ) )   die( 1 );

global $smwgIP, $smwgDIIP;
require_once("$smwgIP/includes/articlepages/SMW_OrderedListPage.php");
require_once("$smwgIP/includes/storage/SMW_Store.php");
require_once("$smwgDIIP/specials/WebServices/SMW_WSStorage.php");

/**
 * Implementation of MediaWiki's Article that shows additional information on
 * WebService pages.
 */
class SMWWebServicePage extends SMWOrderedListPage {

	private $mArticles = array();
	private $mProperties = array();
	private $mFromArticle;
	private $mUntilArticle;

	/**
	 * Initialize the limits
	 */
	protected function initParameters() {
		global $smwgWWSArticleLimit, $wgRequest;
		$this->limit = $smwgWWSArticleLimit;

		$this->mFromArticle = $wgRequest->getVal( 'fromarticle' );
		$this->mUntilArticle = $wgRequest->getVal( 'untilarticle' );
		return true;
	}

	/**
	 * Fill the internal arrays with the set of articles to be displayed (possibly plus one additional
	 * article that indicates further results).
	 */
	protected function doQuery() {
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
		$articleIDs = WSStorage::getDatabase()
		->getWSArticles($this->getTitle()->getArticleID(), $options);

		foreach($articleIDs as $articleId){
			$this->mArticles[] = Title::newFromID($articleId);
		}

		if ($reverse) {
			$this->mArticles = array_reverse($this->mArticles);
		}

		foreach ($this->mArticles as $title) {
			$this->articles_start_char[] = $wgContLang->convert( $wgContLang->firstChar( $title->getText() ) );
		}

	}

	/**
	 * Generates the headline for the page list and the HTML encoded list of pages which
	 * shall be shown.
	 */
	protected function getPages() {
		wfProfileIn( __METHOD__ . ' (SMW)');
		$r = '';

		global $wgArticlePath;
		$ws = WebService::newFromID($this->getTitle()->getArticleID());
		if($ws != null){
			if(strpos($wgArticlePath, "?") > 0){
				$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL()."&wwsdId=".$this->getTitle()->getArticleID();
			} else {
				$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL()."?wwsdId=".$this->getTitle()->getArticleID();
			}
			$r .= '<h4><span class="mw-headline"><a href="'.$url.'">'.wfMsg('smw_wws_edit_in_gui').'</a></span></h4>';
		}

		$ti = htmlspecialchars( $this->mTitle->getText() );

		// list articles
		$nav = $this->myGetNavigationLinks('WWSArticleResults', $this->mArticles,
		$this->mFromArticle, $this->mUntilArticle,
		                                 'fromarticle', 'untilarticle');
		$r .= '<a name="WWSArticleResults"></a>' . "<div id=\"mw-pages\">\n";
		$r .= '<h4><span class="mw-headline">' . wfMsg('smw_wws_articles_header',$ti) . "</h4>\n";
		$r .= wfMsg('smw_wws_articlecount', min($this->limit, count($this->mArticles))) . "\n";
		$r .= $nav;
		$r .= $this->myShortList( $this->mArticles, $this->articles_start_char, $this->mUntilArticle) . "\n</div>";
		$r .= $nav;


		wfProfileOut( __METHOD__ . ' (SMW)');
		return $r;
	}


	/**
	 * Generates the prev/next link part to the HTML code of the top and bottom section of the page.
	 */
	protected function myGetNavigationLinks($fragment, &$articles, $from, $until,
	$fromLabel, $untilLabel) {
		global $wgUser, $wgLang;
		@ $sk =& $this->getSkin();
		$limitText = $wgLang->formatNum( $this->limit );

		$ac = count($articles);
		if ($until != '') {
			if ($ac > $this->limit) { // (we assume that limit is at least 1)
				$first = $articles[1]->getDBkey();
			} else {
				$first = '';
			}
			$last = $until;
		} elseif ( ($ac > $this->limit) || ($from != '') ) {
			$first = $from;
			if ( $ac > $this->limit) {
				$last = $articles[$ac-1]->getDBkey();
			} else {
				$last = '';
			}
		} else {
			return '';
		}

		$prevLink = htmlspecialchars( wfMsg( 'prevn', $limitText ) );
		$this->mTitle->setFragment("#$fragment"); // make navigation point to the result list
		if( $first != '' ) {
			$prevLink = $sk->makeLinkObj( $this->mTitle, $prevLink,
			wfArrayToCGI( array( $untilLabel => $first ) ) );
		}
		$nextLink = htmlspecialchars( wfMsg( 'nextn', $limitText ) );
		if( $last != '' ) {
			$nextLink = $sk->makeLinkObj( $this->mTitle, $nextLink,
			wfArrayToCGI( array( $fromLabel => $last ) ) );
		}
		return "($prevLink) ($nextLink)";
	}

	/**
	 * Format a list of articles chunked by letter in a table that shows subject articles in
	 * one column and object articles/values in the other one.
	 */
	protected function myShortList(&$articles, &$startChar, $until) {
		global $wgContLang;

		$ac = count($articles);
		if ($ac > $this->limit) {
			if ($until != '') {
				$start = 1;
			} else {
				$start = 0;
				$ac = $ac - 1;
			}
		} else {
			$start = 0;
		}

		$r = '<table style="width: 100%; ">';
		$prevchar = 'None';
		for ($index = $start; $index < $ac; $index++ ) {
			global $smwgIP;
			include_once($smwgIP . '/includes/SMW_Infolink.php');
			// Header for index letters
			if ($startChar[$index] != $prevchar) {
				$r .= '<tr><th class="smwpropname"><h3>' .
				htmlspecialchars( $startChar[$index] ) .
				      "</h3></th><th></th></tr>\n";
				$prevchar = $startChar[$index];
			}

			$searchlink = SMWInfolink::newBrowsingLink('+',$articles[$index]->getPrefixedText());
			$r .= '<tr><td class="smwpropname">' . $this->getSkin()->makeKnownLinkObj( $articles[$index],
			$wgContLang->convert( $articles[$index]->getPrefixedText() ) ) .
			  '&nbsp;' . $searchlink->getHTML($this->getSkin()) .
			  '</td><td class="smwprops">';
			$r .= "</td></tr>\n";
		}
		$r .= '</table>';
		return $r;
	}

}
