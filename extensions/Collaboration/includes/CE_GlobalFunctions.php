<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Collaboration-Extension.
 *
 *   The Collaboration-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Collaboration-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup Collaboration
 * 
 * This file contains global functions that are called from the Collaboration extension.
 *
 * @author Benjamin Langguth
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Collaboration extension. It is not a valid entry point.\n" );
}

/**
 * Switch on Collaboration features. This function must be called in
 * LocalSettings.php after CE_Initialize.php was included and default values
 * that are defined there have been modified.
 * For readability, this is the only global function that does not adhere to the
 * naming conventions.
 *
 * This function installs the extension, sets up all autoloading, special pages
 * etc.
 */
function enableCollaboration() {
	global $cegIP, $cegEnableCollaboration,  $cegEnableComment,
	$wgExtensionMessagesFiles, $wgExtensionAliasesFiles, $wgExtensionFunctions, $wgAutoloadClasses, $wgHooks;

	#global $wgSpecialPages, $wgSpecialPageGroups;

	require_once($cegIP . '/specials/Comment/CE_CommentParserFunctions.php');

	$wgExtensionFunctions[] = 'cefSetupExtension';
	$wgHooks['LanguageGetMagic'][] = 'cefAddMagicWords'; // setup names for parser functions (needed here)
	$wgExtensionMessagesFiles['Collaboration'] = $cegIP . '/languages/CE_Messages.php'; // register messages (requires MW=>1.11)


	///// Set up autoloading; essentially all classes should be autoloaded!
	#$wgAutoloadClasses['CECommentDBHelper'] = $cegIP . '/storage/CE_CommentDBHelper.php';

	//--- Comment classes ---
	//$wgAutoloadClasses['CECommentStorage'] = $cegIP . '/specials/Comment/CE_CommentStorage.php';
	$wgAutoloadClasses['CEComment'] = $cegIP . '/specials/Comment/CE_Comment.php';
	$wgAutoloadClasses['CECommentUtils'] = $cegIP . '/specials/Comment/CE_CommentUtils.php';

	//--- Autoloading for exception classes ---
	$wgAutoloadClasses['CEException'] = $cegIP . '/exeptions/CE_Exception.php';
	//$wgAutoloadClasses['CEQueryException'] = $cegIP . '/exeptions/CE_QueryException.php';
	//$wgAutoloadClasses['CEStorageException'] = $cegIP . '/exeptions/CE_StorageException.php';

	require_once($cegIP . '/specials/Comment/CE_CommentAjaxAccess.php');
	//so that other extensions know about the Collaboration-Extension
	$cegEnableCollaboration = true;

	return true;
}

/**
 * Do the actual initialisation of the extension. This is just a delayed init that
 * makes sure MediaWiki is set up properly before we add our stuff.
 *
 * The main things this function does are: register all hooks, set up extension
 * credits, and init some globals that are not for configuration settings.
 */
function cefSetupExtension() {
	wfProfileIn('cefSetupExtension');
	global $cegIP, $wgHooks, $wgParser, $wgExtensionCredits,
	$wgLanguageCode, $wgVersion, $wgRequest, $wgContLang,
	$cegEnableComment, $cegEnableCurrentUsers;

	//--- Register hooks ---
	#global $wgHooks;

	wfLoadExtensionMessages('Collaboration');

	//TODO: Language files

	$spns_text = $wgContLang->getNsText(NS_SPECIAL);
	// register AddHTMLHeader functions for special pages
	// to include javascript and css files (only on special page requests).

	if (stripos($wgRequest->getRequestURL(), $spns_text.":Collaboration") !== false
	|| stripos($wgRequest->getRequestURL(), $spns_text."%3ACollaboration") !== false) {
		$wgHooks['BeforePageDisplay'][]='cefAddSpecialPageHeader';
	}else {
		$wgHooks['BeforePageDisplay'][]='cefAddNonSpecialPageHeader';
	}

	/*# B: CurrentUser
	 if ( $cegEnableCurrentUsers ) {
		include_once($cegIP.'/specials/CurrentUsers/CE_CurrentUsers.php');
		}*/

	### credits (see Special:Version) ###
	$wgExtensionCredits['other'][]= array(
		'name' => 'Collaboration',
		'version' => CE_VERSION,
		'author' => "Benjamin Langguth and others",
		'url' => 'http://smwforum.ontoprise.de',
		'description' => 'Some fancy collaboration tools.'
		);


	### Register autocompletion icon ###
	$wgHooks['smwhACNamespaceMappings'][] = 'cefRegisterACIcon';
	wfProfileOut('cefSetupExtension');

	return true;
}

/**
 * Adding headers for non-special-pages
 * Currently only used by comments
 *
 * @param OutputPage $out
 * @return bool: true
 */
function cefAddNonSpecialPageHeader(&$out) {
	global $cegScriptPath;

	#cefAddJSLanguageScripts($out);
	if (!defined('SMW_HALO_VERSION')) {
	   $out->addScript("<script type=\"text/javascript\" src=\"". $cegScriptPath .  "/scripts/prototype.js\"></script>");
	}

	$out->addScript("<script type=\"text/javascript\" src=\"". $cegScriptPath .  "/scripts/Comment/CE_Comment.js\"></script>");

	//TODO:script.acoul.us

	$out->addLink(array(
		'rel'   => 'stylesheet',
		'type'  => 'text/css',
		'media' => 'screen, projection',
		'href'  => $cegScriptPath. '/skins/Comment/CE_Comment.css'
	));

return true;
}

