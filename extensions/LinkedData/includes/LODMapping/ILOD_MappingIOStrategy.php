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
 * This file contains the interface ILODMappingIOStrategy.
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
interface ILODMappingIOStrategy  {
	
	/**
	 * Checks if the mapping with the given ID exists in the store for mappings.
	 *
	 * @param string $mappingID
	 * 		ID of the mapping
	 * 
	 * @return bool
	 * 	<true>, if the mapping exists
	 * 	<false> otherwise
	 * 
	 */
	public function existsMapping($mappingID);
	
	/**
	 * Stores the given mapping.
	 * 
	 * @param LODMapping $mapping
	 * 		This object defines a mapping for a linked data source.
	 * 
	 * @return bool 
	 * 		<true> if the mapping was stored successfully or
	 * 		<false> otherwise
	 * 	
	 */
	public function saveMapping(LODMapping $mapping);

	/**
	 * Loads the definition of the mapping with the given ID.
	 *
	 * @param string $mappingID
	 * 		ID of the mapping
	 * 
	 * @return LODMapping
	 * 		The definition of the mapping or <null>, if there is no such mapping
	 * 		with the given ID.
	 */
	public function loadMapping($mappingID);
	
	/**
	 * Deletes the mapping with the ID $mappingID.
	 *
	 * @param string $mappingID
	 * 		ID of the mapping.
	 */
	public function deleteMapping($mappingID);
	
}