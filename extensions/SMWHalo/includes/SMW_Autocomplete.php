<?php
/*  Copyright 2007, ontoprise GmbH
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
 */

/**
 * @file
 * @ingroup SMWHaloAutocompletion
 *
 * Created on 21.02.2007
 * Author: KK
 * AutoCompletion Dispatcher
 *
 * @defgroup SMWHaloAutocompletion SMWHalo Autocompletion
 * @ingroup SMWHalo
 */

// Register AJAX functions
if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ac_AutoCompletionDispatcher';
$wgAjaxExportList[] = 'smwf_ac_AutoCompletionOptions';


define('SMW_AC_NORESULT', "noResult");
define('SMW_AC_MAX_RESULTS', 15);
define('SMW_AC_MAX_INSTANCE_SAMPLES', 5);

$smwhgAutoCompletionStore = null;

global $smwgHaloIP;
require_once( $smwgHaloIP . "/includes/SMW_DBHelper.php");
require_once( $smwgHaloIP . "/includes/SMW_Autocomplete_Storage.php");

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
function smwf_ac_AutoCompletionDispatcher($articleName, $userInputToMatch, $userContext, $constraints) {
	global $wgLang;

	smwLog(($userContext != null ? $userContext : "").$userInputToMatch, "AC", "activated", $articleName);
	// remove common namespaces from user input
	$namespaceDelimiter = strpos($userInputToMatch, ":");
	$namespaceText = $namespaceDelimiter !== false ? substr($userInputToMatch, 0, $namespaceDelimiter) : NULL;
	$userInputToMatch = $namespaceDelimiter !== false ? substr($userInputToMatch, $namespaceDelimiter+1) : $userInputToMatch;
	// remove whitespaces from user input and replace with underscores
	$userInputToMatch = str_replace(" ","_",$userInputToMatch);

	// Check for context or not
	if ($userContext == null || $userContext == "" || !AutoCompletionRequester::isContext($userContext)) {
		// no context: that means only non-semantic AC is possible. Maybe a $constraints string is specified
		if ($constraints == null || $constraints == 'null' || $constraints == 'all') {
			// if no constraints defined, search for (nearly) all pages

			// if $namespaceText is a valid namespace prefix
			// then $namespaceIndex contains the according index.
			global $wgExtraNamespaces, $wgLang;
			$namespaceIndex=false;
			if (!is_null($namespaceText)) {
				$namespaceIndex = $wgLang->getNsIndex($namespaceText);

				if ($namespaceIndex === false) {
					$flippedNamespaces = array_flip($wgExtraNamespaces);
					if (in_array($namespaceText, $flippedNamespaces)) {
						$namespaceIndex = $flippedNamespaces[$namespaceText];
					}
				}
			}

			// if no namespace, just search all namespaces
			if ($namespaceIndex === false) {
				$namespaces = array_unique(array_merge(array(SMW_NS_PROPERTY, NS_CATEGORY, NS_MAIN, NS_TEMPLATE, SMW_NS_TYPE), array_keys($wgExtraNamespaces)));
				$pages = AutoCompletionHandler::executeCommand("namespace: ".implode(",", $namespaces), $userInputToMatch);
			} else {
				$pages = AutoCompletionHandler::executeCommand("namespace: ".$namespaceIndex, $userInputToMatch);
			}

		} else {
			// otherwise use constraints
			$pages = AutoCompletionHandler::executeCommand($constraints, $userInputToMatch);
		}
		
		AutoCompletionRequester::attachCategoryHints($pages);
		AutoCompletionRequester::attachImageURL($pages);
		$result = AutoCompletionRequester::encapsulateAsXML($pages);
		return $result;
	} else if (stripos($userContext, "[[") === 0){
		// semantic context
		// decide according to context which autocompletion is appropriate
		// ------------------------
		// 1. category case
		// ------------------------
		if (stripos(strtolower($userContext), strtolower($wgLang->getNsText(NS_CATEGORY)).":") > 0) {
			$categories = smwfGetAutoCompletionStore()->getPages($match, array(NS_CATEGORY));
			AutoCompletionRequester::attachCategoryHints($categories);
			return $categories;
		}
		// ------------------------------------------------
		// 2./3. property target case / property value case
		// ------------------------------------------------
		else if (stripos($userContext,":=") > 0 || stripos($userContext,"::") > 0) {

			// get property name
			$propertyText = trim(substr($userContext, 2, stripos($userContext,"::")-2));
			$pv = SMWPropertyValue::makeUserProperty($propertyText);
			$typeID = $pv->getPropertyTypeID();

			// returns syntax examples for several datatypes
			if ($typeID == '_dat') {
				return AutoCompletionRequester::encapsulateConstantMatchesAsXML(explode("|",(wfMsg('smw_ac_datetime_proposal'))));
			} else if ($typeID == '_boo') {
				return AutoCompletionRequester::encapsulateConstantMatchesAsXML(array_merge(explode(",",wfMsg('smw_true_words')), explode(",",wfMsg('smw_false_words'))));
			} else if ($typeID == '_geo') {
				return AutoCompletionRequester::encapsulateConstantMatchesAsXML(explode("|",(wfMsg('smw_ac_geocoord_proposal'))));
			} else if ($typeID == '_rec') {

				// request expected types of record property
				$values = smwfGetStore()->getPropertyValues(Title::newFromText($propertyText, SMW_NS_PROPERTY), SMWPropertyValue::makeProperty("_LIST"));
				$typeValues = $values[0]->getTypeValues();

				$proposal = "";
				for($i = 0; $i < count($typeValues); $i++) {
					$tv = $typeValues[$i];
					$proposal .= $tv->getWikiValue();
					if ($i < count($typeValues)-1) $proposal .= "; ";
				}

				return AutoCompletionRequester::encapsulateConstantMatchesAsXML(array($proposal));
			} else if ($typeID == '_ema') {
				return AutoCompletionRequester::encapsulateConstantMatchesAsXML(array(wfMsg('smw_ac_email_proposal')));
			} else if ($typeID == '_tem') {
				return AutoCompletionRequester::encapsulateConstantMatchesAsXML(explode(",",wfMsg('smw_ac_temperature_proposal')));
			} else if ($typeID == '_tel') {
				return AutoCompletionRequester::encapsulateConstantMatchesAsXML(array(wfMsg('smw_ac_telephone_proposal')));
			}

			// try enumeration values and units
			$attributeValues = AutoCompletionRequester::getPropertyValueProposals($userContext, $userInputToMatch);

			if ($attributeValues != SMW_AC_NORESULT) {
				return $attributeValues;
			} else {
				// try instance values that match
				$propertyTargets = AutoCompletionRequester::getPropertyTargetProposals($userContext, $userInputToMatch);
				return $propertyTargets;
			}


			// --------------------------------
			// 4.property name case
			// --------------------------------
		} else {
			
			$result = AutoCompletionRequester::getPropertyProposals($articleName, $userInputToMatch);
            AutoCompletionRequester::attachCategoryHints($result);
            AutoCompletionRequester::attachImageURL($result);
            return AutoCompletionRequester::encapsulateAsXML($result);
			

		}

	} else if (stripos($userContext, "{{") === 0) {
		// template context
		global $wgLang;
		$namespace = NS_TEMPLATE;
		if (defined('SF_NS_FORM')) {
			$form_ns_text = $wgLang->getNsText(SF_NS_FORM);
			if ($namespaceText == $form_ns_text) {
				$namespace = SF_NS_FORM;
			}
		}
		$result = AutoCompletionRequester::getTemplateOrFormProposals($userContext, $userInputToMatch , $namespace );
		return $result;

	} else if (preg_match('/\|\s*template\s*=/', $userContext) > 0) {
		// template query parameter context
		$pages = AutoCompletionHandler::executeCommand("namespace: ".NS_TEMPLATE, $userInputToMatch);
		$result = AutoCompletionRequester::encapsulateAsXML($pages);
		return $result;
	} else if (preg_match('/\|\s*format\s*=/', $userContext) > 0) {
		// format query parameter context
		global $smwgResultFormats;
		$pages = AutoCompletionHandler::executeCommand("values: ".implode(",",array_keys($smwgResultFormats)), $userInputToMatch);
		$result = AutoCompletionRequester::encapsulateAsXML($pages);
		return $result;
	} else if (stripos($userContext, "?") === 0) {
		// query printout context
		$pages = AutoCompletionHandler::executeCommand("namespace: ".SMW_NS_PROPERTY, $userInputToMatch);
		$result = AutoCompletionRequester::encapsulateAsXML($pages);
		return $result;
	} else if (stripos($userContext, "|") === 0) {
		// general query parameter context
		$pages = AutoCompletionHandler::executeCommand("values: sort=,order=asc/desc/reverse,limit=,offset=,format=,headers=,mainlabel=,link=,default=,intro=,outro=,searchlabel=,template=", $userInputToMatch);
		$result = AutoCompletionRequester::encapsulateAsXML($pages);
		return $result;
	}
}

