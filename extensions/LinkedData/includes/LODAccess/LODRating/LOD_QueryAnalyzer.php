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
 * This file defines the class LODQueryAnalyzer
 * 
 * @author Thomas Schweitzer
 * Date: 21.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class analyzes the triples of a query to be able to rate them.
 * When a user wants to rate a value which is a result of a query the system
 * must find the triple(s) that led to this value. This can be achieved by
 * looking at the structure of the query. All values in the same table row as the
 * value to be rated will be inserted into the triples ofthe original query.
 * If variables remain unbound, the modified query can executed to find their
 * values. Thus all required triples can be identified. 
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODQueryAnalyzer  {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	private $mParser;    		// TSCSparqlQueryParser: The parser for the query
	private $mRewriter;			// LODRatingRewriter: the rewriter for the query
	private $mRewrittenQuery;	// string: The rewritten query
	private $mBindings;			// array<TSCSparqlQueryResult>: All known bindings
	private $mQuery;			// string: The original query
	private $mQueryParams;		// array: The parameters of the original query
	
	/**
	 * Constructor for LODQueryAnalyzer
	 *
	 * It rewrites a query so that the triples that match this query
	 * can be identified.
	 * 
	 * @param string $query
	 * 		This SPARQL query will be rewritten
	 * @param array $queryParams
	 * 		An array of query parameters as key-value pairs
	 * @param array<LODSparqlResult> $bindings
	 * 		An array of sparql result bindings that consists of values, their type
	 * 		and the variables they are bound to.
	 * 
	 * @return LODQueryAnalyzer
	 * 		A new query analyzer.
	 * 
	 * @throws LODRatingException
	 * 		CANNOT_REWRITE_QUERY, if the query can not be rewritten e.g. because
	 * 			it contains unions. 
	 */		
	function __construct($query, $queryParams, array $bindings) {
		$this->mBindings = $bindings;
		$this->mQuery = $query;
		$this->mQueryParams = $queryParams;
		$this->mParser = new TSCSparqlQueryParser($query);
		$this->mRewriter = new LODRatingRewriter($bindings);
		$this->mParser->visitQuery($this->mRewriter);
			
		$serializer = new LODSparqlRatingSerializer();
		$this->mParser->visitQuery($serializer);
		$this->mRewrittenQuery = $serializer->getSerialization();
	}
	

	//--- getter/setter ---
	public function getRewrittenQuery()		{ return $this->mRewrittenQuery; }
	
	public function getBoundVariables()		{ return $this->mRewriter->getBoundVariables(); }
	
	public function getUnboundVariables()	{ return $this->mRewriter->getUnboundVariables(); }
	

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	
	/**
	 * Retrieves the triple information for a bound or unbound variable.
	 *
	 * @param string $var
	 * 		Name of the variable
	 * 
	 * @return array<LODRatingTripleInfo>
	 * 		All triple information objects that are related to the given variable.
	 */
	public function getTripleInfoForVariable($var) {
		return $this->mRewriter->getTripleInfoForVariable($var);
	}
	
	/**
	 * This function binds all unbound variables by asking the rewritten query
	 * again. As there may be several result sets for the unbound variables
	 * a corresponding number of triple sets is generated and returned.
	 * 
	 * @return array(array(string variable name => LODRatingTripleInfo))
	 * 		Each inner array of triples is a possible solution of the original
	 * 		query. The outer array contains all solutions.
	 */
	public function bindAndGetAllTriples() {
		// Does the rewritten query contain unbound variables?
		$resultSet = array();
		if (count($this->getUnboundVariables()) == 0) {
			// no unbound variables => there is only one result set and all triples
			// are fully specified.
			$result = array();
			$bound = $this->getBoundVariables();
			foreach ($bound as $variable) {
				$result[$variable] = $this->getTripleInfoForVariable($variable);
			}
			$resultSet[] = $result;
			return $resultSet;
		}
		
		// Ask the rewritten query with unbound variables again
		
		// First, serialize the query parameters
		$params = "";
		$first = true;
		foreach ($this->mQueryParams as $param => $value) {
			if (!$first) {
				$params .= "|";
			} else {
				$first = false;
			}
			$params .= "$param=$value";
		}
		$tsa = new TSCTripleStoreAccess();
		$result = $tsa->queryTripleStore($this->mRewrittenQuery, null, $params);
		
		$rows = $result->getRows();
		foreach ($rows as $row) {
			// Each row of the result will lead to a new set of triples for
			// the result.
			$bindings = array_values($row->getResults());
			$mergedBindings = array_merge($this->mBindings, $bindings);
			$rewriter = new LODRatingRewriter($mergedBindings);
			$parser = new TSCSparqlQueryParser($this->mQuery);
			$parser->visitQuery($rewriter);
			
			$result = array();
			$bound = $rewriter->getBoundVariables();
			foreach ($bound as $variable) {
				$result[$variable] = $rewriter->getTripleInfoForVariable($variable);
			}
			$resultSet[] = $result;
		}
		return $resultSet;
		
	}
	

	//--- Private methods ---
}