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
 * @ingroup LinkedDataAdministration
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
 * This class provides access to the storage object for mappings for Linked Data content. 
 * Mappings can be saved, loaded and delete with the storage object. 
 * 
 * @author Thomas Schweitzer
 * 
 */
class  LODMappingStore  {
	
	//--- Constants ---
	
	//--- Private fields ---
	
	
	// ILODMappingStore
	// This object handles the actual input and output operations
	private static $mStore;

	
	/**
	 * No instance of LODMappingStore can be created
	 *
	 */		
	private function __construct() {
	}
	

	/**
	 * Sets the actual implementation of the store that stores and retrieves mappings from a store.
	 * This function must be called before getStore().
	 *
	 * @param ILODMappingStore $store
	 * 		An implementation of the interface ILODMappingStore that handles the
	 * 		actual IO operations for mappings.
	 */
	public static function setStore($store) { self::$mStore = $store; }
	
	/**
	 * Returns the actual implementation of the store that stores and retrieves mappings from a store.
	 * The function setStore() must be called before this one.
	 *
	 * @return ILODMappingStore
	 * 		An implementation of the interface ILODMappingStore that handles the
	 * 		actual IO operations for mappings.
	 * @throws LODMappingException(LODMappingException::NO_STORE_SET)
	 * 		if no store is set
	 */
	public static function getStore() {
		if (!isset(self::$mStore)) {
			throw new LODMappingException(LODMappingException::NO_STORE_SET);
		}
		return self::$mStore;
	}
	
	//--- Public methods ---
	
	
	
	//--- Private methods ---
	
}

