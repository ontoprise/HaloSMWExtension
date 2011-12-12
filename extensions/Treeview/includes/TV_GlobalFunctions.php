<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup TreeView
 *
 * This file contains global functions that are called from the TreeView
 * extension.
 * 
 * @author Thomas Schweitzer
 * Date: 30.11.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}

/**
 * Switch on the TreeView extension. This function must be called in
 * LocalSettings.php after TV_Initialize.php was included and default values
 * that are defined there have been modified.
 * For readability, this is the only global function that does not adhere to the
 * naming conventions.
 *
 * This function installs the extension, sets up all autoloading, special pages
 * etc.
 */
function enableTreeView() {
	global $tvgIP, $wgExtensionFunctions, $wgAutoloadClasses,  
	       $wgExtensionMessagesFiles, $wgLanguageCode;

	$wgExtensionFunctions[] = 'tvfSetupExtension';
	$wgExtensionMessagesFiles['TreeView'] = $tvgIP . '/languages/TV_Messages.php'; // register messages (requires MW=>1.11)

	///// Set up autoloading; essentially all classes should be autoloaded!
	$wgAutoloadClasses['TVLanguage']   = $tvgIP . '/languages/TV_Language.php';
	$wgAutoloadClasses['TVLanguageEn'] = $tvgIP . '/languages/TV_LanguageEn.php';
	$wgAutoloadClasses['TVLanguageDe'] = $tvgIP . '/languages/TV_LanguageDe.php';
	$wgAutoloadClasses['TVParserFunctions'] = $tvgIP . '/includes/TV_ParserFunctions.php';
	$wgAutoloadClasses['TVFacetedSearchExtension'] = $tvgIP . '/includes/TV_FacetedSearchExtension.php';
	
	global $wgHooks;
	$wgHooks['ParserFirstCallInit'][] = 'TVParserFunctions::initParserFunctions';
	$wgHooks['LanguageGetMagic'][]    = 'TVParserFunctions::languageGetMagic';
	$wgHooks['MakeGlobalVariablesScript'][] = 'tvfOnResourceLoaderGetConfigVars';
	$wgHooks['MakeGlobalVariablesScript'][] = "FSFacetedSearchSpecial::addJavaScriptVariables";
	
	$wgHooks['FacetedSearchExtensionTop'][] = 'TVFacetedSearchExtension::injectTreeViewDefinition';
	$wgHooks['FacetedSearchExtensionBottomMenu'][] = 'TVFacetedSearchExtension::injectBottomMenu';
	$wgHooks['FacetedSearchExtensionAddResources'][] = 'TVFacetedSearchExtension::addResources';
	
	tvfInitContentLanguage($wgLanguageCode);

	return true;
}

/**
 * Do the actual initialisation of the extension. This is just a delayed init that
 * makes sure MediaWiki is set up properly before we add our stuff.
 *
 * The main things this function does are: register all hooks, set up extension
 * credits, and init some globals that are not for configuration settings.
 */
function tvfSetupExtension() {
	wfProfileIn('tvfSetupExtension');
	global $tvgIP, $wgHooks, $wgExtensionCredits;

	//--- Register hooks ---
//	$wgHooks['userCan'][] = 'HACLEvaluator::userCan';

	// Register hooks
//	$wgHooks['ArticleSaveComplete'][]  = 'HACLParserFunctions::articleSaveComplete';

	tvfSetupScriptAndStyleModule();

	//--- credits (see "Special:Version") ---
	$wgExtensionCredits['semantic'][]= array(
        'name'=>'TreeView',
        'version'=>TV_TREEVIEW_VERSION,
        'author'=>"Maintained by [http://smwplus.com ontoprise GmbH].", 
        'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:TreeView_extension',
        'description' => 'Generates trees based on semantic properties.');

	wfProfileOut('tvfSetupExtension');
	return true;
}

/**
 * Creates a module for the resource loader that contains all scripts and styles
 * that are needed for this extension.
 */
function tvfSetupScriptAndStyleModule() {
	global $wgResourceModules, $tvgIP, $tvgScriptPath;
	$moduleTemplate = array(
		'localBasePath' => $tvgIP,
		'remoteBasePath' => $tvgScriptPath,
		'group' => 'ext.TreeView'
	);
	
	$wgResourceModules['ext.TreeView.tree'] = $moduleTemplate + array(
	// JavaScript and CSS styles. To combine multiple file, just list them as an array.
		'scripts' => array(
			'/scripts/TV_JSTreeWidget.js',
			'/scripts/TV_TreeView.js',
			'/scripts/TV_SolrTreeViewManager.js',
			'/scripts/TV_TreeViewWidget.js',
			'/scripts/TV_TreeViewTheme.js'
	       ),
	       	
		'styles' => array(
            '/skin/treeview.css',
            ),

		'dependencies' => array(
			'ext.facetedSearch.ajaxSolr', 
			'ext.jquery.tree'
            )
	);

	$wgResourceModules['ext.FacetedSearchTreeView.tree'] = $moduleTemplate + array(
	// JavaScript and CSS styles. To combine multiple file, just list them as an array.
		'scripts' => array(
			'/scripts/TV_FacetedSearchExtension.js',
			'/scripts/TV_TreeParserFunctionWidget.js'
	       ),
	       	
		'styles' => array(
//            '/some.css',
            ),
            
		'messages' => array('tv_cl_solr_pfp'),

		'dependencies' => array(
            'ext.facetedSearch.special',
			'ext.TreeView.tree'
            )
	);

}
/**********************************************/
/***** language settings                  *****/
/**********************************************/

/**
 * Initialise a global language object for content language. This
 * must happen early on, even before user language is known, to
 * determine labels for additional parser functions. In contrast, messages
 * can be initialised much later when they are actually needed.
 */
function tvfInitContentLanguage($langcode) {
	global $tvgIP, $tvgContLang;
	if (!empty($tvgContLang)) {
		return;
	}
	wfProfileIn('tvfInitContentLanguage');

	$tvContLangFile = 'TV_Language' . str_replace( '-', '_', ucfirst( $langcode ) );
	$tvContLangClass = 'TVLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );
	if (file_exists($tvgIP . '/languages/'. $tvContLangFile . '.php')) {
		include_once( $tvgIP . '/languages/'. $tvContLangFile . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($tvContLangClass)) {
		include_once($tvgIP . '/languages/TV_LanguageEn.php');
		$tvContLangClass = 'TVLanguageEn';
	}
	$tvgContLang = new $tvContLangClass();

	wfProfileOut('tvfInitContentLanguage');
}

/**
 * Adds configuration variables to the Resource Loader. They can be accessed in
 * JavaScript.
 * 
 * @param $vars
 */
function tvfOnResourceLoaderGetConfigVars(&$vars) {
	global $tvgTreeThemes;
	$vars['tvgTreeThemes'] = $tvgTreeThemes;
	return true;
}
