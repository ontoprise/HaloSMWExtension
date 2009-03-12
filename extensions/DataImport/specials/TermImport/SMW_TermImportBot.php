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
 *
 *  Author: Thomas Schweitzer
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningIssues.php");
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
		//unlink($filename);
		
		$result = $this->importTerms($settings);

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
		
		echo "Starting to import terms...\n";
		
		global $smwgDIIP;
		require_once($smwgDIIP . '/specials/TermImport/SMW_XMLParser.php');
		
		$parser = new XMLParser($settings);
		$result = $parser->parse();
		if ($result !== TRUE) {
			return $result;
		}
		
		$tlModule  = $parser->getValuesOfElement(array('TLModules', 'Module', 'id'));
		if (count($tlModule) == 0) {
			return "Missing transport layer module.";
		}
		$dalModule = $parser->getValuesOfElement(array('DALModules', 'Module', 'id'));
		if (count($dalModule) == 0) {
			return "Missing data access layer module.";
		}
				
		global $smwgDIIP;
		require_once($smwgDIIP . '/specials/TermImport/SMW_WIL.php');
		$wil = new WIL();
		$tlModules = $wil->getTLModules();
		
		echo("\n wil connected");
		$res = $wil->connectTL($tlModule[0], $tlModules);
		if (stripos($res, '<value>true</value>') === false) {
			return "Connecting the transport layer module $tlModule[0] failed.";
		}
		$dalModules = $wil->getDALModules();
		$res = $wil->connectDAL($dalModule[0], $dalModules);
		if (stripos($res, '<value>true</value>') === false) {
			return "Connecting the data access layer module $dalModule[0] failed.";
		}
				
		$source = $parser->serializeElement(array('DataSource'));
		$importSets = $parser->serializeElement(array('ImportSets'));
		$inputPolicy = $parser->serializeElement(array('InputPolicy'));
		
		echo("\n get Terms");
		$terms = $wil->getTerms($source, $importSets, $inputPolicy);
		
		echo("\n Terms in place");
		
		$mappingPolicy = $parser->serializeElement(array('MappingPolicy'));
		$conflictPolicy = $parser->serializeElement(array('ConflictPolicy'));
		
		$result = $this->createArticles($terms, $mappingPolicy, $conflictPolicy);
		
		echo "Bot finished!\n";
		if ($result === true) {
			$result = wfMsg('smw_ti_import_successful');			
		}
		return $result;
		
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

		global $smwgDIIP;
		require_once($smwgDIIP . '/specials/TermImport/SMW_XMLParser.php');
		
		echo("\n start creating articles");
		
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
		
		echo("\n create xml parser");
		$parser = new XMLParser($terms);
		echo("\n parse xml");
		$result = $parser->parse();
		echo("\n xml parsed");
		if ($result !== TRUE) {
			echo("\n".$result."was not true");
			return $result;
		}
		
		// Count number of terms to import for the progress bar
		$nextElem = 0;
		$numTerms = 0;
		while (($term = $parser->getElement(array('terms', 'term'), $nextElem))) {
			++$numTerms;
			echo("\n".$numTerms);
		}
		echo "Number of terms: $numTerms \n";
		
		$this->setNumberOfTasks(1);
		$this->addSubTask($numTerms);
		
		$nextElem = 0;
		$noErrors = true;
		while (($term = $parser->getElement(array('terms', 'term'), $nextElem))) {
			$caResult = $this->createArticle($term['TERM'][0]['value'], $mp, $cp);
			$this->worked(1);
			
			if ($caResult !== true) {
				$noErrors = false;
			}
		}
		return $noErrors ? true : wfMsg('smw_ti_import_errors');
	}
	
	/**
	 * Creates an article for the given term according to the mapping and 
	 * conflict policy. The special ontology properties that can be defined for
	 * terms (sub-category etc.) are considered and used for creating corresponding 
	 * annotations.
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
 		$log = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
		
		$title = $term['ARTICLENAME'];
		if (is_array($title)) {
			$title = $title[0]['value'];
		}
		if (!$title) {
			$log->addGardeningIssueAboutArticle(
				$this->id, SMW_GARDISSUE_MISSING_ARTICLE_NAME, 
				Title::newFromText(wfMsg('smw_ti_import_error')));
			return wfMsg('smw_ti_missing_articlename');
		}
		$title = strip_tags($title);
		if ($title == '') {
			$log->addGardeningIssueAboutArticle(
				$this->id, SMW_GARDISSUE_MISSING_ARTICLE_NAME, 
				Title::newFromText(wfMsg('smw_ti_import_error')));
			return wfMsg('smw_ti_invalid_articlename', $title);
		}

		// Create the ontological properties
		list($ontoAnno, $namespace) = $this->createOntologyAnnotations($term);
		
		$articleName = $namespace.$title;
		$title = Title::newFromText($articleName);
		$article = new Article($title);
		
		$updated = false;
		// Check if the article already exists
		if ($article->exists()) {
			// The article exists
			// Can an existing article be overwritten?
			if (!$overwriteExistingArticle) {
				echo wfMsg('smw_ti_articleNotUpdated', $title)."\n";
				$log->addGardeningIssueAboutArticle($this->id, SMW_GARDISSUE_UPDATE_SKIPPED, $title);
				return wfMsg('smw_ti_articleNotUpdated', $title);
			}
			$updated = true;
		}
		
		// Create the content of the article based on the mapping policy
		$content = $this->createContent($term, $mappingPolicy);
		
		if (!empty($ontoAnno)) {
			$content .= "\n\n".$ontoAnno;
		}
		
		// Create/update the article
		$success = $article->doEdit($content, wfMsg('smw_ti_creationComment'));
		if (!$success) {
			$log->addGardeningIssueAboutArticle($this->id, SMW_GARDISSUE_CREATION_FAILED, $title);
			return wfMsg('smw_ti_creationFailed', $title);
		}
		
		echo "Article $articleName ";
		echo $updated==true ? "updated\n" : " created.\n";
		$log->addGardeningIssueAboutArticle(
				$this->id, 
				$updated == true ? SMW_GARDISSUE_UPDATED_ARTICLE 
				                 : SMW_GARDISSUE_ADDED_ARTICLE, 
				$title);
		
		return true;
	}
	
	/**
	 * Creates the content of an article based on the description of the term and
	 * the mapping policy.
	 * The method calls itself recursively. The further parameters ($offset, 
	 * $replace and $level) are only used in the recursion.
	 *
	 * @param array $term
	 * 		The XML structure of the term encoded in nested arrays.
	 * @param string $mappingPolicy
	 * 		The mapping policy as content of the corresponding article.
	 * @param int $offset
	 * 		Start for search operations in the mapping policy
	 * @param  bool $useMapping
	 * 		If <true>, the content of a mapping-element is used for the final
	 * 		text. Otherwise it is skipped with all child mappings.
	 * @param int $level
	 * 		Level of recursion.
	 * 
	 * @return mixed string or array
	 * 		string: The content of the article.
	 * 		array: If the method returns from a recursion, an array is returned. It 
	 * 		contains the content of a mapping-element and the offset for the
	 * 		next search operation.
	 */
	private function createContent(&$term, $mappingPolicy, $offset = 0, 
	                               $useMapping = true, $level = 0) {
		
		$result = '';
		
		while (true) {
			$openPos = stripos($mappingPolicy, '<mapping', $offset);
			$closePos = stripos($mappingPolicy, '</mapping>', $offset);
			$closeFirst = (($openPos !==false && $closePos !== false && $closePos < $openPos)
			               || ($openPos === false && $closePos !== false));
			               
			if ($openPos === false || $closeFirst) {
				// no further mapping tags 
				if ($level == 0) {
					if ($closeFirst && $openPos !== false) {
						// there are further opening mappings after an unmatched
						// </mapping>
						// => append till the start of the next mapping
						$result .= substr($mappingPolicy, $offset, $openPos-$offset);
						$offset = $openPos;
					} else {
						// append the rest of the text
						$result .= substr($mappingPolicy, $offset);
						return $result;
					}
				} else {
					// append until the next </mapping>
					$result .= substr($mappingPolicy, $offset, $closePos-$offset);
					return array($result, $closePos+10);
				}
			} else {
				// append text from $offset till the beginning of the mapping
				$result .= substr($mappingPolicy, $offset, $openPos - $offset);
				
				$useMap = $useMapping;
				$parameters = null;
				if ($useMap) {
					// opening mapping found
					$numMatch = preg_match('/<mapping\s*properties\s*=\s*"(.*?)"\s*>/i',
					                       $mappingPolicy, $parameters, 
					                       PREG_OFFSET_CAPTURE, $openPos);
					
					if ($numMatch == 0 || $parameters[0][1] != $openPos) {
						// The mapping is invalid 
						// => just append the invalid mapping to the result
						//    and continue in the loop
						$result .= '<mapping';
						$offset = $openPos + 8;
						continue;
					} else {
						// continue after the opening mapping tag
						$openPos += strlen($parameters[0][0]);
						$parameters = $parameters[1][0];
						$parameters = explode(',', $parameters);
						$numParam = count($parameters);
						for ($i = 0; $i < $numParam; ++$i) {
							$p = trim($parameters[$i]);
							$p = preg_replace("/ +/", "__SPACE__", $p);
							if (!$term[strtoupper($p)]) {
								// the parameter is not present 
								// => skip the whole content of the mapping
								$useMap = false;
								break;
							}
						}
											
					}
				}
				// process the content of the mapping
				list($r,$offset) = $this->createContent($term, $mappingPolicy, 
				                                        $openPos, $useMap, $level+1);
				if ($useMap) {
					// replace the parameters in the result string by their actual
					// values
					foreach ($parameters as $p) {
						$p = trim($p);
						$p_blank = preg_replace("/ +/", "__SPACE__", $p);
						$v = $term[strtoupper($p_blank)][0]['value'];
						$p = '{{{'.$p.'}}}';
						$r = str_replace($p, $v, $r);
					}
					$result .= $r; 
				}
			}
		}
	}
	
	/**
	 * A term may contain ontological properties. These are converted to 
	 * annotations in form of wiki text or namespaces. 
	 * 
	 * The following mapping is applied from ontological properties to wiki:
	 * isCategory => Namespace: Category (language dependent) 
	 * isProperty => Namespace: Property (language dependent) 
	 * isOfCategory(cat) => [[Category:cat]]
	 * isSubCategoryOf(superCat) => Namespace: Category and [[Category:superCat]]
	 * isSubPropertyOf(superProp) => Namespace: Property and 
	 *                               [[subproperty of::Property:superProp]]
	 *
	 * @param array $term
	 * 		This parsed XML structure contains the description of one term.
	 * 
	 * @return array(string, string)
	 * 		-The wiki text of the annotations.
	 *      -The namespace (Category, Property)
	 * 
	 */
	private function createOntologyAnnotations(&$term) {
		global $wgLang, $smwgContLang;
		
		$anno = '';
		$namespace = '';
		
		//edit! referenzen, wenn nicht null. sonst direkter zugriff
		$temp = print_r(&$term, true);
		//$temp();
		$isCat     = &$term['ISCATEGORY'];
		$isProp    = &$term['ISPROPERTY'];
		$cat       = &$term['ISOFCATEGORY'];
		$subCatOf  = &$term['ISSUBCATEGORYOF'];
		$subPropOf = &$term['ISSUBPROPERTYOF'];
		
		if ($isCat) {
			$namespace = $wgLang->getNsText(NS_CATEGORY).':'; 
		}
		if ($isProp) {
			$namespace = $wgLang->getNsText(SMW_NS_PROPERTY).':';
		}
		if ($cat) {
			$anno .= '[['.$wgLang->getNsText(NS_CATEGORY).':'.$cat[0]['value']."]]\n";
		}
		if ($subCatOf) {
			$namespace = $wgLang->getNsText(NS_CATEGORY).':'; 
			$anno .= '[['.$wgLang->getNsText(NS_CATEGORY).':'.$subCatOf[0]['value']."]]\n";
		}
		if ($subPropOf) {
			$specialProperties = $smwgContLang->getPropertyLabels();
			
			$namespace = $wgLang->getNsText(SMW_NS_PROPERTY).':'; 
			$anno .= '[['.$specialProperties["_SUBP"].':'
			         .$wgLang->getNsText(SMW_NS_PROPERTY).':'.$subPropOf[0]['value']."]]\n";
		}
		
		$result = array($anno, $namespace);
		
		return $result;
		
	}
	
}

