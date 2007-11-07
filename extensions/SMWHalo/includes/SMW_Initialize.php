<?php
/*
 * Created on 13.09.2007
 *
 * Author: kai
 */
define('SMW_HALO_VERSION', '1.0beta');

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


require_once($smwgHaloIP."/includes/SMW_ResourceManager.php");
/**
 * Configures SMW Halo Extension for initialization.
 * Must be called *AFTER* SMW is intialized.
 */
function enableSMWHalo() {
	global $wgExtensionFunctions;
	global $smwgOWLFullExport;
	$wgExtensionFunctions[] = 'smwgHaloSetupExtension';
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

	// Remove the existing smwfSaveHook and replace it with the
	// new and functionally enhanced smwfHaloSaveHook
	$wgHooks['ArticleSaveComplete'] = array_diff($wgHooks['ArticleSaveComplete'], array('smwfSaveHook'));
	$wgHooks['ArticleSaveComplete'][] = 'smwfHaloSaveHook'; // store annotations
	
	// Register parser hooks for advanced annotation mode
	global $wgRequest; 
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
		
	// register file extensions for upload
	$wgFileExtensions[] = 'owl'; // for ontology import
	
	// Register job classes
	$wgJobClasses['SMW_UpdateLinksAfterMoveJob'] = 'SMW_UpdateLinksAfterMoveJob';
	$wgJobClasses['SMW_UpdateCategoriesAfterMoveJob'] = 'SMW_UpdateCategoriesAfterMoveJob';
	$wgJobClasses['SMW_UpdatePropertiesAfterMoveJob'] = 'SMW_UpdatePropertiesAfterMoveJob';
	$wgJobClasses['SMW_UpdateJob'] = 'SMW_UpdateJob';
	
	// register message system
	smwfHaloInitContentMessages();
	smwfHaloInitUserMessages(); // maybe a lazy init would save time like in SMW?

	// add additional special properties to SMW
	$smwgHaloContLang->registerSpecialProperties();
	
	// add some AJAX calls
	require_once('SMW_Autocomplete.php');
	require_once('SMW_CombinedSearch.php');
	require_once('SMW_ContentProviderForAura.php');


	// Register new or overwrite existing special pages
	$wgAutoloadClasses['SMW_OntologyBrowser'] = $smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowser.php';
	$wgSpecialPages['OntologyBrowser'] = array('SMW_OntologyBrowser');

	$wgAutoloadClasses['SMWGardening'] = $smwgHaloIP . '/specials/SMWGardening/SMW_Gardening.php';
	$wgSpecialPages['Gardening'] = array('SMWGardening');

	$wgAutoloadClasses['SMWHelpSpecial'] = $smwgHaloIP . '/specials/SMWHelpSpecial/SMWHelpSpecial.php';
	$wgSpecialPages['ContextSensitiveHelp'] = array('SMWHelpSpecial');

	$wgAutoloadClasses['SMWQueryInterface'] = $smwgHaloIP . '/specials/SMWQueryInterface/SMWQueryInterface.php';
	$wgSpecialPages['QueryInterface'] = array('SMWQueryInterface');

	$wgSpecialPages['Properties'] = array('SMWSpecialPage','Properties', 'smwfDoSpecialProperties', $smwgHaloIP . '/specials/SMWQuery/SMWAdvSpecialProperties.php');
	$wgSpecialPages['ExportRDF'] = array('SMWSpecialPage','ExportRDF', 'doSpecialExportRDF', $smwgHaloIP . '/specials/SMWExport/SMW_ExportRDF.php');

	$wgSpecialPages['GardeningLog'] = array('SMWSpecialPage','GardeningLog', 'smwfDoSpecialLogPage', $smwgHaloIP . '/specials/SMWGardening/SMW_GardeningLogPage.php');
	// import global functions and remaining AJAX calls
	require_once($smwgHaloIP . '/specials/SMWQueryInterface/SMW_QIAjaxAccess.php' );
	require_once($smwgHaloIP . '/includes/SMW_GlobalFunctionsForSpecials.php');
	require_once($smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowserAjaxAccess.php');
	require_once($smwgHaloIP . '/includes/SemanticToolbar/SMW_ToolbarFunctions.php');
	require_once($smwgHaloIP . '/includes/SMW_OntologyManipulator.php');
	require_once($smwgHaloIP . '/includes/SMW_Logger.php');
	
	// import available job classes (for refactoring)
	require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdateJob.php');
	require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdateLinksAfterMoveJob.php');
	require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdatePropertiesAfterMoveJob.php');
	require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdateCategoriesAfterMoveJob.php');

	// Register MW hooks
	$wgHooks['ArticleFromTitle'][] = 'smwfHaloShowListPage';
	$wgHooks['BeforePageDisplay'][]='smwfHaloAddHTMLHeader';
	$wgHooks['SpecialMovepageAfterMove'][] = 'smwfGenerateUpdateAfterMoveJob';

	// Register Annotate-Tab
	$wgHooks['SkinTemplateContentActions'][] = 'smwfAnnotateTab';
	
	// Register Credits
	$wgExtensionCredits['parserhook'][]= array('name'=>'SMWHalo&nbsp;Extension', 'version'=>SMW_HALO_VERSION, 
			'author'=>"Thomas&nbsp;Schweitzer, Kai&nbsp;K&uuml;hn, Markus&nbsp;Nitsche, J&ouml;rg Heizmann, Frederik&nbsp;Pfisterer, Robert Ulrich, Daniel Hansch, Moritz Weiten and others. Maintained by [http://www.ontoprise.de Ontoprise].", 
			'url'=>'https://sourceforge.net/projects/halo-extension', 
			'description' => 'Facilitate the use of Semantic Mediawiki for a large community of non-tech-savvy users. [http://ontoworld.org/wiki/Halo_Extension View feature description.]');
	return true;
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
	SMWGardening::getGardeningLogAccess()->setup(true);
	SMWGardening::getGardeningIssuesAccess()->setup(true);
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
		$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css');
		
		// serialize the css
		$jsm->serializeCSS($out);

		if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {

			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
			$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');

			$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/slider.js');

			// The above id is essential for the JavaScript to find out the $smwgHaloScriptPath to
			// include images. Changes in the above must always be coordinated with the script!

			//global $smwhgEnableLogging;
			//if($smwhgEnableLogging  === true){
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

			//Add script for Highlighting
			//$highlightScript = '<script type="text/javascript" src="' . $smwgHaloScriptPath . '/skins/SemanticToolbar/SMW_Highlight.js"></script>';
			//$out->addScript($highlightScript);

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
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "annotate");			
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "edit");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "annotate");
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_FactboxType.js', "view");
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

		foreach ($titlesToUpdate as $uptitle) {
			$jobs[] = new SMW_UpdateLinksAfterMoveJob($uptitle, $params);
		}

		if ($oldtitle->getNamespace()===SMW_NS_PROPERTY) {
			$titlesToUpdate = $store->getAllPropertySubjects( $oldtitle );
			foreach ($titlesToUpdate as $uptitle)
				$jobs[] = new SMW_UpdatePropertiesAfterMoveJob($uptitle, $params);
		}

		if ($oldtitle->getNamespace()===NS_CATEGORY) {
			$titlesToUpdate = $store->getSpecialSubjects( SMW_SP_HAS_CATEGORY, $oldtitle );
			foreach ($titlesToUpdate as $uptitle)
				$jobs[] = new SMW_UpdateCategoriesAfterMoveJob($uptitle, $params);
		}

		Job :: batchInsert($jobs);
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
		global $smwgIP;
		include_once($smwgIP . '/includes/SMW_Factbox.php'); // Normally this must have happende, but you never know ...

		$title=$article->getTitle();
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
				$currentvalues = SMWFactbox :: $semdata->getSpecialValues($type); //current values, array containing title objects for attributes or data value objects for the rest

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
		global $smwgHaloAAMParser;
		if ($smwgHaloAAMParser == null) {
			return true;
		}
		$text = $smwgHaloAAMParser->wikiTextOffset2HTML($text);
		$text = $smwgHaloAAMParser->highlightAnnotations2HTML($text);
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
		
		$article->getTitle()->invalidateCache();
		$article->view();
		$article->getTitle()->invalidateCache();
		return false;
	}
?>