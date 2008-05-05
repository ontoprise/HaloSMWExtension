<?php
/*
 * Created on 13.09.2007
 *
 * Author: kai
 */
define('SMW_HALO_VERSION', '1.1');

 // constant for special schema properties
define('SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT', 1);
define('SMW_SSP_HAS_MAX_CARD', 2);
define('SMW_SSP_HAS_MIN_CARD', 3);
define('SMW_SSP_IS_INVERSE_OF', 4);
define('SMW_SSP_IS_EQUAL_TO', 5);

// constants for special categories
define('SMW_SC_TRANSITIVE_RELATIONS', 0);
define('SMW_SC_SYMMETRICAL_RELATIONS', 1);

// constants for special properties, used for datatype assignment and storage
define('SMW_SP_CONVERSION_FACTOR_SI', 1000);

$smwgHaloIP = $IP . '/extensions/SMWHalo';
$smwgHaloScriptPath = $wgScriptPath . '/extensions/SMWHalo';
$smwgHaloAAMParser = null;
$smwgProcessedAnnotations = null;

require_once($smwgHaloIP."/includes/SMW_ResourceManager.php");
/**
 * Configures SMW Halo Extension for initialization.
 * Must be called *AFTER* SMW is intialized.
 */
function enableSMWHalo() {
	global $wgExtensionFunctions;
	global $smwgOWLFullExport;
	global $wgHooks;
	$wgExtensionFunctions[] = 'smwgHaloSetupExtension';
	$wgHooks['LanguageGetMagic'][] = 'smwfAddHaloMagicWords';
	$smwgOWLFullExport = TRUE;
}

/**
 * Intializes SMW Halo Extension.
 * Called from SMW during initialization.
 */
