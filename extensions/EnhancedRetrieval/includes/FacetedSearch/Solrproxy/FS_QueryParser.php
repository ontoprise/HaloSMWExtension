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
 * This file contains the class FSQueryParser
 * 
 * @author Thomas Schweitzer
 * Date: 27.03.2012
 * 
 */
if ( !defined( 'SOLRPROXY' ) ) {
	die( "This file is part of the FacetedSearch extension. It is not a valid entry point.\n" );
}

//--- Includes ---

/**
 * This class parses a SOLR query and makes the parameters accessible.
 * 
 * @author Thomas Schweitzer
 * 
 */
class FSQueryParser {
	
	//--- Constants ---
		
	//--- Private fields ---
	private $mParameters;
	
	/**
	 * Constructor for FSQueryParser
	 *
	 * @param string $query
	 * 		The query string that was given as an URL with & as separator for the
	 * 		parameters.
	 */		
	function __construct($query) {
		if ($query) {
			$numMatches = preg_match_all("/(.*?)=(.*?)(&|$)/", $query, $matches);
			if ($numMatches !== false && $numMatches > 0) {
				$this->mParameters = array();
				// Copy parameter names and values into the internal array as
				// key value pairs
				for ($i = 0; $i < $numMatches; $i++) {
					$p = $matches[1][$i];
					$v = $matches[2][$i];
					if (array_key_exists($p, $this->mParameters)) {
						// If there are several values for a parameter, store them all
						// in an array.
						if (!is_array($this->mParameters[$p])) {
							$this->mParameters[$p] = array($this->mParameters[$p]);
						}
						$this->mParameters[$p][] = $v;
					} else {
						$this->mParameters[$p] = $v;
					}
				}
			}
		}
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	/**
	 * Returns the value of the given query parameter. If the value is not set,
	 * null is returned.
	 * @param string $parameter
	 * 		Name of the query parameter
	 * @return mixed
	 * 		Value of the parameter or
	 * 		null
	 */
	public function get($parameter) {
		if (isset($this->mParameters) && array_key_exists($parameter, $this->mParameters)) {
			return $this->mParameters[$parameter];
		}
		return null;
	}
	
	/**
	 * Sets the $value for the given query $parameter. If the current value of
	 * the parameter is an array, the user of the API has to take care of its 
	 * values. This method simply replaces the current value.
	 *  
	 * @param string $parameter
	 * 		Name of the parameter
	 * @param string/array $value
	 * 		The new value of the parameter.
	 */
	public function set($parameter, $value) {
		if (!is_array($this->mParameters)) {
			$this->mParameters = array();
		}
		$this->mParameters[$parameter] = $value;
	}
	
	/**
	 * Serializes the parsed query back to a query string.
	 * @return string
	 * 		The serialized query or an empty string if parsing the query failed.
	 */
	public function serialize() {
		if (!$this->mParameters) {
			return "";
		}
		
		$params = array();
		foreach ($this->mParameters as $param => $value) {
			if (is_array($value)) {
				foreach ($value as $v) {
					$params[] = "$param=$v";
				}
			} else {
				$params[] = "$param=$value";
			}
		}
		$result = implode('&', $params);
		return $result;
	}

	//--- Private methods ---
}