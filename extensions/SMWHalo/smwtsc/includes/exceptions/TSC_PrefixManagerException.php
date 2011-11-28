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
 * @ingroup LinkedDataException
 */
/**
 * This file contains the class for all prefix manager exceptions i.e. 
 * TSCPrefixManagerException.
 * 
 * @author Thomas Schweitzer
 * Date: 13.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

/**
 * Exceptions for the operations of the prefix manager.
 *
 */
class TSCPrefixManagerException extends TSCException {

	//--- Constants ---
	
	// A colon is missing in a prefixed URI
	// Parameters:
	// 	1 - The prefixed URI
	const MISSING_COLON = 1;	
	
	// The prefix in a prefixed URI is unknown
	// Parameters:
	// 	1 - The prefixed URI
	const UNKNOWN_PREFIX_IN_URI = 2;
	
	// The prefix is unknown
	// Parameters:
	// 	1 - The unknown prefix
	const UNKNOWN_PREFIX = 3;
	
	
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
    		case self::MISSING_COLON:
    			$msg = "Internal error. A colon is missing in the prefixed URI \"{$args[1]}\".";
    			break;
    		case self::UNKNOWN_PREFIX_IN_URI:
    			$msg = "Internal error. The prefix in the prefixed URI \"{$args[1]}\" is unknown.";
    			break;
    		case self::UNKNOWN_PREFIX:
    			$msg = "Internal error. The prefix \"{$args[1]}\" is unknown.";
    			break;
    	}
    	return $msg;
    }
}
