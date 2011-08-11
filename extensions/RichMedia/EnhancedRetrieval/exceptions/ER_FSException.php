<?php
/**
 * @file
 * @ingroup ER_Exception
 */

/*  Copyright 2011, ontoprise GmbH
*   This file is part of the Enhanced Retrieval Extension.
*
*   The Enhanced Retrieval Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Enhanced Retrieval Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the exception class for Faceted Search
 * 
 * @author Thomas Schweitzer
 * Date: 22.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

/**
 * Exceptions for the Faceted Search.
 *
 */
class ERFSException extends ERException {

	//--- Constants ---
	
	// An incomplete configuration was given.
	// Parameters:
	// 1 - Missing fields in the configuration
	const INCOMPLETE_CONFIG = 1;	

	// An unsupported value was given.
	// Parameters:
	// 1 - Unsupported values
	const UNSUPPORTED_VALUE = 2;	
	
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
    		case self::INCOMPLETE_CONFIG:
    			$msg = "The configuration for creating a Faceted Search Indexer is incomplete:\n $args[1]";
    			break;
    		case self::UNSUPPORTED_VALUE:
    			$msg = "The configuration for creating a Faceted Search Indexer contains unsupported values:\n $args[1]";
    			break;
    	}
    	return $msg;
    }
}
