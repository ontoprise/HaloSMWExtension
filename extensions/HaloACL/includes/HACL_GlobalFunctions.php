<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains global functions that are called from the Halo-Access-Control-List
 * extension.
 * 
 * @author Thomas Schweitzer
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

/**
 * Switch on Halo Access Control Lists. This function must be called in 
 * LocalSettings.php after HACL_Initialize.php was included and default values
 * that are defined there have been modified.
 * For readability, this is the only global function that does not adhere to the
 * naming conventions.
 *
 * This function installs the extension, sets up all autoloading, special pages
 * etc.
 */
function enableHaloACL() {
	global $haclgIP, $wgExtensionFunctions, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups, $wgHooks, $wgExtensionMessagesFiles, $wgJobClasses, $wgExtensionAliasesFiles;

	$wgExtensionFunctions[] = 'haclfSetupExtension';
	$wgHooks['LanguageGetMagic'][] = 'haclfAddMagicWords'; // setup names for parser functions (needed here)
	$wgExtensionMessagesFiles['HaloACL'] = $haclgIP . '/languages/HACL_Messages.php'; // register messages (requires MW=>1.11)

	// Register special pages aliases file
	$wgExtensionAliasesFiles['HaloACL'] = $haclgIP . '/languages/HACL_Aliases.php';

	///// Set up autoloading; essentially all classes should be autoloaded!
	$wgAutoloadClasses['HACLEvaluator'] = $haclgIP . '/includes/HACL_Evaluator.php';
	$wgAutoloadClasses['HaloACLSpecial'] = $haclgIP . '/specials/HACL_ACLSpecial.php';
	$wgAutoloadClasses['HACLStorage'] = $haclgIP . '/includes/HACL_Storage.php';
	$wgAutoloadClasses['HACLGroup'] = $haclgIP . '/includes/HACL_Group.php';
		
	//--- Autoloading for exception classes ---
	$wgAutoloadClasses['HACLException']        = $haclgIP . '/exceptions/HACL_Exception.php';
	$wgAutoloadClasses['HACLStorageException'] = $haclgIP . '/exceptions/HACL_StorageException.php';
	$wgAutoloadClasses['HACLGroupException']   = $haclgIP . '/exceptions/HACL_GroupException.php';
	
	
	return true;
}

/**
 * Do the actual initialisation of the extension. This is just a delayed init that
 * makes sure MediaWiki is set up properly before we add our stuff.
 *
 * The main things this function does are: register all hooks, set up extension 
 * credits, and init some globals that are not for configuration settings.
 */
function haclfSetupExtension() {
	wfProfileIn('haclfSetupExtension');
	global $haclgIP, $wgHooks, $wgParser, $wgExtensionCredits, 
	       $wgLanguageCode, $wgVersion;

	//--- Register hooks ---
	global $wgHooks;
	$wgHooks['userCan'][] = 'HACLEvaluator::userCan';

	wfLoadExtensionMessages('HaloACL');
	///// Register specials pages
	global $wgSpecialPages, $wgSpecialPageGroups;
	$wgSpecialPages['HaloACL']      = array('HaloACLSpecial');
	$wgSpecialPageGroups['HaloACL'] = 'hacl_group';

	
#	$wgHooks['InternalParseBeforeLinks'][] = 'SMWParserExtensions::onInternalParseBeforeLinks'; // parse annotations in [[link syntax]]

/*
	if( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
		$wgHooks['ParserFirstCallInit'][] = 'SMWParserExtensions::registerParserFunctions';
	} else {
		if ( class_exists( 'StubObject' ) && !StubObject::isRealObject( $wgParser ) ) {
			$wgParser->_unstub();
		}
		SMWParserExtensions::registerParserFunctions( $wgParser );
	}
*/
	       
	//--- credits (see "Special:Version") ---
	$wgExtensionCredits['other'][]= array(
		'name'=>'HaloACL', 
		'version'=>HACL_HALOACL_VERSION, 
		'author'=>"Thomas Schweitzer", 
		'url'=>'http://smwforum.ontoprise.de', 
		'description' => 'Protect the content of your wiki.');

	wfProfileOut('haclfSetupExtension');
	return true;
}

/**********************************************/
/***** namespace settings                 *****/
/**********************************************/

/**
 * Init the additional namespaces used by HaloACL. The
 * parameter denotes the least unused even namespace ID that is
 * greater or equal to 100.
 */
function haclfInitNamespaces() {
	global $haclgNamespaceIndex, $wgExtraNamespaces, $wgNamespaceAliases, 
	       $wgNamespacesWithSubpages, $wgLanguageCode, $haclgContLang;

	if (!isset($haclgNamespaceIndex)) {
		$haclgNamespaceIndex = 300;
	}

	define('HACL_NS_ACL',       $haclgNamespaceIndex);
	define('HACL_NS_ACL_TALK',  $haclgNamespaceIndex+1);

	haclfInitContentLanguage($wgLanguageCode);

	// Register namespace identifiers
	if (!is_array($wgExtraNamespaces)) { 
		$wgExtraNamespaces=array(); 
	}
	$namespaces = $haclgContLang->getNamespaces();
	$namespacealiases = $haclgContLang->getNamespaceAliases();
	$wgExtraNamespaces = $wgExtraNamespaces + $namespaces;
	$wgNamespaceAliases = $wgNamespaceAliases + $namespacealiases;

	// Support subpages for the namespace ACL
	$wgNamespacesWithSubpages = $wgNamespacesWithSubpages + array(
		HACL_NS_ACL => true,
		HACL_NS_ACL_TALK => true
	);
}


/**********************************************/
/***** language settings                  *****/
/**********************************************/

/**
 * Set up (possibly localised) names for HaloACL
 */
function haclfAddMagicWords(&$magicWords, $langCode) {
//	$magicWords['ask']     = array( 0, 'ask' );
	return true;
}

/**
 * Initialise a global language object for content language. This
 * must happen early on, even before user language is known, to
 * determine labels for additional namespaces. In contrast, messages
 * can be initialised much later when they are actually needed.
 */
function haclfInitContentLanguage($langcode) {
	global $haclgIP, $haclgContLang;
	if (!empty($haclgContLang)) { 
		return; 
	}
	wfProfileIn('haclfInitContentLanguage');

	$haclContLangFile = 'HACL_Language' . str_replace( '-', '_', ucfirst( $langcode ) );
	$haclContLangClass = 'HACLLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );
	if (file_exists($haclgIP . '/languages/'. $haclContLangFile . '.php')) {
		include_once( $haclgIP . '/languages/'. $haclContLangFile . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($haclContLangClass)) {
		include_once($haclgIP . '/languages/HACL_LanguageEn.php');
		$haclContLangClass = 'HACLLanguageEn';
	}
	$haclgContLang = new $haclContLangClass();

	wfProfileOut('haclfInitContentLanguage');
}


