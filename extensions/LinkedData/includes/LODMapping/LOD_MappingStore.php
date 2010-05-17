<?php
/**
 * @file
 * @ingroup LinkedDataAdministration
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
 * This file defines the class LODMappingStore.
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
 * This class handles the storage of mappings for Linked Data content. 
 * Mappings can be saved, loaded and delete from the store. The store must be
 * configured with an I/O strategy that determines where the mapping is stored 
 * and from where it is loaded.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  LODMappingStore  {
	
	//--- Constants ---
	const LOD_SOURCE_DEFINITION_GRAPH = "LODSourceDefinitionGraph";
	const LOD_BASE_URI = "http://www.ontoprise.de/";
	const LOD_SOURCE_DEFINITION_URI_SUFFIX = "sd#";
	const LOD_SOURCE_DEFINITION_PROPERTY_SUFFIX = "sdprop#";
	
	//--- Private fields ---
	
	// LODMappingStore
	// Points to the singleton instance of the LODMappingStore store.
	private static $mInstance;
	
	// ILODMappingIOStrategy
	// This object handles the actual input and output operations
	private static $mIOStrategy;

	
	/**
	 * Constructor for LODMappingStore
	 *
	 */		
	protected function __construct() {
		self::$mInstance = $this;
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

	/**
	 * Sets the IO strategy that stores and retrieves mappings from a store.
	 * This function must be called before any other IO operation of this class.
	 *
	 * @param ILODMappingIOStrategy $ioStrategy
	 * 		An implementation of the interface ILODMappingIOStrategy that handles the
	 * 		actual IO operations for mappings.
	 */
	public static function setIOStrategy($ioStrategy) { self::$mIOStrategy = $ioStrategy; }
	
	//--- Public methods ---
	
	/**
	 * Returns the singleton instance of this class.
	 * 
	 * @return LODMappingStore
	 * 		The only instance of this class.
	 *
	 */
	public static function getInstance() {
		if (!self::$mInstance) {
			$c = __CLASS__;
			new $c;
		}
		return self::$mInstance;
	}

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
	 * @throws LODMappingException(LODMappingException::NO_IO_STRATEGY_SET)
	 * 		if no IO strategy is set
	 *  
	 */
	public function existsMapping($mappingID) {
		if (!isset(self::$mIOStrategy)) {
			throw new LODMappingException(LODMappingException::NO_IO_STRATEGY_SET);
		}
		return self::$mIOStrategy->existsMapping($mappingID);
		
	}
	
	/**
	 * Stores the given mapping with the IO strategy
	 * 
	 * @param LODMapping $mapping
	 * 		This object defines a mapping for a linked data source.
	 * 
	 * @return bool 
	 * 		<true> if the mapping was stored successfully or
	 * 		<false> otherwise
	 * @throws LODMappingException(LODMappingException::NO_IO_STRATEGY_SET)
	 * 		if no IO strategy is set
	 * 	
	 */
	public function saveMapping(LODMapping $mapping) {
		if (!isset(self::$mIOStrategy)) {
			throw new LODMappingException(LODMappingException::NO_IO_STRATEGY_SET);
		}
		return self::$mIOStrategy->saveMapping($mapping);
	}
	
	/**
	 * Loads the definition of the mapping with the given ID. The IO strategy
	 * must be set before this method is called.
	 *
	 * @param string $mappingID
	 * 		ID of the mapping
	 * 
	 * @return LODMapping
	 * 		The definition of the mapping or <null>, if there is no such mapping
	 * 		with the given ID.
	 * @throws LODMappingException(LODMappingException::NO_IO_STRATEGY_SET)
	 * 		if no IO strategy is set
	 */
	public function loadMapping($mappingID) {
		if (!isset(self::$mIOStrategy)) {
			throw new LODMappingException(LODMappingException::NO_IO_STRATEGY_SET);
		}
		return self::$mIOStrategy->loadMapping($mappingID);
	}
	
	/**
	 * Deletes the mapping with the ID $mappingID.
	 * The IO strategy must be set before this method is called.
	 *
	 * @param string $mappingID
	 * 		ID of the mapping.
	 */
	public function deleteMapping($mappingID) {
		if (!isset(self::$mIOStrategy)) {
			throw new LODMappingException(LODMappingException::NO_IO_STRATEGY_SET);
		}
		return self::$mIOStrategy->deleteMapping($mappingID);
	}
	
	
	//--- Private methods ---
	
}

