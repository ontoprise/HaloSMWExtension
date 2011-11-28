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
 *
 * This file contains the base class for all Linked Data Exceptions i.e. TSCException.
 * 
 * @author Thomas Schweitzer
 * Date: 03.05.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}


/**
 * This is the base class for all exceptions of the Linked Data Extension.
 *
 */
class TSCException extends Exception {

	// An internal error occurred
	// Parameters:
	// 1 - Description of the internal error
	const INTERNAL_ERROR = 1;
	
	
	/**
	 * Constructor of the TSCException.
	 *
	 * @param int $args[0]
	 * 		A user defined error code.
	 * @param string $args[1]
	 * 		The error message
	 */
    public function __construct($args) {
    	$code = 0;
    	if (!is_array($args)) {
    		$code = $args;
    		$args = func_get_args();
    	} else {
    		// If the constructor is called from sub-classes, all parameters
    		// are passed as array
    		$code = $args[0];
    	}
    	$msg = $this->createMessage($args);
    	
    	// initialize super class
        parent::__construct($msg, $code);
    }
    
    protected function createMessage($args) {
    	$msg = "";
    	switch ($args[0]) {
   			case self::INTERNAL_ERROR:
    			$msg = "Internal error: $args[1]";
    			break;
    	}
    	return $msg;
    }
    
}