/**
 * Adding headers for special-pages
 * Currently not used by comments
 *
 * @param OutputPage $out
 * @return bool: true
 */
function cefAddSpecialPageHeader(&$out) {
	return true;
}

/*********************************/
/***** namespace settings *****/
/*********************************/

/**
 * Init the additional namespaces used by Collaboration. The
 * parameter denotes the least unused even namespace ID that is
 * greater or equal to 100.
 */
function cefInitNamespaces() {
	global $cegCommentNamespaceIndex, $wgExtraNamespaces, $wgNamespaceAliases,
	$wgNamespacesWithSubpages, $wgLanguageCode, $cegContLang;

	if (!isset($cegCommentNamespaceIndex)) {
		$cegCommentNamespaceIndex = 700;
	}

	define('CE_COMMENT_NS', $cegCommentNamespaceIndex);
	define('CE_COMMENT_NS_TALK', $cegCommentNamespaceIndex+1);
	
	cefInitContentLanguage($wgLanguageCode);

	// Register namespace identifiers
	if (!is_array($wgExtraNamespaces)) {
		$wgExtraNamespaces = array();
	}

	$ceNamespaces = $cegContLang->getNamespaces();
	$ceNamespacealiases = $cegContLang->getNamespaceAliases();
	$wgExtraNamespaces = $wgExtraNamespaces + $ceNamespaces;
	$wgNamespaceAliases = $wgNamespaceAliases + $ceNamespacealiases;

	// make the NS semantic
	global $smwgNamespacesWithSemanticLinks;
	$smwgNamespacesWithSemanticLinks[CE_COMMENT_NS] = true;

	return true;
}

/**
 * Internationalized messages
 */

/**
 * Set up (possibly localised) names for HaloACL
 */
function cefAddMagicWords(&$magicWords, $langCode) {
	#$magicWords['showcomments']     = array( 0, 'showcomments' );
	#$magicWords['showcommentform']     = array( 0, 'showcommentform' );
	return true;
}

/**
 * Initialise a global language object for content language. This
 * must happen early on, even before user language is known, to
 * determine labels for additional namespaces. In contrast, messages
 * can be initialised much later when they are actually needed.
 */
function cefInitContentLanguage($langcode) {
	global $cegIP, $cegContLang;
	if (!empty($cegContLang)) {
		return;
	}
	wfProfileIn('cefInitContentLanguage');

	$ceContLangFile = 'CELanguage' . str_replace( '-', '_', ucfirst( $langcode ) );
	$ceContLangClass = 'CELanguage' . str_replace( '-', '_', ucfirst( $langcode ) );
	if (file_exists($cegIP . '/languages/'. $ceContLangFile . '.php')) {
		include_once( $cegIP . '/languages/'. $ceContLangFile . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($ceContLangClass) ) {
		include_once($cegIP . '/languages/CELanguageEn.php');
		$ceContLangClass = 'CELanguageEn';
	}
	$cegContLang = new $ceContLangClass();

	wfProfileOut('cefInitContentLanguage');

	return true;
}

function cefInitMessages() {
	global $cegMessagesInitialized;
	if (isset($cegMessagesInitialized)) return; // prevent double init

	cefInitUserMessages(); // lazy init for ajax calls

	$cegMessagesInitialized = true;

	return true;
}

/**
 * Registers Collaboration extension User messages.
 */
function cefInitUserMessages() {
	global $wgMessageCache, $cegContLang, $wgLanguageCode;
	cefInitContentLanguage($wgLanguageCode);

	global $cegIP, $cegLang;
	if (!empty($cegLang)) { return; }
	global $wgMessageCache, $wgLang;
	$cegLangClass = 'CELanguage' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($cegIP . '/languages/'. $cegLangClass . '.php')) {
		include_once( $cegIP . '/languages/'. $cegLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($cegLangClass)) {
		global $cegContLang;
		$cegLang = $cegContLang;
	} else {
		$cegLang = new $cegLangClass();
	}

	$wgMessageCache->addMessages($cegLang->getUserMsgArray(), $wgLang->getCode());

	return true;
}

/**
 * Add appropriate JS language script
 */
function cefAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
	global $cegIP, $cegScriptPath, $wgUser;

	// content language file
	$jsm->addScript('<script type="text/javascript" src="' . $cegScriptPath .
		'/scripts/Language/CE_Language.js' . '"></script>', $mode, $namespace, $pages);
	$lng = '/scripts/Language/CE_Language';

	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($cegScriptPath . $lng)) {
			$jsm->addScript($cegScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScript($cegScriptPath . '/scripts/Language/CE_LanguageEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScript($cegScriptPath . '/scripts/Language/CE_LanguageEn.js', $mode, $namespace, $pages);
	}

	// user language file
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($cegScriptPath . $lng)) {
			$jsm->addScript($cegScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScript($cegScriptPath . '/scripts/Language/CE_LanguageUserEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScript($cegScriptPath . '/scripts/Language/CE_LanguageUserEn.js', $mode, $namespace, $pages);
	}

	return true;
}

/**
 * Registers the autocompletion icons of the Comment namespace for the SMWHaloAutocompletion.
 * 
 * @param array $namespaceMappings
 * @return bool
 */
function cefRegisterACIcon( &$namespaceMappings) {
	global $cegScriptPath;
	$namespaceMappings[CE_COMMENT_NS] = $cegScriptPath . '/skins/Comment/icons/smw_plus_comment_icon_16x16.gif';
	return true;
}