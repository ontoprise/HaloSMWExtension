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

define('SEMANTIC_RULES_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');
if (!defined("SMW_HALO_VERSION")) {
	trigger_error("SMWHalo is required but not installed.");
	die();
}

// buildnumber index for MW to define a script's version.
$srgStyleVersion = preg_replace('/[^\d]/', '', '{{$BUILDNUMBER}}' );
if (strlen($srgStyleVersion) > 0) {
	$srgStyleVersion= '?'.$srgStyleVersion;
}

// check if quad drive is used. If so, stop here because it is not supported.
global $smwgHaloQuadMode;
if($smwgHaloQuadMode == true) {
	trigger_error("Rule extension will not work with the 'SMWTripleStoreQuad' client. Please deactivate it and replace it by 'SMWTripleStore'.");
	die();
}

// check if TSC is configured
global $wgCommandLineMode, $smwgHaloWebserviceEndpoint;
if ($wgCommandLineMode) {
	// in command line mode, just print a WARNING, otherwise DF may stop working
	if (!isset($smwgHaloWebserviceEndpoint)) {
		echo "\n\nWARNING: TSC is NOT configured. Take a look here: \n\thttp://smwforum.ontoprise.com/smwforum/index.php/Help:TripleStore_Basic\n";
	}
} else {
	if (!isset($smwgHaloWebserviceEndpoint)) {
		trigger_error("TSC is NOT configured. Take a look here: <a href=\"http://smwforum.ontoprise.com/smwforum/index.php/Help:TripleStore_Basic\">SMW-Forum</a>");
		die();
	}
}


$wgExtensionFunctions[] = 'ruleSetupExtension';
$srgSRIP = $IP . '/extensions/SemanticRules';

global $smwgHaloEnableObjectLogicRules;
$smwgHaloEnableObjectLogicRules=true;

/**
 * Setups rule extension
 *
 * @return boolean (MW Hook)
 */
function ruleSetupExtension() {
	global $srgSRIP, $wgScriptPath, $smwgDefaultRuleStore, $wgHooks, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups, $wgExtensionCredits;
	$wgHooks['BeforePageDisplay'][]='srfAddHTMLHeader';
	$wgHooks['BeforePageDisplay'][]='srfAddOBContent';
	$wgHooks['smw_ob_attachtoresource'][] = 'srAttachToResource';


	$smwgDefaultRuleStore = "SRRuleStore";

	srfSRInitUserMessages();


	$wgHooks['InternalParseBeforeLinks'][] = 'srfTripleStoreParserHook';
	$wgHooks['smw_ob_add'][] = 'srfAddToOntologyBrowser';
	$wgHooks['us_extend_search'][] = 'srfAddToUnifiedSearch';



	$wgAutoloadClasses['SRRuleWidget'] = $srgSRIP . '/includes/SR_RuleWidget.php';
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

	global $wgOut;
	srfRegisterJSModules($wgOut);

	$wgExtensionCredits['parserhook'][]= array('name'=>'Rule&nbsp;knowledge&nbsp;extension', 'version'=>SEMANTIC_RULES_VERSION,
            'author'=>"Thomas&nbsp;Schweitzer, Kai&nbsp;K&uuml;hn. Owned by [http://www.ontoprise.de ontoprise GmbH].", 
            'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:Rule_Knowledge_Extension',
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
		$html = SRRuleEndpoint::getInstance()->searchForRulesByFragment(array($searchTerms[0], false), "widget", false);
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
	$switch .= "<img style=\"margin-left: 3px;margin-bottom: -1px\" src=\"$wgScriptPath/extensions/SemanticRules/skins/images/rule.gif\"></img><a class=\"treeSwitch\" id=\"ruleTreeSwitch\" onclick=\"globalActionListener.switchTreeComponent(event,'ruleTree')\">".wfMsg('smw_ob_ruleTree')."</a>";

	// additional rule box with metadata
	$boxContainer = "<div id=\"ruleContainer\" style=\"display:none\">
	 <span class=\"OB-header\"><img src=\"$wgScriptPath/extensions/SemanticRules/skins/images/rule.gif\"></img> ".wfMsg('sr_ob_rulelist')."</span>
	 <div id=\"ruleList\" class=\"ruleTreeListColors\"> 
	 
	 </div>
	 </div>";

	return true;
}

function srAttachToResource($schemaElements, & $resourceAttachments, $nsIndex) {
	$ruleEndpoint = SRRuleEndpoint::getInstance();
	$resources = array();
	TSNamespaces::getInstance(); // assure namespaces are initialized
	$allNamespaces = TSNamespaces::getAllNamespaces();

	foreach($schemaElements as $p) {
		list($title, $hasSubElement) = $p;
		$resources[] = $allNamespaces[$nsIndex].$title->getDBkey();
			
	}
	if (!empty($resources)) {
		$resourceAttachments = $ruleEndpoint->getDefiningRules($resources);
	}
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
 * Register language JS modules
 *
 * @param $out
 */
function srfRegisterJSLanguageModules(& $out) {
	global $srgSRIP, $wgLanguageCode, $wgUser, $wgScriptPath, $srgStyleVersion, $wgResourceModules;

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
	$lng = '';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($srgSRIP . $lng)) {
			$userLanguageFile = 'SemanticRules'. $lng;
		} else {
			$userLanguageFile = 'SR_LanguageUserEn.js';

		}
	} else {
		$userLanguageFile = 'SR_LanguageUserEn.js';
	}

	$moduleTemplate = array(
        'localBasePath' => $srgSRIP,
        'remoteBasePath' => $wgScriptPath . '/extensions/SemanticRules',
        'group' => 'ext.semanticrules'
        );

        $wgResourceModules['ext.semanticrules.language'] = $moduleTemplate + array(
        'scripts' => array(
            'scripts/languages/'.$userLanguageFile,
            'scripts/languages/SR_Language.js'
            
            ),
        'styles' => array(

            ),
        'dependencies' => array(
          
             )
             );



}

