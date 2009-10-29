<?php
/**
 * Semantic rules extension entry point
 *
 * @author: Kai K�hn / ontoprise / 2009
 */

if ( !defined( 'MEDIAWIKI' ) ) die;

define('SEMANTIC_RULES_VERSION', '1.0');
if (!defined("SMW_HALO_VERSION")) {
	trigger_error("SMWHalo is required but not installed.");
	die();
}

$wgExtensionFunctions[] = 'srfSetupExtension';
$srgSRIP = $IP . '/extensions/SemanticRules';

/**
 * Setups rule extension
 *
 * @return boolean (MW Hook)
 */
function srfSetupExtension() {
	global $srgSRIP, $smwgDefaultRuleStore, $wgHooks, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups;
	$wgHooks['BeforePageDisplay'][]='srfAddHTMLHeader';

	$smwgDefaultRuleStore = "SRRuleStore";

	srfSRInitUserMessages();

	
	$wgHooks['InternalParseBeforeLinks'][] = 'srfTripleStoreParserHook';
	require_once($srgSRIP . '/includes/SR_RulesAjax.php');
    require_once($srgSRIP . '/includes/SR_WebInterfaces.php');
    
	$wgAutoloadClasses['SRRuleStore'] = $srgSRIP . '/includes/SR_RuleStore.php';
	$wgAutoloadClasses['SRExplanations'] = $srgSRIP . '/specials/Explanations/SR_Explanations.php';
	$wgSpecialPages['Explanations'] = array('SRExplanations');
	$wgSpecialPageGroups['Explanations'] = 'smwplus_group';

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
	global $srgSRIP, $wgScriptPath, $smwgEnableFlogicRules, $wgRequest, $wgTitle;

    $SF = ($wgTitle->getNamespace() == -1 &&
           in_array($wgTitle->getBasetext(), array("AddData", "EditData")));
	$action = $wgRequest->getVal('action');
	if ($action != "edit" && $action != "annotate" && $action != "formedit" && !$SF) return true;

    srfAddJSLanguageScripts($out);

	$rulesEnabled = isset($smwgEnableFlogicRules)
	? (($smwgEnableFlogicRules) ? 'true' : 'false')
	: 'false';
	$out->addScript('<script type= "text/javascript">var smwgEnableFlogicRules='.$rulesEnabled.';</script>'."\n");

	$out->addLink(array('rel'   => 'stylesheet','type'  => 'text/css',
	                    'media' => 'screen, projection','href'  => $wgScriptPath . '/extensions/SemanticRules/skins/rules.css'));

	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_Rule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_CategoryRule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_CalculationRule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_PropertyChain.js"></script>');
    
	$localname = SpecialPage::getLocalNameFor("Explanations");
	global $wgTitle;
	if ($wgTitle->getNamespace() == NS_SPECIAL && $wgTitle->getText() == $localname) {
		$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_Explanations.js"></script>');
	}
	return true;

}

/**
 * Parses rules from page text
 *
 * @return boolean (SMWHalo hook)
 */
function srfTripleStoreParserHook(&$parser, &$text, &$strip_state = null) {
	global $smwgEnableFlogicRules, $smwgTripleStoreGraph;
	// rules
	// meant to be a hash map $ruleID => $ruleText,
	// where $ruleID has to be a URI (i.e. containing at least one colon)

	$rules = array();
	if (isset($smwgEnableFlogicRules)) {

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
			for ($j = 0; $j < count($matchesheader[0]); $j++) {
				if (trim($matchesheader[1][$j]) == 'native') {
					$native = true;
				}
				
			}
			// fetch name of rule (ruleid) and put into rulearray
			for ($j = 0; $j < count($matchesheader[0]); $j++) {
				if (trim($matchesheader[1][$j]) == 'name') {
					$name = $matchesheader[2][$j];
					$is_url = strpos($name, ":");
					if ($is_url === false) {
						// no valid URL given, so build one
						$url = $smwgTripleStoreGraph . "#" . urlencode($name);
					} else {
						$url = $name;
					}
					
					$ruletext = str_replace("&lt;","<", $ruletext);
					$ruletext = str_replace("&gt;",">", $ruletext);
					$rules[] = array($url, $ruletext, $native);
				}
			}
		}

		// remove rule tags from text
		$text = preg_replace($ruleTagPattern, "", $text);
	}

	SMWTripleStore::$fullSemanticData->setRules($rules);
	return true;
}
?>