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
 * This file contains the class LODPersistentMappingStore.
 * 
 * @author Thomas Schweitzer
 * Date: 05.07.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This is class is a wrapper for other LOD mapping stores that makes mappings 
 * persistent in an SQL table. All method invocations are passed down to the
 * wrapped store.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODPersistentMappingStore  {
	
	//--- Private fields ----
	private $mWrappedStore;
	
	//--- Constructor ---
	
	/**
	 * Constructor.
	 * Creates the wrapping mapping store.
	 * 
	 * @param ILODMappingsStore wrappedStore
	 * 		The wrapped store. All method invocations are passed down to this 
	 * 		store.
	 *
	 */
	public function __construct($wrappedStore) {
		$this->mWrappedStore = $wrappedStore;
	}
	
	//--- Public functions ---
	
	/**
	 * Checks if a mapping between $source and $target exists in the store 
	 * for mappings.
	 * This call is immediately passed to the wrapped store.
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
	public function existsMapping($source, $target) {
		return $this->mWrappedStore->existsMapping($source, $target);
	}
	
	/**
	 * Adds the given mapping to the store. Already existing mappings with the
	 * same source and target are not replaced but enhanced.
	 * The mapping is store for persistence in an SQL table. The call is then
	 * passed to the wrapped store.
	 * 
	 * @param LODMapping $mapping
	 * 		This object defines a mapping for a linked data source.
	 * 
	 * @return bool 
	 * 		<true> if the mapping was stored successfully or
	 * 		<false> otherwise
	 * 	
	 */
	public function addMapping(LODMapping $mapping) {
		// Store mapping in SQL table
		$db =& wfGetDB( DB_MASTER );

		$db->replace($db->tableName('lod_mapping_persistence'), null, array(
            'source'	   => $mapping->getSource(),
            'target '      => $mapping->getTarget(),
			'mapping_text' => $mapping->getMappingText()));
		
		
		// Invoke the wrapped store
		return $this->mWrappedStore->addMapping($mapping);
	}
	
	/**
	 * Loads all definitions of mappings between $source and $target.
	 * This call is immediately passed to the wrapped store.
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
	public function getAllMappings($source = null, $target = null) {
		return $this->mWrappedStore->getAllMappings($source, $target);
	}
	
	/**
	 * Deletes all mappings between $source and $target.
	 * The mapping is removed from the SQL table. The call is then
	 * passed to the wrapped store.
	 *
	 * @param string source
	 * 		ID of the source. If <null>, all mappings with the to the given target
	 * 		are deleted.
	 * @param string target
	 * 		ID of the target. If <null>, all mappings from the given source are 
	 * 		deleted.
	 * If both parameters are <null>, all existing mappings are deleted.
	 */
	public function removeAllMappings($source = null, $target = null) {
		$db =& wfGetDB( DB_MASTER );

		// Delete the group from the hierarchy of groups (as parent and as child)
		$table = $db->tableName('lod_mapping_persistence');
		if (isset($source) && isset($target)) {
			$db->delete($table, array('source' => $source, 
			                          'target' => $target));
		} else if (isset($source)) {
			$db->delete($table, array('source' => $source));
		} else if (isset($target)) {
			$db->delete($table, array('target' => $target));
		} else {
			$db->delete($table, '*');
		}
		
		return $this->mWrappedStore->removeAllMappings($source, $target);
	}
	
	/**
	 * Returns the IDs of all sources in the store.
	 * This call is immediately passed to the wrapped store.
	 * 
	 * @return array<string>
	 * 		An array of source IDs.
	 */
	public function getAllSources() {
		return $this->mWrappedStore->getAllSources();
	}
	
	/**
	 * Returns the IDs of all targets in the store.
	 * This call is immediately passed to the wrapped store.
	 * 
	 * @return array<string>
	 * 		An array of target IDs.
	 */
	public function getAllTargets() {
		return $this->mWrappedStore->getAllTargets();
	}
	
}