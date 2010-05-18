<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the Data Import-Extension.
*
*   The Data Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
 * @ingroup DIWebServices
 * Interface for the access to web services.
 * 
 * @author Thomas Schweitzer
 * 
 */
interface IWebServiceClient {
	
	//todo: i have outcommented methods that are not available for RESTful web services
	
	//--- Public functions ---
	
	/**
	 * Returns an array with the names of all operations of the web service.
	 *
	 * @return array<string>
	 * 		Names of all available operations.
	 */
	//public function getOperations();

	/**
	 * Returns the definition of an operation of the web service with its 
	 * result and parameters.
	 *
	 * @param string $opName
	 * 		Name of the requested operation.
	 * @return array
	 * 		Definition of the operation or 
	 *      <null>, if it is not provided by the service.
	 * 		The first element of the array contains the type name of the result. 
	 *      The following elements are arrays with two elements for the parameters. 
	 *      The first element is the parameter name, the second is its type.
	 *      Example:
	 * 		0: ResultType
	 *      1: (param1, TypeOfParam1)   
	 *      2: (param2, TypeOfParam2)
	 *    
	 */
	//public function getOperation($opName);
	
	
	/**
	 * Checks if the type with the given name is a custom type i.e. if it is
	 * defined in the WSDL file.
	 *
	 * @param string $typename
	 * 		Name of the type. Its namespace is stripped
	 * @return bool
	 * 		<true>, if the type is defined in the WSDL
	 * 		<false> otherwise.
	 */
	//public function isCustomType($typename);
	
	/**
	 * Tries to find the definitions of types in the WSDL.
	 * 
	 * 
	 * @param string $typename
	 * 		Name of the type whose definition is requested. Its namespace is stripped.
	 * 
	 * @return array(fieldname=>type) 
	 * 		The type's definition in an associative array that maps the fields
	 * 		of a (complex) type to its data type. If the type does not exist,
	 *      <null> is returned.
	 * 
	 */
	//public function getTypeDefinition($typename);
	
	/**
	 * Calls the web service
	 *
	 */
	public function call($operationName, $parameters);
	
	/**
	 * Return the URI of the WS
	 *
	 */
	public function getURI();
	
}