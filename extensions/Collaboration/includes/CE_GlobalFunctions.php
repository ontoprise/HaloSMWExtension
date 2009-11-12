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
 * This file contains global functions that are called from the Collaboration extension.
 *
 * @author Benjamin Langguth
 *
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
	global $wgExtensionFunctions, $cegEnableCollaboration, $cegIP;
	
	$wgExtensionFunctions[] = 'cefSetupExtension';
	#$wgHooks['LanguageGetMagic'][] = 'cefAddMagicWords'; // setup names for parser functions (needed here)
	
	$wgExtensionMessagesFiles['Collaboration'] = $cegIP . '/languages/CE_Messages.php'; // register messages (requires MW=>1.11)
	
	///// Set up autoloading; essentially all classes should be autoloaded!
	$wgAutoloadClasses['CEComment'] = $cegIP . '/specials/Comment/CE_Comment.php';
	$wgAutoloadClasses['CECommentAjax'] = $cegIP . '/specials/Comment/CE_CommentAjaxAccess.php';
	#$wgAutoloadClasses['CEamarsch'] = $cegIP. '/specials/Comment/CE_CommentDisplayParserFunction.php';
	
	//--- Autoloading for exception classes ---
	#$wgAutoloadClasses['CEException']        = $cegIP . '/exceptions/CE_Exception.php';
	
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
		$wgLanguageCode, $wgVersion, $wgRequest, $wgContLang;
	
	//--- Register hooks ---
	#global $wgHooks;
	#$wgHooks['userCan'][] = 'HACLEvaluator::userCan';
	
	#wfLoadExtensionMessages('Collaboration');
	
	//TODO: Language files
	
		
	#$wgExtensionFunctions[] = 'smwfSetupCEExtension';
	
	global $wgExtensionCredits;
	
	$wgExtensionCredits['other'][]= array(
		'name'=>'Collaboration',
		'version'=>CE_VERSION,
		'author'=>"Benjamin Langguth and others",
		'url'=>'http://smwforum.ontoprise.de',
		'description' => 'Some fancy collaboration tools.'
	);

	// Register autocompletion icon
	$wgHooks['smwhACNamespaceMappings'][] = 'cefRegisterACIcon';	
	
	global $cegEnableComment, $cegEnableCurrentUsers;
	
	# A: Comment
	if ( $cegEnableComment ) {

		#put some more here? NO, we need the classes otherwise, too.

		$cegCommentNamespace = array(CE_NS_COMMENT);
		//TODO: THat's buggy!:'
		// Class not found error!
		#$wgHooks['BeforePageDisplay'][] = 'CEComment::cefAddHTMLHeader';
	}
	
	# B: CurrentUser
	if ( $cegEnableCurrentUsers ) {
		include_once($cegIP.'/specials/CurrentUsers/CE_CurrentUsers.php');
	}

	 wfProfileOut('cefSetupExtension');
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
	global $cegNamespaceIndex, $wgExtraNamespaces, $wgNamespaceAliases,
	$wgNamespacesWithSubpages, $wgLanguageCode, $cegContLang;
	
	if (!isset($cegNamespaceIndex)) {
		$cegNamespaceIndex = 400;
	}
	
	define('CE_NS_COMMENT', $cegNamespaceIndex);
	define('CE_NS_COMMENT_TALK', $cegNamespaceIndex+1);
	
	cefInitContentLanguage($wgLanguageCode);
	
	// Register namespace identifiers
	if (!is_array($wgExtraNamespaces)) {
		$wgExtraNamespaces = array();
	}
	$ceNamespaces = $cegContLang->getNamespaces();
	$ceNamespacealiases = $cegContLang->getNamespaceAliases();
	$wgExtraNamespaces = $wgExtraNamespaces + $ceNamespaces;
	$wgNamespaceAliases = $wgNamespaceAliases + $ceNamespacealiases;
	
	// no support for subpages for these namespaces
}

/**
 * Internationalized messages
 */
function cefInitMessages() {
	global $cegMessagesInitialized;
	if (isset($cegMessagesInitialized)) return; // prevent double init

	cefInitUserMessages(); // lazy init for ajax calls

	$cegMessagesInitialized = true;
}

/**
 * Registers Collaboration Content messages.
 */
function cefInitContentLanguage($langcode) {
	global $cegIP, $cegContLang;
	if (!empty($cegContLang)) { return; }

	$cegContLangClass = 'CELanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($cegIP . '/languages/'. $cegContLangClass . '.php')) {
		include_once( $cegIP . '/languages/'. $cegContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($cegContLangClass)) {
		include_once($cegIP . '/languages/CELanguageEn.php');
		$cegContLangClass = 'SMW_CELanguageEn';
	}
	$cegContLang = new $cegContLangClass();
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
}

/**
 * Add appropriate JS language script
 */
function cefAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
	global $cegIP, $cegScriptPath, $wgUser;
	
	 // content language file
	$jsm->addScript('<script type="text/javascript" src="'.$cegScriptPath . '/scripts/Language/CE_Language.js'.  '"></script>', $mode, $namespace, $pages);
	$lng = '/scripts/Language/CE_Language';
	
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($cegScriptPath . $lng)) {
			$jsm->addScriptIf($cegScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($cegScriptPath . '/scripts/Language/CE_LanguageEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($cegScriptPath . '/scripts/Language/CE_LanguageEn.js', $mode, $namespace, $pages);
	}

	// user language file
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($cegScriptPath . $lng)) {
			$jsm->addScriptIf($cegScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($cegScriptPath . '/scripts/Language/CE_LanguageUserEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($cegScriptPath . '/scripts/Language/CE_LanguageUserEn.js', $mode, $namespace, $pages);
	}
}

function cefRegisterACIcon(& $namespaceMappings) {
	global $cegIP;
	$namespaceMappings[CE_COMMENT_NS] = $cegIP . '/skins/images/CE_Comment_AutoCompletion.gif';
	return true;
}

#$wgHooks['ParserBeforeStrip'][] = 'ArticleComment::addCommentTag';
#-> into DislpayParserFunction