function smwgHaloSetupExtension() {
	global $smwgIP, $smwgHaloIP, $wgHooks, $smwgMasterGeneralStore, $wgFileExtensions, $wgJobClasses, $wgExtensionCredits;
	global $smwgHaloContLang, $wgAutoloadClasses, $wgSpecialPages, $wgAjaxExportList, $wgGroupPermissions;
	global $mediaWiki;
	
	$smwgMasterGeneralStore = NULL;
	
	// register SMW hooks
	$wgHooks['smwInitializeTables'][] = 'smwfHaloInitializeTables';
	$wgHooks['smwNewSpecialValue'][] = 'smwfHaloSpecialValues';
	$wgHooks['smwInitDatatypes'][] = 'smwfHaloInitDatatypes';
	$wgHooks['smwBeforeUpdate'][] = 'smwfBeforeSemanticUpdate';
	$wgHooks['smwAfterUpdate'][] = 'smwfAfterSemanticUpdate';
	

	// Remove the existing smwfSaveHook and replace it with the
	// new and functionally enhanced smwfHaloSaveHook
	$wgHooks['ArticleSaveComplete'] = array_diff($wgHooks['ArticleSaveComplete'], array('smwfSaveHook'));
	$wgHooks['ArticleSaveComplete'][] = 'smwfHaloSaveHook'; // store annotations
	$wgHooks['ArticleSave'][] = 'smwfHaloPreSaveHook';
	
	global $wgRequest, $wgContLang, $wgCommandLineMode;
	
    $spns_text = $wgContLang->getNsText(NS_SPECIAL);
    $tyns_text = $wgContLang->getNsText(SMW_NS_TYPE);
    $sp_aliases = $wgContLang->getSpecialPageAliases();
    
	// register AddHTMLHeader functions for special pages
	// to include javascript and css files (only on special page requests).
	if (stripos($wgRequest->getRequestURL(), $spns_text.":") !== false) {
		$wgHooks['BeforePageDisplay'][]='smwOBAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwGAAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwfQIAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwFWAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwACLAddHTMLHeader';
	}
	// Register parser hooks for advanced annotation mode
	
	$action = $wgRequest->getVal('action');
	if ($action == 'annotate') {
		$wgHooks['ParserBeforeStrip'][] = 'smwfAAMBeforeStrip';
		$wgHooks['ParserAfterStrip'][] = 'smwfAAMAfterStrip';
		$wgHooks['InternalParseBeforeLinks'][] = 'smwfAAMBeforeLinks';
		$wgHooks['ParserBeforeTidy'][] = 'smwfAAMBeforeTidy';
		$wgHooks['ParserAfterTidy'][] = 'smwfAAMAfterTidy';
		$wgHooks['OutputPageBeforeHTML'][] = 'smwfAAMBeforeHTML';
	}
	$wgHooks['UnknownAction'][] = 'smwfAnnotateAction';
	
	// autocompletion option registration
	$wgHooks['UserToggles'][] = 'smwfAutoCompletionToggles';
	$wgHooks['SetUserDefinedCookies'][] = 'smwfSetUserDefinedCookies';
	
	//parser function for multiple template annotations
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterCommaAnnotation';
		
	// register file extensions for upload
	$wgFileExtensions[] = 'owl'; // for ontology import
	    
	// Register job classes (if Move operation or maintenance script)
    $needRefactorJobs = ($wgRequest->getVal("title") == $spns_text.':'.$sp_aliases['Movepage'][0])
                        || (stripos($wgRequest->getVal("title"), $tyns_text.":") === 0);
    
   
	if ($needRefactorJobs || $wgCommandLineMode) {
		$wgJobClasses['SMW_UpdateLinksAfterMoveJob'] = 'SMW_UpdateLinksAfterMoveJob';
		$wgJobClasses['SMW_UpdateCategoriesAfterMoveJob'] = 'SMW_UpdateCategoriesAfterMoveJob';
		$wgJobClasses['SMW_UpdatePropertiesAfterMoveJob'] = 'SMW_UpdatePropertiesAfterMoveJob';
		$wgJobClasses['SMW_UpdateJob'] = 'SMW_UpdateJob';
	}
	$wgJobClasses['SMW_LocalGardeningJob'] = 'SMW_LocalGardeningJob';
	// register message system (not for ajax, only by demand)
	if ($action != 'ajax') {
		smwfHaloInitMessages();
	}
	
	// add some AJAX calls
	if ($action == 'ajax') {
		$method_prefix = smwfGetAjaxMethodPrefix();
		
		// decide according to ajax method prefix which script(s) to import
		switch($method_prefix) {
			case '_ac_' : smwfHaloInitMessages();
			            require_once($smwgHaloIP . '/includes/SMW_Autocomplete.php');
						break;
			case '_cs_' : smwfHaloInitMessages();
						require_once($smwgHaloIP . '/includes/SMW_CombinedSearch.php');
						break;
			case '_ga_' : smwfHaloInitMessages();
						require_once($smwgHaloIP . '/specials/SMWGardening/SMW_GardeningAjaxAccess.php');
						break;
			case '_ob_' : smwfHaloInitMessages();
						require_once($smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowserAjaxAccess.php');
						break; 			
			case '_fw_' : smwfHaloInitMessages(); 
						 require_once($smwgHaloIP . '/specials/SMWFindWork/SMW_FindWorkAjaxAccess.php');
						break;		
			case '_ca_' : smwfHaloInitMessages();
						require_once($smwgHaloIP . '/includes/SMW_ContentProviderForAura.php');
						break;	
			case '_qi_' : smwfHaloInitMessages();
						require_once($smwgHaloIP . '/specials/SMWQueryInterface/SMW_QIAjaxAccess.php' );
						break;
			case '_tb_' : smwfHaloInitMessages();
						require_once($smwgHaloIP . '/includes/SemanticToolbar/SMW_ToolbarFunctions.php');
						break;
			case '_om_' : smwfHaloInitMessages();
						require_once($smwgHaloIP . '/includes/SMW_OntologyManipulator.php');
						break;
			case '_al_' : require_once($smwgHaloIP . '/specials/ACL/ACLSpecialPage.php');
						break;
						
			default: // default case just imports everything (should be avoided)
				smwfHaloInitMessages();
				require_once($smwgHaloIP . '/includes/SMW_Autocomplete.php');
				require_once($smwgHaloIP . '/includes/SMW_CombinedSearch.php');
				require_once($smwgHaloIP . '/includes/SMW_ContentProviderForAura.php');
				require_once($smwgHaloIP . '/specials/SMWQueryInterface/SMW_QIAjaxAccess.php' );
				require_once($smwgHaloIP . '/specials/SMWGardening/SMW_GardeningAjaxAccess.php');
				require_once($smwgHaloIP . '/specials/SMWFindWork/SMW_FindWorkAjaxAccess.php');
				require_once($smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowserAjaxAccess.php');
				require_once($smwgHaloIP . '/includes/SemanticToolbar/SMW_ToolbarFunctions.php');
				require_once($smwgHaloIP . '/includes/SMW_OntologyManipulator.php');
				require_once($smwgHaloIP . '/specials/ACL/ACLSpecialPage.php');
		}
		
		
	} else { // otherwise register special pages

		// Register new or overwrite existing special pages
		$wgAutoloadClasses['SMW_OntologyBrowser'] = $smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowser.php';
		$wgSpecialPages['OntologyBrowser'] = array('SMW_OntologyBrowser');
	
		$wgAutoloadClasses['SMWGardening'] = $smwgHaloIP . '/specials/SMWGardening/SMW_Gardening.php';
		$wgSpecialPages['Gardening'] = array('SMWGardening');
		
		$wgAutoloadClasses['ACLSpecialPage'] = $smwgHaloIP . '/specials/ACL/ACLSpecialPage.php';
		$wgSpecialPages['ACL'] = array('ACLSpecialPage');
	
		$wgAutoloadClasses['SMWHelpSpecial'] = $smwgHaloIP . '/specials/SMWHelpSpecial/SMWHelpSpecial.php';
		$wgSpecialPages['ContextSensitiveHelp'] = array('SMWHelpSpecial');
	
		$wgAutoloadClasses['SMWQueryInterface'] = $smwgHaloIP . '/specials/SMWQueryInterface/SMWQueryInterface.php';
		$wgSpecialPages['QueryInterface'] = array('SMWQueryInterface');
	
		$wgSpecialPages['Properties'] = array('SMWSpecialPage','Properties', 'smwfDoSpecialProperties', $smwgHaloIP . '/specials/SMWQuery/SMWAdvSpecialProperties.php');
		
		//KK: Deactivate Halo RDFExport. It is too buggy
		//$wgSpecialPages['ExportRDF'] = array('SMWSpecialPage','ExportRDF', 'doSpecialExportRDF', $smwgHaloIP . '/specials/SMWExport/SMW_ExportRDF.php');
	
		$wgSpecialPages['GardeningLog'] = array('SMWSpecialPage','GardeningLog', 'smwfDoSpecialLogPage', $smwgHaloIP . '/specials/SMWGardening/SMW_GardeningLogPage.php');
						
		$wgSpecialPages['FindWork'] = array('SMWSpecialPage','FindWork', 'smwfDoSpecialFindWorkPage', $smwgHaloIP . '/specials/SMWFindWork/SMW_FindWork.php');
	}
	
	// include SMW logger (exported as ajax function but also used locally)
	require_once($smwgHaloIP . '/includes/SMW_Logger.php');
	
	// import available job classes (for refactoring)
	// do this only when the page is actually moved.
	if ($needRefactorJobs || $wgCommandLineMode) {
		require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdateJob.php');
		require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdateLinksAfterMoveJob.php');
		require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdatePropertiesAfterMoveJob.php');
		require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdateCategoriesAfterMoveJob.php');
	}
	require_once($smwgHaloIP . '/includes/Jobs/SMW_LocalGardeningJob.php');
	
	// Register MW hooks
	$wgHooks['ArticleFromTitle'][] = 'smwfHaloShowListPage';
	$wgHooks['BeforePageDisplay'][]='smwfHaloAddHTMLHeader';
	$wgHooks['SpecialMovepageAfterMove'][] = 'smwfGenerateUpdateAfterMoveJob';

	// Register Annotate-Tab
	$wgHooks['SkinTemplateContentActions'][] = 'smwfAnnotateTab';
	
	
	// Register Credits
	$wgExtensionCredits['parserhook'][]= array('name'=>'SMW+&nbsp;Extension', 'version'=>SMW_HALO_VERSION, 
			'author'=>"Thomas&nbsp;Schweitzer, Kai&nbsp;K&uuml;hn, Markus&nbsp;Nitsche, J&ouml;rg Heizmann, Frederik&nbsp;Pfisterer, Robert Ulrich, Daniel Hansch, Moritz Weiten and Michael Erdmann. Maintained by [http://www.ontoprise.de Ontoprise].", 
			'url'=>'https://sourceforge.net/projects/halo-extension', 
			'description' => 'Facilitate the use of Semantic Mediawiki for a large community of non-tech-savvy users. [http://ontoworld.org/wiki/Halo_Extension View feature description.]');
	return true;
}

function smwfHaloInitMessages() {
	global $smwgHaloContLang, $smwgMessagesInitialized;
	if (isset($smwgMessagesInitialized)) return; // prevent double init
	smwfHaloInitContentMessages();
	smwfHaloInitUserMessages(); // lazy init for ajax calls
		
	// add additional special properties to SMW
	$smwgHaloContLang->registerSpecialProperties();
	$smwgMessagesInitialized = true;
}
/**
 * Registeres SMW Halo Datatypes. Called from SMW.
 */
function smwfHaloInitDatatypes() {
	global $wgAutoloadClasses, $smwgHaloIP, $smwgHaloContLang;
	$wgAutoloadClasses['SMWChemicalFormulaTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_ChemFormula.php';
	SMWDataValueFactory::registerDatatype('_chf', 'SMWChemicalFormulaTypeHandler',
	                                      $smwgHaloContLang->getHaloDatatype('smw_hdt_chemical_formula'));
	$wgAutoloadClasses['SMWChemicalEquationTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_ChemEquation.php';
	SMWDataValueFactory::registerDatatype('_che', 'SMWChemicalEquationTypeHandler',
	                                      $smwgHaloContLang->getHaloDatatype('smw_hdt_chemical_equation'));
	$wgAutoloadClasses['SMWMathematicalEquationTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_MathEquation.php';
	SMWDataValueFactory::registerDatatype('_meq', 'SMWMathematicalEquationTypeHandler',
	                                      $smwgHaloContLang->getHaloDatatype('smw_hdt_mathematical_equation'));
	$wgAutoloadClasses['SMWSIUnitTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_SI.php';
	SMWDataValueFactory::registerDatatype('_siu', 'SMWSIUnitTypeHandler',
	                                      $smwgHaloContLang->getSpecialPropertyLabel(SMW_SP_CONVERSION_FACTOR_SI));

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
function smwfHaloInitializeTables() {
	global $smwgHaloIP;
	require_once($smwgHaloIP . '/specials/SMWGardening/SMW_Gardening.php');
	SMWGardeningIssuesAccess::getGardeningIssuesAccess()->setup(true);
	SMWGardeningLog::getGardeningLogAccess()->setup(true);
	smwfGetSemanticStore()->setup(true);
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
function smwfDBSupportsFunction($lib) {
		$dbr =& wfGetDB( DB_SLAVE );
		$res = $dbr->query('SELECT * FROM mysql.func WHERE dl LIKE '.$dbr->addQuotes($lib.'.%'));
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
		$jsm->addCSSIf($smwgHaloScriptPath . '/skins/CombinedSearch/CombinedSearch.css', "all", -1, NS_SPECIAL.":".wfMsg('search'));
		$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/semantictoolbar.css', "all", -1, NS_SPECIAL.":".wfMsg('search') );
		$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/semantictoolbar.css', "edit");
		$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/semantictoolbar.css', "annotate");
		$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "annotate");
		
		// serialize the css
		$jsm->serializeCSS($out);
		
		/* 
		 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		 * 
		 * Note: If you add new scripts to this section you have to update SMW_packscripts.php scripts too.
		 * Just add the script's name (not whole path) in the 'smw' section.
		 * 
		 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		 * */
		if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {

			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
			$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');
			
			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/effects.js');
			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/slider.js');
			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/dragdrop.js');

			// The above id is essential for the JavaScript to find out the $smwgHaloScriptPath to
			// include images. Changes in the above must always be coordinated with the script!

			//global $smwgEnableLogging;
			//if($smwgEnableLogging  === true){
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Logger/smw_logger.js', "all");
			//}
			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js');

			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js');

			smwfHaloAddJSLanguageScripts($jsm);

			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Framework.js', "edit");

			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Framework.js', "annotate");
			
			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Framework.js', "all", -1, NS_SPECIAL.":".wfMsg('search'));

			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Divcontainer.js', "edit");

			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Divcontainer.js', "annotate");
			
			$jsm->addScriptIf($wgStylePath . '/ontoskin/STB_Divcontainer.js', "all", -1, NS_SPECIAL.":".wfMsg('search'));

			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Autocompletion/wick.js');

			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Help.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Help.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Help.js', "all", -1, NS_SPECIAL.":".wfMsg('search'));

			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_SaveAnnotations.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "annotate");			
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/CombinedSearch/CombinedSearch.js', "view", -1, NS_SPECIAL.":".wfMsg('search'));
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Editarea/edit_area_loader.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Editarea/SMWEditInterface.js', "edit");
			$jsm->addScriptIf($wgStylePath . '/ontoskin/obSemToolContribution.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "annotate");
			
		} else {
			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
			$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');
			
			//FIXME: these scripts must be exchanged by a full editor script
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Editarea/edit_area_loader.js', "edit");
						
			smwfHaloAddJSLanguageScripts($jsm);
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js');
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralScripts.js');
			
			
		}

		// serialize the scripts
		$jsm->serializeScripts($out);
		return true; // always return true, in order not to stop MW's hook processing!
}

/**
 * Add appropriate JS language script
 */
function smwfHaloAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
	global $smwgHaloIP, $wgLanguageCode, $smwgHaloScriptPath, $wgUser;
	
	// content language file
	$lng = '/scripts/Language/SMW_Language';
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgHaloIP . $lng)) {
			$jsm->addScriptIf($smwgHaloScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_LanguageEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_LanguageEn.js', $mode, $namespace, $pages);
	}
	
	// user language file
	$lng = '/scripts/Language/SMW_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgHaloIP . $lng)) {
			$jsm->addScriptIf($smwgHaloScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_LanguageUserEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_LanguageUserEn.js', $mode, $namespace, $pages);
	}
}

/**
 * Callback function for hook 'SMW_SpecialValue'. It returns a data value for
 * the special type "_siu" (SI-Units), if requested.
 */
function smwfHaloSpecialValues($typeID, $value, $caption, &$result) {
	if ($typeID == SMW_SP_CONVERSION_FACTOR_SI) {
		$result = SMWDataValueFactory::newTypeIDValue('_siu', $value, $caption);
	}
	return true;
}

/**
 * Called when an article has been moved.
 */
function smwfGenerateUpdateAfterMoveJob(& $moveform, & $oldtitle, & $newtitle) {
		$store = smwfGetStore();

		$titlesToUpdate = $oldtitle->getLinksTo();
		$params[] = $oldtitle->getText();
		$params[] = $newtitle->getText();

		$fullparams[] = $oldtitle->getPrefixedText();
		$fullparams[] = $newtitle->getPrefixedText();
		
		foreach ($titlesToUpdate as $uptitle) {
			$jobs[] = new SMW_UpdateLinksAfterMoveJob($uptitle, $fullparams);
		}

		if ($oldtitle->getNamespace()==SMW_NS_PROPERTY) {
			$titlesToUpdate = $store->getAllPropertySubjects( $oldtitle );
			foreach ($titlesToUpdate as $uptitle)
				$jobs[] = new SMW_UpdatePropertiesAfterMoveJob($uptitle, $params);
		}

		if ($oldtitle->getNamespace()==NS_CATEGORY) {
			$titlesToUpdate = $store->getSpecialSubjects( SMW_SP_HAS_CATEGORY, $oldtitle );
			foreach ($titlesToUpdate as $uptitle)
				$jobs[] = new SMW_UpdateCategoriesAfterMoveJob($uptitle, $params);
		}

		Job :: batchInsert($jobs);
		return true;
}
	
	/**
	 * Called *before* an article is saved. Used for LocalGardening
	 *
	 * @param unknown_type $article
	 * @param unknown_type $user
	 * @param unknown_type $text
	 * @param unknown_type $summary
	 * @param unknown_type $minor
	 * @param unknown_type $watch
	 * @param unknown_type $sectionanchor
	 * @param unknown_type $flags
	 */
    function smwfHaloPreSaveHook(&$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags) {
    	// -- LocalGardening --
    	global $smwgLocalGardening;
    	if (isset($smwgLocalGardening) && $smwgLocalGardening == true) {
	        $gard_jobs[] = new SMW_LocalGardeningJob($article->getTitle(), "save");
	        Job :: batchInsert($gard_jobs);
    	}
        return true;
        // --------------------
    }
	/**
	 * Called *before* semantic annotations are updated.
	 * Save annotation ratings in global variable.
	 * 
	 * @param & $title's name which is updated (textual form)
	 */
	function smwfBeforeSemanticUpdate(& $title) {
		global $smwgProcessedAnnotations;
		// save currently processed annotations in $processedAnnotations
		$smwgProcessedAnnotations = smwfGetSemanticStore()->getRatedAnnotations(str_replace(" ","_", $title));
		return true;
	}
	
	/**
	 * Called *after* semantic annotations has been updated.
	 * Store saved annotations ratings.
	 * 
	 * @param & $title's name which is updated (textual form)
	 */
	function smwfAfterSemanticUpdate(& $title) {
		global $smwgProcessedAnnotations;
		if ($smwgProcessedAnnotations != NULL) {
			foreach($smwgProcessedAnnotations as $pa) {
				smwfGetSemanticStore()->rateAnnotation(str_replace(" ","_", $title), $pa[0], $pa[1], $pa[2] );
			}
		}
		return true;
	}
	
	/**
	*  This method will be called after an article is saved
	*  and stores the semantic properties in the database. One
	*  could consider creating an object for deferred saving
	*  as used in other places of MediaWiki.
	*  This hook extends SMW's smwfSaveHook insofar that it
	*  updates dependent properties or individuals when a type
	*  or propeerty gets changed. 
	*/
	function smwfHaloSaveHook(&$article, &$user, &$text) {
		global $smwgIP, $smwgHaloIP;
		include_once($smwgIP . '/includes/SMW_Factbox.php'); // Normally this must have happende, but you never know ...
		include_once($smwgHaloIP . '/specials/SMWGardening/SMW_Gardening.php'); 
		
		$title=$article->getTitle();
		SMWGardeningIssuesAccess::getGardeningIssuesAccess()->setGardeningIssueToModified($title);
		
		$updatejobflag = 0;
        
		
	 	/**
		 * Checks if the semantic data has been changed.
		 * Sets the updateflag is so.
		 */
		if ($namespace = $article->getTitle()->getNamespace() == SMW_NS_PROPERTY || SMW_NS_TYPE) {
			$specsTocheck = array (
				SMW_SP_HAS_TYPE,
				SMW_SP_POSSIBLE_VALUE,
				SMW_SP_CONVERSION_FACTOR
			);

			$oldstore = smwfGetStore();

			foreach ($specsTocheck as $type) {
				$oldvalues = $oldstore->getSpecialValues($title, $type); //old values , array containing strings
				$currentvalues = SMWFactbox :: $semdata->getPropertyValues($type); //current values, array containing title objects for attributes or data value objects for the rest

				/**
				 * TODO This seems kind of wrong, still. I guess there is an easier to way to check
				 * if currentvalues and oldvalues have indeed a diff.
				 */
				$currentstrings = array ();
				if ($type == SMW_SP_HAS_TYPE) {
					foreach ($currentvalues as $currentdata) {
						if ($currentdata instanceof Title) {
							$currentstrings[] = $currentdata->getText();
						} else {
							$currentstrings[] = $currentdata->getWikiValue();
						}
					}
				} else {
					if ($type == SMW_SP_POSSIBLE_VALUE || SMW_SP_CONVERSION_FACTOR) {
						foreach ($currentvalues as $currentdata) {
							$currentstrings[] = $currentdata->getWikiValue();
						}
					}
				}
				$oldstrings = array ();
				if ($type == SMW_SP_HAS_TYPE) {
					foreach ($oldvalues as $olddata) {
						$oldstrings[] = $olddata->getWikiValue();
					}
				} else {
					if ($type == SMW_SP_POSSIBLE_VALUE || SMW_SP_CONVERSION_FACTOR) {
						foreach ($oldvalues as $olddata) {
							if ($olddata instanceof SMWDataValue)
								$oldstrings[] = $olddata->getWikiValue();
						}
					}
				}

				// double side diff
				$diff = array_merge(array_diff($currentstrings, $oldstrings), array_diff($oldstrings, $currentstrings));

				if (!empty ($diff)) {
					
					$updatejobflag = 1;
					break;
				}

			}

	 	}

		SMWFactbox::storeData($title, smwfIsSemanticsProcessed($title->getNamespace()));

		/*
		 * Triggers the relevant Updatejobs if necessary
		 */
		if ($updatejobflag == 1) {
			$store = smwfGetStore();
			if ($article->getTitle()->getNamespace() == SMW_NS_PROPERTY) {
				smwLog("Property type of '".$article->getTitle()->getText()."' changed.", "RF", "smwfHaloSaveHook");
				
				$subjectsOfAttribpages = $store->getAllPropertySubjects($article->getTitle());

				foreach ($subjectsOfAttribpages as $titleb) {
					if ($titleb != NULL) { 
						$jobs[] = new SMW_UpdateJob($titleb);
					}
				}
					
				
			} else {
				if ($article->getTitle()->getNamespace() == SMW_NS_TYPE) {
					
					smwLog("Type '".$article->getTitle()->getText()."' changed.", "RF", "smwfHaloSaveHook");
					
					$subjects = array ();
					$subjects = $store->getSpecialSubjects(SMW_SP_HAS_TYPE, $title);

					foreach ($subjects as $titlesofattributepagestoupdate) {
						$subjectsOfAttribpages = array ();
						$subjectsOfAttribpages = $store->getAllPropertySubjects($titlesofattributepagestoupdate);

						foreach ($subjectsOfAttribpages as $titleb) {
							if ($titleb != NULL) { 
								$jobs[] = new SMW_UpdateJob($titleb);
							}
						}
					}
				}
			}
			Job :: batchInsert($jobs);
	 	}
		return true; // always return true, in order not to stop MW's hook processing!
	}
	
function smwfAnnotateTab ($content_actions) {
	//Check if ontoskin is available
	global $wgUser;
	if($wgUser->getSkin()->skinname != 'ontoskin')
		return true;
	//Check if edit tab is present, if not don't at annote tab
	if (!array_key_exists('edit',$content_actions) )
		return true;
	global $wgUser, $wgRequest;
	$action = $wgRequest->getText( 'action' );
	//Build annotate tab
	global $wgTitle;  
	$main_action['main'] = array(
        	'class' => ($action == 'annotate') ? 'selected' : false,
        	'text' => wfMsg('smw_annotation_tab'), //Title of the tab
        	'href' => $wgTitle->getLocalUrl('action=annotate')   //where it links to
	);
      	
	//Find position of edit button
	$editpos = count(range(0,$content_actions['edit']))+1;
	//Split array
	$beforeedit = array_slice($content_actions,0,$editpos-1);
	$afteredit = array_slice($content_actions,$editpos-1,count($content_actions));
	//Merge array with new action
	$content_actions = array_merge( $beforeedit, $main_action);   //add a new action
	$content_actions = array_merge( $content_actions, $afteredit); 
	return true;
}

/**
 * This function is called from the parser, before <nowiki> parts have been 
 * removed and before templates etc are expanded.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 * @param unknown_type $strip_stat
 */
function smwfAAMBeforeStrip(&$parser, &$text, &$strip_stat) {
	global $smwgHaloIP, $smwgHaloAAMParser;
	require_once( "$smwgHaloIP/includes/SMW_AAMParser.php");

	
	if ($smwgHaloAAMParser == null) {
		$smwgHaloAAMParser = new SMWH_AAMParser($text);
	}
	$parser->mOptions->setEditSection(false);
	$text = $smwgHaloAAMParser->addWikiTextOffsets($text);
	return true;
}

 
/**
 * This function is called from the parser, after <nowiki> parts have been 
 * removed, but before templates etc are expanded.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 * @param unknown_type $strip_stat
 */
function smwfAAMAfterStrip(&$parser, &$text, &$strip_stat) {
	global $smwgHaloAAMParser;
	if ($smwgHaloAAMParser == null) {
		return true;
	}
	$text = $smwgHaloAAMParser->highlightAnnotations($text);
	return true;
}

/**
 * This function is called from the parser, after templates etc. are 
 * expanded.
 * Annotations i.e. text enclosed in [[]] is highlighted.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 */
function smwfAAMBeforeLinks(&$parser, &$text) { 
	return true;
}

/**
 * This function is called from the parser, when the HTML is nearly completely
 * generated.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 */
function smwfAAMBeforeTidy(&$parser, &$text) { 
	return true;
}

/**
 * This function is called from the parser, when the HTML is nearly completely
 * generated.
 * The wiki text offsets that have been introduced in a previous parsing stage
 * are replaced by their corresponding HTML.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 */
function smwfAAMAfterTidy(&$parser, &$text) {
	global $smwgHaloAAMParser, $wgOut, $wgTitle;
	if ($smwgHaloAAMParser == null) {
		return true;
	}
	$text = $smwgHaloAAMParser->wikiTextOffset2HTML($text);
	$text = $smwgHaloAAMParser->highlightAnnotations2HTML($text);
	// Set the article's title
//	$t = wfMsg( 'smw_annotating', $parser->mTitle->getPrefixedText() );
	$t = wfMsg( 'smw_annotating', $wgTitle->getPrefixedText() );
	$wgOut->setPageTitle($t);
	
	return true;
}

/**
 * This function is called from the parser, when the HTML is nearly completely
 * generated.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 */
function smwfAAMBeforeHTML(&$out, &$text) { 
	return true;
}
	
/**
 * This function is called when the annotation mode is activated. It renders
 * the article with highlighted annotations.
 *
 * @param string $action The action i.e. "annotate"
 * @param Article $article The article that will be displayed.
 * @return false => processing should continue
 */
function smwfAnnotateAction($action, $article) {
	
	$title = $article->getTitle();
	$title->invalidateCache();
	$article->view();

	// The resolution of timestamps for the cache is only in seconds. Invalidate
	// the cache by setting a timestamp 2 seconds from now.
	$now = wfTimestamp(TS_MW, time()+2);
	$dbw = wfGetDB( DB_MASTER );
	$success = $dbw->update( 'page',
			array( /* SET */
				'page_touched' => $now
			), array( /* WHERE */
				'page_namespace' => $title->getNamespace() ,
				'page_title' => $title->getDBkey()
			), 'SMW_Initialize::smwfAnnotateAction'
		);
	
	return false;
}

// OntologyBrowser scripts callback
// includes necessary script and css files.
 function smwOBAddHTMLHeader(&$out) {
 	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;
		
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode, $smwgScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/effects.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/dragdrop.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":OntologyBrowser");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");
		
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/ontologytools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeview.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewActions.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewData.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/deployOntologyBrowser.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/OntologyBrowser/treeview.css', "all", -1, NS_SPECIAL.":OntologyBrowser");
	$jsm->addCSSIf($smwgScriptPath . '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":OntologyBrowser");

	return true;
}

// ACL scripts callback
// includes necessary script and css files.
 function smwACLAddHTMLHeader(&$out) {
 	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;
		
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode, $smwgScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":ACL");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":ACL");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":ACL");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":ACL");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/ACL/acl.js', "all", -1, NS_SPECIAL.":ACL");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":ACL");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":ACL");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":ACL");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":ACL");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/ACL/acl.js', "all", -1, NS_SPECIAL.":ACL");
	}

	$jsm->addCSSIf($smwgScriptPath . '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":ACL");

	return true;
}

