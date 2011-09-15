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
 * This file contains the class for all Rating Exceptions i.e. 
 * LODRatingException.
 * 
 * @author Thomas Schweitzer
 * Date: 19.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

/**
 * Exceptions for the operations concerning ratings.
 *
 */
class LODRatingException extends TSCException {

	//--- Constants ---
	
	// A SPARQL query can not be rewritten.
	// Parameters:
	// 	1 - Query
	//  2 - Reason, why query can not be rewritten
	const CANNOT_REWRITE_QUERY = 1;	
	
	// The query can not be rewritten as it contains a UNION.
	// No parameters.
	const QUERY_CONTAINS_UNION = 2;
	
	// The rating key has a wrong format
	// 1 - The rating key.
	const WRONG_RATING_KEY = 3;
	
	// There is no query with the ID ...
	// 1 -  Query ID
	const INVALID_QUERY_ID = 4;
	
	// The results of an invalid row are queried.
	// 1 - Index of the row 
	const INVALID_ROW = 5;
	
	
	/**
	 * Constructor of the exception.
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
    		case self::CANNOT_REWRITE_QUERY:
    			$msg = "The following query can not be rewritten:\n {$args[1]}\nThe reason is: {$args[2]}\n";
    			break;
    		case self::WRONG_RATING_KEY:
    			$msg = "The rating key '{$args[1]}' has a wrong format.\n";
    			break;
    		case self::INVALID_QUERY_ID:
    			$msg = "There is no query with the ID {$args[1]}\n";
    			break;
    		case self::INVALID_ROW:
    			$msg = "The results of the invalid row with index {$args[1]} are queried.\n";
    			break;
    	}
    	return $msg;
    }
}
