<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the interface ILODMappingStore.
 * 
 * @author Thomas Schweitzer
 * Date: 12.05.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This is the interface of the IO strategy that is used in the mapping store 
 * (LODMappingStore). Classes that implement this interface must be abled to
 * store, load and delete instances of LODMapping in some way.
 * 
 * @author Thomas Schweitzer
 * 
 */
interface ILODMappingStore  {
	
	/**
	 * Checks if a mapping exists in the Mapping Store
	 *
	 * @param LODMapping mapping
	 * 		The mapping to check for
	 * 
	 * @return bool
	 * 	<true>, if the mapping exists
	 * 	<false> otherwise
	 * 
	 */
	public function existsMapping($mapping);
	
	/**
	 * Adds the given mapping to the store. Already existing mappings with the
	 * same source and target are not replaced but enhanced.
	 * 
	 * @param LODMapping $mapping
	 * 		This object defines a mapping for a linked data source.
	 * 
	 * @return bool 
	 * 		<true> if the mapping was stored successfully or
	 * 		<false> otherwise
	 * 	
	 * @param string persistencyLayerId
	 * 		An id, that adresses the tripples of this mapping in the 
	 * 		persistency layer. Normally the article name is used.
	 */
	public function addMapping(LODMapping $mapping, $persistencyLayerId);

	/**
	 * Loads all definitions of mappings between $source and $target.
	 *
	 * @param string source
	 * 		ID of the source. If <null>, all mappings with the to the given target
	 * 		are returned.
	 * @param string target
	 * 		ID of the target. If <null>, all mappings from the given source are 
	 * 		returned.
	 * If both parameters are <null>, all existing mappings are returned.
	 * 
	 * @return array<LODMapping>
	 * 		The definitions of matching mappings or an empty array, if there are 
	 * 		no such mappings.
	 */
	public function getAllMappings($source = null, $target = null);
	
	/**
	 * Deletes all mappings having source and range $source and $target.
	 *
	 * @param string source
	 * 		ID of the source. If <null>, all mappings with the to the given target
	 * 		are deleted.
	 * @param string target
	 * 		ID of the target. If <null>, all mappings from the given source are 
	 * 		deleted.
	 * @param string $persistencyLayerId
	 * 		Must be the same Id that was used when storing the triples.	
	 * 
	 */
	public function removeAllMappings($source, $target, $persistencyLayerId);
	
	/**
	 * Returns the IDs of all sources in the store.
	 * 
	 * @return array<string>
	 * 		An array of source IDs.
	 */
	//public function getAllSources();
	
	/**
	 * Returns the IDs of all targets in the store.
	 * 
	 * @return array<string>
	 * 		An array of target IDs.
	 */
	//public function getAllTargets();
	
	/**
	 * As mappings are stored in articles the system must know which mappings
	 * (i.e. source-target pairs) are stored in an article.
	 * This function stores a source-target pairs for an article.
	 * @param string $articleName
	 * 		Fully qualified name of an article
	 * @param string $source
	 * 		Name of the mapping source
	 * @param string $target
	 * 		Name of the mapping target
	 */
	public function addMappingToPage($articleName, $source, $target);
	
	/**
	 * Returns an array of source-target pairs of mappings that are stored in the
	 * article with the name $articleName
	 * @param string $articleName
	 * 		Fully qualified name of an article
	 * @return array(array(string source, string $target))
	 */
	public function getMappingsInArticle($articleName);
	
	/**
	 * Deletes all mappings that are stored in the article with the name 
	 * $articleName. Also calls remove all Mappings in order to delte
	 * mappings from TSC
	 * 
	 * @param string $articleName
	 * 		Fully qualified name of an article
	 */
	public function removeAllMappingsFromPage($articleName);
	
	
	
}