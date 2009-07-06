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
 * Pages in the namespace TermImport are enhanced with additional lists:
 *  - A list of all runs of a TermImport
 *
 * Some code based is on CategoryPage.php and SMW_PropertyPage.php
 *
 * @author: Ingo Steinbauer
 */

if( !defined( 'MEDIAWIKI' ) )   die( 1 );

global $smwgIP, $smwgDIIP;
require_once("$smwgIP/includes/articlepages/SMW_OrderedListPage.php");
require_once("$smwgIP/includes/storage/SMW_Store.php");

/**
 * Implementation of MediaWiki's Article that shows additional information on
 * WebService pages.
 */
class SMWTermImportPage extends SMWOrderedListPage {

	private $mArticles = array();
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
	}

	/**
	 * Generates the headline for the page list and the HTML encoded list of pages which
	 * shall be shown.
	 */
	protected function getPages() {
		return;
	}


	/**
	 * Generates the prev/next link part to the HTML code of the top and bottom section of the page.
	 */
	protected function getNavigationLinks($fragment, &$articles, $from, $until,
			$fromLabel, $untilLabel) {
		return;
	}

}

?>
