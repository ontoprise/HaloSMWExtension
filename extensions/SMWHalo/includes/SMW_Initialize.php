<?php
/*
 * Created on 13.09.2007
 *
 * Author: kai
 */

 // constant for special schema properties
define('SMW_SSP_HAS_DOMAIN_HINT', 1);
define('SMW_SSP_HAS_RANGE_HINT', 2);
define('SMW_SSP_HAS_MAX_CARD', 3);
define('SMW_SSP_HAS_MIN_CARD', 4);
define('SMW_SSP_IS_INVERSE_OF', 5);
define('SMW_SSP_IS_EQUAL_TO', 6);

// constants for special categories
define('SMW_SC_TRANSITIVE_RELATIONS', 0);
define('SMW_SC_SYMMETRICAL_RELATIONS', 1);

$smwgHaloIP = $IP . '/extensions/SMWHalo';
$smwgHaloScriptPath = $wgScriptPath . '/extensions/SMWHalo';

/**
 * Configures SMW Halo Extension for initialization.
 * Must be called *AFTER* SMW is intialized.
 */
function enableSMWHalo() {
	global $wgExtensionFunctions;
	$wgExtensionFunctions[] = 'smwgHaloSetupExtension';
}

/**
 * Intializes SMW Halo Extension.
 * Called from SMW during initialization.
 */
