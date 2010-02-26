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
 * @ingroup DITermImport
 * 
 * @author Thomas Schweitzer
 */

/**
 * Interface of the Transport Layer (TL) that is part of the term import feature.
 * The TL receives terms in an XML format from the data access layer (DAL) and 
 * passes them to the Wiki Import Layer (WIL).
 * 
 * @author Thomas Schweitzer
 */

interface ITL {
	
	/**
	 * Returns a list of module IDs with corresponding user readable descriptions
	 * of modules in the Data Access Layer that can be connected to this TL module.
	 * (Available DAL module descriptions can be stored in a configuration file 
	 * for the TL module. The connection settings for the DAL modules should be 
	 * stored in this file as well. Thus, an administrator can provide the DAL 
	 * modules and the user can select one.)
	 *
	 * @return string
	 * 		XML structure with module IDs and description 
	 * 		Example:
	 * 		<?xml version="1.0"?>
	 *		<DALModules xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <Module>
	 *		        <id>ReadCSV</id>
	 *		        <desc>Reads a file of comma separated values.</desc>
   	 * 				<!--
   	 *              There may be further XML elements that are ignored by the
   	 * 				TL. However, this whole <Module> description is passed to
   	 * 				subsequent functions. So it may contain further information
   	 * 				needed by the module.
   	 * 				-->
	 * 		    </Module>
	 *		    <!-- ... further Module elements ... -->
	 *		</DALModules >
	 * 		
	 * 		If an error occurrs, <null> is returned.
	 */
	public function getDALModules();
  
	/**
	 * Establishes a connection to the DAL module with the given ID.
	 *  
	 * @param string $moduleID
	 * 		The ID of a module that is specified in the following description e.g.
	 * 		"ReadCSV"
	 *  
	 * @param string $moduleDesc
	 * 		Description of the DAL modules as XML structure as returned by 
	 * 		getDALModules().
	 * 
	 * @return string
	 * 		An XML structure with the value <true> if the connection was 
	 * 		successfully established, <false> and an error message otherwise. 
	 * 		Example:
	 * 		<?xml version="1.0"?>
	 *		<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <value>true</value>
	 *		    <message>Successfully connected to module "ReadCSV".</message>
	 *		</ReturnValue >
	 * 
	 */
	public function connectDAL($moduleID, &$moduleDesc);
	
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details.
	 * 
	 * @return string or <null> if no DAL module is connected
	 * 
	 */
	public function getSourceSpecification();
	
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details.
	 * 
	 * @parameter string $signature
	 * @param string mappingPolicy
	 * @parameter boolean conflictPolicy
	 * @return true or string if an error occured
	 *
	 */
	public function executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName);
	
     
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details.
	 * 
	 * @param string $dataSourceSpec:
	 * @return string or <null> if no DAL module is connected
	 *
	 */
	public function getImportSets($dataSourceSpec);
     
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details. 
	 *     
	 * @param string $dataSourceSpec 
     * @param string $importSet 
     * @return string or <null> if no DAL module is connected
	 *
	 */
	public function getProperties($dataSourceSpec, $importSet);
	
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details.
	 * Returns a list of the names of all terms that match the input policy. 
	 *
	 * @param string $dataSourceSpec
	 * @param string $importSet
	 * @param string $inputPolicy
	 * 
	 * @return string or <null> if no DAL module is connected
	 * 
	 */
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy);
	
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details.
	 * Generates the XML description of all terms in the data source that match 
	 * the input policy.
	 * 
	 * @param string $dataSourceSpec
     * @param string $importSet
     * @param string $inputPolicy
     * @param string $conflictPolicy
     * 
     * @return string or <null> if no DAL module is connected
	 *
	 */
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy);
	
}