<?php
/*
 * Created on 21.02.2007
 * Author: KK
 * AutoCompletion Dispatcher
 */

// Register AJAX functions
if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ac_AutoCompletionDispatcher';
$wgAjaxExportList[] = 'smwf_ac_AutoCompletionOptions';


define('SMW_AC_NORESULT', "noResult");
define('SMW_AC_MAX_RESULTS', 15);

$smwhgAutoCompletionStore = null;

global $smwgHaloIP;
require_once( $smwgHaloIP . "/includes/SMW_DBHelper.php");

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
 function smwf_ac_AutoCompletionDispatcher($articleName, $userInputToMatch, $userContext, $typeHint, $options="") {
 	global $smwgSemanticAC, $wgLang;
 	
	smwLog(($userContext != null ? $userContext : "").$userInputToMatch, "AC", "activated", $articleName);
  	// remove common namespaces from user input
 	$userInputToMatch = AutoCompletionRequester::removeCommonNamespaces($userInputToMatch); 
 	// remove whitespaces from user input and replace with underscores
 	$userInputToMatch = str_replace(" ","_",$userInputToMatch);

 	// check for constraint specific autocompletion (show only properties with certain constraints)
 	// syntax: array consisting of Categories (and) ORed categories concatenated by "|" 
 	// e.g. "Category:Person", "Category:Car|Category:Boat"
 		
 	if ($options != "" || $options != null) {
		$result = SMWQueryProcessor::getResultFromQueryString($options, array('format' => 'ul'), array(), SMW_OUTPUT_WIKI);
		
		$result = strip_tags($result);
		preg_match_all('/\[\[[^\|]+/', $result, $matches); 
		$pages = $matches[0];		
		$title[] = array();
		
		foreach ($pages as $page) {
			$page = substr($page, 2); // remove the "[["
			if (strlen($userInputToMatch) > 0) {
	 			if (stripos($page, $userInputToMatch) !== false) {
		 			$title[] = Title::newFromDBkey($page);
				}
			}
			else {			
				$title[] = Title::newFromDBkey($page);
			}
		}		
		
 	 	$result = AutoCompletionRequester::encapsulateAsXML($title);
	 	AutoCompletionRequester::logResult($result, $articleName);
	 	if ($result != SMW_AC_NORESULT) {
	 		return $result;
	 	}
 	}
 	
 	if ($userContext == null || $userContext == "" || !AutoCompletionRequester::isContext($userContext)) {
 			// no context: that means only non-semantic AC is possible. Maybe a typeHint is specified
 			if ($typeHint == null || $typeHint == 'null') {
 				// if no $typeHint defined, search for (nearly) all pages.
 	    		$nsToSearch = array(SMW_NS_PROPERTY, NS_CATEGORY, NS_MAIN, NS_TEMPLATE, SMW_NS_TYPE);
                if (defined("SMW_NS_WEB_SERVICE")) $nsToSearch[] = SMW_NS_WEB_SERVICE;
                $pages = smwfGetAutoCompletionStore()->getPages($userInputToMatch, $nsToSearch);
 	    		
 			} else {
 				// otherwise use type hint 
 				$pages = AutoCompletionRequester::getTypeHintProposals($userInputToMatch, $typeHint);
 				if (empty($pages)) {
 					// fallback to standard search
 					$nsToSearch = array(SMW_NS_PROPERTY, NS_CATEGORY, NS_MAIN, NS_TEMPLATE, SMW_NS_TYPE);
                    if (defined("SMW_NS_WEB_SERVICE")) $nsToSearch[] = SMW_NS_WEB_SERVICE;
                    $pages = smwfGetAutoCompletionStore()->getPages($userInputToMatch, $nsToSearch);
 				} 			
 			}
 			$result = AutoCompletionRequester::encapsulateAsXML($pages);
 	    	return $result;
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
 	    	
 	    	$attributeValues = AutoCompletionRequester::getPropertyValueProposals($userContext, $userInputToMatch);
 	    	 	    	
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
 * Return options
 */
function smwf_ac_AutoCompletionOptions() {
	global $wgUser;
	return $wgUser->getOption( "autotriggering" ) == 1 ? "auto" : "manual";
}

function &smwfGetAutoCompletionStore() {
		global $smwhgAutoCompletionStore, $smwgHaloIP;
		if ($smwhgAutoCompletionStore == NULL) {
			global $smwgBaseStore;
			switch ($smwgBaseStore) {
				case (SMW_STORE_TESTING):
					$smwhgAutoCompletionStore = null; // not implemented yet
					trigger_error('Testing store not implemented for HALO extension.');
				break;
				case ('SMWHaloStore2'): default:
                    $smwhgAutoCompletionStore = new AutoCompletionStorageSQL2();
                break;
				case ('SMWHaloStore'): default:
					$smwhgAutoCompletionStore = new AutoCompletionStorageSQL();
				break;
			}
		}
		return $smwhgAutoCompletionStore;
}

/**
 * TODO: Document, including member functions
 */
class AutoCompletionRequester { 
	
	
	public static function getTypeHintProposals($userInputToMatch, $typeHint) {
		$pages = array();
		$typeHints = explode(";", $typeHint);
 		foreach($typeHints as $th) {
		 	if (is_numeric($th)) {
		 		// if there is a numeric type hint, consider it as a namespace
		 		$typeHintNum = $th + 0;
		 		$pages = smwfGetAutoCompletionStore()->getPages($userInputToMatch, array($typeHintNum));
				 	    		
		 	} else if (strpos($th, ":") !== false) {
		 		// if typeHint contains ':'
		 		$page = Title::newFromText(substr($th, 0, 1) == ':' ? substr($th, 1) : $th);
		 		if ($page == NULL || $page->getNamespace() != NS_MAIN) {
		 			// ignore non-instances
		 			continue;
		 		}
		 						 					 			
		 		$pages = smwfGetAutoCompletionStore()->getPropertyForInstance($userInputToMatch, $page, false);
		   		
 			} else {
 				
		 		// in all other cases, consider it as type
				$pages = smwfGetAutoCompletionStore()->getPropertyWithType($userInputToMatch, $th);
				if (empty($pages)) {
					global $smwgContLang;
					$dtl = $smwgContLang->getDatatypeLabels();
					$pages = smwfGetAutoCompletionStore()->getPropertyWithType($userInputToMatch, $dtl['_str']);
				}
		  		
			}
			if (!empty($pages)) break;
 		}
 			
 		return $pages;
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
		
 	    	global $smwgContLang, $smwgHaloContLang, $smwgSemanticAC, $wgLang;
 	    	$specialProperties = $smwgContLang->getPropertyLabels();
 	    	$specialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
 	    		
 	    	// special properties
 	    	if (stripos(strtolower($userContext), strtolower($specialProperties["_SUBP"])) > 0) {
 	    		$pages = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_PROPERTY));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else if (stripos(strtolower($userContext), strtolower($specialSchemaProperties[SMW_SSP_IS_INVERSE_OF])) > 0) {
 	    		$pages = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_PROPERTY));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else if (stripos(strtolower($userContext),strtolower($specialProperties["_TYPE"])) > 0) { 
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
 	    		if ($smwgSemanticAC) { 
 	    			if (stripos($userContext,":=") > 0) { 
 	    				$relationText = substr($userContext, 2, stripos($userContext,":=")-2);
 	    			} else {
 	    				$relationText = substr($userContext, 2, stripos($userContext,"::")-2);
 	    			}
 	    	
 	    			$property = Title::newFromText($relationText, SMW_NS_PROPERTY);
 	    		
 	    			
 	    			$domainRangeAnnotations = smwfGetStore()->getPropertyValues($property, smwfGetSemanticStore()->domainRangeHintProp);
 	    				    			
 	    			$pages = smwfGetAutoCompletionStore()->getInstanceAsTarget($match, $domainRangeAnnotations);
 	    			
 	    			if (count($pages) == 0) {
 	    				// fallback to non semantic AC
 	    				$pages = smwfGetAutoCompletionStore()->getPages($match, array(NS_MAIN));
 	    				
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
	public static function getPropertyValueProposals($userContext, $userInput) {
			
 	    	if (stripos($userContext,":=") > 0) {
 	    		$propertyText = trim(substr($userContext, 2, stripos($userContext,":=")-2));
 	    	} else {
 	    		$propertyText = trim(substr($userContext, 2, stripos($userContext,"::")-2));
 	    	}
 	    	// try units first, then possible values
 	    	$property = Title::newFromText($propertyText, SMW_NS_PROPERTY);
 	    	$unitsList = smwfGetAutoCompletionStore()->getUnits($property, $userInput);
 	    	
 	    	if (count($unitsList) > 0) {
 	    		$attvalues = AutoCompletionRequester::encapsulateEnumsOrUnitsAsXML($unitsList);
 	    	} else {
 	    		$possibleValues = smwfGetAutoCompletionStore()->getPossibleValues($property);
 	    		$attvalues = AutoCompletionRequester::encapsulateEnumsOrUnitsAsXML($possibleValues);
 	    	}
 	    	return $attvalues;
	}
	
	/**
	 * Get property proposals. Consider special properties too.
	 */
	public static function getPropertyProposals($articleName, $match) {
		global $smwgSemanticAC, $wgLang;
		   if ($smwgSemanticAC) { 
 	    		// get all categories of the article
 	    		$articleTitle = Title::newFromText($articleName);
 	    		$pages = smwfGetAutoCompletionStore()->getPropertyForInstance($match, $articleTitle, true);
			    if (count($pages) == 0) {
			    	// fallback to non semantic AC
			    	$pages = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_PROPERTY, NS_MAIN));
			    	
			    }
 	    	} else {
 	    		$pages = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_PROPERTY, NS_MAIN));
 	    	}
 	    	// special handling for special relations
 	    	$specialMatches = array(); // keeps matches of special relations
 	    	global $smwgContLang;
 	    	$specialProperties = $smwgContLang->getPropertyLabels();
 	    	if (stripos(strtolower($wgLang->getNsText(NS_CATEGORY)), strtolower($match)) !== false) {
 	    		$specialMatches[] = Title::newFromText(strtolower($wgLang->getNsText(NS_CATEGORY)), NS_CATEGORY);
 	    	}
 	    	if (stripos(strtolower($specialProperties["_SUBP"]), preg_replace("/_/", " ", strtolower($match))) !== false) {
	 	    	$specialMatches[] = Title::newFromText($specialProperties["_SUBP"], SMW_NS_PROPERTY);
 	    	}
 	    	
 	    	if (stripos(strtolower($specialProperties["_TYPE"]), preg_replace("/_/", " ", strtolower($match))) !== false) {
 	    		$specialMatches[] = Title::newFromText($specialProperties["_TYPE"], SMW_NS_PROPERTY);
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
 		global $smwgSemanticAC, $wgLang;
 		if ($smwgSemanticAC) {
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
			$xmlResult .= "<match type=\"500\">".htmlspecialchars($eou)."</match>";
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
		
		foreach($parameters as $param) {
			list($paramName, $defaultValue) = $param;
			$result .= !is_numeric($paramName) ? "|".$paramName."=$defaultValue\n" : "|\n";
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
		preg_match_all("/\{\{\{([^\}]*)\}\}\}/", $content, $matches);
		for($i = 0, $n = count($matches[1]); $i < $n; $i++) {
			$param = $matches[1][$i];
			if (!array_key_exists($param,$parameters)) {
			     $parameters[$param] = explode("|",$param);
			}
		}
		return $parameters;
	}
	
	
}

abstract class AutoCompletionStorage {
	
	/**
	 * Returns units which matches the types of the given property and the substring
	 * 
	 * @param Title $property
	 * @param string $substring
	 * 
	 * @return array of strings
	 */
	public abstract function getUnits(Title $property, $substring);
	
	/**
	 * Returns possible values for a given property.
	 * 
	 * @param Title $property 
	 * @return array of strings
	 */
	public abstract function getPossibleValues(Title $property);
	
	/**
 	* Retrieves pages matching the requestoptions and the given namespaces
 	* 
 	* @param string match
 	* @param array of integer or NULL
 	* 
 	* @return array of Title
 	*/
	public abstract function getPages($match, $namespaces = NULL);
	
	/**
	 * Returns properties containing $match with unit $unit
	 * 
	 * TODO: should be transferred to storage layer
	 * 
	 * @param string $match substring
	 * @param string $typeLabel primitive type or unit
	 */
	public abstract function getPropertyWithType($match, $typeLabel); 
	
	/**
	 * Returns (including inferred) properties which match a given $instance for domain or range
	 * If $instance is not part of any category, it will return an empty result set.
	 * 
	 * @param string $userInputToMatch substring must be part of property title
	 * @param Title $instance
	 * @param boolean $matchDomainOrRange True, if $instance must match domain, false for range
	 */
	public abstract function getPropertyForInstance($userInputToMatch, $instance, $matchDomainOrRange);
	
	/**
	 * Returns instances which are member of the given range(s) and which match $userInputToMatch.
	 *
	 * @param string $userInputToMatch
	 * @param Array of SMWNaryValue $domainRangeAnnotations
	 * 
	 * @return array of (Title instance)
	 */
	public abstract function getInstanceAsTarget($userInputToMatch, $domainRangeAnnotations);
}

/**
 * TODO: Document, including member functions
 */
class AutoCompletionStorageSQL extends AutoCompletionStorage {
	
	public function getUnits(Title $property, $substring) {
		$all_units = array();
		$substring = str_replace("_", " ",$substring);
		
		// get all types of a property (normally 1)
		$hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
		$conversionFactorDV = SMWPropertyValue::makeProperty("_CONV");
		$conversionFactorSIDV = SMWPropertyValue::makeProperty("___cfsi");
		$types = smwfGetStore()->getPropertyValues($property, $hasTypeDV);
		foreach($types as $t) {
			if ($t->isBuiltIn()) continue; // ignore builtin types, because they have no unit
			$subtypes = explode(";", $t->getXSDValue());
			foreach($subtypes as $st) {
				// get all units registered for a given type
				$typeTitle = Title::newFromText($st, SMW_NS_TYPE);
				$units = smwfGetStore()->getPropertyValues($typeTitle, $conversionFactorDV);
				$units_si = smwfGetStore()->getPropertyValues($typeTitle, $conversionFactorSIDV);
				$all_units = array_merge($all_units, $units, $units_si);
			}
		}
		$result = array();
		
		// regexp for a measure (=number + unit)
		$measure = "/(([+-]?\d*(\.\d+([eE][+-]?\d*)?)?)\s+)?(.*)/";
		
		// extract unit substring and ignore the number (if existing)
		preg_match($measure, $substring, $matches);
		$substring = strtolower($matches[5]);
		
		// collect all units which match the substring (if non empty, otherwise all)
		foreach($all_units as $u) {
			$s_units = explode(",", $u->getXSDValue());
			foreach($s_units as $su) {
				if ($substring != '') {
					if (strpos(strtolower($su), $substring) > 0) {
						preg_match($measure, $su, $matches);
						if (count($matches) >= 5) $result[] = $matches[5];// ^^^ 5th brackets
					}
				} else {
					preg_match($measure, $su, $matches);
					if (count($matches) >= 5) $result[] = $matches[5];// ^^^ 5th brackets
				}					
			}
		}
		return array_unique($result);	// make sure all units appear only once.
	}
	
	public function getPossibleValues(Title $property) {
		$possibleValueDV = SMWPropertyValue::makeProperty("_PVAL");
		$poss_values = smwfGetStore()->getPropertyValues($property, $possibleValueDV);
		$result = array();
		foreach($poss_values as $v) {
			$result[] = $v->getXSDValue();
		}		
		return $result;
	}
	
	public function getPages($match, $namespaces = NULL) {
		$result = "";
		$db =& wfGetDB( DB_SLAVE );
		$sql = "";
		$page = $db->tableName('page');
		$requestoptions = new SMWRequestOptions();
		$requestoptions->limit = SMW_AC_MAX_RESULTS;
		$options = DBHelper::getSQLOptionsAsString($requestoptions);
		if ($namespaces == NULL || count($namespaces) == 0) {
			
			$sql .= '(SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes($match.'%').') ORDER BY page_title ';
			
		} else {
		
			for ($i = 0, $n = count($namespaces); $i < $n; $i++) { 
				if ($i > 0) $sql .= ' UNION ';
				$sql .= '(SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes($match.'%').') AND page_namespace='.$db->addQuotes($namespaces[$i]).' ORDER BY page_title) UNION ';
				$sql .= '(SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND page_namespace='.$db->addQuotes($namespaces[$i]).' ORDER BY page_title) ';
			}
					
		}
						
		$result = array();
		
		$res = $db->query($sql.$options);
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	
	public function getPropertyWithType($match, $typeLabel) {
		$db =& wfGetDB( DB_SLAVE );
		$smw_specialprops = $db->tableName('smw_specialprops');
		$page = $db->tableName('page');
		$result = array();
		$typeID = SMWDataValueFactory::findTypeID($typeLabel);

		$res = $db->query("(SELECT p1.page_title AS title FROM $smw_specialprops s2 " .
		                    "JOIN $page p2 ON s2.subject_id=p2.page_id " .
		                    "JOIN $smw_specialprops s1 ON LOCATE(p2.page_title, s1.value_string) > 0 " .
		                    "JOIN $page p1 ON s1.subject_id = p1.page_id " .
		                    'WHERE UPPER(p1.page_title) LIKE UPPER(' . $db->addQuotes("%$match%") .
		                    ') AND p1.page_namespace = ' . SMW_NS_PROPERTY .
		                    ' AND s2.value_string REGEXP ' . $db->addQuotes("([0-9].?[0-9]*|,) $typeLabel(,|$)") .
		                    ') UNION DISTINCT ' .
		                    '(SELECT page_title AS title FROM '.$smw_specialprops.' JOIN '.$page.' ON subject_id = page_id' .
		                    ' WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND property_id = '."_TYPE".' AND UPPER(value_string) = UPPER('.$db->addQuotes($typeID).'))' .
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
		$db =& wfGetDB( DB_SLAVE );
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
					' WHERE r.nary_pos = '.$nary_pos.' AND n.attribute_title = '. $db->addQuotes(smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()). ' AND r.object_title IN (SELECT cl_to FROM '.$categorylinks.' WHERE cl_from = ' .$db->addQuotes($instance->getArticleID()).') AND UPPER(n.subject_title) LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').') AND p.page_is_redirect = 0)');
	
		$db->query('INSERT INTO smw_ob_properties_sub  (SELECT DISTINCT page_id AS category FROM '.$categorylinks.' JOIN '.$page.' ON cl_to = page_title AND page_namespace = '.NS_CATEGORY.' WHERE cl_from = ' .$instance->getArticleID().')');    
		
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
		
		
		$db->query('DROP TEMPORARY TABLE smw_ob_properties');
		$db->query('DROP TEMPORARY TABLE smw_ob_properties_super');
		$db->query('DROP TEMPORARY TABLE smw_ob_properties_sub');
		return $result;
	}
	
	public function getInstanceAsTarget($userInputToMatch, $domainRangeAnnotations) {
		global $smwgDefaultCollation;
        $db =& wfGetDB( DB_SLAVE ); 
        $page = $db->tableName('page');
        $categorylinks = $db->tableName('categorylinks');
    
        if (!isset($smwgDefaultCollation)) {
            $collation = '';
        } else {
            $collation = 'COLLATE '.$smwgDefaultCollation;
        }
        // create virtual tables
        $db->query( 'CREATE TEMPORARY TABLE smw_ob_instances (instance VARCHAR(255) '.$collation.')
                    TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );
        
        $db->query( 'CREATE TEMPORARY TABLE smw_ob_instances_sub (category VARCHAR(255) '.$collation.' NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );
        $db->query( 'CREATE TEMPORARY TABLE smw_ob_instances_super (category VARCHAR(255) '.$collation.' NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );
      
        // initialize with direct instances
        foreach($domainRangeAnnotations as $dr) {               
        	$dvs = $dr->getDVs();
        	if ($dvs[1] == NULL || !$dvs[1]->isValid()) continue;
            $db->query('INSERT INTO smw_ob_instances (SELECT page_title AS instance FROM '.$page.' ' .
                        'JOIN '.$categorylinks.' ON page_id = cl_from ' .
                        'WHERE page_is_redirect = 0 AND page_namespace = '.NS_MAIN.' AND cl_to = '.$db->addQuotes($dvs[1]->getTitle()->getDBkey()).' AND UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
    
       
            $db->query('INSERT INTO smw_ob_instances_super VALUES ('.$db->addQuotes($dvs[1]->getTitle()->getDBkey()).')');
            
        }
        
        $maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
        // maximum iteration length is maximum category tree depth.
        do  {
            $maxDepth--;
            
            // get next subcategory level
            $db->query('INSERT INTO smw_ob_instances_sub (SELECT DISTINCT page_title AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_id = cl_from WHERE page_namespace = '.NS_CATEGORY.' AND cl_to IN (SELECT * FROM smw_ob_instances_super))');
            
            // insert direct instances of current subcategory level
            $db->query('INSERT INTO smw_ob_instances (SELECT page_title AS instance FROM '.$page.' ' .
                        'JOIN '.$categorylinks.' ON page_id = cl_from ' .
                        'WHERE page_is_redirect = 0 AND page_namespace = '.NS_MAIN.' AND cl_to IN (SELECT * FROM smw_ob_instances_sub) AND UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
            
            // copy subcatgegories to supercategories of next iteration
            $db->query('DELETE FROM smw_ob_instances_super');
            $db->query('INSERT INTO smw_ob_instances_super (SELECT * FROM smw_ob_instances_sub)');
            
            // check if there was least one more subcategory. If not, all instances were found.
            $res = $db->query('SELECT COUNT(category) AS numOfSubCats FROM smw_ob_instances_super');
            $numOfSubCats = $db->fetchObject($res)->numOfSubCats;
            $db->freeResult($res);
            
            $db->query('DELETE FROM smw_ob_instances_sub');
            
        } while ($numOfSubCats > 0 && $maxDepth > 0);
        
    
        $db->query('DROP TEMPORARY TABLE smw_ob_instances_super');
        $db->query('DROP TEMPORARY TABLE smw_ob_instances_sub');
        
       
        $res = $db->query('SELECT DISTINCT instance FROM smw_ob_instances ORDER BY instance LIMIT '.SMW_AC_MAX_RESULTS);
        
        $results = array();
        if($db->numRows( $res ) > 0)
        {
            $row = $db->fetchObject($res);
           
               while($row)
                {   
                    $instance = Title::newFromText($row->instance, NS_MAIN);
                    $results[] = $instance;
                    $row = $db->fetchObject($res);
                }
            
        }
        $db->freeResult($res);
        
        // drop virtual tables
        $db->query('DROP TEMPORARY TABLE smw_ob_instances');
        return $results;
	}
}

class AutoCompletionStorageSQL2 extends AutoCompletionStorageSQL {
	
public function getPropertyWithType($match, $typeLabel) {
        $db =& wfGetDB( DB_SLAVE );
        $smw_spec2 = $db->tableName('smw_spec2');
        $smw_ids = $db->tableName('smw_ids');
        $page = $db->tableName('page');
        $result = array();
        $typeID = SMWDataValueFactory::findTypeID($typeLabel);
        $hasTypePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeProperty("_TYPE"));
        $res = $db->query('(SELECT i2.smw_title AS title FROM '.$smw_ids.' i2 '.
					           'JOIN '.$smw_spec2.' s1 ON i2.smw_id = s1.s_id AND s1.p_id = '.$hasTypePropertyID.' '.
					           'JOIN '.$smw_ids.' i ON s1.value_string = i.smw_title AND i.smw_namespace = '.SMW_NS_TYPE.' '.
					           'JOIN '.$smw_spec2.' s2 ON s2.s_id = i.smw_id AND s2.value_string REGEXP ' . $db->addQuotes("([0-9].?[0-9]*|,) $typeLabel(,|$)") .
					           'WHERE i2.smw_namespace = '.SMW_NS_PROPERTY.' AND UPPER(i2.smw_title) LIKE UPPER(' . $db->addQuotes("%$match%").'))'.
					        ' UNION (SELECT smw_title AS title FROM smw_ids i '.
					           'JOIN '.$smw_spec2.' s1 ON i.smw_id = s1.s_id AND s1.p_id = '.$hasTypePropertyID.' '.
					           'WHERE UPPER(i.smw_title) LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND '.
					           'UPPER(s1.value_string) = UPPER('.$db->addQuotes($typeID).') AND smw_namespace = '.SMW_NS_PROPERTY.') '.
					        'LIMIT '.SMW_AC_MAX_RESULTS);
        
       
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
        $db =& wfGetDB( DB_SLAVE );
        $page = $db->tableName('page');
        $categorylinks = $db->tableName('categorylinks');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
       
        
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
        
        $domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()) );
        if ($domainAndRange == NULL) {
            $domainAndRangeID = -1; // does never exist
        } else {
            $domainAndRangeID = $domainAndRange->smw_id;
        }
        
        $db->query('INSERT INTO smw_ob_properties (SELECT q.smw_id AS id, q.smw_title AS property FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = '.$nary_pos.' AND r.smw_title IN (SELECT cl_to FROM '.$categorylinks.' WHERE cl_from = ' .$db->addQuotes($instance->getArticleID()).') AND r.smw_namespace = '.NS_CATEGORY.' AND UPPER(q.smw_title) LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
        
      
        $db->query('INSERT INTO smw_ob_properties_sub  (SELECT DISTINCT page_id AS category FROM '.$categorylinks.' JOIN '.$page.' ON cl_to = page_title AND page_namespace = '.NS_CATEGORY.' WHERE cl_from = ' .$instance->getArticleID().')');    
        
        $maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
        // maximum iteration length is maximum category tree depth.
        do  {
            $maxDepth--;
            
            // get next supercategory level
            $db->query('INSERT INTO smw_ob_properties_super (SELECT DISTINCT page_id AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_title = cl_to WHERE page_namespace = '.NS_CATEGORY.' AND cl_from IN (SELECT * FROM smw_ob_properties_sub))');
            
            // insert direct properties of current supercategory level
            $db->query('INSERT INTO smw_ob_properties (SELECT q.smw_id AS id, q.smw_title AS property FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = '.$nary_pos.' AND r.smw_title IN (SELECT * FROM smw_ob_properties_super) AND r.smw_namespace = '.NS_CATEGORY.' AND UPPER(q.smw_title) LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
           
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
        
        
        $db->query('DROP TEMPORARY TABLE smw_ob_properties');
        $db->query('DROP TEMPORARY TABLE smw_ob_properties_super');
        $db->query('DROP TEMPORARY TABLE smw_ob_properties_sub');
        return $result;
    }
   
}
?>