function smwgHaloSetupExtension() {
	global $smwgHaloIP, $wgHooks, $smwgMasterGeneralStore;

	$smwgMasterGeneralStore = NULL;

	$wgHooks['SMW_Datatypes'][] = 'smwfHaloInitDatatypes';
	$wgHooks['SMW_InitializeTables'][] = 'smwfHaloInitializeTables';
	$wgHooks['SMW_FactBoxLinks'][] = 'smwfHaloFactBoxLinks';
	$wgHooks['ArticleFromTitle'][] = 'smwfHaloShowListPage';

	smwfHaloInitContentMessages();
	smwfHaloInitUserMessages();

	require_once('SMW_Autocomplete.php');
	require_once('SMW_CombinedSearch.php');
	require_once('SMW_ContentProviderForAura.php');
	require_once($smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowserAjaxAccess.php');

	require_once($smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowser.php');
	require_once($smwgHaloIP . '/specials/SMWGardening/SMW_Gardening.php');
	require_once($smwgHaloIP . '/specials/SMWHelpSpecial/SMWHelpSpecial.php');
	require_once($smwgHaloIP . '/specials/SMWQueryInterface/SMWQueryInterface.php');
	require_once($smwgHaloIP . '/specials/SMWQuery/SMWAdvSpecialProperties.php');
	require_once($smwgHaloIP . '/specials/SMWExport/SMW_ExportRDF.php');
	require_once($smwgHaloIP . '/includes/SemanticToolbar/SMW_ToolbarFunctions.php');
	require_once($smwgHaloIP . '/includes/SMW_OntologyManipulator.php');

	require_once($smwgHaloIP . '/includes/SMW_Logger.php');


	$wgHooks['BeforePageDisplay'][]='smwfHaloAddHTMLHeader';

	return true;
}

/**
 * Registeres SMW Halo Datatypes. Called from SMW.
 */
function smwfHaloInitDatatypes() {
	global $smwgHaloContLang;
	$typeID = $smwgHaloContLang->getDatatypeLabel('smw_chemicalformula');
	SMWDataValueFactory::registerDataValueClass(str_replace(' ', '_', $typeID),'ChemFormula','SMWChemicalFormulaTypeHandler');
	$typeID = $smwgHaloContLang->getDatatypeLabel('smw_chemicalequation');
	SMWDataValueFactory::registerDataValueClass(str_replace(' ', '_', $typeID),'ChemEquation','SMWChemicalEquationTypeHandler');
	$typeID = $smwgHaloContLang->getDatatypeLabel('smw_mathematicalequation');
	SMWDataValueFactory::registerDataValueClass(str_replace(' ', '_', $typeID),'MathEquation','SMWMathematicalEquationTypeHandler');

	SMWDataValueFactory::registerDataValueClass('_siu','SI','SMWSIUnitTypeHandler');
	return true;
}

/**
 * Registers additional factbox links.
 */
function smwfHaloFactBoxLinks(&$links) {
	global $wgContLang, $wgServer, $wgScriptPath;
	$oblink = SMWInfolink::newExternalLink(wfMsgForContent('smw_viewinOB'), $wgServer.$wgScriptPath."/index.php/".$wgContLang->getNsText(NS_SPECIAL) . ':OntologyBrowser'.'?ns='.SMWFactbox::$semdata->getSubject()->getNsText().'&title='.SMWFactbox::$semdata->getSubject()->getDBkey(), 'oblink');
	$links[] = array('smwoblink', $oblink);
	return true;
}

/**
 * Registers special pages for some namespaces
 */
function smwfHaloShowListPage(&$title, &$article){
	global $smwgHaloIP;
	if ( $title->getNamespace() == NS_CATEGORY ) {
		require_once($smwgHaloIP . '/includes/articlepages/SMW_CategoryPage.php');
		$article = new SMWCategoryPage($title);
	}
	return true;
}

/**
 * Creates or updates additional tables needed by HALO.
 * Called from SMW when admin re-initializes tables
 */
function smwfHaloInitializeTables($verbose) {
	$haloSQLStore = NULL;
	global $smwgDefaultStore, $smwgHaloIP;
			switch ($smwgDefaultStore) {
				case (SMW_STORE_MWDB): default:
					require_once($smwgHaloIP . '/includes/SMW_InitializeSQLDB.php');
					$haloSQLStore = new HaloSQLTableFactory();
				break;
			}
	if ($haloSQLStore != NULL) {
		$haloSQLStore->createOrUpdateTables($verbose);
	}
	return true;
}
/**
 * Registers SMW Halo Content messages.
 */
function smwfHaloInitContentLanguage($langcode) {
		global $smwgHaloIP, $smwgHaloContLang;
		if (!empty($smwgHaloContLang)) { return; }

		$smwContLangClass = 'SMW_HaloLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

		if (file_exists($smwgHaloIP . '/languages/'. $smwContLangClass . '.php')) {
			include_once( $smwgHaloIP . '/languages/'. $smwContLangClass . '.php' );
		}

		// fallback if language not supported
		if ( !class_exists($smwContLangClass)) {
			include_once($smwgHaloIP . '/languages/SMW_HaloLanguageEn.php');
			$smwContLangClass = 'SMW_HaloLanguageEn';
		}
		$smwgHaloContLang = new $smwContLangClass();


}

/**
 * Registers SMW Halo User messages.
 */
function smwfHaloInitUserMessages() {
		global $smwgHaloIP, $smwgHaloLang;
		if (!empty($smwgHaloLang)) { return; }

		global $wgMessageCache, $wgLang;

		$smwLangClass = 'SMW_HaloLanguage' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

		if (file_exists($smwgHaloIP . '/languages/'. $smwLangClass . '.php')) {
			include_once( $smwgHaloIP . '/languages/'. $smwLangClass . '.php' );
		}
		// fallback if language not supported
		if ( !class_exists($smwLangClass)) {
			global $smwgContLang;
			$smwgHaloLang = $smwgContLang;
		} else {
			$smwgHaloLang = new $smwLangClass();
		}

		$wgMessageCache->addMessages($smwgHaloLang->getUserMsgArray(), $wgLang->getCode());

	}

function smwfHaloInitContentMessages() {
		global $smwgHaloContMessagesInPlace;
		if ($smwgHaloContMessagesInPlace) { return; }

		global $wgMessageCache, $smwgHaloContLang, $wgLanguageCode;
		smwfHaloInitContentLanguage($wgLanguageCode);

		$wgMessageCache->addMessages($smwgHaloContLang->getContentMsgArray(), $wgLanguageCode);
		$smwgHaloContMessagesInPlace = true;

}

/**
 * Returns GeneralStore
 */
function &smwfGetSemanticStore() {
		global $smwgMasterGeneralStore, $smwgHaloIP;
		if ($smwgMasterGeneralStore == NULL) {
			global $smwgDefaultStore;
			switch ($smwgDefaultStore) {
				case (SMW_STORE_TESTING):
					$smwgMasterGeneralStore = null; // not implemented yet
					trigger_error('Testing store not implemented for HALO extension.');
				break;
				case (SMW_STORE_MWDB): default:
					require_once($smwgHaloIP . '/includes/SMW_SemanticStoreSQL.php');
					$smwgMasterGeneralStore = new SMWSemanticStoreSQL();
				break;
			}
		}
		return $smwgMasterGeneralStore;
}

/**
 * Checks if a database function is available (considers only UDF functions).
 */
function smwfDBSupportsFunction($functionname) {
		$dbr =& wfGetDB( DB_SLAVE );
		$res = $dbr->query('SELECT * FROM mysql.func WHERE name = '.$dbr->addQuotes($functionname).' AND type='.$dbr->addQuotes('function'));
		$hasSupport = ($dbr->numRows($res) > 0);
		$dbr->freeResult( $res );
		return $hasSupport;
	}

/**
 * Called from MW to fill HTML Header before page is displayed.
 */
function smwfHaloAddHTMLHeader(&$out) {
		global $wgStylePath;
		global $smwgHaloScriptPath,$smwgHaloIP, $smwgDeployVersion, $wgLanguageCode;

		$jsm = SMWResourceManager::SINGLETON();

		$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Autocompletion/wick.css');
		$jsm->addCSSIf($smwgHaloScriptPath . '/scripts/CombinedSearch/CombinedSearch.css', "all", -1, NS_SPECIAL.":".wfMsg('search'));

		// serialize the css
		$jsm->serializeCSS($out);

		if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {

			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
			$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');
			// The above id is essential for the JavaScript to find out the $smwgHaloScriptPath to
			// include images. Changes in the above must always be coordinated with the script!

			global $smwhgEnableLogging;
			if($smwhgEnableLogging  === true){
				$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Logger/smw_logger.js', "all");
			}
			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js');

			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js');

			smwfHaloAddJSLanguageScripts($jsm);

			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Framework.js', "edit");

			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Framework.js', "all", -1, NS_SPECIAL.":".wfMsg('search'));

			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Divcontainer.js', "edit");

			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Divcontainer.js', "all", -1, NS_SPECIAL.":".wfMsg('search'));

			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Autocompletion/wick.js');

			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Help.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Help.js', "all", -1, NS_SPECIAL.":".wfMsg('search'));

			//Add script for Highlighting
			//$highlightScript = '<script type="text/javascript" src="' . $smwgHaloScriptPath . '/skins/SemanticToolbar/SMW_Highlight.js"></script>';
			//$out->addScript($highlightScript);

			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_FactboxType.js', "view");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/CombinedSearch/CombinedSearch.js', "view", -1, NS_SPECIAL.":".wfMsg('search'));
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Editarea/edit_area_loader.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Editarea/SMWEditInterface.js', "edit");
			$jsm->addScriptIf($wgStylePath . '/ontoskin/obSemToolContribution.js', "edit");

		} else {
			smwfHaloAddJSLanguageScripts($jsm);

			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployScripts.js');

			//FIXME: these scripts must be exchanged by a full editor script
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Editarea/edit_area_loader.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Editarea/SMWEditInterface.js', "edit");
		}

		// serialize the scripts
		$jsm->serializeScripts($out);
		return true; // always return true, in order not to stop MW's hook processing!
}

/**
 * Add appropriate JS language script
 */
function smwfHaloAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
	global $smwgHaloIP, $wgLanguageCode, $smwgHaloScriptPath;
	$lng = '/scripts/Language/SMW_Language';
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgHaloIP . $lng)) {
			$jsm->addScriptIf($smwgHaloScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgHaloScriptPath . '/skins/Language/SMW_LanguageEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath . '/skins/Language/SMW_LanguageEn.js', $mode, $namespace, $pages);
	}
}
?>