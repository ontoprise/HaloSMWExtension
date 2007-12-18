<?php
/*
 * Created on 21.02.2007
 * Author: KK
 * AutoCompletion Dispatcher
 */

// Register AJAX functions

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwfAutoCompletionDispatcher';
$wgAjaxExportList[] = 'smwfAutoCompletionOptions';

// Register hooks
global $wgHooks;
$wgHooks['UserToggles'][] = 'smwfAutoCompletionToggles';
$wgHooks['SetUserDefinedCookies'][] = 'smwfSetUserDefinedCookies';

define('SMW_AC_NORESULT', "noResult");
define('SMW_AC_MAX_RESULTS', 15);

$smwhgAutoCompletionStore = null;

global $smwgIP;
require_once( $smwgIP . "/includes/SMW_Datatype.php");
require_once( $smwgIP . "/includes/SMW_DataValueFactory.php");
 /*
  * Dispatches an auto-completion request.
  * 
  * Gets the user input splitted up in 2 parts: 
  * Right part: consisting of all chars left from cursor which may belong
  * to a article's title.
  * Left part: the rest until [[. Left part may be empty! In this case,
  * no context could be identified.
  *  
  * Example: user types [[category:Me and presses Ctrl+^
  * $userInputToMatch = Me
  * $userContext = [[category:
  * 
  * Returns: xml representation with titles and type of entities.
  */
 function smwfAutoCompletionDispatcher($articleName, $userInputToMatch, $userContext, $typeHint) {
 	global $semanticAC, $wgLang;
 	
	smwLog(($userContext != null ? $userContext : "").$userInputToMatch, "AC", "activated", $articleName);
  	// remove common namespaces from user input
 	$userInputToMatch = AutoCompletionRequester::removeCommonNamespaces($userInputToMatch); 
 	// remove whitespaces from user input and replace with underscores
 	$userInputToMatch = str_replace(" ","_",$userInputToMatch);
 	
 	if ($userContext == null || $userContext == "" || !AutoCompletionRequester::isContext($userContext)) {
 			// no context: that means only non-semantic AC is possible. Maybe a typeHint is specified
 			if ($typeHint == null || $typeHint == 'null') {
 				// if no $typeHint defined, search for (nearly) all pages.
 	    		$pages = smwfGetAutoCompletionStore()->getPages($userInputToMatch, array(SMW_NS_PROPERTY, NS_CATEGORY, NS_MAIN, NS_TEMPLATE, SMW_NS_TYPE));
 	    		$result = AutoCompletionRequester::encapsulateAsXML($pages);
 	    		AutoCompletionRequester::logResult($result, $articleName);
 	    		return $result;
 			} else {
 				return AutoCompletionRequester::getTypeHintProposals($articleName, $userInputToMatch, $typeHint);
 			}
 			
 	} else if (stripos($userContext, "[[") === 0){  
 		// semantic context
 		// decide according to context which autocompletion is appropriate
 	   
 	    // ------------------------	
 	    // 1. category case
 	    // ------------------------	
 	    if (stripos(strtolower($userContext), strtolower($wgLang->getNsText(NS_CATEGORY)).":") > 0) { 
 	    	$result = AutoCompletionRequester::getCategoryProposals($userInputToMatch);
 	    	AutoCompletionRequester::logResult($result, $articleName);
 	    	return $result;
 	    }
 	     
 	    // ------------------------------------------------
 	    // 2./3. property target case / property value case
 	    // ------------------------------------------------	
 	    else if (stripos($userContext,":=") > 0 || stripos($userContext,"::") > 0) {
 	    	
 	    	$propertyTargets = AutoCompletionRequester::getPropertyTargetProposals($userContext, $userInputToMatch);
 	    	
 	    	$attributeValues = AutoCompletionRequester::getPropertyValueProposals($userContext);
 	    	
 	    	// if there is a unit or possible values, show them. Otherwise show instances.
 	    	$result = $attributeValues != SMW_AC_NORESULT ? $attributeValues : $propertyTargets;
 	    	AutoCompletionRequester::logResult($result, $articleName);
 	    	return $result;
 	 	    
 	     	    	
 	    // --------------------------------
 	    // 4.property name case
 	    // --------------------------------	
 	    } else {
 	    	$result = AutoCompletionRequester::getPropertyProposals($articleName, $userInputToMatch);
 	    	AutoCompletionRequester::logResult($result, $articleName);
 	    	return $result;
 	    	
 	    }
 
 	} else if (stripos($userContext, "{{") === 0) {  
 		// template context
 		$result = AutoCompletionRequester::getTemplateProposals($userContext, $userInputToMatch);
 		AutoCompletionRequester::logResult($result, $articleName);
 	    return $result;
 		
 	}
  	
 }
 
