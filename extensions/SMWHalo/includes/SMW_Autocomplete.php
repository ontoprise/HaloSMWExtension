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
 	

  	// remove common namespaces from user input
 	$userInputToMatch = AutoCompletionRequester::removeCommonNamespaces($userInputToMatch); 
 	// remove whitespaces from user input and replace with underscores
 	$userInputToMatch = str_replace(" ","_",$userInputToMatch);
 	
 	if ($userContext == null || $userContext == "" || !AutoCompletionRequester::isContext($userContext)) {
 			// no context: that means only non-semantic AC is possible.
 			
 			// if there is a type hint (=namespace), use it.
 			if ($typeHint != null && is_numeric($typeHint)) {
 				$typeHintNum = $typeHint + 0;
 				$pages = AutoCompletionRequester::getPages($userInputToMatch, array($typeHintNum));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages);
 			} else { 
 	    		$pages = AutoCompletionRequester::getPages($userInputToMatch, array(SMW_NS_PROPERTY, NS_CATEGORY, NS_MAIN, NS_TEMPLATE, SMW_NS_TYPE));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages);
 			}
 	    	
 	} else if (stripos($userContext, "[[") === 0){  
 		// semantic context
 		// decide according to context which autocompletion is appropriate
 	   
 	    // ------------------------	
 	    // 1. category case
 	    // ------------------------	
 	    if (stripos(strtolower($userContext), strtolower($wgLang->getNsText(NS_CATEGORY)).":") > 0) { 
 	    	return AutoCompletionRequester::getCategoryProposals($userInputToMatch);
 	    }
 	     
 	    // ------------------------------------------------
 	    // 2./3. property target case / property value case
 	    // ------------------------------------------------	
 	    else if (stripos($userContext,":=") > 0 || stripos($userContext,"::") > 0) {
 	    	
 	    	$propertyTargets = AutoCompletionRequester::getPropertyTargetProposals($userContext, $userInputToMatch);
 	    	
 	    	$attributeValues = AutoCompletionRequester::getPropertyValueProposals($userContext);
 	    	
 	    	// if there is a unit or possible values, show them. Otherwise show instances.
 	    	return $attributeValues != SMW_AC_NORESULT ? $attributeValues : $propertyTargets;
 	 	    
 	     	    	
 	    // --------------------------------
 	    // 4.property name case
 	    // --------------------------------	
 	    } else {
 	    	return AutoCompletionRequester::getPropertyProposals($articleName, $userInputToMatch);
 	    	
 	    }
 
 	} else if (stripos($userContext, "{{") === 0) {  
 		// template context
 		return AutoCompletionRequester::getTemplateProposals($userContext, $userInputToMatch);
 		
 	}
  	
 }
 
/**
 * Register extra AC related options in Preferences->Misc
 */
function smwfAutoCompletionToggles(&$extraToggles) {
	$extraToggles[] = "autotriggering";
}

function smwfSetUserDefinedCookies(&$wgCookiePrefix, &$exp, &$wgCookiePath, &$wgCookieDomain, &$wgCookieSecure) {
	global $wgUser;
	$triggerMode = $wgUser->getOption( "autotriggering" ) == 1 ? "auto" : "manual";
	setcookie("AC_mode", $triggerMode);
	return true;
}

/**
 * Return options
 */
function smwfAutoCompletionOptions() {
	global $wgUser;
	return $wgUser->getOption( "autotriggering" ) == 1 ? "auto" : "manual";
}


class AutoCompletionRequester { 
	
	/**
	 * Get category proposals matching $match.
	 */
	public static function getCategoryProposals($match) {
		$categories = AutoCompletionRequester::getPages($match, array(NS_CATEGORY));
 	    return AutoCompletionRequester::encapsulateAsXML($categories);
	}
	
