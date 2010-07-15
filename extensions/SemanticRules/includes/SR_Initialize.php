<?php
/**
 * @file
 * @ingroup SemanticRules
 *
 * @defgroup SemanticRules Semantic Rules extension
 *
 * Semantic rules extension entry point
 *
 * @author: Kai K�hn / ontoprise / 2009
 */

if ( !defined( 'MEDIAWIKI' ) ) die;

define('SEMANTIC_RULES_VERSION', '{{$VERSION}}');
if (!defined("SMW_HALO_VERSION")) {
	trigger_error("SMWHalo is required but not installed.");
	die();
}

global $smwgDefaultStore;
if($smwgDefaultStore != 'SMWTripleStore') {
	trigger_error("Triplestore not active. See manual how to activate.");
	die();
}

$wgExtensionFunctions[] = 'ruleSetupExtension';
$srgSRIP = $IP . '/extensions/SemanticRules';
$smwgEnableObjectLogicRules=true;

/**
 * Setups rule extension
 *
 * @return boolean (MW Hook)
 */
function ruleSetupExtension() {
	global $srgSRIP, $smwgDefaultRuleStore, $wgHooks, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups, $wgExtensionCredits;
	$wgHooks['BeforePageDisplay'][]='srfAddHTMLHeader';
	$wgHooks['BeforePageDisplay'][]='srfAddOBContent';
	$wgHooks['smw_ob_attachtoresource'][] = 'srAttachToResource';


	$smwgDefaultRuleStore = "SRRuleStore";

	srfSRInitUserMessages();


	$wgHooks['InternalParseBeforeLinks'][] = 'srfTripleStoreParserHook';
	$wgHooks['smw_ob_add'][] = 'srfAddToOntologyBrowser';
	$wgHooks['us_extend_search'][] = 'srfAddToUnifiedSearch';
	
	


	$wgAutoloadClasses['SRRuleStore'] = $srgSRIP . '/includes/SR_RuleStore.php';
	$wgAutoloadClasses['SRRuleEndpoint'] = $srgSRIP . '/includes/SR_RuleEndpoint.php';

	$wgAutoloadClasses['SMWAbstractRuleObject'] = $srgSRIP . '/includes/SR_AbstractRuleObject.php';
	$wgAutoloadClasses['SMWConstant'] = $srgSRIP . '/includes/SR_Constant.php';


	$wgAutoloadClasses['SMWFormulaParser'] = $srgSRIP . '/includes/SR_FormulaParser.php';
	$wgAutoloadClasses['SMWLiteral'] = $srgSRIP . '/includes/SR_Literal.php';
	$wgAutoloadClasses['SMWPredicate'] = $srgSRIP . '/includes/SR_Predicate.php';
	$wgAutoloadClasses['SMWPredicateSymbol'] = $srgSRIP . '/includes/SR_PredicateSymbol.php';
	$wgAutoloadClasses['SMWRuleObject'] = $srgSRIP . '/includes/SR_RuleObject.php';
	$wgAutoloadClasses['SMWTerm'] = $srgSRIP . '/includes/SR_Term.php';
	$wgAutoloadClasses['SMWVariable'] = $srgSRIP . '/includes/SR_Variable.php';

	global $wgRequest;
	$action = $wgRequest->getVal('action');
	if ($action == 'ajax') {
			
		require_once($srgSRIP . '/includes/SR_RulesAjax.php');

	}

	/*$wgAutoloadClasses['SRExplanations'] = $srgSRIP . '/specials/Explanations/SR_Explanations.php';
	 $wgSpecialPages['Explanations'] = array('SRExplanations');
	 $wgSpecialPageGroups['Explanations'] = 'smwplus_group';*/

	$wgExtensionCredits['parserhook'][]= array('name'=>'SemanticRules&nbsp;Extension', 'version'=>SEMANTIC_RULES_VERSION,
            'author'=>"Thomas&nbsp;Schweitzer, Kai&nbsp;K&uuml;hn. Maintained by [http://www.ontoprise.de Ontoprise].", 
            'url'=>'https://sourceforge.net/projects/halo-extension', 
            'description' => 'Enables the power of rules to SMWHalo');


	return true;
}