/**
 * Register extra AC related options in Preferences->Misc
 */
function smwfAutoCompletionToggles(&$extraToggles) {
	$extraToggles[] = "autotriggering";
	return true;
}

function smwfSetUserDefinedCookies(&$wgCookiePrefix, &$exp, &$wgCookiePath, &$wgCookieDomain, &$wgCookieSecure) {
	global $wgUser,$wgScriptPath;
	$triggerMode = $wgUser->getOption( "autotriggering" ) == 1 ? "auto" : "manual";
	setcookie("AC_mode", $triggerMode, 0, "$wgScriptPath/"); // cookie gets invalid at session-end.
	return true;
}

/**
 * Return options
 */
function smwfAutoCompletionOptions() {
	global $wgUser;
	return $wgUser->getOption( "autotriggering" ) == 1 ? "auto" : "manual";
}

function &smwfGetAutoCompletionStore() {
		global $smwhgAutoCompletionStore, $smwgHaloIP;
		if ($smwhgAutoCompletionStore == NULL) {
			global $smwgDefaultStore;
			switch ($smwgDefaultStore) {
				case (SMW_STORE_TESTING):
					$smwhgAutoCompletionStore = null; // not implemented yet
					trigger_error('Testing store not implemented for HALO extension.');
				break;
				case (SMW_STORE_MWDB): default:
					$smwhgAutoCompletionStore = new AutoCompletionStorageSQL();
				break;
			}
		}
		return $smwhgAutoCompletionStore;
}

class AutoCompletionRequester { 
	
	
	public static function getTypeHintProposals($articleName, $userInputToMatch, $typeHint) {
		$result = array();
		$typeHints = explode(";", $typeHint);
 		foreach($typeHints as $th) {
		 	if (is_numeric($th)) {
		 		// if there is a numeric type hint, consider it as a namespace
		 		$typeHintNum = $th + 0;
		 		$pages = smwfGetAutoCompletionStore()->getPages($userInputToMatch, array($typeHintNum));
		 		$result = AutoCompletionRequester::encapsulateAsXML($pages);
		  		AutoCompletionRequester::logResult($result, $articleName);
		 	    		
		 	    		
		 	} else if (strpos($th, ":") !== false) {
		 		// if typeHint contains ':'
		 		$page = Title::newFromText(substr($th, 0, 1) == ':' ? substr($th, 1) : $th);
		 		if ($page->getNamespace() != NS_MAIN) {
		 			// ignore non-instances
		 			return SMW_AC_NORESULT;
		 		}
		 						 					 			
		 		$properties = smwfGetAutoCompletionStore()->getPropertyForInstance($userInputToMatch, $page, false);
		   		$result = AutoCompletionRequester::encapsulateAsXML($properties);
	    		return $result;
		   		AutoCompletionRequester::logResult($result, $articleName);
		 	    		
 			} else {
 				
		 		// in all other cases, consider it as type
				$properties = smwfGetAutoCompletionStore()->getPropertyWithType($userInputToMatch, $th);
				$result = AutoCompletionRequester::encapsulateAsXML($properties);
	    		AutoCompletionRequester::logResult($result, $articleName);
		 	    		
			}
			if ($result != SMW_AC_NORESULT) break;
 		}
 			
 		return $result;
	}
	/**
	 * Get category proposals matching $match.
	 */
	public static function getCategoryProposals($match) {
		$categories = smwfGetAutoCompletionStore()->getPages($match, array(NS_CATEGORY));
 	    return AutoCompletionRequester::encapsulateAsXML($categories);
	}
	
