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
 * @ingroup FacetedSearch
 *
 * This file contains the class FSResultParser
 * 
 * @author Thomas Schweitzer
 * Date: 28.02.2012
 * 
 */
if ( !defined( 'SOLRPROXY' ) ) {
	die( "This file is part of the FacetedSearch extension. It is not a valid entry point.\n" );
}

//--- Includes ---

/**
 * This class parses the results of a SOLR query with JSON format
 * 
 * @author Thomas Schweitzer
 * 
 */
class FSResultParser {
	
	//--- Constants ---
		
	//--- Private fields ---
	
	/**
	 * Constructor for FSResultParser
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	public static function parseResult($jsonResult) {
		$prefix = '_jqjsp(';
		if (strpos($jsonResult, $prefix) === 0) {
			$jsonResult = substr($jsonResult, strlen($prefix), 
								 strlen($jsonResult) - strlen($prefix) - 1);
		}
		
		return json_decode($jsonResult);
	}
	
	public static function serialize($jsonResult) {
		return '_jqjsp('.json_encode($jsonResult).')';
	}
	

	//--- Private methods ---
}