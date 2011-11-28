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
 * This file contains the class TSCPersistentTripleStoreAccess which allows 
 * modifying and querying the content of the connected triple store. 
 *
 * @author Thomas Schweitzer
 * Date: 1.10.2010
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
 * Inserted triples can be persisted in a database so that the Java-sided 
 * triple store connector can recreate the content of the TS after a restart.
 * Sets of triples are stored with a component and an ID so that they can deleted
 * from the persistent store.
 *
 * @author Thomas Schweitzer
 *
 */
class TSCPersistentTripleStoreAccess extends TSCTripleStoreAccess {

	//--- Constants ---
	
	//--- Private fields ---
	
	// array(string => string)
	// This is a map from graph names to all triples of these graphs that were 
	// inserted, serialized in TriG syntax.
	private $mSerializedTriples = array();
	
	// boolean
	// Normally the method deleteTriples() throws an exception as the persistence
	// layer can not track the triples that are deleted by this command. However,
	// this operation may be allowed if the developer takes care that the deleted
	// triples are also removed from the persistence layer.
	private $mAllowDeleteTriples = false;
	

	/**
	 * Constructor for TSCTripleStoreAccess
	 *
	 * @param bool $allowDeleteTriples
	 * Normally the method deleteTriples() throws an exception as the persistence
	 * layer can not track the triples that are deleted by this command. However,
	 * this operation may be allowed if the developer takes care that the deleted
	 * triples are also removed from the persistence layer. 
	 * WARNING: This may lead to inconsistencies i.e. triples that were deleted
	 * 		from the triple store will be restored when it is restarted. Use this
	 * 		at your own risk!!
	 */
	function __construct($allowDeleteTriples = false) {
		parent::__construct();
		$this->mAllowDeleteTriples = $allowDeleteTriples;
	}


	//--- getter/setter ---

	//	public function setXY($xy)               {$this->mXY = $xy;}