/**
 * Register JS modules
 *
 * @param $out
 */
function srfRegisterJSModules(& $out) {
	global $wgResourceModules, $moduleTemplate, $wgScriptPath, $srgSRIP;
	$moduleTemplate = array(
        'localBasePath' => $srgSRIP,
        'remoteBasePath' => $wgScriptPath . '/extensions/SemanticRules',
        'group' => 'ext.semanticrules'
        );

        // Module for the Ruleditor
        $wgResourceModules['ext.semanticrules.ruleditor'] = $moduleTemplate + array(
        'scripts' => array(
            'scripts/SR_Rule.js',
            'scripts/SR_CategoryRule.js',
            'scripts/SR_CalculationRule.js',
            'scripts/SR_PropertyChain.js'
          
            ),
        'styles' => array(

            ),
        'dependencies' => array(
            'ext.semanticrules.rulewidget',
            'ext.smwhalo.semanticToolbar'
            )
            );

            // Module for the Rule widget
            $wgResourceModules['ext.semanticrules.rulewidget'] = $moduleTemplate + array(
        'scripts' => array(
            'scripts/SR_Rulewidget.js'
            ),
        'styles' => array(
            'skins/rules.css',
            'skins/prettyPrinterForRules.css'
            ),
        'dependencies' => array(
            'ext.smwhalo.general',
            'ext.semanticrules.language'
            )
            );

            $wgResourceModules['ext.semanticrules.obruleextension'] = $moduleTemplate + array(
        'scripts' => array(
            'scripts/SR_OB_extensions.js'
            ),
        'styles' => array(
           'skins/rules.css',
            ),
        'dependencies' => array(
            'ext.semanticrules.language',
            'ext.semanticrules.rulewidget'
            )
            );


            srfRegisterJSLanguageModules($out);
}

/**
 * Includes javascript/css files.
 *
 * @param $out
 * @return boolean (MW hook)
 */
function srfAddHTMLHeader(& $out) {
	global $srgSRIP, $wgScriptPath, $wgRequest, $wgTitle, $srgStyleVersion, $wgResourceModules;

	// load this module on every page
	$out->addModules(array('ext.semanticrules.rulewidget'));

	$SF = ($wgTitle->getNamespace() == -1 &&
	in_array($wgTitle->getBasetext(), array("AddData", "EditData")));
	$action = $wgRequest->getVal('action');
	if ($action != "edit" && $action != "annotate" && $action != "formedit" && !$SF) return true;

	// load this only when in editmode
	$out->addModules(array('ext.semanticrules.ruleditor'));

	return true;

}

function srfAddOBContent(& $out) {
	$localname = SpecialPage::getLocalNameFor("DataExplorer");

	global $wgTitle, $wgScriptPath, $wgResourceModules;
	if ($wgTitle->getNamespace() == NS_SPECIAL && $wgTitle->getText() == $localname) {
		// load only on OntoloyBrowser special page
		$out->addModules(array('ext.semanticrules.obruleextension'));

	}
	return true;
}

/**
 * Parses rules from page text
 *
 * @return boolean (SMWHalo hook)
 */
function srfTripleStoreParserHook(&$parser, &$text, &$strip_state = null) {
	global $smwgHaloEnableObjectLogicRules, $smwgHaloTripleStoreGraph;
	// rules
	// meant to be a hash map $ruleID => $ruleText,
	// where $ruleID has to be a URI (i.e. containing at least one colon)

	$rules = array();
	if (isset($smwgHaloEnableObjectLogicRules)) {

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
			$type="USER_DEFINED";
			$tsc_uri = "";
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
				if (trim($matchesheader[1][$j]) == 'uri') {
					$tsc_uri = $matchesheader[2][$j];
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
					$tsNamespaces = TSNamespaces::getInstance(); // assure namespaces are initialized
					$allNamespaces = TSNamespaces::getAllNamespaces();

					$uri = $tsNamespaces->getNSURI($ns) . urlencode($pageTitle->getDBkey()) . "$$" . urlencode(str_replace(' ', '_', $name));

					$ruletext = str_replace("&lt;","<", $ruletext);
					$ruletext = str_replace("&gt;",">", $ruletext);

					// check if rule already exists. If so, use date of last change to
					// indicate that the rule was actually not changed.
					list($exist, $last_changed) = SMWRuleStore::getInstance()->existsRule(array($uri, $ruletext, $native, $active, $type));
					$ruleTuple = array($uri, $ruletext, $native, $active, $type, $last_changed, $tsc_uri);

					$rules[] = $ruleTuple;
				}
			}


			$rw = new SRRuleWidget($uri, $ruletext, $active, $native);
			$replaceBy = $rw->asHTML();

			$text = str_replace($matches[0][$i], $replaceBy, $text);
		}

	}


	SMWTripleStore::$fullSemanticData->setRules($rules);
	return true;
}
