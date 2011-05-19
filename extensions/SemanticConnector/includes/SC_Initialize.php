<?php
/*
 * Created on 01.09.2009
 *
 * Author: Ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMW_CONNECTOR_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

$smwgConnectorIP = $IP . '/extensions/SemanticConnector';
$smwgConnectorScriptPath = $wgScriptPath . '/extensions/SemanticConnector';
$smwgConnectorEnabled = true;

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'smwgConnectorSetupExtension';

$wgAutoloadClasses['SCParserFunctions'] = $smwgConnectorIP . '/includes/SC_ParserFunctions.php';
// FIXME: Can be removed when new style magic words are used (introduced in r52503)
$wgHooks['LanguageGetMagic'][] = 'SCParserFunctions::languageGetMagic';

function smwfConnectorInitMessages() {
	global $smwgConnectorMessagesInitialized;
	if (isset($smwgConnectorMessagesInitialized)) return; // prevent double init

	smwfConnectorInitUserMessages(); // lazy init for ajax calls

	$smwgConnectorMessagesInitialized = true;
}
function smwfConnectorInitUserMessages() {
	global $wgMessageCache, $smwgConnectorContLang, $wgLanguageCode;
	smwfConnectorInitContentLanguage($wgLanguageCode);

	global $smwgConnectorIP, $smwgConnectorLang;
	if (!empty($smwgConnectorLang)) { return; }
	global $wgMessageCache, $wgLang;
	$smwLangClass = 'SC_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($smwgConnectorIP . '/languages/'. $smwLangClass . '.php')) {
		include_once( $smwgConnectorIP . '/languages/'. $smwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($smwLangClass)) {
		global $smwgConnectorContLang;
		$smwgConnectorLang = $smwgConnectorContLang;
	} else {
		$smwgConnectorLang = new $smwLangClass();
	}

	$wgMessageCache->addMessages($smwgConnectorLang->getUserMsgArray(), $wgLang->getCode());
}
function smwfConnectorInitContentLanguage($langcode) {
	global $smwgConnectorIP, $smwgConnectorContLang;
	if (!empty($smwgConnectorContLang)) { return; }

	$smwContLangClass = 'SC_Language' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($smwgConnectorIP . '/languages/'. $smwContLangClass . '.php')) {
		include_once( $smwgConnectorIP . '/languages/'. $smwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($smwContLangClass)) {
		include_once($smwgConnectorIP . '/languages/SC_LanguageEn.php');
		$smwContLangClass = 'SC_LanguageEn';
	}
	$smwgConnectorContLang = new $smwContLangClass();
}

function smwfConnectorInitializeTables() {
	global $smwgConnectorIP;
	require_once( $smwgConnectorIP . '/includes/SC_Storage.php' );
	SCStorage::getDatabase()->setup(true);

	return true;
}

function smwfConnectorGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
}

/**
 * Intializes Semantic Connector Extension.
 * Called from SC during initialization.
 */