	//--- Public methods ---
	
	
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
		$this->serializeTriplesForGraph($graph, $triples);
		parent::insertTriples($graph, $triples);		
	}

	/**
	 * This methode overwrites the method of the parent class and throws an 
	 * exception. The persistence layer can not handle delete operations on triples
	 * as the parent class can do this. Triples must be deleted with the method
	 * deletePersitentTriples().
	 * The constructor of this class may be called with the parameter 
	 * $allowDeleteTriples = true. In this case the parent method is called. 
	 * WARNING: This may lead to inconsistencies i.e. triples that were deleted
	 * 		from the triple store will be restored when it is restarted.
	 * 
	 * @param string $graph
	 * @param string $wherePattern
	 * @param string $deleteTemplate
	 * 
	 * @throws TSCTSAException
	 * 		INVALID_METHOD
	 */
	public function deleteTriples($graph, $wherePattern, $deleteTemplate) {
		if ($this->mAllowDeleteTriples) {
			parent::deleteTriples($graph, $wherePattern, $deleteTemplate);
		} else {
			throw new TSCTSAException(TSCTSAException::INVALID_METHOD, 
									  "deleteTriples", "TSCPersistentTripleStoreAccess");
		}
	}
	
	/**
	 * Sends all collected SPARUL commands to the Triple Store. Afterwards all
	 * commands are deleted and the definition of prefixes is reset.
	 * Furthermore, the inserted triples are converted to TriG and stored in the
	 * database with the given $component and $id. This function can be called
	 * several times with the same parameter values. In this case several entries
	 * will be created. These parameters serve as a key for deleting the
	 * persisted triples.
	 * If both parameters are <null>, the parent-method is called and the
	 * triples will not be persisted.
	 * 
	 * @param string $component
	 * 		An arbitrary component identifier. A component must know how to
	 * 		generate unique IDs (see $id below) whereas other components can use
	 * 		the same IDs. This guarantees that triples from different components
	 * 		can be distinguished even if they use the same ID.
	 * @param string $id
	 * 		An ID for the triples that will be persisted. This ID is local to the
	 * 		given component.
	 *
	 * @return bool
	 * 		<true> if successful
	 * 		<false> if a SoapFault exception was catched
	 */
	public function flushCommands($component = null, $id = null) {
		
		if (!is_null($component) && !is_null($id)) {
			$trig  = $this->serializePrefixes();
			$trig .= $this->serializeAllTriples();
			
			// Write the triples in TriG format to the database
			$db = TSCStorage::getDatabase();
			$db->persistTriples($component, $id, $trig);
			
			// Reset the set of triples
			$this->mSerializedTriples = array();
		}
		return parent::flushCommands();
	}
	
	/**
	 * Deletes all persistent triples of the component $component with the
	 * ID $id from the database and from the triple store. This method will call
	 * flushCommands(). Make sure that no other commands are waiting to be flushed.
	 * 
	 * @param string $component
	 * 		Component to which the triples belong
	 * @param string $id
	 * 		ID with respect to the component. If the ID is <null>, all triples
	 * 		of the component are deleted.
	 */
	public function deletePersistentTriples($component, $id = null) {
		$db = TSCStorage::getDatabase();
		// Get all TriG serializations from the persistency layer
		$trigs = $db->readPersistentTriples($component, $id);
		if (count($trigs) > 0) {
			// Send all TriG serializations to the triple store who will delete them
			foreach ($trigs as $t) {
				// Escape all quotation marks in the TriG serialization.
				$t = str_replace("\\", "\\\\", $t);
				$t = str_replace("\"", "\\\"", $t);
				$t = str_replace("\n", "\\n", $t);
				$t = str_replace("\r", "\\r", $t);
				$this->mCommands[] = "DELETE DATA \"$t\"";
			}
			parent::flushCommands();
		}
				
		$db->deletePersistentTriples($component, $id);
		
	}


	//--- Private methods ---

	/**
	 * Serializes the prefixes that were added since the last flush.
	 * @return string
	 * 		All prefixes in TriG format. 
	 */
	private function serializePrefixes() {
		// Transform prefixes to TriG format
		$prefixes = $this->getPrefixes();
		$numPrefixes = preg_match_all("/PREFIX\s*([^\s]*?):\s*<([^\s]*?)>/", $prefixes, $matches);
		
		$trig = "";
		for ($i = 0; $i < $numPrefixes; ++$i) {
			$trig .= "@prefix {$matches[1][$i]}: <{$matches[2][$i]}> .\n";
		}
		return $trig."\n";
	}
	
	/**
	 * Serializes the given triples for the given graph as TriG and stores them
	 * in $mSerializedTriples.
	 * 
	 * See http://www4.wiwiss.fu-berlin.de/bizer/TriG/
	 * 
	 * @param string $graph
	 * 		The name of the graph to which the triples are added.
	 * @param array<TSCTriple> $triples
	 * 		An array of triple descriptions.
	 * 
	 * @return string
	 * 		The serialized triples.
	 * 
	 */
	private function serializeTriplesForGraph($graph, array $triples) {
		
		// Add graph
		$trig = @$this->mSerializedTriples[$graph];
		if (!isset($trig)) {
			$trig = "";
		}
		
		// Add triples
		foreach ($triples as $t) {
			// The TriG format of triples is the same as for SPARUL.
			$trig .= "\t".$t->toSPARUL()." \n";
		}
		
		// close graph
		$this->mSerializedTriples[$graph] = $trig;
	}
	
	/**
	 * Serializes all triples in all graphs in TriG format. Graphs and their
	 * triples are stored in the field $mSerializedTriples.
	 */
	private function serializeAllTriples() {
		
		$trig = "";
		
		foreach ($this->mSerializedTriples as $graph => $triples) {
			// add the graph name
			$trig .= (preg_match("~^http://~i", $graph))
						? "<$graph>"
						: $graph;
			$trig .= " {\n$triples}\n\n";
		}
		return $trig;
	}

}