	/**
	 * Get Property target proposals. Consider special properties too
	 */
	public static function getPropertyTargetProposals($userContext, $match) {
		// special handling for special relations
		
 	    	global $smwgContLang, $smwgHaloContLang, $semanticAC, $wgLang;
 	    	$specialProperties = $smwgContLang->getSpecialPropertiesArray();
 	    	$specialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
 	    		
 	    	// special properties
 	    	if (stripos(strtolower($userContext), strtolower($specialProperties[SMW_SP_SUBPROPERTY_OF])) > 0) {
 	    		$pages = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_PROPERTY));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else if (stripos(strtolower($userContext),strtolower($specialProperties[SMW_SP_HAS_TYPE])) > 0) { 
 	    		// has type relation. First check for user types
 	    		$pages = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_TYPE));
 	    		// then check builtin types 
 	    		$typeLabels = array_values(SMWDataValueFactory::getKnownTypeLabels());
 	    		$lower_match = strtolower($match);
 	    		foreach($typeLabels as $l) {
	 	    		if (strpos(strtolower($l), $lower_match) !== false) {
	 	    			$pages[] = Title::newFromText($l, SMW_NS_TYPE);
	 	    		}
 	    				
 	    		}
 	    		
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else if (stripos(strtolower($userContext),strtolower($specialSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT])) > 0) { 
 	    		// has domain hint relation 
 	    		$pages = smwfGetAutoCompletionStore()->getPages($match, array(NS_CATEGORY));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else {
 	    		if ($semanticAC) { 
 	    			if (stripos($userContext,":=") > 0) { 
 	    				$relationText = substr($userContext, 2, stripos($userContext,":=")-2);
 	    			} else {
 	    				$relationText = substr($userContext, 2, stripos($userContext,"::")-2);
 	    			}
 	    	
 	    			$property = Title::newFromText($relationText, SMW_NS_PROPERTY);
 	    		
 	    			$domainRangeRelation = smwfGetSemanticStore()->domainRangeHintRelation;
 	    			$domainRangeAnnotations = smwfGetStore()->getPropertyValues($property, $domainRangeRelation);
 	    			$pages = array();
 	    			$options = new SMWRequestOptions();
 	    			$options->limit = SMW_AC_MAX_RESULTS;
 	    			if (count($domainRangeAnnotations) > 0) { // ignore multiple ranges
 	    				$dv = $domainRangeAnnotations[0]->getDVs();
 	    				if ($dv[1] !== NULL && $dv[1]->isValid()) {
 	    					$pages = smwfGetSemanticStore()->getInstances($dv[1]->getTitle(), $options);
	    				}
 	    			}
 	    			return AutoCompletionRequester::encapsulateAsXML($pages);
 	    		} else {  	
 	    			// all others
 	   				$pages = smwfGetAutoCompletionStore()->getPages($match, array(NS_MAIN));
 	   				return AutoCompletionRequester::encapsulateAsXML($pages);
 	    		}
 	    	}
 	    	
	}
	
	/**
	 * Get attribute values (units and enums)
	 */
	public static function getPropertyValueProposals($userContext) {
			
 	    	if (stripos($userContext,":=") > 0) {
 	    		$attributeText = trim(substr($userContext, 2, stripos($userContext,":=")-2));
 	    	} else {
 	    		$attributeText = trim(substr($userContext, 2, stripos($userContext,"::")-2));
 	    	}
 	    	// try units first, then possible values
 	    	$unitsList = SMWTypeHandlerFactory::getUnitsList($attributeText);
 	    	if (count($unitsList) > 0) {
 	    		$attvalues = AutoCompletionRequester::encapsulateEnumsOrUnitsAsXML($unitsList);
 	    	} else {
 	    		$possibleValues = SMWTypeHandlerFactory::getPossibleValues($attributeText);
 	    		$attvalues = AutoCompletionRequester::encapsulateEnumsOrUnitsAsXML($possibleValues);
 	    	}
 	    	return $attvalues;
	}
	
	/**
	 * Get property proposals. Consider special properties too.
	 */
	public static function getPropertyProposals($articleName, $match) {
		global $semanticAC, $wgLang;
		if ($semanticAC) { 
 	    		// get all categories of the article
 	    		$articleTitle = Title::newFromText($articleName);
 	    		$pages = smwfGetAutoCompletionStore()->getPropertyForInstance($match, $articleTitle, true);
			
 	    	} else {
 	    		$pages = smwfGetAutoCompletionStore()->getPages($match, array(NS_MAIN, SMW_NS_PROPERTY, SMW_NS_RELATION));
 	    	}
 	    	// special handling for special relations
 	    	$specialMatches = array(); // keeps matches of special relations
 	    	global $smwgContLang;
 	    	$specialProperties = $smwgContLang->getSpecialPropertiesArray();
 	    	if (stripos(strtolower($wgLang->getNsText(NS_CATEGORY)), strtolower($match)) !== false) {
 	    		$specialMatches[] = Title::newFromText(strtolower($wgLang->getNsText(NS_CATEGORY)), NS_CATEGORY);
 	    	}
 	    	if (stripos(strtolower($specialProperties[SMW_SP_SUBPROPERTY_OF]), preg_replace("/_/", " ", strtolower($match))) !== false) {
	 	    	$specialMatches[] = Title::newFromText($specialProperties[SMW_SP_SUBPROPERTY_OF], SMW_NS_PROPERTY);
 	    	}
 	    	
 	    	if (stripos(strtolower($specialProperties[SMW_SP_HAS_TYPE]), preg_replace("/_/", " ", strtolower($match))) !== false) {
 	    		$specialMatches[] = Title::newFromText($specialProperties[SMW_SP_HAS_TYPE], SMW_NS_PROPERTY);
 	    	}
 	    		// make sure the special relations come first
 	    	$pages = array_merge($specialMatches, $pages);
 	    		
 	    	return AutoCompletionRequester::encapsulateAsXML($pages);
	}
	
	/**
	 * Get template proposals.
	 */
	public static function getTemplateProposals($userContext, $match) {
		// template context
 		global $semanticAC, $wgLang;
 		if ($semanticAC) {
 			// TODO: need template schema data. current implementation is the same as for non-semantic AC.
 			
 			// -------------- this is obsolete --------------------
 			// parse template paramters
 			$templateParameters = explode("|", $userContext);
 			
 			if (count($templateParameters) > 1) { 
 				// if it is a parameter try all semantic namespaces
 				$results = smwfGetAutoCompletionStore()->getPages($match, array(NS_MAIN, SMW_NS_PROPERTY));
 				return AutoCompletionRequester::encapsulateAsXML($results);
 			} else { // otherwise it is a template name
 	    		$templates = smwfGetAutoCompletionStore()->getPages($match, array(NS_TEMPLATE));
 	    		$extraData = array();
 	    		foreach($templates as $t) {
 	    			$extraData[] = TemplateReader::formatTemplateParameters($t);
 	    		}
 	    		return AutoCompletionRequester::encapsulateAsXML($templates, false, $extraData);
 			}
 			// ^^^^^^^^^^^^^^ this is obsolete ^^^^^^^^^^^^^^^^^^^^^^^
 		} else {
 			// parse template paramters
 			$templateParameters = explode("|", $userContext);
 			if (count($templateParameters) > 1) { 
	 			// if it is a parameter try all semantic namespaces
 				$results = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_PROPERTY, NS_MAIN));
 				return AutoCompletionRequester::encapsulateAsXML($results);
 			} else { // otherwise it is a template name
 	    		$templates = smwfGetAutoCompletionStore()->getPages($match, array(NS_TEMPLATE));
 	    		$extraData = array();
 	    		foreach($templates as $t) {
 	    			$extraData[] = TemplateReader::formatTemplateParameters($t);
 	    		}
 	    		return AutoCompletionRequester::encapsulateAsXML($templates, false, $extraData);
 			}
 		}
	}
	/**
 	* Heuristic to determine weather $userContext describes a semantic context or not.
 	*/
	public static function isContext($userContext) {
		if (stripos($userContext, "{{") === 0 && stripos($userContext, "}}") === false) {
			return true;  
		}
		if (stripos($userContext, "[[") === 0 && stripos($userContext, "]]") === false) {
			return true;  
		}
		return false;
	}

	/**
 	* Encapsulate an array of Titles in a xml string
 	* 
 	* @param $titles Array of Title
 	* @param $putNameSpaceInName If true system would return 'namespace:localname' otherwise 'localname'
 	* @param $extraData Extra data which is pasted behind the Title. (array sizes of $titles and $extraData must matched, if used.)
 	* @return xml string
 	*/
	public static function encapsulateAsXML(array & $titles, $putNameSpaceInName = false, $extraData = NULL) {
		if ($extraData != NULL && count($titles) != count($extraData)) {
			return SMW_AC_NORESULT;
		}
		$xmlResult = '';
				
		for($i = 0, $n = count($titles); $i < $n; $i++) {
			if ($titles[$i] == NULL) continue;
			$namespace = $titles[$i]->getNsText(); //AutoCompletionRequester::getNamespaceText();
			$extra = $extraData != NULL ? $extraData[$i] : ""; 
			$xmlResult .= "<match type=\"".$titles[$i]->getNamespace()."\">".($putNameSpaceInName ? $namespace.":" : "").htmlspecialchars($titles[$i]->getDBkey().$extra)."</match>";
		}
		return empty($titles) ? SMW_AC_NORESULT : '<result>'.$xmlResult.'</result>';
	}

	/**
 	*  Encapsulate an array of enums or units in a xml string.
 	*/
	public static function encapsulateEnumsOrUnitsAsXML($arrayofEnumsOrUnits) {
		$xmlResult = '';
		foreach($arrayofEnumsOrUnits as $eou) {
			$xmlResult .= "<match type=\"200\">".htmlspecialchars($eou)."</match>";
		}
		return empty($arrayofEnumsOrUnits) ? SMW_AC_NORESULT : '<result>'.$xmlResult.'</result>';
	}
	
	

	
	
	/**
	 * Removes the common SMW namespace from $titleText.
	 */
	public static function removeCommonNamespaces($titleText) {
		global $smwgContLang;
		$namespaces = array_values($smwgContLang->getNamespaces());
		$regex = "";
		for ($i = 0, $n = count($namespaces); $i < $n; $i++) {
			if ($i < $n-1) {
				$regex .= $namespaces[$i].":|";
			} else {
				$regex .= $namespaces[$i].":";
			}
		}
		return preg_replace("/".$regex."/", "", $titleText);
	
	}
	/**
	 * Retrieves attribute and relation titles matching requestoptions which do not exist in page table, 
	 * i.e. the method retrieves attributes/relation which were already used but not defined.
	 * 
	 * @return array of Title objects 
	 */
	/*private static function getUndefinedPropertiesFromSMWTables(& $result, $namespaces, $requestoptions) {
		$db =& wfGetDB( DB_MASTER );
		if (in_array(SMW_NS_ATTRIBUTE, $namespaces)) {
			$attConds = DBHelper::getSQLConditions($requestoptions,'attribute_title','attribute_title');
			$res = $db->query('SELECT DISTINCT attribute_title FROM smw_attributes WHERE attribute_title NOT IN (SELECT page_title FROM page WHERE attribute_title = page_title)'.$attConds.";");
		    if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = Title::newFromText($row->attribute_title, SMW_NS_ATTRIBUTE);
				}
			}
			$db->freeResult($res);
		}
		if (in_array(SMW_NS_RELATION, $namespaces)) {
			$relConds = DBHelper::getSQLConditions($requestoptions,'relation_title','relation_title');
			$res = $db->query('SELECT DISTINCT relation_title FROM smw_relations WHERE relation_title NOT IN (SELECT page_title FROM page WHERE relation_title = page_title)'.$relConds.";");
		    if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = Title::newFromText($row->relation_title, SMW_NS_RELATION);
				}
			}
			$db->freeResult($res);
		}
	}*/
	
	
	
		
	/*private static function getNamespaceText($page) {
 		global $smwgContLang, $wgLang;
 		$nsArray = $smwgContLang->getNamespaces();
 		if ($page->getNamespace() == NS_TEMPLATE || $page->getNamespace() == NS_CATEGORY) {
 			$ns = $wgLang->getNsText($page->getNamespace());
 				} else { 
 			$ns = $page->getNamespace() != NS_MAIN ? $nsArray[$page->getNamespace()] : "";
 		}
 		return $ns;
 	}*/
 	
 	public function logResult(& $result, $articleName) {
 		if ($result == SMW_AC_NORESULT) {
 	    			smwLog("","AC","no result", $articleName);
 	    } else {
 	    			smwLog("","AC","opened", $articleName);
 	    }
 	}
}

