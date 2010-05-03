<?php
/**
 * @file
 * @ingroup LinkedDataStorage
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
 * This file contains the class LODTripleStoreAccess which allows modifying and
 * querying the content of the connected triple store.
 * 
 * @author Thomas Schweitzer
 * Date: 30.04.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class simplifies accessing the triple store via the Triple Store Connector.
 * It allows to
 * - create graphs
 * - delete triples
 * - insert triples
 * - query the triple store
 * 
 * All commands that modify the triple store are collected until they are flushed.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  LODTripleStoreAccess  {
	
	//--- Constants ---
		
	//--- Private fields ---
	
	// array<string>:
	// Each string is a SPARUL command that is executed when the method
	// "flushCommands()" is called.
	private $mCommands = array();
	
	// string
	// This string contains namespace prefixes like 
	// PREFIX sd:<http://www.ontoprise.de/sd#> 
	// The prefixes are added to each command in $mCommands
	private $mPrefixes = "";
	
	/**
	 * Constructor for LODTripleStoreAccess
	 * 
	 */		
	function __construct() {
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	/**
	 * Adds the string with namespace prefixes to the current prefix string.
	 * The prefixes are added to each new command that is added in the other 
	 * methods e.g. deleteTriples or insertTriples. Consequently, prefixes must
	 * be set before these methods are called.
	 *
	 * @param string $prefixes
	 * 		One or more prefixes in a string e.g. 
	 * 		PREFIX sd:<http://www.ontoprise.de/sd#> 
	 */
	public function addPrefixes($prefixes) {
		$this->mPrefixes .= $prefixes;
	}
	
	/**
	 * Creates a graph with the name $graph in the triple store. If the graph 
	 * already exists, nothing happens.
	 *
	 * @param string $graph
	 * 		Name of the new graph.
	 */
	public function createGraph($graph) {
		$this->mCommands[] = $this->mPrefixes."\nCREATE SILENT <$graph>";
	}

	/**
	 * Deletes triples in the graph $graph, where the $wherePattern matches with
	 * the template $deleteTemplate. 
	 * (See http://www.w3.org/TR/2009/WD-sparql11-update-20091022/#t514)
	 * This method creates a SPARUL command that is collected in this
	 * instance. Prefixes that have been added with addPrefixes() are added to 
	 * the new command.
	 * Invoke flushCommands() to send all commands to the triple store.
	 *
	 * @param string $graph
	 * 		The name of the graph whose triples are deleted.
	 * @param string $wherePattern
	 * 		The pattern of the triples that are candidates for the deletion e.g.
	 * 		"MySubject ?p ?o" selects all triples of "MySubject"
	 * @param string $deleteTemplate
	 * 		The delete template is applied to all variable bindings of the 
	 * 		where-pattern e.g. "YourSubject ?p ?o" deletes all triples of 
	 * 		"YourSubject" with the same predicates and object as "MySubject".
	 */
	public function deleteTriples($graph, $wherePattern, $deleteTemplate) {
		$this->mCommands[] = 
			$this->mPrefixes
			."DELETE FROM <$graph> \n"
			."  { $deleteTemplate } \n"
			."WHERE\n" 
			."  { $wherePattern } ";		
	}
	
	/**
	 * Inserts the given triples $triples into the graph $graph.
	 * (See http://www.w3.org/TR/2009/WD-sparql11-update-20091022/#t515)
	 * This method creates a SPARUL command that is collected in this
	 * instance. Prefixes that have been added with addPrefixes() are added to 
	 * the new command.
	 * Invoke flushCommands() to send all commands to the triple store.
	 * 
	 * @param string $graph
	 * 		The name of the graph to which the triples are added.
	 * @param array<LODTriple> $triples
	 * 		An array of triple descriptions.
	 */
	public function insertTriples($graph, array $triples) {
		$cmds = "";
		foreach ($triples as $t) {
			$cmds .= $t->toSPARUL()." \n";
		}
		
		$this->mCommands[] = 
			$this->mPrefixes
			."INSERT DATA INTO <$graph> \n"
			."{\n"
			.$cmds
			."\n}";
	}
	
	
	/**
	 * Sends all collected SPARUL commands to the Triple Store. Afterwards all
	 * commands are deleted and the definition of prefixes is reset.
	 *
	 * @return bool
	 * 		<true> if successful
	 * 		<false> otherwise
	 */
	public function flushCommands() {
		$con = TSConnection::getConnector(); 
		$con->connect();
		$con->update("/topic/WIKI.TS.UPDATE", $this->mCommands);
		$con->disconnect();
		
		// Delete the array of commands and all prefixes
		$this->mCommands = array();
		$this->mPrefixes = "";
		 
		return true;
	}
	
	/**
	 * Sends a query to the Triple Store.
	 *
	 * @param array $query
	 * 		A SPARQL or ASK query.
	 * @param string $graph
	 * 		The graph to query. If not set, the graph stored in the global variable
	 * 		$smwgTripleStoreGraph is queried.
	 * 
	 * @return LODSparqlQueryResult
	 * 		The result of the query encapsulated in an object or <null> on failure.
	 */
	public function queryTripleStore($query, $graph = "") {
		$con = TSConnection::getConnector(); 
		$con->connect();
		$result = $con->query($query, "merge=false", $graph);
		$con->disconnect();
		
		$result = self::parseSparqlXMLResult($result);
		return $result;
	}

	
	//--- Private methods ---
	
	
	/**
	 * This method parses the SPARQL XML Result that is returned by a query. 
	 * The result is returned as instance of LODSparqlQueryResult.
	 *
	 * @param string $sparqlXMLResult
	 * 		A Sparql query result as XML string according to 
	 * 		http://www.w3.org/TR/rdf-sparql-XMLres/
	 * @return LODSparqlQueryResult
	 * 		The result in an object oriented structure or 
	 * 		<null> if there is no or an invalid result. 
	 */	
	private function parseSparqlXMLResult($sparqlXMLResult) {
		$dom = simplexml_load_string($sparqlXMLResult);
		if ($dom === FALSE) {
			return null;
		}
		
		// Store results in a query result object
		$queryResult = new LODSparqlQueryResult();
		
		// add variables to the query result
		$variables = $dom->xpath('//variable');
		foreach ($variables as $v) {
			$attrib = $v->attributes();
			$name = (string) $attrib['name'];
			$queryResult->addVariable($name);
		}
		
		// add all rows to the query result
		$results = $dom->xpath('//result');
		
		foreach ($results as $r) {
			$binding = $r->binding;
			$row = new LODSparqlResultRow();
			$c = count($binding);
			for ($i = 0; $i < $c; ++$i) {
				$b = $binding[$i];
				// A binding is bound to a variable. Its value can be a URI or
				// a literal. (We do not support blank nodes.)
				$attrib = $b->attributes();
				$name = (string) $attrib['name'];
				if (isset($b->uri)) {
					$row->addResult($name, new LODSparqlResultURI($name, (string) $b->uri));
				} else if (isset($b->literal)) {
					$attrib = $b->literal->attributes();
					$datatype = isset($attrib['datatype']) 
									? (string) $attrib['datatype'] : null;
					$lang     = isset($attrib['xml:lang']) 
									? (string) $attrib['xml:lang'] : null;
					$row->addResult($name, new LODSparqlResultLiteral($name, (string) $b->literal, $datatype, $lang));
				}
			}
			$queryResult->addRow($row);
		}
		
		return $queryResult;
		
	}
	
}