/**
 * Adds additional results to the UnifiedSearch result list.
 * 
 * @param array $searchTerms array of search terms (currently only first is regarded)
 * @param array $receivers array of receivers
 * @param string & $results HTML result 
 */
function srfAddToUnifiedSearch($searchTerms, $receivers, & $results) {
	if (in_array("SemanticRules", $receivers)) {
		$html = SRRuleEndpoint::getInstance()->searchForRulesByFragment($searchTerms, "widget", false);
		if ($html != '') {
			$html = "<span style=\"margin: 5px;\">".wfMsg('sr_rulesfound')."</span>".$html;
		}
		$results = $html;
	}
	return true;
}

/**
 * Registers extensions for the OntologyBrowser.
 *
 * @param $treeContainer
 * @param $boxContainer
 * @param $menu
 * @param $switch
 */
function srfAddToOntologyBrowser(& $treeContainer, & $boxContainer, & $menu, & $switch) {
	global $wgScriptPath, $wgUser;

	if (is_null($wgUser) || !$wgUser->isAllowed("ontologyediting")) {
		return true;
	}

	// additional rule tree container
	$treeContainer .= '<div id="ruleTree" style="display:none" class="ruleTreeListColors treeContainer"></div>';

	// additional switch
	$switch .= "<img src=\"$wgScriptPath/extensions/SemanticRules/skins/images/rule.gif\"></img><a class=\"treeSwitch\" id=\"ruleTreeSwitch\" onclick=\"globalActionListener.switchTreeComponent(event,'ruleTree')\">".wfMsg('smw_ob_ruleTree')."</a>";

	// additional rule box with metadata
	$boxContainer = "<div id=\"ruleContainer\" style=\"display:none\">
	 <span class=\"OB-header\"><img src=\"$wgScriptPath/extensions/SemanticRules/skins/images/rule.gif\"></img> ".wfMsg('sr_ob_rulelist')."</span>
	 <div id=\"ruleList\" class=\"ruleTreeListColors\"> 
	 
	 </div>
	 </div>";

	return true;
}

function srAttachToResource($properties, & $resourceAttachments, $nsIndex) {
	$ruleEndpoint = SRRuleEndpoint::getInstance();
	$resources = array();
	new TSNamespaces(); // assure namespaces are initialized
	$allNamespaces = TSNamespaces::getAllNamespaces();

	foreach($properties as $p) {
		list($name, $hasSubProperty) = $p;
		$resources[] = $allNamespaces[$nsIndex].$name->getDBkey();
			
	}
	$resourceAttachments = $ruleEndpoint->getDefiningRules($resources);
	return true;
}



/**
 * Registers SR user/content messages.
 */
function srfSRInitUserMessages() {
	global $wgMessageCache, $wgLang, $srgSRIP;

	$srLangClass = 'SR_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($srgSRIP.'/languages/'. $srLangClass . '.php')) {
		include_once($srgSRIP.'/languages/'. $srLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($srLangClass)) {
		include_once($srgSRIP.'/languages/SR_LanguageEn.php' );
		$srgLang = new SR_LanguageEn();
	} else {

		$srgLang = new $srLangClass();
	}

	$wgMessageCache->addMessages($srgLang->getUserMessages(), $wgLang->getCode());
	$wgMessageCache->addMessages($srgLang->getContentMessages(), $wgLang->getCode());

}

/**
 * Register SR javascript user/content messages
 *
 * @param $out
 */
function srfAddJSLanguageScripts(& $out) {
	global $srgSRIP, $wgLanguageCode, $wgUser, $wgScriptPath;

	// content language file
	$lng = '/scripts/languages/SR_Language';
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($srgSRIP . $lng)) {
			// add content language script file
		} else {
			// add english default content language script file
		}
	} else {
		// add english default content language script file
	}

	// user language file
	$lng = '/scripts/languages/SR_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($srgSRIP . $lng)) {
			$out->addScript('<script type="text/javascript" src="'.$wgScriptPath .'/extensions/SemanticRules'. $lng .'"></script>');
		} else {
			$out->addScript('<script type="text/javascript" src="'.$wgScriptPath .'/extensions/SemanticRules'. '/scripts/languages/SR_LanguageUserEn.js"></script>');

		}
	} else {
		$out->addScript('<script type="text/javascript" src="'.$wgScriptPath .'/extensions/SemanticRules'. '/scripts/languages/SR_LanguageUserEn.js"></script>');
	}

	// base language script
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath .'/extensions/SemanticRules'. '/scripts/languages/SR_Language.js"></script>');
}

