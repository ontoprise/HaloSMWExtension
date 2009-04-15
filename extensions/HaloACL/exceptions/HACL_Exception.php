<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Insert description here
 * 
 * @author Thomas Schweitzer
 * Date: 02.04.2009
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

/**
 * Base class for all exceptions of HaloACL.
 *
 */
class HACLException extends Exception {
	
	/**
	 * Constructor of the HaloACL exception.
	 *
	 * @param string $message
	 * 		The error message
	 * @param int $code
	 * 		A user defined error code.
	 */
    public function __construct($message, $code = 0) {
    	// initialize super class
        parent::__construct($message, $code);
    }
}
