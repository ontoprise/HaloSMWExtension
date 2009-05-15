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
	$wgAutoloadClasses['HACLSecurityDescriptor'] = $haclgIP . '/includes/HACL_SecurityDescriptor.php';
	$wgAutoloadClasses['HACLRight'] = $haclgIP . '/includes/HACL_Right.php';
	
	//--- Autoloading for exception classes ---
	$wgAutoloadClasses['HACLException']        = $haclgIP . '/exceptions/HACL_Exception.php';
	$wgAutoloadClasses['HACLStorageException'] = $haclgIP . '/exceptions/HACL_StorageException.php';
	$wgAutoloadClasses['HACLGroupException']   = $haclgIP . '/exceptions/HACL_GroupException.php';
	$wgAutoloadClasses['HACLSDException']      = $haclgIP . '/exceptions/HACL_SDException.php';
	$wgAutoloadClasses['HACLRightException']   = $haclgIP . '/exceptions/HACL_RightException.php';
	
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
	       $wgLanguageCode, $wgVersion, $wgRequest, $wgContLang;

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

	$spns_text = $wgContLang->getNsText(NS_SPECIAL);
	// register AddHTMLHeader functions for special pages
	// to include javascript and css files (only on special page requests).
	if (stripos($wgRequest->getRequestURL(), $spns_text.":HaloACL") !== false
			|| stripos($wgRequest->getRequestURL(), $spns_text."%3AHaloACL") !== false) {
		$wgHooks['BeforePageDisplay'][]='haclAddHTMLHeader';
	}
	
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

/**
 * Adds Javascript and CSS files
 *
 * @param OutputPage $out
 * @return true
 */
function haclAddHTMLHeader(&$out){
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	global $haclgHaloScriptPath;

	$out->addScript("<script type=\"text/javascript\" src=\"". $haclgHaloScriptPath .  "/scripts/prototype.js\"></script>");
	$out->addScript("<script type=\"text/javascript\" src=\"". $haclgHaloScriptPath .  "/scripts/haloacl.js\"></script>");
	$out->addScript("<script type=\"text/javascript\" src=\"". $haclgHaloScriptPath .  "/scripts/scriptaculous.js\"></script>");
	$out->addScript("<script type=\"text/javascript\" src=\"". $haclgHaloScriptPath .  "/scripts/effects.js\"></script>");
	//$out->addScript("<script type=\"text/javascript\" src=\"". $haclgHaloScriptPath .  "/scripts/builders.js\"></script>");
	//$out->addScript("<script type=\"text/javascript\" src=\"". $haclgHaloScriptPath .  "/scripts/controls.js\"></script>");
	//$out->addScript("<script type=\"text/javascript\" src=\"". $haclgHaloScriptPath .  "/scripts/dragdrop.js\"></script>");
	//$out->addScript("<script type=\"text/javascript\" src=\"". $haclgHaloScriptPath .  "/scripts/slider.js\"></script>");
	
	$out->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => $haclgHaloScriptPath . '/skins/haloacl.css'
                    ));

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

/**
 * Returns the ID and name of the given user.
 *
 * @param User/string/int $user
 * 		User-object, name of a user or ID of a user. If <null> or empty, the
 *      currently logged in user is assumed.
 * @return array(int,string)
 * 		(Database-)ID of the given user and his name. For the sake of 
 *      performance the name is not retrieved, if the ID of the user is
 * 		passed in parameter $user.
 * @throws 
 * 		HACLException(HACLException::UNKOWN_USER)
 * 			...if the user does not exist.
 */
function haclfGetUserID($user) {
	$userID = 0;
	$userName = '';
	if (is_null($user) || empty($user)) {
		// no user given 
		// => the current user's ID is requested
		global $wgUser; 
		$userID = $wgUser->getId();
		$userName = $wgUser->getName();
	} else if (is_int($user) || is_numeric($user)) {
		// user-id given
		$userID = (int) $user;
	} else if (is_string($user)) {
		// name of user given
		$u = User::newFromName($user);
		$userID = $u->getId();
		$userName = $user;
	} else if (is_a($user, 'User')) {
		// User-object given
		$userID = $user->getId();
		$userName = $user->getName();
	}
	
	if ($userID === 0) {
		// invalid user
		throw new HACLException(HACLException::UNKOWN_USER,'"'.$user.'"');
	}
	
	return array($userID, $userName);
	
}

