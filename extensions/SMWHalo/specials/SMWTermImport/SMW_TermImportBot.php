<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Author: Thomas Schweitzer
 */

global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");

/**
 * This bot imports terms of an external vocabulary.
 *
 */
class TermImportBot extends GardeningBot {


	function __construct() {
		parent::GardeningBot("smw_termimportbot");
	}

	public function getHelpText() {
		return wfMsg('smw_gard_termimportbothelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function allowedForUserGroups() {
		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
	}

	/**
	 * Returns an array of parameter objects
	 */
	public function createParameters() {
	
		$params = array();
		
		return $params;
	}

	/**
	 * This method is called by the bot framework. $paramArray contains the
	 * name of a temporary file that contains the settings for the import.
	 */
	public function run($paramArray, $isAsync, $delay) {
		echo "...started!\n";
		$result = "";
		
		$filename = $paramArray["settings"];
		$settings = file_get_contents($filename);
		unlink($filename);
				
		$this->importTerms($settings);
		
		return $result;

	}
	
	/**
	 * This function sets up the modules of the import framework according to the 
	 * settings, reads the terms and creates articles for them.
	 *
	 * @param string $settings
	 * 		This XML string contains the modules (Transport Layer, Data Access Layer),
	 * 		the data source, the import sets and the input, mapping and conflict
	 * 		policy. 
	 * 
	 * @return mixed (boolean, string)
	 * 		<true>, if all terms were successfully imported or an
	 * 		error message, otherwise.
	 * 
	 */
	public function importTerms($settings) {
		global $smwgHaloIP;
		require_once($smwgHaloIP . '/specials/SMWTermImport/SMW_XMLParser.php');
		
		$parser = new XMLParser($settings);
		$result = $parser->parse();
		if ($result !== TRUE) {
			return $result;
		}
		
		$tlModule  = $parser->getValuesOfElement(array('TLModules', 'Module', 'id'));
		//TODO Fehlerbehandlung
		$dalModule = $parser->getValuesOfElement(array('DALModules', 'Module', 'id'));
		//TODO Fehlerbehandlung
		
		global $smwgHaloIP;
		require_once($smwgHaloIP . '/specials/SMWTermImport/SMW_WIL.php');
		$wil = new WIL();
		$tlModules = $wil->getTLModules();
	
		$res = $wil->connectTL($tlModule[0], $tlModules);
		//TODO Fehlerbehandlung
		$dalModules = $wil->getDALModules();
		$res = $wil->connectDAL($dalModule[0], $dalModules);
		//TODO Fehlerbehandlung
		
		$source = $parser->serializeElement(array('DataSource'));
		$importSets = $parser->serializeElement(array('ImportSets'));
		$inputPolicy = $parser->serializeElement(array('InputPolicy'));
		
		$terms = $wil->getTerms($source, $importSets, $inputPolicy);
		
		$mappingPolicy = $parser->serializeElement(array('MappingPolicy'));
		$conflictPolicy = $parser->serializeElement(array('ConflictPolicy'));
		
		$this->createArticles($terms, $mappingPolicy, $conflictPolicy);
		
	}
	
	/**
	 * Creates articles for the terms according to the mapping and conflict policy.
	 *
	 * @param string $terms
	 * 		This XML string contains all terms that the DAL delivered
	 * @param string $mappingPolicy
	 * 		This XML string contains the name of an article that is a template
	 * 		for the articles that will be created.
	 * @param string $conflictPolicy
	 * 		This XML string specifies, if existing articles will be overwritten.
	 * @return mixed (boolean, string)
	 * 		<true>, if all terms were successfully imported or an
	 * 		error message, otherwise.
	 * 
	 */
	private function createArticles($terms, $mappingPolicy, $conflictPolicy) {

		global $smwgHaloIP;
		require_once($smwgHaloIP . '/specials/SMWTermImport/SMW_XMLParser.php');
		
		$parser = new XMLParser($mappingPolicy);
		$result = $parser->parse();
		if ($result !== TRUE) {
			return $result;
		}
		$mp = $parser->getValuesOfElement(array('MappingPolicy','page'));
		if (!is_array($mp) || !$mp[0]) {
			return wfMsg('smw_ti_missing_mp');
		}
		$mp = $mp[0];
		// get the content of the article that contains the mapping policy
		$mp = strip_tags($mp);
		if ($mp == '') {
			return wfMsg('smw_ti_missing_mp', $mp);
		}
		
		$mp = Title::newFromText($mp);
		$mp = new Article($mp);
		$mp = $mp->getContent();		
		
		$parser = new XMLParser($conflictPolicy);
		$result = $parser->parse();
		if ($result !== TRUE) {
			return $result;
		}
		$cp = $parser->getValuesOfElement(array('ConflictPolicy','overwriteExistingTerms'));
		$cp = $cp[0];
		$cp = strtolower($cp) == 'true' ? true : false;
		
		$parser = new XMLParser($terms);
		$result = $parser->parse();
		if ($result !== TRUE) {
			return $result;
		}
		
		$nextElem = 0;
		while (($term = $parser->getElement(array('terms', 'term'), $nextElem))) {
			$caResult = $this->createArticle($term, $mp, $cp);
		}
	}
	
	/**
	 * Creates an article for the given term according to the mapping and 
	 * conflict policy.
	 *
	 * @param array $term
	 * 		This parsed XML structure contains the description of one term.
	 * @param string $mappingPolicy
	 * 		The content of the article that is a template for the articles that 
	 * 		will be created.
	 * @param boolean $overwriteExistingArticle
	 * 		Specifies, if existing articles will be overwritten:
	 * 		<true> => overwrite, <false> => skip article
	 * @return mixed (boolean, string)
	 * 		<true>, if the term was successfully imported or an
	 * 		error message, otherwise.
	 */
	private function createArticle(&$term, $mappingPolicy, $overwriteExistingArticle) {
		$title = $term['ARTICLENAME'];
		if (is_array($title)) {
			$title = $title[0];
		}
		if (!$title) {
			return wfMsg('smw_ti_missing_articlename');
		}
		$title = strip_tags($title);
		if ($title == '') {
			return wfMsg('smw_ti_invalid_articlename', $title);
		}
		
		$title = Title::newFromText($title);
		$article = new Article($title);
		
		// Check if the article already exists
		if ($article->exists()) {
			// The article exists
			// Can an existing article be overwritten?
			if (!$overwriteExistingArticle) {
				return wfMsg('smw_ti_articleNotUpdated', $title);
			}
		}
		
		// Create the content of the article based on the mapping policy
		$content = $this->createContent($term, $mappingPolicy);
		$content = $term['CONTENT'];
		if (is_array($content)) {
			$content = $content[0];
		}
		
		// Create/update the article
		$success = $article->doEdit($content, wfMsg('smw_ti_creationComment'));
		if (!$success) {
			return wfMsg('smw_ti_creationFailed', $title);
		}
		
		return true;
	}
	
	/**
	 * Creates the content of an article based on the description of the term and
	 * the mapping policy.
	 *
	 * @param array $term
	 * 		The XML structure of the term encoded in nested arrays.
	 * @param string $mappingPolicy
	 * 		The mapping policy as content of the corresponding article.
	 * 
	 * @return string
	 * 		The content of the article.
	 */
	private function createContent(&$term, $mappingPolicy) {
		
	}
}

// Create one instance to register the bot.
new TermImportBot();
?>
