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
 * This file defines the class TSCSparqlQueryParser.
 * 
 * @author Thomas Schweitzer
 * Date: 19.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---

/**
 * This class uses the ARC2 library to parse SPARQL queries. The parsed query
 * i.e. its structure tree can be traversed with visitors.
 * 
 * @author Thomas Schweitzer
 * 
 */
class TSCSparqlQueryParser  {
	
	//--- Constants ---
		
	//--- Private fields ---
	private $mQuery;    		//string: The query string that will be processed
	private $mParser;			// The ARC2 parser
	private $mQueryStructure;	// The structure of the parsed query
	private $mBaseIRI = null;	// string: The base IRI 
	
	/**
	 * Constructor for TSCSparqlQueryParser
	 *
	 * @param string $query
	 * 		This is the query string that will be parsed.
	 */		
	function __construct($query) {
		$this->mParser = ARC2::getSPARQLParser();
		
		// The ARC2 parser add a base IRI if it is not given in the query.
		// This is an unwanted behavior.
		$baseIRI = null;
		if (preg_match("/BASE <(.*?)>/", $query, $baseIRI) === 1) {
			$this->mBaseIRI = $baseIRI[1];
		}
		
		/* parse a query */
		$this->mParser->parse($query);
		if (!$this->mParser->getErrors()) {
			$this->mQueryStructure = $this->mParser->getQueryInfos();
		} else {
			// TODO throw error
			print_r($this->mParser->getErrors());
		}
		
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	
	/**
	 * Traverses the structure of a parsed query and invokes the appropriate
	 * methods of a visitor for each node.
	 * 
	 * @param TSCSparqlQueryVisitor $visitor
	 * 		The methods of this visitor are called for the nodes of the query's
	 * 		structure tree.
	 */
	public function visitQuery(TSCSparqlQueryVisitor $visitor) {
		$this->traverseQuery($this->mQueryStructure, $visitor);
	}

	
	//--- Private methods ---
	
	/**
	 * Traverses the structure of a parsed query and invokes the appropriate
	 * methods of a visitor for each node.
	 * 
	 * @param array $pattern
	 * 		The current pattern that is traversed.
	 * 
	 * @param TSCSparqlQueryVisitor $visitor
	 * 		The methods of this visitor are called for the nodes of the query's
	 * 		structure tree.
	 */
	private function traverseQuery(array &$pattern, TSCSparqlQueryVisitor $visitor) {
		if (array_key_exists('query', $pattern)) {
			// $pattern is the root
			
			if (!isset($this->mBaseIRI)) {
				// Remove unwanted base IRI
				unset($pattern['base']);	
			}
			$visitor->preVisitRoot($pattern);
			$this->traverseQuery($pattern['query'], $visitor);
			$visitor->postVisitRoot($pattern);
		} else if (array_key_exists('result_vars', $pattern)) {
			// $pattern is the top level node of the query
			$visitor->preVisitQuery($pattern);
			$this->traverseQuery($pattern['pattern'], $visitor);
			$visitor->postVisitQuery($pattern);
		} else {
			// The possible patterns are
			// group, union, optional, filter, graph, triples, triple
			$fName = "preVisit".ucfirst($pattern['type']);
			$visitor->$fName($pattern);
			
			$patterns = &$pattern['patterns'];
			if (isset($patterns)) {
				$numPatterns = count($patterns)-1;
				$i = 0;
				foreach($patterns as &$p) {
					$this->traverseQuery($p, $visitor);
					if ($i < $numPatterns) {
						// Call the "inter"-visitor only if there is at least
						// one more pattern.
						$fName = "interVisit".ucfirst($pattern['type']);
						$visitor->$fName($pattern);
					}
					++$i;
				}
			}

			$fName = "postVisit".ucfirst($pattern['type']);
			$visitor->$fName($pattern);
		}
		
		
	}
}