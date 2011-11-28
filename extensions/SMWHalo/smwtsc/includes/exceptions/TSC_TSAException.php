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
 * This file contains the class for all Triple Store Access Exceptions i.e. 
 * TSCTSAException.
 * 
 * @author Thomas Schweitzer
 * Date: 12.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

/**
 * Exceptions for the operations of the Triple Store Access.
 *
 */
class TSCTSAException extends TSCException {

	//--- Constants ---
	
	// The given method must not be called.
	// Parameters:
	// 	1 - Name of the method
	//  2 - Name of the class
	const INVALID_METHOD = 1;	
	
	
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
    		case self::INVALID_METHOD:
    			$msg = "Internal error. The method {$args[1]} must not be called for class {$args[2]} .";
    			break;
    	}
    	return $msg;
    }
}
