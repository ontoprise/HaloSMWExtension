<?php
/*  Copyright 2011, ontoprise GmbH
*  This file is part of the Faceted Search Module of the Enhanced Retrieval Extension.
*
*   The Enhanced Retrieval Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Enhanced Retrieval Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the class FSIncrementalUpdater.
 * 
 * @author Thomas Schweitzer
 * Date: 23.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}


/**
 * Listens to changes, deletes and moves of articles in MediaWiki and updates 
 * the index accordingly.
 * 
 * @author Thomas Schweitzer
 * 
 */
class FSIncrementalUpdater  {
	
	//--- Constants ---
		
	//--- Private fields ---
	
	/**
	 * Constructor for  FSIncrementalUpdater
	 */		
	private function __construct() {
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---

	/**
	 * This function is called after saving an article is completed.
	 * It starts an update of the index for the given article.
	 * 
	 * @param Article $article
	 * 		The saved article.
	 */
	public static function onArticleSaveComplete(Article &$article, $user, $text) {
		$indexer = FSIndexerFactory::create();
		$indexer->updateIndexForArticle($article, $user, $text);
		return true;
	}
	
	/**
	 * This function is called after an article was moved.
	 * It starts an update of the index for the given article.
	 * 
	 * @param Title $title
	 * @param Title $newTitle
	 * @param unknown_type $user
	 * @param unknown_type $oldid
	 * @param unknown_type $newid
	 * @return bool
	 * 		As a hook function it always returns <true>
	 */
	public static function onTitleMoveComplete(Title &$title, Title &$newTitle, $user, $oldid, $newid) {
		$indexer = FSIndexerFactory::create();
		$indexer->updateIndexForMovedArticle($oldid, $newid);
		return true;
	}
	
	/**
	 * This method is called, when an article is deleted. It is removed from
	 * the index.
	 *
	 * @param unknown_type $article
	 * @param unknown_type $user
	 * @param unknown_type $reason
	 * 
	 * @return bool
	 * 		As a hook function it always returns <true>
	 */
	public static function onArticleDelete(&$article, &$user, &$reason) {
		$indexer = FSIndexerFactory::create();
		$indexer->deleteDocument($article->getID());
		return true;
	}	

	//--- Private methods ---
}