/**
 * Includes javascript/css files.
 *
 * @param $out
 * @return boolean (MW hook)
 */
function srfAddHTMLHeader(& $out) {
	global $srgSRIP, $wgScriptPath, $smwgEnableObjectLogicRules, $wgRequest, $wgTitle;

	// load these two on every page
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_Rulewidget.js"></script>');
	$out->addLink(array('rel'   => 'stylesheet','type'  => 'text/css',
                        'media' => 'screen, projection','href'  => $wgScriptPath . '/extensions/SemanticRules/skins/rules.css'));
	
	$SF = ($wgTitle->getNamespace() == -1 &&
	in_array($wgTitle->getBasetext(), array("AddData", "EditData")));
	$action = $wgRequest->getVal('action');
	if ($action != "edit" && $action != "annotate" && $action != "formedit" && !$SF) return true;

	srfAddJSLanguageScripts($out);

	$rulesEnabled = isset($smwgEnableObjectLogicRules)
	? (($smwgEnableObjectLogicRules) ? 'true' : 'false')
	: 'false';
	$out->addScript('<script type= "text/javascript">var smwgEnableFlogicRules='.$rulesEnabled.';</script>'."\n");

	
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_Rule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_CategoryRule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_CalculationRule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_PropertyChain.js"></script>');


	return true;

}

function srfAddOBContent(& $out) {
	$localname = SpecialPage::getLocalNameFor("OntologyBrowser");

	global $wgTitle, $smwgEnableObjectLogicRules, $wgScriptPath;
	if ($wgTitle->getNamespace() == NS_SPECIAL && $wgTitle->getText() == $localname) {
		srfAddJSLanguageScripts($out);
		$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_OB_extensions.js"></script>');
		$rulesEnabled = isset($smwgEnableObjectLogicRules)
		? (($smwgEnableObjectLogicRules) ? 'true' : 'false')
		: 'false';
		$out->addScript('<script type= "text/javascript">var smwgEnableFlogicRules='.$rulesEnabled.';</script>'."\n");

		$out->addLink(array('rel'   => 'stylesheet','type'  => 'text/css',
                        'media' => 'screen, projection','href'  => $wgScriptPath . '/extensions/SemanticRules/skins/rules.css'));

	}
	return true;
}

/**
 * Parses rules from page text
 *
 * @return boolean (SMWHalo hook)
 */