/**
 * Return options and namespace icon mappings.
 */
function smwf_ac_AutoCompletionOptions() {
	global $wgUser;
	if (isset($wgUser) && !is_null($wgUser)) {
		$autoTriggering = $wgUser->getOption( "autotriggering" ) == 1 ? "autotriggering=auto" : "autotriggering=manual";
	} else {
		$autoTriggering = "autotriggering=manual";
	}
	$namespaceMappings = array();
	wfRunHooks('smwhACNamespaceMappings', array (&$namespaceMappings));
	$serializedMappings = "";
	$first = true;
	foreach($namespaceMappings as $nsIndex => $imgPath) {
		$serializedMappings .= ",$nsIndex=$imgPath";
	}
	return "$autoTriggering$serializedMappings";
}

function &smwfGetAutoCompletionStore() {
	global $smwhgAutoCompletionStore, $smwgHaloIP;
	if ($smwhgAutoCompletionStore == NULL) {
		global $smwgDefaultStore;
		switch ($smwgDefaultStore) {

			case ('SMWTripleStoreQuad'):
				global $smwhgAutoCompletionTSC;
				if (isset($smwhgAutoCompletionTSC) && $smwhgAutoCompletionTSC === true) {
					// activate TSC autocompletion only explicitly
					$smwhgAutoCompletionStore = new AutoCompletionStorageTSCQuad();
				} else {
					$smwhgAutoCompletionStore = new AutoCompletionStorageSQL2();
				}
				break;
			case ('SMWTripleStore'): // do not search in TSC because wiki and TSC are synchronous
			case ('SMWHaloStore2'):
			default:
				$smwhgAutoCompletionStore = new AutoCompletionStorageSQL2();
				break;
		}
	}
	return $smwhgAutoCompletionStore;
}

