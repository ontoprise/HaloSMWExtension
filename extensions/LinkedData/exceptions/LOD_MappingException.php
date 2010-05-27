<?php
/**
 * @file
 * @ingroup LinkedDataException
 */

/*  Copyright 2010, ontoprise GmbH
*   This file is part of the Linked Data Extension.
*
*   The Linked Data Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Linked Data Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the class for all Linked Data Mapping Exceptions i.e. 
 * LODMappingException.
 * 
 * @author Thomas Schweitzer
 * Date: 12.05.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

/**
 * Exceptions for the operations Linked Data Mappings.
 *
 */
class LODMappingException extends LODException {

	//--- Constants ---
	
	// No store is set in the LODMappingStore.
	// Parameters:
	// 	No parameter
	const NO_STORE_SET = 1;	
	
	
	/**
	 * Constructor of the group exception.
	 *
	 * @param int $code
	 * 		A user defined error code.
	 */
    public function __construct($code = 0) {
    	$args = func_get_args();
    	// initialize super class
        parent::__construct($args);
    }
    
    protected function createMessage($args) {
    	$msg = "";
    	switch ($args[0]) {
    		case self::NO_STORE_SET:
    			$msg = "Internal error. No store is set in class LODMappingStore.";
    			break;
    	}
    	return $msg;
    }
}