// Gardening scripts callback
// includes necessary script and css files.
function smwGAAddHTMLHeader(&$out) {
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;
	
	global $smwgHaloScriptPath, $smwgDeployVersion;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/scriptaculous.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Language/SMW_Language.js',  "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Gardening/gardening.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Gardening/deployGardening.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		
	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Gardening/gardening.css', "all", -1, NS_SPECIAL.":Gardening");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Gardening/gardeningLog.css', "all", -1, NS_SPECIAL.":GardeningLog");

	return true;
}

// QueryInterface scripts callback
// includes necessary script and css files.
function smwfQIAddHTMLHeader(&$out){
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;
	
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgScriptPath;


	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Logger/smw_logger.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/treeviewQI.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/queryTree.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/Query.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/QIHelper.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/SemanticToolbar/SMW_Help.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":QueryInterface");
	} else {

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/deployQueryInterface.js', "all", -1, NS_SPECIAL.":QueryInterface");
	}
	$jsm->addCSSIf($smwgScriptPath .  '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":QueryInterface");	
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/treeview.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/qi.css', "all", -1, NS_SPECIAL.":QueryInterface");

	return true; // do not load other scripts or CSS
}

// FindWork page callback
// includes necessary script and css files.
function smwFWAddHTMLHeader(& $out) {
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;
	
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode, $smwgScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":FindWork");
		
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":FindWork");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":FindWork");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/FindWork/findwork.js', "all", -1, NS_SPECIAL.":FindWork");
		
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":FindWork");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":FindWork");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/FindWork/findwork.js', "all", -1, NS_SPECIAL.":FindWork");
	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/FindWork/findwork.css', "all", -1, NS_SPECIAL.":FindWork");
	
	return true;
}