function srfTripleStoreParserHook(&$parser, &$text, &$strip_state = null) {
	global $smwgEnableObjectLogicRules, $smwgTripleStoreGraph;
	// rules
	// meant to be a hash map $ruleID => $ruleText,
	// where $ruleID has to be a URI (i.e. containing at least one colon)

	$rules = array();
	if (isset($smwgEnableObjectLogicRules)) {

		// search rule tags
		$ruleTagPattern = '/&lt;rule(.*?&gt;)(.*?.)&lt;\/rule&gt;/ixus';
		preg_match_all($ruleTagPattern, trim($text), $matches);

		// at least one parameter and content?
		for($i = 0; $i < count($matches[0]); $i++) {
			$header = trim($matches[1][$i]);
			$ruletext = trim($matches[2][$i]);

			// parse header parameters
			$ruleparamterPattern = "/([^=]+)=\"([^\"]*)\"/ixus";
			preg_match_all($ruleparamterPattern, $header, $matchesheader);

			$native = false;
			$active = true;
			$type="UNDEFINED";
			for ($j = 0; $j < count($matchesheader[0]); $j++) {
				if (trim($matchesheader[1][$j]) == 'native') {
					$native = trim($matchesheader[2][$j]) == 'true';
				}
				if (trim($matchesheader[1][$j]) == 'active') {
					$active = trim($matchesheader[2][$j]) == 'true';
				}
				if (trim($matchesheader[1][$j]) == 'type') {
					$type = $matchesheader[2][$j];
				}
			}

			// normalize $type which is given in content language to TSC internal constants.
			$ruleTypesAsContentLang = array(wfMsg('sr_definition_rule'),
			wfMsg('sr_property_chaining'),
			wfMsg('sr_calculation'));
				
			switch($type) {
				case $ruleTypesAsContentLang[0]: $type = "DEFINITION";break;
				case $ruleTypesAsContentLang[1]: $type = "PROP_CHAINING";break;
				case $ruleTypesAsContentLang[2]: $type = "CALCULATION";break;
			}


			// fetch name of rule (ruleid) and put into rulearray
			for ($j = 0; $j < count($matchesheader[0]); $j++) {
				if (trim($matchesheader[1][$j]) == 'name') {
					$name = $matchesheader[2][$j];

					// create URI. It denotes on which page the rule is located.
					global $srgStateChangedPage;
					global $wgTitle;
					$pageTitle = $wgTitle;
					if (isset($srgStateChangedPage)) {
						$pageTitle = $srgStateChangedPage;
					}
					$ns = $pageTitle->getNamespace();
					new TSNamespaces(); // assure namespaces are initialized
					$allNamespaces = TSNamespaces::getAllNamespaces();

					$uri = $allNamespaces[$ns] . urlencode($pageTitle->getDBkey()) . "$$" . urlencode(str_replace(' ', '_', $name));

					$ruletext = str_replace("&lt;","<", $ruletext);
					$ruletext = str_replace("&gt;",">", $ruletext);
					
					// check if rule already exists. If so, use date of last change to 
					// indicate that the rule was actually not changed.
					list($exist, $last_changed) = SMWRuleStore::getInstance()->existsRule(array($uri, $ruletext, $native, $active, $type));
					$ruleTuple = array($uri, $ruletext, $native, $active, $type, $last_changed);
					
					$rules[] = $ruleTuple;
				}
			}
			//FIXME: remove rule serialize selector for this release.
			//$replaceBy = '<div id="rule_content_'.$i.'" ruleID="'.htmlspecialchars($uri).'" class="ruleWidget"><span style="margin-left: 5px;">'.htmlspecialchars($name).'</span> | '.wfMsg('sr_ruleselector').'<select style="margin-top: 5px;" name="rule_content_selector'.$i.'" onchange="sr_rulewidget.selectMode(event)"><option mode="easyreadible">'.wfMsg('sr_easyreadible').'</option><option mode="stylized">'.wfMsg('sr_stylizedenglish').'</option></select> '. // tab container
			
			global $wgTitle;
			$prefixedText = !is_null($wgTitle) ? $wgTitle->getPrefixedText() : "";  
			$status = $active ? "active" : "inactive";
			$statusColor = $active ? "green" : "red";
			$replaceBy = '<h2>'.wfMsg('sr_rulesdefinedfor').' '.$prefixedText.'</h2><div id="rule_content_'.$i.'" ruleID="'.htmlspecialchars($uri).'" class="ruleWidget"><img style="margin-top: 5px;margin-left: 5px;" src="extensions/SemanticRules/skins/images/rule.gif"/><span style="margin-left: 5px;font-weight:bold;">
			             '.htmlspecialchars($name).'</span> <span style="float:right;margin-right: 10px;margin-top: 5px;">'.wfMsg('sr_rulestatus').':<span style="font-weight: bold;color:'.$statusColor.';">'.$status.'</span></span><hr/>'. // tab container
			             '<div id="rule_content_'.$i.'_easyreadible" class="ruleSerialization">'.htmlspecialchars($ruletext).'</div>'. // tab 1
			             '<div id="rule_content_'.$i.'_stylized" class="ruleSerialization" style="display:none;">Stylized english</div>'.
			             '</div>'; // tab 2
			
			$text = str_replace($matches[0][$i], $replaceBy, $text);
		}

	}

	
	SMWTripleStore::$fullSemanticData->setRules($rules);
	return true;
}
