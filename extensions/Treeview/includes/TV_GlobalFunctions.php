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
 * For readability, this is the only global function of the extension.
 *
 */
function enableTreeView() {
	return TreeViewExtension::enable();
}



/**
 * This is the main class of the TreeView extension. It consists mainly of 
 * static methods for setting up the extension.
 * 
 * @author Thomas Schweitzer
 * 
 */
class TreeViewExtension  {
	
	//--- Constants ---
		
	//--- Private fields ---
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	/**
	 * This function installs the extension, sets up all autoloading, special 
	 * pages etc.
	 */
	public static function enable() {
		global $tvgIP, $wgExtensionFunctions, $wgAutoloadClasses,  
		       $wgExtensionMessagesFiles, $wgLanguageCode;
	
		$wgExtensionFunctions[] = 'TreeViewExtension::setupExtension';
		$wgExtensionMessagesFiles['TreeView'] = $tvgIP . '/languages/TV_Messages.php'; // register messages (requires MW=>1.11)
	
		///// Set up autoloading; essentially all classes should be autoloaded!
		$wgAutoloadClasses['TVLanguage']   = $tvgIP . '/languages/TV_Language.php';
		$wgAutoloadClasses['TVLanguageEn'] = $tvgIP . '/languages/TV_LanguageEn.php';
		$wgAutoloadClasses['TVLanguageDe'] = $tvgIP . '/languages/TV_LanguageDe.php';
		$wgAutoloadClasses['TVParserFunctions'] = $tvgIP . '/includes/TV_ParserFunctions.php';
		$wgAutoloadClasses['TVFacetedSearchExtension'] = $tvgIP . '/includes/TV_FacetedSearchExtension.php';
		$wgAutoloadClasses['TVNavigationTree'] = $tvgIP . '/includes/TV_NavigationTree.php';
		
		global $wgHooks;
		$wgHooks['ParserFirstCallInit'][] = 'TVParserFunctions::initParserFunctions';
		$wgHooks['LanguageGetMagic'][]    = 'TVParserFunctions::languageGetMagic';
		$wgHooks['MakeGlobalVariablesScript'][] = 'TreeViewExtension::onResourceLoaderGetConfigVars';
		$wgHooks['MakeGlobalVariablesScript'][] = "FSFacetedSearchSpecial::addJavaScriptVariables";
		
		$wgHooks['FacetedSearchExtensionTop'][] = 'TVFacetedSearchExtension::injectTreeViewDefinition';
		$wgHooks['FacetedSearchExtensionBottomMenu'][] = 'TVFacetedSearchExtension::injectBottomMenu';
		$wgHooks['FacetedSearchExtensionAddResources'][] = 'TVFacetedSearchExtension::addResources';
		
		$wgHooks['OntoSkinInsertTreeNavigation'][] = 'TVNavigationTree::showNavigationTree';
		
		self::initContentLanguage($wgLanguageCode);
	
		return true;
			
	}


	/**
	 * Do the actual initialisation of the extension. This is just a delayed init that
	 * makes sure MediaWiki is set up properly before we add our stuff.
	 *
	 * The main things this function does are: register all hooks, set up extension
	 * credits, and init some globals that are not for configuration settings.
	 */
	public static function setupExtension() {
		wfProfileIn('TreeViewExtension::setupExtension');
		global $tvgIP, $wgHooks, $wgExtensionCredits;
	
		//--- Register hooks ---
	
		self::setupScriptAndStyleModule();
	
		//--- credits (see "Special:Version") ---
		$wgExtensionCredits['semantic'][]= array(
	        'name'=>'TreeView',
	        'version'=>TV_TREEVIEW_VERSION,
	        'author'=>"Maintained by [http://smwplus.com ontoprise GmbH].", 
	        'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:TreeView_extension',
	        'description' => 'Generates trees based on semantic properties.');
	
		wfProfileOut('TreeViewExtension::setupExtension');
		return true;
	}
	
	/**
	 * Adds configuration variables to the Resource Loader. They can be accessed in
	 * JavaScript.
	 * 
	 * @param $vars
	 */
	public static function onResourceLoaderGetConfigVars(&$vars) {
		global $tvgTreeThemes;
		$vars['tvgTreeThemes'] = $tvgTreeThemes;
		return true;
	}
		
	/**
	 * Language dependent identifiers in $text that have the format {{identifier}}
	 * are replaced by the language string that corresponds to the identifier.
	 * 
	 * @param string $text
	 * 		Text with language identifiers
	 * @return string
	 * 		Text with replaced language identifiers.
	 */
	public static function replaceLanguageStrings($text) {
		// Find all identifiers
		$numMatches = preg_match_all("/(\{\{(.*?)\}\})/", $text, $identifiers);
		if ($numMatches === 0) {
			return $text;
		}

		// Get all language strings
		$langStrings = array();
		foreach ($identifiers[2] as $id) {
			$langStrings[] = wfMsg($id);
		}
		
		// Replace all language identifiers
		$text = str_replace($identifiers[1], $langStrings, $text);
		return $text;
	}
    
	
	//--- Private methods ---
	
	/**
	 * Creates a module for the resource loader that contains all scripts and styles
	 * that are needed for this extension.
	 */
	private static function setupScriptAndStyleModule() {
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
	            
			'messages' => array('tv_treepf_template'),
	
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
	private static function initContentLanguage($langcode) {
		global $tvgIP, $tvgContLang;
		if (!empty($tvgContLang)) {
			return;
		}
		wfProfileIn('TreeViewExtension::initContentLanguage');
	
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
	
		wfProfileOut('TreeViewExtension::initContentLanguage');
	}
	
}