class TemplateReader {
	
	/**
	 * Format template parameters: One parameter per line. Adds separator pipe
	 */
	public static function formatTemplateParameters($template) {
		$result = "\n";
		$parameters = TemplateReader::getParameters($template);
		foreach($parameters as $p) {
			$result .= !is_numeric($p) ? "|".$p."=\n" : "|\n";
		}
		return $result;
	} 
	
	/**
	 * Get Template parameters as array of strings. Returns no doubles.
	 */
	private static function getParameters($template) {
		$rev = Revision::newFromTitle($template);
		$content = $rev->getText();
		$matches = array();
		$parameters = array();
		preg_match_all("/\{\{\{([^\}\|]*)\}\}\}/", $content, $matches);
		for($i = 0, $n = count($matches[1]); $i < $n; $i++) {
			$parameters[] = $matches[1][$i];
		}
		return array_unique($parameters);
	}
	
	
}

abstract class AutoCompletionStorage {
	/**
 	* Retrieves pages matching the requestoptions and the given namespaces
 	* 
 	* TODO: should be transferred to storage layer
 	* 
 	* @return array of Title
 	*/
	public abstract function getPages($match, $namespaces = NULL, $requestoptions = NULL);
	
	/**
	 * Returns properties containing $match with unit $unit
	 * 
	 * TODO: should be transferred to storage layer
	 * 
	 * @param $match substring
	 * @param $typeLabel primitive type or unit
	 */
	public abstract function getPropertyWithType($match, $typeLabel); 
	