	/**
	 * Get Property target proposals. Consider special properties too
	 */
	public static function getPropertyTargetProposals($userContext, $match) {
		// special handling for special relations
		
 	    	global $smwgContLang, $semanticAC, $wgLang;
 	    	$specialProperties = $smwgContLang->getSpecialPropertiesArray();
 	    	$specialSchemaProperties = $smwgContLang->getSpecialSchemaPropertyArray();
 	    		
 	    	// special properties
 	    	if (stripos(strtolower($userContext), strtolower($specialProperties[SMW_SP_SUBPROPERTY_OF])) > 0) {
 	    		$pages = AutoCompletionRequester::getPages($match, array(SMW_NS_PROPERTY));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else if (stripos(strtolower($userContext),strtolower($specialProperties[SMW_SP_HAS_TYPE])) > 0) { 
 	    		// has type relation 
 	    		$pages = AutoCompletionRequester::getPages($match, array(SMW_NS_TYPE));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else if (stripos(strtolower($userContext),strtolower($specialSchemaProperties[SMW_SSP_HAS_DOMAIN_HINT])) > 0) { 
 	    		// has domain hint relation 
 	    		$pages = AutoCompletionRequester::getPages($match, array(NS_CATEGORY));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else if (stripos(strtolower($userContext),strtolower($specialSchemaProperties[SMW_SSP_HAS_RANGE_HINT])) > 0) { 
 	    		// has range hint relation 
 	    		$pages = AutoCompletionRequester::getPages($match, array(NS_CATEGORY));
 	    		return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
 	    	} else {
 	    		if ($semanticAC) { 
 	    			if (stripos($userContext,":=") > 0) { 
 	    				$relationText = substr($userContext, 2, stripos($userContext,":=")-2);
 	    			} else {
 	    				$relationText = substr($userContext, 2, stripos($userContext,"::")-2);
 	    			}
 	    	
 	    			$property = Title::newFromText($relationText, SMW_NS_PROPERTY);
 	    		
 	    			$rangeRelation = smwfGetOntologyBrowserAccess()->rangeHintRelation;
 	    			$categories = smwfGetStore()->getPropertyValues($property, $rangeRelation);
 	    			$pages = array();
 	    			foreach ($categories as $c) {
 	    				$instances = smwfGetOntologyBrowserAccess()->getDirectInstances($c->getTitle());
 	    				$pages = array_merge($pages, $instances);
 	    			}
 	    			return AutoCompletionRequester::encapsulateAsXML($pages);
 	    		} else {  	
 	    			// all others
 	   				$pages = AutoCompletionRequester::getPages($match, array(NS_MAIN));
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
 	    		$categoriesOfArticle = smwfGetOntologyBrowserAccess()->getCategoriesForInstance($articleTitle);
 	    		
 	    		$domainRelation = smwfGetOntologyBrowserAccess()->domainHintRelation;
 	    		$pages = array();
 	    		foreach($categoriesOfArticle as $category) {
 	    			$value = SMWDataValueFactory::newTypeIDValue('_wpg');
  					$value->setValues($category->getDBKey(), $category->getNamespace());
 	    			$properties = smwfGetStore()->getPropertySubjects($domainRelation, $value);
 	    			$pages = array_merge($pages, $properties);
 	    		}
			
 	    	} else {
 	    		$pages = AutoCompletionRequester::getPages($match, array(NS_MAIN, SMW_NS_PROPERTY, SMW_NS_RELATION));
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
 				$results = AutoCompletionRequester::getPages($match, array(NS_MAIN, SMW_NS_PROPERTY));
 				return AutoCompletionRequester::encapsulateAsXML($results);
 			} else { // otherwise it is a template name
 	    		$templates = AutoCompletionRequester::getPages($match, array(NS_TEMPLATE));
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
 				$results = AutoCompletionRequester::getPages($match, array(SMW_NS_PROPERTY, NS_MAIN));
 				return AutoCompletionRequester::encapsulateAsXML($results);
 			} else { // otherwise it is a template name
 	    		$templates = AutoCompletionRequester::getPages($match, array(NS_TEMPLATE));
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
 	* @param $titles array of Title
 	* @param $putNameSpaceInName If true system would return 'namespace:localname' otherwise 'localname'
 	* @param optional extra data which is pasted behind the Title. (array sizes of $titles and $extraData must matched, if used.)
 	* @return xml string
 	*/
	public static function encapsulateAsXML($titles, $putNameSpaceInName = false, $extraData = NULL) {
		if ($extraData != NULL && count($titles) != count($extraData)) {
			return SMW_AC_NORESULT;
		}
		$result = '<result>';
		global $smwgContLang;
		$ns = $smwgContLang->getNamespaceArray();
		for($i = 0, $n = count($titles); $i < $n; $i++) {
			// special handling for non-SMW namespace Category
			$namespace = AutoCompletionRequester::getNamespaceText($titles[$i]);
			$extra = $extraData != NULL ? $extraData[$i] : ""; 
			$result .= "<match type=\"".$titles[$i]->getNamespace()."\">".($putNameSpaceInName ? $namespace.":" : "").$titles[$i]->getDBkey().$extra."</match>";
		}
		return empty($titles) ? SMW_AC_NORESULT : $result.'</result>';
	}

	/**
 	*  Encapsulate an array of enums or units in a xml string.
 	*/
	public static function encapsulateEnumsOrUnitsAsXML($arrayofEnumsOrUnits) {
		$result = '<result>';
		foreach($arrayofEnumsOrUnits as $eou) {
			$result .= "<match type=\"-1\">".$eou."</match>";
		}
		return empty($arrayofEnumsOrUnits) ? SMW_AC_NORESULT : $result.'</result>';
	}
	
	

	/**
 	* Retrieves pages matching the requestoptions and the given namespaces
 	* 
 	* @return array of Title
 	*/
	public static function getPages($match, $namespaces = NULL, $requestoptions = NULL) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = "";
		
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
		$res = $db->query('(SELECT page_title, page_namespace FROM page WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes($match.'%').') AND ' .$sql.' ORDER BY page_namespace DESC) '.
							' UNION (SELECT page_title, page_namespace FROM page WHERE UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND '.$sql.' ORDER BY page_namespace DESC) LIMIT '.SMW_AC_MAX_RESULTS.'');
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	/**
	 * Removes the common SMW namespace from $titleText.
	 */
	public static function removeCommonNamespaces($titleText) {
		global $smwgContLang;
		$namespaces = array_values($smwgContLang->getNamespaceArray());
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
	private static function getUndefinedPropertiesFromSMWTables(& $result, $namespaces, $requestoptions) {
		$db =& wfGetDB( DB_MASTER );
		if (in_array(SMW_NS_ATTRIBUTE, $namespaces)) {
			$attConds = getSQLConditions($requestoptions,'attribute_title','attribute_title');
			$res = $db->query('SELECT DISTINCT attribute_title FROM smw_attributes WHERE attribute_title NOT IN (SELECT page_title FROM page WHERE attribute_title = page_title)'.$attConds.";");
		    if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = Title::newFromText($row->attribute_title, SMW_NS_ATTRIBUTE);
				}
			}
			$db->freeResult($res);
		}
		if (in_array(SMW_NS_RELATION, $namespaces)) {
			$relConds = getSQLConditions($requestoptions,'relation_title','relation_title');
			$res = $db->query('SELECT DISTINCT relation_title FROM smw_relations WHERE relation_title NOT IN (SELECT page_title FROM page WHERE relation_title = page_title)'.$relConds.";");
		    if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = Title::newFromText($row->relation_title, SMW_NS_RELATION);
				}
			}
			$db->freeResult($res);
		}
	}
	
	/**
	 * Transform input parameters into a suitable array of SQL options.
	 * The parameter $valuecol defines the string name of the column to which
	 * sorting requests etc. are to be applied.
	 */
	private static function getSQLOptions($requestoptions, $valuecol = NULL) {
		$sql_options = array();
		if ($requestoptions !== NULL) {
			if ($requestoptions->limit >= 0) {
				$sql_options['LIMIT'] = $requestoptions->limit;
			}
			if ($requestoptions->offset > 0) {
				$sql_options['OFFSET'] = $requestoptions->offset;
			}
			if ( ($valuecol !== NULL) && ($requestoptions->sort) ) {
				$sql_options['ORDER BY'] = $requestoptions->ascending ? $valuecol : $valuecol . ' DESC';
			}
		}
		return $sql_options;
	}

	/**
	 * Transform input parameters into a suitable string of additional SQL conditions.
	 * The parameter $valuecol defines the string name of the column to which
	 * value restrictions etc. are to be applied.
	 * @param $requestoptions object with options
	 * @param $valuecol name of SQL column to which conditions apply
	 * @param $labelcol name of SQL column to which string conditions apply, if any
	 */
	 private static function getSQLConditions($requestoptions, $valuecol, $labelcol = NULL) {
		$sql_conds = '';
		if ($requestoptions !== NULL) {
			$db =& wfGetDB( DB_MASTER ); // TODO: use slave?
			if ($requestoptions->boundary !== NULL) { // apply value boundary
				if ($requestoptions->ascending) {
					if ($requestoptions->include_boundary) {
						$op = ' >= ';
					} else {
						$op = ' > ';
					}
				} else {
					if ($requestoptions->include_boundary) {
						$op = ' <= ';
					} else {
						$op = ' < ';
					}
				}
				$sql_conds .= ' AND ' . $valuecol . $op . $db->addQuotes($requestoptions->boundary);
			}
			if ($labelcol !== NULL) { // apply string conditions
				foreach ($requestoptions->getStringConditions() as $strcond) {
					$string = str_replace(array('_', ' '), array('\_', '\_'), $strcond->string);
					switch ($strcond->condition) {
						case SMW_STRCOND_PRE:
							$string .= '%';
							break;
						case SMW_STRCOND_POST:
							$string = '%' . $string;
							break;
						case SMW_STRCOND_MID:
							$string = '%' . $string . '%';
							break;
					}
					if ($requestoptions->isCaseSensitive) { 
						$sql_conds .= ' AND ' . $labelcol . ' LIKE ' . $db->addQuotes($string);
					} else {
						$sql_conds .= ' AND UPPER(' . $labelcol . ') LIKE UPPER(' . $db->addQuotes($string).')';
					}
				}
			}
		}
		return $sql_conds;
	}
	
		
	private static function getNamespaceText($page) {
 		global $smwgContLang, $wgLang;
 		$nsArray = $smwgContLang->getNamespaceArray();
 		if ($page->getNamespace() == NS_TEMPLATE || $page->getNamespace() == NS_CATEGORY) {
 			$ns = $wgLang->getNsText($page->getNamespace());
 				} else { 
 			$ns = $page->getNamespace() != NS_MAIN ? $nsArray[$page->getNamespace()] : "";
 		}
 		return $ns;
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


?>
