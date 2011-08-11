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
 * This file contains the interface for all faceted search indexers.
 * 
 * @author Thomas Schweitzer
 * Date: 22.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}

/**
 * Interface of indexers for faceted search. The indexer indexes the semantic
 * data of the wiki and processes it for faceted search. Queries for facets are
 * answered by the indexer.
 * 
 * @author thsc
 *
 */
interface IFSIndexer {
	
	/**
	 * Pings the server of the indexer and checks if it is responding.
	 * @return bool
	 * 	<true>, if the server is responding
	 * 	<false> otherwise
	 */
	public function ping();
	
	/**
	 * Creates a full index of all available semantic data.
	 * 
	 * @param bool $clean
	 * 		If <true> (default), the existing index is cleaned before the new
	 * 		index is created.
	 */
	public function createFullIndex($clean = true);
	
	/**
	 * Deletes the complete index.
	 */
	public function deleteIndex();
	
	/**
	 * Updates the index for the given $article.
	 * It retrieves all semantic data of the new version and adds it to the index.
	 * 
	 * @param Article $article
	 * 		The article that changed.
	 */
	public function updateIndexForArticle(Article $article, $user, $text);
	
}