	/**
	 * Returns (including inferred) properties which match a given $instance for domain or range
	 * If $instance is not part of any category, it will return an empty result set.
	 * 
	 * @param $userInputToMatch substring must be part of property title
	 * @param $instance
	 * @param $matchDomainOrRange True, if $instance must match domain or range
	 */
	public abstract function getPropertyForInstance($userInputToMatch, $instance, $matchDomainOrRange);
}

class AutoCompletionStorageSQL extends AutoCompletionStorage {
	
	public function getPages($match, $namespaces = NULL, $requestoptions = NULL) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = "";
		$page = $db->tableName('page');
		if ($namespaces != NULL) {
			$sql .= '(';
			for ($i = 0, $n = count($namespaces); $i < $n; $i++) { 
				if ($i > 0) $sql .= ' OR ';
				$sql .= 'page_namespace='.$db->addQuotes($namespaces[$i]);
			}
			if (count($namespaces) == 0) $sql .= 'true';
			$sql .= ') ';
		} else  {
			$sql = 'true';
		}
		
				
		$result = array();
		
		// add additional titles from smw-titles which do not exist in the page table
		//AutoCompletionRequester::getUndefinedPropertiesFromSMWTables($result, $namespaces, $requestoptions);
		
		// query for pages which begin with $match AND for pages which contain $match. In this order.
		$res = $db->query('(SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes($match.'%').') AND ' .$sql.' ORDER BY page_namespace DESC) '.
							' UNION (SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND '.$sql.' ORDER BY page_namespace DESC) LIMIT '.SMW_AC_MAX_RESULTS.'');
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	
	public function getPropertyWithType($match, $typeLabel) {
		$db =& wfGetDB( DB_MASTER );
		$smw_specialprops = $db->tableName('smw_specialprops');
		$page = $db->tableName('page');
		$result = array();
		$typeID = SMWDataValueFactory::findTypeID($typeLabel);
		
		$res = $db->query('(SELECT page_title AS title FROM '.$smw_specialprops.' s1 ' .
							'JOIN '.$smw_specialprops.' s2 ON LOCATE(s2.subject_title, s1.value_string) > 0 ' .
							'JOIN '.$page.' ON s1.subject_id = page_id ' .
							'WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND s1.subject_namespace = '.SMW_NS_PROPERTY.
							' AND s2.value_string REGEXP '.$db->addQuotes('([0-9].?[0-9]*|,) '.$typeLabel.'(,|$)').
							') UNION DISTINCT ' .
							'(SELECT page_title AS title FROM '.$smw_specialprops.' JOIN '.$page.' ON subject_id = page_id' .
							' WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND property_id = '.SMW_SP_HAS_TYPE.' AND UPPER(value_string) = UPPER('.$db->addQuotes($typeID).'))' .
							'  LIMIT '.SMW_AC_MAX_RESULTS);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->title, SMW_NS_PROPERTY);
			}
		}
		
		$db->freeResult($res);
		
		return $result;
	}
	
	
	public function getPropertyForInstance($userInputToMatch, $instance, $matchDomainOrRange) {
		global $smwgDefaultCollation;
		$db =& wfGetDB( DB_MASTER );
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$smw_nary = $db->tableName('smw_nary');
		$smw_nary_relations = $db->tableName('smw_nary_relations');
		
		$nary_pos = $matchDomainOrRange ? 0 : 1;
		
		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties (id INT(8) NOT NULL, property VARCHAR(255) '.$collation.')
		            TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
		
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_sub (category INT(8) NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_super (category INT(8) NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
		            
		$db->query('INSERT INTO smw_ob_properties (SELECT n.subject_id AS id, n.subject_title AS property FROM '.$smw_nary.' n JOIN '.$smw_nary_relations.' r ON n.subject_id = r.subject_id JOIN '.$page.' p ON n.subject_id = p.page_id '.
					' WHERE r.nary_pos = '.$nary_pos.' AND n.attribute_title = '. $db->addQuotes(smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()). ' AND r.object_title IN (SELECT cl_to FROM categorylinks WHERE cl_from = ' .$db->addQuotes($instance->getArticleID()).') AND UPPER(n.subject_title) LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').') AND p.page_is_redirect = 0)');
	
		$db->query('INSERT INTO smw_ob_properties_sub  (SELECT DISTINCT page_id AS category FROM categorylinks JOIN page ON cl_to = page_title AND page_namespace = '.NS_CATEGORY.' WHERE cl_from = ' .$instance->getArticleID().')');    
		
		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		// maximum iteration length is maximum category tree depth.
		do  {
			$maxDepth--;
			
			// get next supercategory level
			$db->query('INSERT INTO smw_ob_properties_super (SELECT DISTINCT page_id AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_title = cl_to WHERE page_namespace = '.NS_CATEGORY.' AND cl_from IN (SELECT * FROM smw_ob_properties_sub))');
			
			// insert direct properties of current supercategory level
			$db->query('INSERT INTO smw_ob_properties (SELECT n.subject_id AS id, n.subject_title AS property FROM '.$smw_nary.' n JOIN '.$smw_nary_relations.' r ON n.subject_id = r.subject_id JOIN '.$page.' p ON n.subject_id = p.page_id '.
					' WHERE r.nary_pos = '.$nary_pos.' AND n.attribute_title = '. $db->addQuotes(smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()). ' AND p.page_is_redirect = 0 AND r.object_id IN (SELECT * FROM smw_ob_properties_super) AND UPPER(n.subject_title) LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
	
			
			// copy supercatgegories to subcategories of next iteration
			$db->query('DELETE FROM smw_ob_properties_sub');
			$db->query('INSERT INTO smw_ob_properties_sub (SELECT * FROM smw_ob_properties_super)');
			
			// check if there was least one more supercategory. If not, all properties were found.
			$res = $db->query('SELECT COUNT(category) AS numOfSuperCats FROM smw_ob_properties_sub');
			$numOfSuperCats = $db->fetchObject($res)->numOfSuperCats;
			$db->freeResult($res);
			
			$db->query('DELETE FROM smw_ob_properties_super');
			
		} while ($numOfSuperCats > 0 && $maxDepth > 0);   
		
		$res = $db->query('SELECT DISTINCT property FROM smw_ob_properties');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->property, SMW_NS_PROPERTY);
			}
		}
		
		$db->freeResult($res);
		
		
		$db->query('DROP TABLE smw_ob_properties');
		$db->query('DROP TABLE smw_ob_properties_super');
		$db->query('DROP TABLE smw_ob_properties_sub');
		return $result;
	}
}


?>
