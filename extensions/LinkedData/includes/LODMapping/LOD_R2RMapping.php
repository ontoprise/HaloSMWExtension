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
 * This file contains the class LODR2RMapping.
 * 
 * @author Ingo Steinbauer
 * Date: 28.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * This class manages R2R mappings.
 * 
 * @author Ingo Steinbauer
 * 
 */
class LODR2RMapping  extends LODMapping{
	
	/*
	 * Prefixed URI of the mapping type
	 */
	public static $mappingType = 'smw-lde:R2RMapping';
	
	/**
	 * Constructor for  LODR2RMapping
	 *
	 * @param string $mappingText
	 * 		The text of the mapping 
	 * @param string $source
	 * 		The ID of the mapping's source
	 * @param string $target
	 * 		The ID of the mapping's target. If not set, the default mapping 
	 * 		target that is defined in the global variable $lodgDefaultMappingTarget
	 * 		is used.
	 */		
	function __construct($uri, $mappingText, $source, $target = null, $additionalProps = null) {
		parent::__construct($uri, $mappingText, $source, $target);
	}
	
	/*
	 * Get the type URI of this mapping
	 */
	public function getMappingType(){
		return self::$mappingType;
	}
	
	/*
	 * Returns true if this mapping equals the given one
	 */
	public function equals($mapping){
		if(!parent::equals($mapping)) return false;

		if(!($mapping instanceof LODR2RMapping)) return false;
		
		return true;
	}
}





