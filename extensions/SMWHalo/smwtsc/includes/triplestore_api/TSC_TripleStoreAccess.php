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
 * @ingroup LinkedDataStorage
 */
/**
 * This file contains the class TSCTripleStoreAccess which allows modifying and
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
class TSCTripleStoreAccess  {

	//--- Constants ---

	//--- Private fields ---

	// array<string>:
	// Each string is a SPARUL command that is executed when the method
	// "flushCommands()" is called.
	protected $mCommands = array();

	// string
	// This string contains namespace prefixes like
	// PREFIX sd:<http://www.ontoprise.de/sd#>
	// The prefixes are added to each command in $mCommands
	protected $mPrefixes = "";

	/**
	 * Constructor for TSCTripleStoreAccess
	 *
	 */
	function __construct() {
	}


	//--- getter/setter ---
	public function getPrefixes() { return $this->mPrefixes; }

	//--- Public methods ---
	
	/**
	 * Checks if the the triple store is properly connected.
	 * 
	 * @return bool
	 * 		true: if the triple store is properly connected
	 * 		false: otherwise
	 */
	public function isConnected() {
		try {
			$con = TSConnection::getConnector();
			$con->connect();
			$status = $con->getStatus(null);
			$con->disconnect();
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * Adds the string with namespace prefixes to the current prefix string.
	 * The prefixes are added to each new command that is added in the other
	 * methods e.g. deleteTriples or insertTriples. Consequently, prefixes must
	 * be set before these methods are called.
	 *
	 * @param string $prefixes
	 * 		One or more prefixes in a string. Each prefix must be of the form:
	 * 		"PREFIX" prefix_name":<"prefix_uri"> "
	 * 		e.g.
	 * 		PREFIX sd:<http://www.ontoprise.de/sd#>
	 * 		Different prefixes in $prefixes must be separated by spaces.
	 */
	public function addPrefixes($prefixes) {
		$this->mPrefixes .= "$prefixes\n";
	}

	/**
	 * Creates a graph with the name $graph in the triple store. If the graph
	 * already exists, nothing happens.
	 *
	 * This method creates a SPARUL command that is collected in this
	 * instance. Prefixes that have been added with addPrefixes() are added to
	 * the new command.
	 * Invoke flushCommands() to send all commands to the triple store.
	 *
	 * @param string $graph
	 * 		Name of the new graph.
	 */
	public function createGraph($graph) {
		$this->mCommands[] = $this->mPrefixes."\nCREATE SILENT GRAPH <$graph>";
	}

	/**
	 * Drops the graph with the name $graph in the triple store. If the graph
	 * does not exists, nothing happens.
	 *
	 * This method creates a SPARUL command that is collected in this
	 * instance. Prefixes that have been added with addPrefixes() are added to
	 * the new command.
	 * Invoke flushCommands() to send all commands to the triple store.
	 *
	 * @param string $graph
	 * 		Name of the graph to drop.
	 */
	public function dropGraph($graph) {
		$this->mCommands[] = $this->mPrefixes."\nDROP SILENT GRAPH <$graph>";
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
	 * @param array<TSCTriple> $triples
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
	 * Loads a file into a graph of the triple store.
	 * @param string $file
	 * 		URI of a file that can be accessed by the triple store e.g.
	 * 		"http://somepath/somefile.n3" or "file://absolutePath/someFile.n3"
	 * @param string $graph
	 * @param string $format
	 */
	public function loadFileIntoGraph($file, $graph, $format) {
		$this->mCommands[] = "LOAD <$file?format=$format> INTO <$graph>\n";
	}

	/**
	 * Sends all collected SPARUL commands to the Triple Store. Afterwards all
	 * commands are deleted and the definition of prefixes is reset.
	 *
	 * @return bool
	 * 		<true> if successful
	 * 		<false> if a SoapFault exception was catched
	 */
	public function flushCommands() {
		try {
			$con = TSConnection::getConnector();
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", $this->mCommands);
			$con->disconnect();
		} catch (SoapFault $e) {
			// A soap fault exception occurred => flushing commands failed
			return false;
		}

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
	 * 		$smwgHaloTripleStoreGraph is queried.
	 * @param string $params
	 * 		A string with the parameters for the query in the following format:
	 * 		parameterName=parameterValue
	 * 		Several parameters are separated by |
	 *
	 * @return TSCSparqlQueryResult
	 * 		The result of the query encapsulated in an object or <null> on failure.
	 */
	public function queryTripleStore($query, $graph = "", $params = null) {
		try {
			$con = TSConnection::getConnector();
			$con->connect();
			$p = "merge=false";
			if (isset($params)) {
				$p .= "|$params";
			}
			$result = $con->query($query, $p, $graph);
			$con->disconnect();
		} catch (Exception $e) {
			// An exception occurred => no result
			return null;
		}

		$result = self::parseSparqlXMLResult($result);
		return $result;
	}
	
	/**
	 * Sends a call  to the LDImporter.
	 * 
	 * @param string $method Method to call
	 * @param string $payload Payload encoded as application/x-www-form-urlencoded
	 * 
	 */
    public function callLDImporter($method, $payload = '') {
        try {
            $con = TSConnection::getConnector();
            $con->connect();
           
            $result = $con->callLDImporter($method, $payload);
            $con->disconnect();
        } catch (Exception $e) {
            // An exception occurred => no result
            return null;
        }
       
        return $result;
    }
	

	//--- Private methods ---


	/**
	 * This method parses the SPARQL XML Result that is returned by a query.
	 * The result is returned as instance of TSCSparqlQueryResult.
	 *
	 * @param string $sparqlXMLResult
	 * 		A Sparql query result as XML string according to
	 * 		http://www.w3.org/TR/rdf-sparql-XMLres/
	 * @return TSCSparqlQueryResult
	 * 		The result in an object oriented structure (which may contain zero
	 * 		result rows or <null> if the result is invalid.
	 */
	private function parseSparqlXMLResult($sparqlXMLResult) {
		$dom = simplexml_load_string($sparqlXMLResult);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		if ($dom === FALSE) {
			return null;
		}

		// Store results in a query result object
		$queryResult = new TSCSparqlQueryResult();

		// add variables to the query result
		$variables = $dom->xpath('//sparqlxml:variable');
		foreach ($variables as $v) {
			$attrib = $v->attributes();
			$name = (string) $attrib['name'];
			$queryResult->addVariable($name);
		}

		// add all rows to the query result
		$results = $dom->xpath('//sparqlxml:result');

		foreach ($results as $r) {
			$binding = $r->binding;
			$row = new TSCSparqlResultRow();
			$c = count($binding);
			for ($i = 0; $i < $c; ++$i) {
				$b = $binding[$i];
				// A binding is bound to a variable. Its value can be a URI or
				// a literal. (We do not support blank nodes.)
				$attrib = $b->attributes();
				$name = (string) $attrib['name'];
				if (isset($b->uri)) {
					$metadata = array();
					if (isset($b->uri->metadata)) {
						$mdNodes = $b->uri->metadata;
						foreach($mdNodes as $mdn) {
							$mdAttrib = $mdn->attributes();
							$mdName = (string) $mdAttrib['name'];
							$mdValue = @$mdn->value;
							if (isset($mdValue)) {
								$metadata[$mdName] = (string) $mdValue;
							}
						}
					}
				
					$row->addResult($name, new TSCSparqlResultURI($name, (string) $b->uri, $metadata));
				} else if (isset($b->literal)) {
					$attrib = $b->literal->attributes();
					$datatype = isset($attrib['datatype'])
									? (string) $attrib['datatype'] : null;
					$lang     = isset($attrib['xml:lang'])
									? (string) $attrib['xml:lang'] : null;
						
					$metadata = array();
					if (isset($b->literal->metadata)) {
						$mdNodes = $b->literal->metadata;
						foreach($mdNodes as $mdn) {
							$mdAttrib = $mdn->attributes();
							$mdName = (string) $mdAttrib['name'];
							$mdValue = @$mdn->value;
							if (isset($mdValue)) {
								$metadata[$mdName] = (string) $mdValue;
							}
						}
					}
					$row->addResult($name, new TSCSparqlResultLiteral($name, (string) $b->literal, $datatype, $lang, $metadata));
				}
			}
			$queryResult->addRow($row);
		}

		return $queryResult;

	}

}

