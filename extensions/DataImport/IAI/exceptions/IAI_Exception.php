<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Interwiki-Article-Import-module in the Data-Import-Extension.
*
*   The DataImport-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The DataImport-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
  * @ingroup DIInterWikiArticleImport
  * 
  * This file contains the base class for IAI exceptions.
 * 
 * @author Thomas Schweitzer
 * Date: 02.04.2009
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the IAI extension. It is not a valid entry point.\n" );
}

/**
 * Base class for all exceptions of IAI.
 *
 */
class IAIException extends Exception {

	// An internal error occurred
	// Parameters:
	// 1 - Description of the internal error
	const INTERNAL_ERROR = 1;
	
	// A HTTP request failed
	// Parameters:
	// 1 - URL of the request
	const HTTP_ERROR = 2;
	
	
	/**
	 * Constructor of the HaloACL exception.
	 *
	 * @param string $message
	 * 		The error message
	 * @param int $code
	 * 		A user defined error code.
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
    
    /**
     * This function generates the error messages for given error codes with
     * their appropriate parameters. This method is overwritten by sub-classes
     * that define their own error codes and messages.
     *
     * @param array<mixed> $args
     * 		$args[0]: error code
     * 		$args[1]-$args[n]: parameters for the error message
     * @return string error message
     */
    protected function createMessage($args) {
    	$msg = "";
    	switch ($args[0]) {
   			case self::INTERNAL_ERROR:
    			$msg = "Internal error: $args[1]";
    			break;
   			case self::HTTP_ERROR:
    			$msg = "HTTP request failed. Could not load data from URL: $args[1]";
    			break;
    	}
    	return $msg;
    }
    
}
