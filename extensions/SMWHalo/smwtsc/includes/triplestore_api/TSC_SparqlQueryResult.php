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
 * This file contains several classes for managing query results:
 * - TSCSparqlQueryResult   : The complete result of a query
 * - TSCSparqlResultRow     : One row in the result.
 * - TSCSparqlResult        : The abstract base class of a single result value
 * - TSCSparqlResultURI     : Representation of a result value which is a URI
 * - TSCSparqlResultLiteral : Representation of a result value which is a literal
 *
 * @author Thomas Schweitzer
 * Date: 30.04.2010
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * This class stores a complete query result. It contains the names of the
 * variables that are bound and a set of (table) rows that contain the actual
 * result values.
 *
 * A query result can be imagined as a table with a column for each variable and
 * a row for each binding of these variables.
 *
 * @author Thomas Schweitzer
 *
 */
class TSCSparqlQueryResult {

	// array<TSCSparqlResultRow>
	// All rows of the result.
	private $mRows = array();

	// array<string>
	// The names of all variables in the result
	private $mVariables = array();

	/**
	 * Returns all rows of the result.
	 *
	 * @return array<TSCSparqlResultRow>
	 * 		An array of rows.
	 */
	public function getRows() {
		return $this->mRows;
	}

	/**
	 * Returns all rows of the result, where $variable has the given $value.
	 *
	 * @param string $variable
	 * 		Name of the variable
	 * @param string $value
	 * 		String representation of the expected value
	 * @return array<TSCSparqlResultRow>
	 * 		An array of rows where the condition is matched. The array is empty
	 * 		if there are no matching results.
	 */
	public function getRowsWhere($variable, $value) {
		$result = array();
		foreach ($this->mRows as $row) {
			$qr = $row->getResult($variable);
			if ($qr && $qr->getValue() == $value) {
				$result[] = $row;
			}
		}
		return $result;
	}

	/**
	 * Returns all variables.
	 *
	 * @return array<string>
	 * 		All variables
	 */
	public function getVariables() {
		return $this->mVariables;
	}

	/**
	 * Adds a variable. This function is called from classes that assemble this
	 * result object.
	 *
	 * @param string $variable
	 * 		The name of the variable.
	 */
	public function addVariable($variable) {
		$this->mVariables[] = $variable;
	}

	/**
	 * Adds a row of results. This function is called from classes that assemble
	 * this result object.
	 *
	 * @param TSCSparqlResultRow $row
	 * 		One row of results.
	 */
	public function addRow(TSCSparqlResultRow $row) {
		$this->mRows[] = $row;
	}

	/**
	 * Converts this result to a table i.e. an array of arrays. The outer array
	 * represents the rows and the inner the values for each column. The columns
	 * are sorted according to the order of variables as returned by getVariables().
	 * Each value is a string.
	 *
	 * @return array<array<string>>
	 * 		All results as a table.
	 */
	public function toTable() {
		$table = array();
		foreach ($this->mRows as $row) {
			$r = array();
			foreach ($this->mVariables as $v) {
				$field = $row->getResult($v);
				$r[] = $field->getValue();
			}
			$table[] = $r;
		}
		return $table;
	}
}


/**
 * This class represents a row in a query result. A row is an array of instances
 * of class TSCSparqlResult. The keys in this array are the names of the variables.
 *
 * @author Thomas Schweitzer
 *
 */
class TSCSparqlResultRow {
	// array<string => TSCSparqlResult>
	// All results of a row.
	private $mResults = array();

	/**
	 * Returns the results of the complete row.
	 *
	 * @return array<string => TSCSparqlResult>
	 */
	public function getResults() {
		return $this->mResults;
	}

	/**
	 * Returns the result for the given $variable.
	 *
	 * @param string $variable
	 * 		Name of the variable whose value will be returned.
	 * @return TSCSparqlResult
	 * 		The requested result or
	 * 		<null> if the variable is not bound
	 */
	public function getResult($variable) {
		return array_key_exists($variable, $this->mResults)
		? $this->mResults[$variable]
		: null;
	}

