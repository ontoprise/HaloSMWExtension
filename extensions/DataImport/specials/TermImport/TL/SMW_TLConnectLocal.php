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
 * Implementation of the Transport Layer (TL) that is part of the term import feature.
 * This TL receives terms in an XML format from a local the data access layer 
 * module (DAL) and passes them to the Wiki Import Layer (WIL).
 * 
 * @author Thomas Schweitzer
 */

global $smwgDIIP;
require_once($smwgDIIP . '/specials/TermImport/SMW_ITL.php');

if ( !defined( 'TERM_IMPORT_PATH' ) ) {
	define ('TERM_IMPORT_PATH', $smwgDIIP.'/specials/TermImport/');
}
define ('DAL_MODULE_CFG', TERM_IMPORT_PATH.'TL/ConnectLocalDAL.cfg');

class TLConnectLocal implements ITL {
	
//--- Fields ---

	// The connected transport layer module of type <ITL>
	private $connectedDAL;

//--- Public methods ---

	/**
	 * Constructor of class TLConnectLocal.
	 *
	 */
	function __construct() {
	}
	
	/**
	 * Returns a list of module IDs with corresponding user readable descriptions
	 * of modules in the Data Access Layer that can be connected to this TL module.
	 * See SMW_ITL.php for further details.
	 *
	 * @return string
	 * 		XML structure with module IDs and description or <null> if an error 
	 * 		occurrs.
	 */
	public function getDALModules() {
		$path = DAL_MODULE_CFG;
		$cfg = fopen(DAL_MODULE_CFG, 'r');
		if ($cfg !== FALSE) {
			$data = fread($cfg, filesize (DAL_MODULE_CFG));
			fclose($cfg);
			return $data;
		}
		return null;
		
	}
  
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
	public function connectDAL($moduleID, &$moduleDesc) {
    	global $smwgDIIP;
		require_once($smwgDIIP . '/specials/TermImport/SMW_XMLParser.php');
		
		$retVal = 'true';
		$parser = new XMLParser($moduleDesc);
		$result = $parser->parse();
    	if ($result !== TRUE) {
			$msg = $result;
			$retVal = 'false';
		} else {
			$moduleSpec = $parser->findElementWithContent('ID', $moduleID);
			if ($moduleSpec) {
				$className = $moduleSpec['CLASS']['value'];		
				$file = $moduleSpec['FILE']['value'];		
				$inc = TERM_IMPORT_PATH . $file . '.php';
				if (include_once $inc) {
		    		$this->connectedDAL = new $className;
		    		$msg = wfMsg('smw_ti_succ_connected', $moduleID);
		    	} else {
		    		$msg = wfMsg('smw_ti_class_not_found', $className);
		    	}
			} else {
				$msg = wfMsg('smw_ti_no_tl_module_spec', $moduleID);
				$retVal = 'false';
			}
		}
		
		return  '<?xml version="1.0"?>'."\n".
 				'<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">'."\n".
     			'<value>'.$retVal.'</value>'."\n".
     			'<message>'.$msg.'</message>'."\n".
 				'</ReturnValue >'."\n";
		
	}
	
	/**
	 * Passes a callback function to the connected DAL
	 *  
	 * @param string $signature
	 * @param string mappingPolicy
	 * @parameter boolean conflictPolicy
	 * @return true or string if an error occured
	 *
	 */
	public function executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName) {
		if ($this->connectedDAL) {
			return $this->connectedDAL->executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName);
		}
		return false;
	}
	
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details.
	 * 
	 * @return string or <null> if no DAL module is connected
	 * 
	 */
	public function getSourceSpecification() {
		if ($this->connectedDAL) {
			return $this->connectedDAL->getSourceSpecification();
		}
		return null;
		
	}
     
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details.
	 * 
	 * @param string $dataSourceSpec:
	 * @return string or <null> if no DAL module is connected
	 *
	 */
	public function getImportSets($dataSourceSpec) {
		if ($this->connectedDAL) {
			return $this->connectedDAL->getImportSets($dataSourceSpec);
		}
		return null;
		
	}
     
	/**
	 * This call is handed down to the corresponding method of the connected 
	 * module in the DAL. See SMW_IDAL.php for further details. 
	 *     
	 * @param string $dataSourceSpec 
     * @param string $importSet 
     * @return string or <null> if no DAL module is connected
	 *
	 */
	public function getProperties($dataSourceSpec, $importSet) {
		if ($this->connectedDAL) {
			return $this->connectedDAL->getProperties($dataSourceSpec, $importSet);
		}
		return null;
		
	}
	
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
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		if ($this->connectedDAL) {
			return $this->connectedDAL
			            ->getTermList($dataSourceSpec, $importSet, $inputPolicy);
		}
		return null;
		
	}
	
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
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		if ($this->connectedDAL) {
			return $this->connectedDAL
			            ->getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy);
		}
		return null;
		
	}
		
}