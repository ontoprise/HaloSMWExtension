<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file defines the class LODRatingRewriter
 * 
 * @author Thomas Schweitzer
 * Date: 20.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class is a visitor of the structure of a parsed SPARQL query. It visits
 * each node and replaces variables by their values, if they are known.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODRatingRewriter extends LODSparqlQueryVisitor {
	
	//--- Constants ---
		
	//--- Private fields ---
	// array(string => LODSparqlResult)
	// Maps from a variable name to the corresponding query result
	private $mBindings;
	
	
	// array(string => array(LODRatingTripleInfo));
	// A map for all bound variables to an array of triple information objects.
	private $mBoundVariables = array();
	
	// array(string => array(LODRatingTripleInfo));
	// A map for all unbound variables to an array of triple information objects.
	private $mUnboundVariables = array();
	
	/**
	 * Constructor for LODRatingRewriter
	 *
	 * @param array<LODSparqlResult> $bindings
	 * 		This array contains the bindings of variables that will be replaced
	 * 		by their values.
	 */		
	function __construct(array $bindings) {
		// Transform the array of bindings so that the variable is a key to the
		// bindings
		$this->mBindings = array();
		foreach ($bindings as $b) {
			$this->mBindings[$b->getVariableName()] = $b;
		}
	}
	

	//--- getter/setter ---
	public function getBoundVariables() {	
		return array_keys($this->mBoundVariables); 
	}

	public function getUnboundVariables() {	
		return array_keys($this->mUnboundVariables); 
	}

	//--- Public methods ---
	
	/**
	 * Visitor method for the main part of a query. All variables are marked as
	 * being selected i.e. "SELECT *"
	 * 
	 * @param array $pattern
	 * 		The pattern of a UNION.
	 * 
	 */
	public function preVisitQuery(&$pattern) {
		$vars = $pattern['result_vars'];
		$newVars = array();
		foreach ($vars as $v) {
			$newVars[] = array("var" => $v['value'], "aggregate" => 0, "alias" => "");
		}
		$pattern['result_vars'] = $newVars;
		
	}
	
	/**
	 * Visitor method for unions. If a query contains a union, the origin of a 
	 * triple can not be securely identified. Thus unions are not supported; an
	 * exception is thrown.
	 * 
	 * @param array $pattern
	 * 		The pattern of a UNION.
	 * 
	 * @throws LODRatingException
	 * 		QUERY_CONTAINS_UNION
	 * 
	 */
	public function preVisitUnion(&$pattern) {
		throw new LODRatingException(LODRatingException::QUERY_CONTAINS_UNION);
	}
	
	
	/**
	 * Visitor for a triple.
	 * Bound variables are replaced in the triple. Triple informations for 
	 * variables are recorded.
	 * 
	 * @param array $pattern
	 * 		Pattern of a triple
	 */
	public function preVisitTriple(&$pattern) {
			
		$subj = $pattern['s'];
		$subjVar = null;
		$subjValue = $subj;
		if ($pattern['s_type'] == "var") {
			$subjVar = $subj;
			// The subject is variable
			if (array_key_exists($subj, $this->mBindings)) {
				$b = $this->mBindings[$subj];
				$pattern['s_type'] = 'uri';
				$subjValue = $pattern['s'] = $b->getValue();
			} else {
				$subjValue = null;
			}
		} else if ($pattern['s_type'] == "bnode") {
			// Subject is a blank node
			$subjVar = str_replace("_:", "__bn", $subj);
			$pattern['s_type'] = "var";
			$pattern['s'] = $subjVar;
			$subjValue = null;
		}
		
		$pred = $pattern['p'];
		$predVar = null;
		$predValue = $pred;
		if ($pattern['p_type'] == "var") {
			$predVar = $pred;
			// The property is variable
			if (array_key_exists($pred, $this->mBindings)) {
				$b = $this->mBindings[$pred];
				$pattern['p_type'] = 'uri';
				$predValue = $pattern['p'] = $b->getValue();
			} else {
				$predValue = null;
			}
		}
		
		$obj = $pattern['o'];
		$objVar   = null;
		$objValue = $obj;
		$objType  = null;
		if ($pattern['o_type'] == "var") {
			$objVar = $obj;
			// The object is variable
			if (array_key_exists($obj, $this->mBindings)) {
				$b = $this->mBindings[$obj];
				if ($b instanceof LODSparqlResultURI) {
					// Result is an URI
					$pattern['o_type'] = 'uri';
					$objValue = $pattern['o'] = $b->getValue();
				} else {
					// Result is a literal
					$pattern['o_type'] = 'literal';
					$objValue = $pattern['o'] = $b->getValue();
					$objType = $pattern['o_datatype'] = $b->getDatatype();
				}
			} else {
				$objValue = null;
			}
		} else if ($pattern['o_type'] == "bnode") {
			// Object is a blank node
			$objVar = str_replace("_:", "__bn", $obj);
			$pattern['o_type'] = "var";
			$pattern['o'] = $objVar;
			$objValue = null;
		}
		
		$triple = new LODTriple($subjValue ? $subjValue : "?$subjVar",
								$predValue ? $predValue : "?$predVar", 
								$objValue  ? $objValue  : "?$objVar", 
								$objType);
								
		$this->addTripleInfo($subjVar, $subjValue, LODRatingTripleInfo::SUBJECT,
							 $triple);
		$this->addTripleInfo($predVar, $predValue, LODRatingTripleInfo::PREDICATE,
							 $triple);
		$this->addTripleInfo($objVar, $objValue, LODRatingTripleInfo::OBJECT,
							 $triple);
	}
	
	/**
	 * Retrieves the triple information for a bound or unbound variable.
	 *
	 * @param string $var
	 * 		Name of the variable
	 * 
	 * @return array<LODRatingTripleInfo> / null
	 * 		All triple information objects that are related to the given variable.
	 * 		<null>, if the variable is unknown.
	 */
	public function getTripleInfoForVariable($var) {
		if (array_key_exists($var, $this->mBoundVariables)) {
			return $this->mBoundVariables[$var];
		} else if (array_key_exists($var, $this->mUnboundVariables)) {
			return $this->mUnboundVariables[$var];
		}
		return null;
	}
	
	
	//--- Private methods ---
	
	/**
	 * Adds a triple info object for (un)bound variables.
	 * 
	 * @param string $var
	 * 		The variable
	 * @param string $value
	 * 		The value of the variable. If the value is <null>, the variable is
	 * 		unbound.
	 * @param int $position
	 * 		The position of the variable in the triple.
	 * @param LODTriple $triple
	 * 		The triple that is related to the variable.
	 */
	private function addTripleInfo($var, $value, $position, $triple) {
		if (!empty($var)) {
			$ti = new LODRatingTripleInfo($var, $position, !is_null($value), $triple);
			if (is_null($value)) {
				$array = &$this->mUnboundVariables;
			} else {
				$array = &$this->mBoundVariables;
			}
									 
			if (!array_key_exists($var, $array)) {
				$array[$var] = array();
			}
			$array[$var][] = $ti;
		}
	}
	
}