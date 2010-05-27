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
	 * Checks if a mapping between $source and $target exists in the store 
	 * for mappings.
	 *
	 * @param string source
	 * 		ID of the source
	 * @param string target
	 * 		ID of the target
	 * 
	 * @return bool
	 * 	<true>, if the mapping exists
	 * 	<false> otherwise
	 * 
	 */
	public function existsMapping($source, $target);
	
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
	 */
	public function addMapping(LODMapping $mapping);

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
	 * Deletes all mappings between $source and $target.
	 *
	 * @param string source
	 * 		ID of the source. If <null>, all mappings with the to the given target
	 * 		are deleted.
	 * @param string target
	 * 		ID of the target. If <null>, all mappings from the given source are 
	 * 		deleted.
	 * If both parameters are <null>, all existing mappings are deleted.
	 */
	public function removeAllMappings($source = null, $target = null);
	
	/**
	 * Returns the IDs of all sources in the store.
	 * 
	 * @return array<string>
	 * 		An array of source IDs.
	 */
	public function getAllSources();
	
	/**
	 * Returns the IDs of all targets in the store.
	 * 
	 * @return array<string>
	 * 		An array of target IDs.
	 */
	public function getAllTargets();
	
}