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
 * This file contains the class LODMapping.
 * 
 * @author Thomas Schweitzer
 * Date: 12.05.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $haclgIP;
//require_once("$haclgIP/...");

/**
 * This class manages mappings among different LOD sources.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  LODMapping  {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	// string
	// Every mapping has an ID
	private $mID;
	
	// string
	// The "source code" of the mapping.
	private $mMappingText;
	
	/**
	 * Constructor for  LODMapping
	 *
	 * @param string $id
	 * 		The ID of the mapping 
	 * @param string $mappingText
	 * 		The text of the mapping 
	 * 
	 */		
	function __construct($id, $mappingText) {
		$this->mID = $id;
		$this->mMappingText = $mappingText;
	}
	

	//--- getter/setter ---
	public function getID()           {return $this->mID;}
	public function getMappingText()  {return $this->mMappingText;}
	
	public function setID($id)        				{$this->mID = $id;}
	public function setMappingText($mappingText)	{$this->mMappingText = $mappingText;}
	
	//--- Public methods ---
	

	//--- Private methods ---
}