/**
 * TODO: Document, including member functions
 */
class AutoCompletionRequester {

	/**
	 * Attaches images URLs to Match Items
	 *
	 * @param & $matches (out)
	 *
	 * @param hash array $matches
	 */
	public static function attachImageURL(& $matches) {
		for($i = 0; $i < count($matches); $i++) {
			$title = is_array($matches[$i])? $matches[$i]['title'] : $matches[$i];
			if ($title->getNamespace() != NS_MAIN) continue;
			$categories = smwfGetSemanticStore()->getCategoriesForInstance($title);
			foreach($categories as $c) {
				$url = smwfGetAutoCompletionStore()->getImageURL($c);
				if (!is_null($url)) {
					$matches[$i]['title'] = $title;
					$matches[$i]['imageurl'] = $url;
					break;
				}
			}
		}

	}

    /**
     * Attaches category information Match Items
     *
     * @param & $matches (out)
     *      hash array or array of Title/string
     *
     * @param hash array $matches
     */
	public static function attachCategoryHints(& $matches) {
		$options = new SMWRequestOptions();
        $options->limit = SMW_AC_MAX_INSTANCE_SAMPLES;
		for($i = 0; $i < count($matches); $i++) {
			$title = is_array($matches[$i])? $matches[$i]['title'] : $matches[$i];
			if (!is_array($matches[$i])) $matches[$i] = array();
			$matches[$i]['title'] = $title;
		    $matches[$i]['instanceSamples'] = array();
			if ($title->getNamespace() == NS_CATEGORY) {
				$instances = smwfGetSemanticStore()->getDirectInstances($title, $options);
				foreach($instances as $inst) {
					$matches[$i]['instanceSamples'][] = $inst->getPrefixedText();
				}
				
			    $parents = $title->getParentCategoryTree();
                $matches[$i]['parentCategories'] = array();
                
                $next = reset(array_keys($parents));
                while($next !== false) {
                    $matches[$i]['parentCategories'][] = $next;
                    $parents = $parents[$next];
                    $next = reset(array_keys($parents));
                }
                $matches[$i]['parentCategories'] = array_reverse($matches[$i]['parentCategories']);
               
			} else if ($title->getNamespace() == NS_MAIN) {
			    $parents = $title->getParentCategoryTree();
                $matches[$i]['parentCategories'] = array();
			
	            $next = reset(array_keys($parents));
                while($next !== false) {
                    $matches[$i]['parentCategories'][] = $next;
                    $parents = $parents[$next];
                    $next = reset(array_keys($parents));
                }
                $matches[$i]['parentCategories'] = array_reverse($matches[$i]['parentCategories']);
			}
			
		}
	}

	

