<?php
/**
 * @file
 * @ingroup SMWHaloSMWDeviations
 */

/*  Copyright 2011, ontoprise GmbH
*  This file is part of the SMWTSC-Extension.
*
*   The SMWTSC-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The SMWTSC-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file defines the class SMWChainPrintRequest
 * 
 * @author Thomas Schweitzer
 * Date: 08.11.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the SMW_TSC extension. It is not a valid entry point.\n" );
}

/**
 * 
 * 
 * @author Thomas Schweitzer
 * 
 */
class SMWChainPrintRequest extends SMWPrintRequest {
		
	//--- Private fields ---

	// {string} Name of the original chain print request 
	private $mChainPrintRequest;
	
	/**
	 * Constructor for SMWChainPrintRequest
	 * 
	 * @param $chainPrintRequest Name of the original chain print request 
	 * @param $mode a constant defining what to printout
	 * @param $label the string label to describe this printout
	 * @param $data optional data for specifying some request, might be a property object, title, or something else; interpretation depends on $mode
	 * @param $outputformat optional string for specifying an output format, e.g. an output unit
	 * @param $params optional array of further, named parameters for the print request
	 */		
	function __construct($chainPrintRequest, $mode, $label, $data = null, 
	                     $outputformat = false, $params = null) {
		$this->mChainPrintRequest = $chainPrintRequest;
		parent::__construct($mode, $label, $data, $outputformat, $params);
	}
	

	//--- getter/setter ---
	public function getChainPrintRequest()   {return $this->mChainPrintRequest;}
	
	//--- Public methods ---
	
	//--- Private methods ---
}