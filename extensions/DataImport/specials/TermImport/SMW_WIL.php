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
 * Implementation of the Wiki Import Layer (WIL) that is part of the term import
 * feature.
 * The WIL receives terms in an XML format from the transport layer and generates
 * articles for the terms.
 *
 * @author Thomas Schweitzer
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $IP, $smwgDIIP;
require_once($smwgDIIP . '/specials/TermImport/SMW_IWIL.php');

if ( !defined( 'TERM_IMPORT_PATH' ) ) {
	define ('TERM_IMPORT_PATH', $smwgDIIP.'/specials/TermImport/');
}
define ('TL_MODULE_CFG', TERM_IMPORT_PATH.'TL/TransportLayer.cfg');

class WIL implements IWIL {

	//--- Fields ---

	// The connected transport layer module of type <ITL>
	private $connectedTL;

	//--- Public methods ---

	/**
	 * Constructor of class WIL.
	 *
	 */
	function __construct() {
	}

	/**
	 * Returns a list of module IDs with corresponding user readable descriptions
	 * of modules in the Transport Layer that can be connected to WIL.
	 * (An administrator can provide the TL modules and the user can select one.)
	 *
	 * @return string :
	 * 		XML structure with module IDs and description. It has the following
	 * 		format:
	 * 		<?xml version="1.0"?>
	 * 		<TLModules xmlns="http://www.ontoprise.de/smwplus#">
	 * 			<Module>
	 * 				<id>id of the module e.g. Connect Local</id>
	 * 		    	<desc>description e.g. This module connects the wiki to local
	 *                                     DAL modules.</desc>
	 * 				<!--
	 *              There may be further XML elements that are ignored by the
	 * 				WIL. However, this whole <Module> description is passed to
	 * 				subsequent functions. So it may contain further information
	 * 				needed by the module.
	 * 				-->
	 * 			</Module>
	 *		    <!-- ... further Module elements ... -->
	 * 		</TLModules >
	 *
	 * 		If no modules are available or if an error occurs, this method
	 * 		returns <null>.
	 */
	public function getTLModules() {
		$path = TL_MODULE_CFG;
		$cfg = fopen(TL_MODULE_CFG, 'r');
		if ($cfg !== FALSE) {
			$data = fread($cfg, filesize (TL_MODULE_CFG));
			fclose($cfg);
			return $data;
		}
		return null;
	}

	/**
	 * Establishes a connection to the TL module with the given ID according to
	 * the module description.
	 *
	 * @param string $moduleID
	 * 		The ID of a module that is specified in the following description e.g.
	 * 		"ConnectLocal"
	 * @param string $moduleDesc
	 * 		Description of (several) TL modules as XML structure as returned by
	 *      getTLModules().
	 * @return string
	 * 		<true> if the connection was successfully established
	 *		<false> and an error message otherwise. This is contained in an XML
	 * 		structure.
	 *
	 *		Example:
	 *		<?xml version="1.0"?>
	 *		<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 *			<value>true</value>
	 *			<message>Successfully connected to module "ConnectLocal".</message>
	 *		</ReturnValue >
	 */
	public function connectTL($moduleID, &$moduleDesc) {
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
					$this->connectedTL = new $className;
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
	 * Returns a list of module IDs with corresponding user readable descriptions
	 * of modules in the Data Access Layer. The call is handed down to the
	 * Transport Layer. See SMW_ITL.php for further details.
	 *
	 * @return string
	 * 		Module descriptions in an XML structure or <null> if an error occurs.
	 */
	public function getDALModules() {
		if ($this->connectedTL) {
			return $this->connectedTL->getDALModules();
		}
		return null;
	}

	/**
	 * Establishes a connection to the DAL module with the given ID.
	 * The call is handed down to the Transport Layer. See SMW_ITL.php for
	 * further details.
	 *
	 * @param string $moduleID
	 * @param string $moduleDesc
	 *
	 * @return string or <null> if no TL module is connected
	 *
	 */
	public function connectDAL($moduleID, &$moduleDesc) {
		if ($this->connectedTL) {
			return $this->connectedTL->connectDAL($moduleID, $moduleDesc);
		}
		return null;
	}

	/**
	 * This call is handed down to the corresponding method of the connected
	 * module in the TL. See SMW_ITL.php for further details.
	 *
	 * @return string or <null> if no TL module is connected
	 *
	 */
	public function getSourceSpecification() {
		if ($this->connectedTL) {
			return $this->connectedTL->getSourceSpecification();
		}
		return null;

	}
	 
	/**
	 * This call is handed down to the corresponding method of the connected
	 * module in the TL. See SMW_ITL.php for further details.
	 *
	 * @param string $dataSourceSpec
	 * @return string or <null> if no TL module is connected
	 *
	 */
	public function getImportSets($dataSourceSpec) {
		if ($this->connectedTL) {
			return $this->connectedTL->getImportSets($dataSourceSpec);
		}
		return null;

	}
	
/**
	 * This call is handed down to the corresponding method of the connected
	 * module in the TL. See SMW_ITL.php for further details.
	 *
	 * @param string $signature
	 * @param string mappingPolicy
	 * @string conflictPolicy
	 * @return true or string if an error occured
	 *
	 */
	public function executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName) {
		if ($this->connectedTL) {
			return $this->connectedTL->executeCallBack($signature, 
				$mappingPolicy, $conflictPolicy, $termImportName);
		}
		return false;

	}
	
	 
	/**
	 * This call is handed down to the corresponding method of the connected
	 * module in the TL. See SMW_ITL.php for further details.
	 * @param string $dataSourceSpec
	 * @param string $importSet
	 * @return string or <null> if no TL module is connected
	 *
	 */
	public function getProperties($dataSourceSpec, $importSet) {
		if ($this->connectedTL) {
			return $this->connectedTL->getProperties($dataSourceSpec, $importSet);
		}
		return null;

	}