function smwgConnectorSetupExtension() {
	global $smwgConnectorIP, $wgExtensionCredits;
	global $wgParser, $wgHooks, $wgJobClasses, $wgAutoloadClasses;
	global $wgSpecialPages, $wgSpecialPageGroups;

	smwfConnectorInitMessages();

	// register SMW hooks
	$wgHooks['smwInitializeTables'][] = 'smwfConnectorInitializeTables';

	// replace LinksUpdateConstructed hook from SemanticMediaWiki, to add schema mapped properties
	foreach($wgHooks['LinksUpdateConstructed'] as $k=>$hookVal) {
		if($hookVal == 'SMWParseData::onLinksUpdateConstructed') {
			$wgHooks['LinksUpdateConstructed'][$k] = 'SCArticleUtils::onLinksUpdateConstructed';
			break;
		}
	}
	
	global $wgRequest, $wgContLang;

	$spns_text = $wgContLang->getNsText(NS_SPECIAL);

	$wgHooks['BeforePageDisplay'][]='smwConnectorAddHTMLHeader';

	$wgJobClasses['SMWConnectorRefreshJob'] = 'SMWConnectorRefreshJob';
	require_once($smwgConnectorIP . '/includes/jobs/SC_RefreshJob.php');

	$wgAutoloadClasses['SCStorage'] = $smwgConnectorIP . '/includes/SC_Storage.php';
	$wgAutoloadClasses['SCProcessor'] = $smwgConnectorIP . '/includes/SC_Processor.php';
	$wgAutoloadClasses['SCArticleUtils'] = $smwgConnectorIP . '/includes/SC_ArticleUtils.php';

	if( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
		$wgHooks['ParserFirstCallInit'][] = 'SCParserFunctions::registerFunctions';
	} else {
		if ( class_exists( 'StubObject' ) && !StubObject::isRealObject( $wgParser ) ) {
			$wgParser->_unstub();
		}
		SCParserFunctions::registerFunctions( $wgParser );
	}

	$action = $wgRequest->getVal('action');
	// add some AJAX calls
	if ($action == 'ajax') {
		$method_prefix = smwfConnectorGetAjaxMethodPrefix();

		// decide according to ajax method prefix which script(s) to import
		switch($method_prefix) {
			case '_sc_' :
				require_once($smwgConnectorIP . '/includes/SC_AjaxAccess.php');
				break;
		}
	} else {
		$wgAutoloadClasses['SCRest'] = $smwgConnectorIP . '/includes/SC_Rest.php';
	
		// add REST-ful page
		$wgSpecialPages['RESTful'] = 'SCRESTful';
		$wgAutoloadClasses['SCRESTful'] = $smwgConnectorIP . '/specials/SC_RESTful.php';
		$wgSpecialPageGroups['RESTful'] = 'sf_group';
		// REST-ful UI
		$wgSpecialPages['ViewREST'] = 'SCViewREST';
		$wgAutoloadClasses['SCViewREST'] = $smwgConnectorIP . '/specials/SC_ViewREST.php';
		$wgSpecialPageGroups['ViewREST'] = 'sf_group';
	}
	
	// Register Credits
	$wgExtensionCredits['parserhook'][]= array(
	'name'=>'Semantic&nbsp;Connector&nbsp;Extension', 'version'=>SMW_CONNECTOR_VERSION,
			'author'=>"Ning Hu, Justin Zhang, [http://smwforum.ontoprise.com/smwforum/index.php/Jesse_Wang Jesse Wang], sponsored by [http://projecthalo.com Project Halo], [http://www.vulcan.com Vulcan Inc.]", 
			'url'=>'http://wiking.vulcan.com/dev', 
			'description' => 'Schema mapping utilities.');

	return true;
}

function smwfConnectorGetJSLanguageScripts(&$pathlng, &$userpathlng) {
	global $smwgConnectorIP, $wgLanguageCode, $smwgConnectorScriptPath, $wgUser;

	// content language file
	$lng = '/scripts/Language/SC_Language';
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgConnectorIP . $lng)) {
			$pathlng = $smwgConnectorScriptPath . $lng;
		} else {
			$pathlng = $smwgConnectorScriptPath . '/scripts/Language/SC_LanguageEn.js';
		}
	} else {
		$pathlng = $smwgConnectorScriptPath . '/scripts/Language/SC_LanguageEn.js';
	}

	// user language file
	$lng = '/scripts/Language/SC_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgConnectorIP . $lng)) {
			$userpathlng = $smwgConnectorScriptPath . $lng;
		} else {
			$userpathlng = $smwgConnectorScriptPath . '/scripts/Language/SC_LanguageUserEn.js';
		}
	} else {
		$userpathlng = $smwgConnectorScriptPath . '/scripts/Language/SC_LanguageUserEn.js';
	}
}

// Connector scripts callback
// includes necessary script and css files.
function smwConnectorAddHTMLHeader(&$out){
	global $wgTitle, $wgOut;
	if($wgTitle->getNamespace() == NS_SPECIAL && $wgTitle->getText() == 'ViewREST') {
		SCViewREST::setupHeader($out);
		return true;
	}
	
	if ($wgTitle->getNamespace() != SF_NS_FORM) return true;

	global $smwgConnectorScriptPath, $smwgScriptPath;
	smwfConnectorGetJSLanguageScripts($pathlng, $userpathlng);
	$wgOut->addLink( array( 'rel' => 'stylesheet', 'type' => 'text/css',
		'href' => $smwgConnectorScriptPath . '/scripts/extjs/resources/css/ext-all.css' ) );
	$wgOut->addLink( array( 'rel' => 'stylesheet', 'type' => 'text/css',
		'href' => $smwgConnectorScriptPath . '/skins/schema_mapping.css' ) );

//	if ( defined( 'SMW_HALO_VERSION' ) ){
//		// halo uses prototype
//		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/adapter/prototype/ext-prototype-adapter.js"></script>');
//	} else {
		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/adapter/ext/ext-base.js"></script>');
//	}
	$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/ext-all.js"></script>');
	$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/ux/Spotlight.js"></script>');
	$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/schema_mapping.js"></script>');

	return true; // do not load other scripts or CSS
}
?>