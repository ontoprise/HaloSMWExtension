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
class LODMapping  {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	
	// string
	// This is the ID of the source of the mapping (see the ID of class 
	// LODSourceDefinition). By convention, the name of the article that defines
	// the mapping is also the source. 
	private $mSource;
	
	// string
	// This is the ID of the target of the mapping, which is typically the wiki.
	// The default value can be configured with the global variable 
	// $lodgDefaultMappingTarget. 
	private $mTarget;
	
	// string
	// The "source code" of the mapping.
	private $mMappingText;
	
	/**
	 * Constructor for  LODMapping
	 *
	 * @param string $mappingText
	 * 		The text of the mapping 
	 * @param string $source
	 * 		The ID of the mapping's source
	 * @param string $target
	 * 		The ID of the mapping's target. If not set, the default mapping 
	 * 		target that is defined in the global variable $lodgDefaultMappingTarget
	 * 		is used.
	 * 
	 */		
	function __construct($mappingText, $source, $target = null) {
		global $lodgDefaultMappingTarget;
		$this->mSource = $source;
		$this->mTarget = isset($target) ? $target : $lodgDefaultMappingTarget;
		$this->mMappingText = $mappingText;
	}
	

	//--- getter/setter ---
	public function getSource()			{return $this->mSource;}
	public function getTarget()			{return $this->mTarget;}
	public function getMappingText()	{return $this->mMappingText;}
	
	public function setSource($s) 				{$this->mSource = $s;}
	public function setTarget($t)  				{$this->mTarget = $t;}
	public function setMappingText($mappingText)	{$this->mMappingText = $mappingText;}
	
	//--- Public methods ---
	

	//--- Private methods ---
}