	/**
	 * This call is handed down to the corresponding method of the connected
	 * module in the TL.
	 * Returns a list of the names of all terms that match the input policy.
	 * See SMW_ITL.php for further details.
	 *
	 * @param string $dataSourceSpec
	 * @param string $importSet
	 * @param string $inputPolicy
	 *
	 * @return string or <null> if no TL module is connected
	 *
	 */
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		if ($this->connectedTL) {
			return $this->connectedTL
			->getTermList($dataSourceSpec, $importSet, $inputPolicy);
		}
		return null;

	}

	/**
	 * This call is handed down to the corresponding method of the connected
	 * module in the TL. See SMW_IDAL.php for further details.
	 * Generates the XML description of all terms in the data source that match
	 * the input policy.
	 * This method is used by the import bot that is started by <importTerms()>.
	 *
	 * @param string $dataSourceSpec
	 * @param string $importSet
	 * @param string $inputPolicy
	 * @param string $conflictPolicy
	 * 	 *
	 * @return string or <null> if no TL module is connected
	 *
	 */
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		if ($this->connectedTL) {
			return $this->connectedTL
			->getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy);
		}
		return null;
	}

	/**
	 * Imports the vocabulary according to the given policies. The content of the
	 * wiki is updated. This method starts a bot with the following parameters:
	 *
	 * @param string $moduleConfig
	 * 		This XML structure describes the modules of the TL and DAL that are
	 * 		needed for the actual import.
	 * 		Example:
	 * 		<?xml version="1.0"?>
	 *		<ModuleConfiguration xmlns="http://www.ontoprise.de/smwplus#">
	 *			  <TLModules>
	 *			    <Module>
	 *			        <id>ConnectLocal</id>
	 *			    </Module>
	 *			  </TLModules >
	 *			  <DALModules>
	 *			    <Module>
	 *			        <id>ReadCSV</id>
	 *			    </Module>
	 *			  </DALModules >
	 *			</ModuleConfiguration>
	 *
	 * @param string $dataSource
	 * 		The description of the data source as returned by
	 * 		<getSourceSpecification()> and filled with the user's values.
	 *
	 * @param string $importSet:
	 * 		One of the import sets that can be retrieved with getImportSet() or
	 * 		empty. The complete XML element <importSet> as specified above is
	 * 		passed as it may contain values besides <name> and <desc>.
	 *
	 * @param string $inputPolicy
	 * 		The input policy as an XML structure.
	 *		The input policy defines which parts of which terms are imported.
	 *
	 * 		Specification of terms to import
	 *		The set of terms to import can be restricted to a list of given items
	 * 		or the terms that match a regular expression or both. Both restrictions
	 * 		complement each other i.e. a term is imported if it is part of the
	 * 		list or matches the regular expression.
	 * 		Example: [Aa]* imports all terms that start with the letter A.
	 *
	 * 		Specification of the data to import
	 * 		The definition of each term consists of several properties. Each property
	 * 		of the definition is given as an XML element. A lists of properties
	 * 		controls which parts are imported. The list must contain at least
	 * 		the <articleName>. If the ontological properties are omitted, the
	 * 		term can not be placed correctly in the wiki's ontology.
	 *
	 * 		Example:
	 *		<?xml version="1.0"?>
	 *		<InputPolicy xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <terms>
	 *		        <regex>[Aa]*</regex>
	 *		        <regex>[Bb]*</regex>
	 *		        <term>Rabbit</term>
	 *		        <term>Fox</term>
	 *		    </terms>
	 *		    <properties>
	 *		       <property>articleName</property>
	 *		       <property>content</property>
	 *		       <property>isSubCategoryOf</property>
	 *		    </properties>
	 *		</InputPolicy >
	 *
	 * @param string $mappingPolicy
	 * 		The mapping policy is like a template for the articles that are
	 * 		created for terms. The policy is stored as a normal page in the wiki.
	 * 		The properties of the imported term appear like template parameters
	 * 		in a special mapping tag in the page.
	 * 		The definition of the mapping policy is defined as XML structure. It
	 * 		contains the name of the page with the policy.
	 *
	 * 		Example:
	 *		<?xml version="1.0"?>
	 *		<MappingPolicy xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <page>TermImport</page>
	 *		</MappingPolicy >
	 *
	 * @param string $conflictPolicy
	 * 		Import jobs can be run several times. A conflict occurs when a term
	 * 		for an already existing article is imported. The conflict policy
	 * 		controls if the article is overwritten or if the new version is
	 * 		ignored.
	 * 		The definition of the conflict policy is defined as XML structure:
	 * 		Example:
	 * 		<?xml version="1.0"?>
	 * 		<ConflictPolicy xmlns="http://www.ontoprise.de/smwplus#">
	 * 		    <overwriteExistingTerms>true</overwriteExistingTerms>
	 * 		</ConflictPolicy >
	 * 		The allowed values for <overwriteExistingTerms> are true and false.
	 *
	 * @return string
	 * 		An XML structure is returned. Its value is <true>, if the bot was
	 * 		successfully started. Otherwise <false> and	an error message are
	 * 		returned.
	 * 		Example:
	 *		<?xml version="1.0"?>
	 *		<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <value>true</value>
	 *		    <message>The import bot was successfully started.</message>
	 *		</ReturnValue >
	 */
	public function importTerms($moduleConfig, $dataSource, $importSet, $inputPolicy,
			$mappingPolicy, $conflictPolicy, $termImportName, $async = true) {
		global $smwgDIIP, $wgUploadDirectory;
		require_once("$smwgDIIP/specials/TermImport/SMW_TermImportBot.php");

		$result = "true";
		$msg = "";

		$settings = "\n<ImportSettings>\n"
		.$moduleConfig."\n"
		.$dataSource."\n"
		.$importSet."\n"
		.$inputPolicy."\n"
		.$mappingPolicy."\n"
		.$conflictPolicy."\n"
		."</ImportSettings>";
		$settings = '<?xml version="1.0"?>'.
		str_replace('<?xml version="1.0"?>', "", $settings);
		
		// The parameters for the bot are passed in a temporary file that will
		// be deleted by the bot.
		$filename = "$wgUploadDirectory/importSettings_".microtime(true);

		if (file_put_contents($filename, $settings)) {
				
			if($async){
			  	$f = rawurlencode($filename);
				$param = "settings=$f,termImportName=".$termImportName;
				$taskID = GardeningBot::runBot('smw_termimportbot', $param);
				if (is_int($taskID)) {
					$msg = wfMsg('smw_ti_botstarted');
				} else {
					$msg = wfMsg('smw_ti_botnotstarted');
					$result = false;
				}
			} else {
				$param = array("settings" => $filename, "termImportName" => $termImportName);
				global $registeredBots;
				$bot = $registeredBots['smw_termimportbot'];
				$taskID = SGAGardeningLog::getGardeningLogAccess()->addGardeningTask('smw_termimportbot');
				$log = $bot->run($param, false, 0);
				$logPageTitle = $log[1];
				$log = $log[0];
				SGAGardeningLog::getGardeningLogAccess()->markGardeningTaskAsFinished(
					$taskID, $log, $logPageTitle);
				
				//todo:error handling
				return $log;
			}
		} else {
			$msg = wfMsg('smw_ti_couldnotwritesettings');
			$result = false;
		}
		return '<?xml version="1.0"?>'."\n".
	 			'<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">'."\n".
	 			"	<value>$result</value>\n".
	 			"	<message>$msg</message>\n".
	 			'</ReturnValue>';

	}

	 
	//--- Protected methods ---

	//--- Private methods ---

}