	/**
	 * Adds a result for the given $variable. This function is called from
	 * classes that assemble this result object.
	 *
	 * @param string $variable
	 * 		Name of the variable
	 * @param TSCSparqlResult $result
	 * 		This object describes the result.
	 */
	public function addResult($variable, TSCSparqlResult $result) {
		$this->mResults[$variable] = $result;
	}
}


/**
 * This is the abstract base class for a single result value of a SPARQL query.
 * It consists of a variable name and a value.
 *
 * @author Thomas Schweitzer
 *
 */

abstract class TSCSparqlResult {

	// string:
	//Name of the variable that contains the result
	private $mVariableName;

	// string
	// The value of the result.
	private $mValue;

	// metadata hashmap. Maps (pre-defined) metadata properties to values.
	private $mMetadata;

	/**
	 * Returns the data type of the value.
	 * @return string
	 * 		The data type of the value. 
	 * 
	 */
	abstract public function getDatatype();
	
	// Returns the name of the variable
	public function getVariableName() {
		return $this->mVariableName;
	}

	// Returns the value
	public function getValue() {
		return $this->mValue;
	}	
    
	// Returns a metadata property value
	public function getMetadataValue($metadataProperty) {
		return array_key_exists($metadataProperty, $this->mMetadata[$metadataProperty]) ? $this->mMetadata[$metadataProperty] : NULL;
	}
	
	// Returns metadata hash map
	public function getMetadata() {
		return $this->mMetadata;
	}

	/**
	 * Creates a new result object. As this class is abstract, it can only be
	 * called from sub-classes.
	 *
	 * @param string $name
	 * 		Name of the variable
	 * @param string $value
	 * 		Value of the variable
	 */
	protected function __construct($name, $value, $metadata = array()) {
		$this->mVariableName = $name;
		$this->mValue = $value;
		$this->mMetadata = $metadata;
	}
}

/**
 * This class represent a URI which is a result of a SPARQL query.
 *
 */
class TSCSparqlResultURI extends TSCSparqlResult{

	/**
	 * Creates a URI result.
	 *
	 * @param string $name
	 * 		The name of the variable that binds the result.
	 * @param string $uri
	 * 		The URI that is bound by the variable.
	 */
	public function __construct($name, $uri, $metadata = array()) {
		parent::__construct($name, $uri, $metadata);
	}

	/**
	 * Returns the data type of the URI.
	 * @return string
	 * 		The data type of the value i.e. http://www.w3.org/2001/XMLSchema#anyURI
	 * 
	 */
	public function getDatatype() {
		return "http://www.w3.org/2001/XMLSchema#anyURI";
	}
	
}

/**
 * This class represent a literal which is a result of a SPARQL query. A literal
 * can have any data type and a language specification.
 *
 */
class TSCSparqlResultLiteral extends TSCSparqlResult {
	// string (uri)
	// The datatype of the literal which is typically a URI like xsd:string or
	// http://www.w3.org/2001/XMLSchema#string
	private $mDatatype;

	// string
	// Language code of the literal e.g. "en" or "de"
	private $mLanguage;

	/**
	 * Creates a literal result.
	 *
	 * @param string $name
	 * 		Name of the variable that binds the value.
	 * @param string $value
	 * 		String representation of the value.
	 * @param string $datatype
	 * 		Data type of the value.
	 * @param string $language
	 * 		Language code of the value.
	 */
	public function __construct($name, $value, $datatype = null, $language = null, $metadata = array()) {
		parent::__construct($name, $value, $metadata);
		$this->mDatatype = $datatype;
		$this->mLanguage = $language;
	}
	
	
	/**
	 * Returns the data type of the literal value.
	 * @return string
	 * 		The data type of the value.
	 * 
	 */
	public function getDatatype() {
		return $this->mDatatype;
	}
   
}