function smwfRegisterHaloInlineQueries( &$parser, &$text, &$stripstate ) {
	$parser->setHook( 'ask', 'smwfProcessHaloInlineQuery' );
	$parser->setFunctionHook( 'ask', 'smwfProcessHaloInlineQueryParserFunction' );
	return true; // always return true, in order not to stop MW's hook processing!
}


/**
 * The <ask> parser hook processing part.
 */
function smwfProcessHaloInlineQuery($text, $param, &$parser) {
	global $smwgQEnabled, $smwgHaloIP, $smwgIQRunningNumber;

	if ($smwgQEnabled) {
		$smwgIQRunningNumber++;
		require_once($smwgHaloIP . '/includes/SMW_QueryHighlighter.php');
		return applyQueryHighlighting($text, $param);
	} else {
		return smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
	}
}

function smwfProcessHaloInlineQueryParserFunction(&$parser) {
	global $smwgQEnabled, $smwgIP, $smwgIQRunningNumber;
	if ($smwgQEnabled) {
		$smwgIQRunningNumber++;
		require_once($smwgIP . '/includes/SMW_QueryProcessor.php');
		$rawparams = func_get_args();
		array_shift( $rawparams ); // we already know the $parser ...

		//return SMWQueryProcessor::getResultFromFunctionParams($params,SMW_OUTPUT_WIKI);
		//return SMWQueryProcessor::getResultFromFunctionParams($params,SMW_OUTPUT_WIKI);

		SMWQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts);
		
		return applyQueryHighlighting($querystring, $params, true, $format, $printouts);
	} else {
		return smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
	}
}

function smwfGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
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

function smwfRegisterCommaAnnotation( &$parser, &$text, &$stripstate ) {
	$parser->setFunctionHook( 'annotateList', 'smwfCommaAnnotation' );
	return true; // always return true, in order not to stop MW's hook processing!
}

function smwfCommaAnnotation(&$parser){
	$params = func_get_args();
	array_shift( $params ); // we already know the $parser ...
	$annoName = $params[0];
	$annoValues = split(',', $params[1]);
	$ret = '';
	for ($i=0; $i<sizeof($annoValues); $i++){
		$val = $annoValues[$i];
		$val = trim($val);
		if ($i == 0)
			$ret .= "[[$annoName::$val]]";
		else
			$ret .= ", [[$annoName::$val]]";
	}
	return $ret;
}

function smwfAddHaloMagicWords(&$magicWords, $langCode){
	$magicWords['annotateList'] = array( 0, 'annotateList' );
	return true;
}
?>