// Create one instance to register the bot.
new TermImportBot();

define('SMW_TERMIMPORT_BOT_BASE', 2200);
define('SMW_GARDISSUE_ADDED_ARTICLE', SMW_TERMIMPORT_BOT_BASE * 100 + 1);
define('SMW_GARDISSUE_UPDATED_ARTICLE', (SMW_TERMIMPORT_BOT_BASE+1) * 100 + 1);
define('SMW_GARDISSUE_MISSING_ARTICLE_NAME', (SMW_TERMIMPORT_BOT_BASE+2) * 100 + 1);
define('SMW_GARDISSUE_CREATION_FAILED', (SMW_TERMIMPORT_BOT_BASE+2) * 100 + 2);
define('SMW_GARDISSUE_UPDATE_SKIPPED', (SMW_TERMIMPORT_BOT_BASE+3) * 100 + 1);

class TermImportBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case SMW_GARDISSUE_ADDED_ARTICLE:
				return wfMsg('smw_ti_added_article', $text1);
			case SMW_GARDISSUE_UPDATED_ARTICLE:
				return wfMsg('smw_ti_updated_article', $text1);
			case SMW_GARDISSUE_MISSING_ARTICLE_NAME:
				return wfMsg('smw_ti_missing_articlename');
			case SMW_GARDISSUE_CREATION_FAILED:
				return wfMsg('smw_ti_creation_failed', $text1);
			case SMW_GARDISSUE_UPDATE_SKIPPED:
				return wfMsg('smw_ti_articleNotUpdated', $text1);
				
			default: return NULL;
				
		}
	}
}

class TermImportBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_TERMIMPORT_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'), 
		                                wfMsg('smw_gardissue_ti_class_added_article'),
		                                wfMsg('smw_gardissue_ti_class_updated_article'),
		                                wfMsg('smw_gardissue_ti_class_system_error'),
		                                wfMsg('smw_gardissue_ti_class_update_skipped'));
	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {
		return array('pageTitle' => $wgRequest->getVal('pageTitle'));
	}

	public function getData($options, $request) {
		$pageTitle = $request->getVal('pageTitle');
		if ($pageTitle != NULL) {
			// show only issue of *ONE* title
			return $this->getGardeningIssueContainerForTitle($options, $request, Title::newFromText(urldecode($pageTitle)));
		} else return parent::getData($options, $request);
	}

	private function getGardeningIssueContainerForTitle($options, $request, $title) {
		$gi_class = $request->getVal('class') == 0 ? NULL : $request->getVal('class') + $this->base - 1;


		$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();

		$gic = array();
		$gis = $gi_store->getGardeningIssues('smw_termimportbot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);


		return $gic;
	}
}

?>