	/**
	 * Get Property target proposals. Consider special properties too
	 */
	public static function getPropertyTargetProposals($userContext, $match) {
		// special handling for special relations

		global $smwgContLang, $smwgHaloContLang, $wgLang;
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
			AutoCompletionRequester::attachImageURL($pages);
			AutoCompletionRequester::attachCategoryHints($pages);
			return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
		} else if (stripos(strtolower($userContext),strtolower($specialSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT])) > 0) {
			// has domain hint relation
			$pages = smwfGetAutoCompletionStore()->getPages($match, array(NS_CATEGORY));
			AutoCompletionRequester::attachCategoryHints($pages);
			return AutoCompletionRequester::encapsulateAsXML($pages, true); // return namespace too!
		} else {

			$propertyTitle = self::getTitleFromContext($userContext);
			if (!is_null($propertyTitle)) {
				$property = Title::newFromText($propertyTitle, SMW_NS_PROPERTY);
				$domainRangeAnnotations = smwfGetStore()->getPropertyValues($property, smwfGetSemanticStore()->domainRangeHintProp);
				$pages = smwfGetAutoCompletionStore()->getInstanceAsTarget($match, $domainRangeAnnotations);
					
			}

			if (empty($pages)) {
				// fallback
				$pages = smwfGetAutoCompletionStore()->getPages($match, array(NS_MAIN));
			}
			AutoCompletionRequester::attachImageURL($pages);
			return AutoCompletionRequester::encapsulateAsXML($pages);

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
			$attvalues = AutoCompletionRequester::encapsulateConstantMatchesAsXML($unitsList);
		} else {
			$possibleValues = smwfGetAutoCompletionStore()->getPossibleValues($property);
			$attvalues = AutoCompletionRequester::encapsulateConstantMatchesAsXML($possibleValues);
		}
		return $attvalues;
	}

	/**
	 * Get property proposals. Consider special properties too.
	 */
	public static function getPropertyProposals($articleName, $match) {
		global $wgLang;

		$pages = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_PROPERTY, NS_MAIN));

		// special handling for special relations
		$specialMatches = array(); // keeps matches of special relations
		global $smwgContLang;
		$specialProperties = $smwgContLang->getPropertyLabels();

		// propose category
		if (stripos(strtolower($wgLang->getNsText(NS_CATEGORY)), strtolower($match)) !== false) {
			$specialMatches[] = Title::newFromText(strtolower($wgLang->getNsText(NS_CATEGORY)), NS_CATEGORY);
		}

		// propose namespaces
		global $wgExtraNamespaces;
		$namespaceToPropose = $wgExtraNamespaces;
		foreach($namespaceToPropose as $ns => $nsText) {
			if (stripos(strtolower($wgLang->getNsText($ns)), strtolower($match)) !== false) {
				$specialMatches[] = Title::newFromText(strtolower($wgLang->getNsText($ns)), $ns);
			}
		}

		if (stripos(strtolower($specialProperties["_SUBP"]), preg_replace("/_/", " ", strtolower($match))) !== false) {
			$specialMatches[] = Title::newFromText($specialProperties["_SUBP"], SMW_NS_PROPERTY);
		}
		if (stripos(strtolower($specialProperties["_TYPE"]), preg_replace("/_/", " ", strtolower($match))) !== false) {
			$specialMatches[] = Title::newFromText($specialProperties["_TYPE"], SMW_NS_PROPERTY);
		}
		// make sure the special relations come first
		$pages = AutoCompletionHandler::mergeResults($specialMatches, $pages);

		return $pages;
	}

	/**
	 * Get template/form proposals.
	 */
	public static function getTemplateOrFormProposals($userContext, $match, $namespace) {
		// template context
		// parse template paramters
		$templateParameters = explode("|", $userContext);
		if (count($templateParameters) > 1) {
			// if it is a template parameter try the semantic namespaces
			$results = smwfGetAutoCompletionStore()->getPages($match, array(SMW_NS_PROPERTY, NS_CATEGORY, NS_MAIN));
			return AutoCompletionRequester::encapsulateAsXML($results);
		} else { // otherwise it is a template or form name
			$templates = smwfGetAutoCompletionStore()->getPages($match, array($namespace));
			$matches = array();
			if (defined('SF_NS_FORM')) {
				foreach($templates as $t) {
					switch($namespace) {
						case NS_TEMPLATE: $matches[] = array($t, false, TemplateReader::formatTemplateParameters($t));break;
						case SF_NS_FORM: $matches[] = array($t, false);break;
					}
				}
			} else {
				foreach($templates as $t) {
					$matches[] = array($t, false, TemplateReader::formatTemplateParameters($t));
				}
			}

			return AutoCompletionRequester::encapsulateAsXML($matches, $namespace != NS_TEMPLATE);
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
		if (stripos($userContext, "|") === 0 && stripos($userContext, "]]") === false) {
			return true;
		}
		if (stripos($userContext, "?") === 0 && stripos($userContext, "]]") === false) {
			return true;
		}
		return false;
	}

	private static function getTitleFromContext($context) {
		preg_match('/\[\[(([^:]|:[^:])+):[:=]/', $context, $matches);
		return isset($matches[1]) ? $matches[1] : NULL;
	}

	/**
	 * Encapsulate an array of Titles in a xml string.
	 *
	 * @param $matches Array of Title/string or array of key-value pairs
	 *             'title' => Title (required)
	 *             'inferred' => boolean (optional)
	 *             'pasteContent'=> string (optional)
	 *             'imageurl'=>string (optional)
	 *             'schemaData'=>tuple(type, range) (optional)
	 *             'instanceSamples'=>array of string (optional)
	 *             'parentCategories'=> array of string (optional)
	 * @param $putNameSpaceInName If true system would return 'namespace:localname' otherwise 'localname'
	 * 
	 * @return xml string
	 */
	public static function encapsulateAsXML(array & $matches, $putNameSpaceInName = false) {

		if (empty($matches)) {
			return SMW_AC_NORESULT;
		}

		// at least 1 match
		$xmlResult = '';

		for($i = 0, $n = count($matches); $i < $n; $i++) {
			$pasteContent = is_array($matches[$i]) && array_key_exists('pasteContent', $matches[$i]) ? $matches[$i]['pasteContent'] :"";
			$inferred = is_array($matches[$i]) &&  array_key_exists('inferred', $matches[$i]) ? $matches[$i]['inferred'] : false;
			$imageURL = is_array($matches[$i]) &&  array_key_exists('imageurl', $matches[$i]) ? $matches[$i]['imageurl'] : "";
			
			$namespaceText = "";
			$extraData = "";
				
			$arity = count($matches[$i]);
			switch($arity) {
				case 1: 
					$title = $matches[$i]; break;
				default:
					$title = $matches[$i]['title'];break;
			}
			if ($title == NULL) continue;

			// set content (ie. the content to display)
			if (is_string($title)) {
				$typeAtt =  "type=\"-1\""; // no namespace, just a value
				$content = $title;
			} else {
				// $title is actual Title obejct
				if (array_key_exists('schemaData', $matches[$i]) && $title->getNamespace() == SMW_NS_PROPERTY) {
					// extraData contains property schema inforamation
					list($typeStr, $rangeStr) = $matches[$i]['schemaData'];
					$extraData = $rangeStr == NULL ? wfMsg('smw_ac_typehint', $typeStr) : wfMsg('smw_ac_typerangehint', $typeStr, $rangeStr);
				}
				if (array_key_exists('instanceSamples', $matches[$i]) && $title->getNamespace() == NS_CATEGORY) {
					if (!empty($extraData)) $extraData .= "<br>";
                    $extraData .= implode(", ", $matches[$i]['instanceSamples']);
                    if (count($matches[$i]['instanceSamples']) == SMW_AC_MAX_INSTANCE_SAMPLES) $extraData .= ", ...";
                }  
                if (array_key_exists('parentCategories', $matches[$i]) && ($title->getNamespace() == NS_CATEGORY || $title->getNamespace() == NS_MAIN)) {
                	if (!empty($extraData)) $extraData .= "<br>";
                    $extraData .= implode(" -> ", $matches[$i]['parentCategories']);
                }
				$typeAtt = "type=\"".$title->getNamespace()."\"";
				$namespaceText = "nsText=\"".$title->getNsText()."\"";
				$content = ($putNameSpaceInName ? htmlspecialchars($title->getPrefixedDBkey()) : htmlspecialchars($title->getDBkey()));
			}
				
			// set all other
			$inferredAtt = $inferred ? 'inferred="true"' : 'inferred="false"';
			$pasteContent = htmlspecialchars($pasteContent);
			$extraData = htmlspecialchars($extraData);
			$imageURLAtt = "imageurl=\"".str_replace('"', '&quot;', $imageURL)."\"";
				
			// assemble match item
			$xmlResult .= "<match $typeAtt $inferredAtt $namespaceText $imageURLAtt><display>$content</display><pasteContent>$pasteContent</pasteContent><extraData>$extraData</extraData></match>";
		}

		return '<result maxMatches="'.SMW_AC_MAX_RESULTS.'">'.$xmlResult.'</result>';
	}

	/**
	 *  Encapsulate an array of constant matches in a XML string.
	 *
	 *  @param array $constantMatches
	 */
	public static function encapsulateConstantMatchesAsXML($constantMatches) {
		if (empty($constantMatches)) {
			return SMW_AC_NORESULT;
		}

		$xmlResult = '';
		foreach($constantMatches as $eou) {
			$xmlResult .= "<match type=\"500\"><display>".htmlspecialchars($eou)."</display><pasteContent/><extraData/></match>";
		}
		return '<result maxMatches="'.SMW_AC_MAX_RESULTS.'">'.$xmlResult.'</result>';
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

/**
 * Handler for the auto-completion query syntax.
 *
 */
class AutoCompletionHandler {


	/**
	 * Parses auto-completion command.
	 *
	 * Syntax is:
	 *
	 *     1. S -> C [ '|' S ]
	 *     2. C -> CT ':' P
	 *     3. P -> PM [ ',' P ] | Epsilon;
	 *
	 *     CT is a command token, PM a parameter token.
	 *     (tokens are alphanumeric with special characters except comma and pipe)
	 *
	 * @param string $commandText
	 * @return array of ($command, $parameters)
	 */
	private static function parseCommand($commandText) {
		$result = array();
		$commands = explode("|", $commandText);
		foreach($commands as $c) {
			$sep = strpos($c, ":");
			if ($sep === false) continue; // something wrong with the command. ignore.
			$command = substr($c, 0, $sep);
			$params = substr($c, $sep + 1);
			if (!is_null($command) && !is_null($params)) {
				$result[] = array($command, explode(",", trim($params)));
			}
		}
		return $result;
	}

	/**
	 * Executes a series of auto-completion commands and stops when it
	 * has a found at least one result. Except when it matches local values.
	 *
	 * @param string $command
	 * @param substring $userInput
	 * @return array of Title, array of (Title, inferred) or array of (Title, inferred, extraContent)
	 */
	public static function executeCommand($command, $userInput) {
		$parsedCommands = self::parseCommand($command);
		$acStore = smwfGetAutoCompletionStore();

		$result = array();
		$first = true;
		foreach($parsedCommands as $c) {

			list($commandText, $params) = $c;

			if ($commandText == 'values') {
				foreach($params as $p) {
					if (empty($userInput) || stripos($p, $userInput) !== false) $result[] = $p;
				}

				// continue to fill in results if possible
			} else if ($commandText == 'fixvalues') {
				foreach($params as $p) {
					$result[] = $p;
				}

				// continue to fill in results if possible
			} else if ($commandText == 'schema-property-domain') {
				if (empty($params[0]) || is_null($params[0])) continue;
				if (smwf_om_userCan($params[0], 'read') == 'true') {
					$category = Title::newFromText($params[0]);
					if (!is_null($category)) {
						$pages = $acStore->getPropertyForCategory($userInput, $category);
						$inf = self::setInferred($pages, !$first);
						$result = self::mergeResults($result, $inf);

					}
				}
				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			} else if ($commandText == 'schema-property-range-instance') {
				if (empty($params[0]) || is_null($params[0])) continue;
				if (smwf_om_userCan($params[0], 'read') == 'true') {
					$instance = Title::newFromText($params[0]);
					if (!is_null($instance)) {
						$pages = $acStore->getPropertyForInstance($userInput, $instance, false);
						$inf = self::setInferred($pages, !$first);
						$result = self::mergeResults($result, $inf);

					}
				}
				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			} else if ($commandText == 'domainless-property') {
				$pages = $acStore->getDomainLessProperty($userInput);
				$result = self::mergeResults($result, self::setInferred($pages, !$first));

				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			} else if ($commandText == 'annotation-property') {
				if (empty($params[0]) || is_null($params[0])) continue;
				if (smwf_om_userCan($params[0], 'read') == 'true') {
					$category = Title::newFromText($params[0]);
					if (!is_null($category)) {
						$pages = $acStore->getPropertyForAnnotation($userInput, $category, false);
						$inf = self::setInferred($pages, !$first);
						$result = self::mergeResults($result, $inf);
					}
				}
				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			} else if ($commandText == 'annotation-value') {
				if (empty($params[0]) || is_null($params[0])) continue;
				if (smwf_om_userCan($params[0], 'read') == 'true') {
					$property = Title::newFromText($params[0]);
					if (!is_null($property)) {
						$pages = $acStore->getValueForAnnotation($userInput, $property);
						$inf = self::setInferred($pages, !$first);
						$result = self::mergeResults($result, $inf);

					}
				}

				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			} else if ($commandText == 'instance-property-range') {
				if (empty($params[0]) || is_null($params[0])) continue;
				if (smwf_om_userCan($params[0], 'read') == 'true') {
					$property = Title::newFromText($params[0]);
					if (!is_null($property)) {
						$domainRangeAnnotations = smwfGetStore()->getPropertyValues($property, smwfGetSemanticStore()->domainRangeHintProp);
						$pages = $acStore->getInstanceAsTarget($userInput, $domainRangeAnnotations);
						$inf = self::setInferred($pages, !$first);
						$result = self::mergeResults($result, $inf);
					}
				}
				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			} else if ($commandText == 'namespace') {
				$namespaceIndexes = array();
				global $wgContLang;
				foreach($params as $p) {
					if (is_numeric($p)) {
						$namespaceIndexes[] = $p;
					} else if (strtolower($p) == "main") {
						$namespaceIndexes[] = 0;
					} else {
						$ns = $wgContLang->getNsIndex( $p );
						$namespaceIndexes[] = $ns;
					}
				}
				$pages = smwfGetAutoCompletionStore()->getPages($userInput, $namespaceIndexes);
				$inf = self::setInferred($pages, !$first);
				$result = self::mergeResults($result, $inf);
				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			} else if ($commandText == 'lexical') {
				$pages = smwfGetAutoCompletionStore()->getPages($userInput);
				$inf = self::setInferred($pages, !$first);
				$result = self::mergeResults($result, $inf);
				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			} else if ($commandText == 'schema-property-type') {
				if (empty($params[0]) || is_null($params[0])) continue;
				$datatype = $params[0];
				$pages = smwfGetAutoCompletionStore()->getPropertyWithType($userInput, $datatype);
				$inf = self::setInferred($pages, !$first);
				$result = self::mergeResults($result, $inf);
				if (count($result) >= SMW_AC_MAX_RESULTS) break;

				global $smwgContLang;
				$dtl = $smwgContLang->getDatatypeLabels();
				$pages = smwfGetAutoCompletionStore()->getPropertyWithType($userInput, $dtl['_str']);
				$inf = self::setInferred($pages, !$first);
				$result = self::mergeResults($result, $inf);
				if (count($result) >= SMW_AC_MAX_RESULTS) break;

			} else if ($commandText == 'ask') {
				if (empty($params[0]) || is_null($params[0])) continue;
				$query = $params[0];

				if (!isset($params[1]) || $params[1] == 'main') {
					$column = "_var0";
				} else {
					$column = strtoupper(substr($params[1],0,1)).substr($params[1],1);
					$column = str_replace(" ", "_", $column);
				}

				$xmlResult = smwfGetAutoCompletionStore()->runASKQuery($query, $userInput,  $column);
				$dom = simplexml_load_string($xmlResult);
				$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
				$queryResults = $dom->xpath('//sparqlxml:binding[@name="'.$column.'"]/sparqlxml:uri');

				// make titles but eliminate duplicates before
				$textTitles = array();

				foreach($queryResults as $r) {
					if (empty($userInput) || stripos(str_replace(" ", "_", (string) $r[0]), $userInput) !== false) {
						$textTitles[] = (string) $r[0];
						if (count($textTitles) >= SMW_AC_MAX_RESULTS) break;
					}
				}
				$textTitles = array_unique($textTitles);
				$titles = array();
				foreach($textTitles as $r) {
					if (smwf_om_userCan($r, 'read') == 'true') {
						$titles[] = TSHelper::getTitleFromURI($r, true);
					}
				}
				$inf = self::setInferred($titles, !$first);
				self::mergeResults($result, $inf);
				if (count($result) >= SMW_AC_MAX_RESULTS) break;
					
			} else if($commandText == "asf-ac"){
				//returns all categories for which an ASF can be created

				if(defined('ASF_VERSION')){
					@ $titles = ASFCategoryAC::getCategories($userInput);
				} else {
					$titles = array();
				}

				$inf = self::setInferred($titles, !$first);
				self::mergeResults($result, $inf);
				if (count($result) >= SMW_AC_MAX_RESULTS) break;
			}

			$first = false;
		}

		return $result;
	}



	/**
	 * Remove all double matches. This may occur if several AC commands are
	 * concatenated.
	 *
	 * @param array $results First element is Title or string and siginifcant for double or not.
	 */
	public static function mergeResults(& $arr1, & $arr2) {
		// merge results
		for($i = 0, $n = count($arr2); $i < $n; $i++) {
			$contains = false;
			for($j = 0, $m = count($arr1); $j < $m; $j++) {
				$cmp = self::isEqualResults($arr1[$j], $arr2[$i]);
				$contains |= $cmp == 0;
			}
			if (!$contains) { // ascending sort order
				$arr1[] = $arr2[$i];
			}
		}
		return $arr1;
	}

	/**
	 * Checks if two matches are equal or not.
	 * FIXME: should be moved into a separated ACMatch class
	 *
	 * @param Match $r1 array with first element to be a Title or string.
	 * @param Match $r2 array with first element to be a Title or string.
	 * @return int < 0 if $r1 < $r2, == 0 if $r1 == $r2, > 0 if $r1 > $r2
	 */
	private static function isEqualResults(& $r1, & $r2) {
		$t1 = is_array($r1) ? $r1['title'] : $r1;
		$t2 = is_array($r2) ? $r2['title'] : $r2;
		$t1_text = $t1 instanceof Title ? $t1->getPrefixedText() : $t1;
		$t2_text = $t2 instanceof Title ? $t2->getPrefixedText() : $t2;
		return strcmp($t1_text, $t2_text);

	}

	/**
	 * Sets the inferred flag of an AC match
	 * FIXME: should be moved into a separated ACMatch class
	 *
	 * @param mixed $acMatches (see encapsulateAsXML)
	 * @param boolean $inferred
	 */
	private static function setInferred($acMatches, $inferred) {
		$newmatches = array();
		foreach($acMatches as $t) {
			if ($t instanceof Title) {
				$newmatches[] = array('title'=>$t, 'inferred'=>$inferred);
			} else {
				$t['inferred'] = $inferred;
				$newmatches[] = $t;

			}
		}
		return $newmatches;
	}
}





