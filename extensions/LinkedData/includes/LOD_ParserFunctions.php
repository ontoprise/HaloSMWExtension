<?php
/**
 * @file
 * @ingroup LinkedData
 */
/*  Copyright 2010, ontoprise GmbH
 *  This file is part of the HaloACL-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file contains the implementation of parser functions for the LinkedData
 * extension.
 *
 * @author Thomas Schweitzer
 * Date: 12.04.2010
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

//--- Includes ---
global $lodgIP;
//require_once("$lodgIP/...");
$wgExtensionFunctions[] = 'lodfInitParserfunctions';

$wgHooks['LanguageGetMagic'][] = 'lodfLanguageGetMagic';


function lodfInitParserfunctions() {
	global $wgParser;

//	LODParserFunctions::getInstance();

//	$wgParser->setFunctionHook('haclaccess', 'HACLParserFunctions::access');

}

function lodfLanguageGetMagic( &$magicWords, $langCode ) {
//	global $lodgContLang;
//	$magicWords['haclaccess']
//		= array( 0, $lodgContLang->getParserFunction(LODLanguage::PF_ACCESS));
	return true;
}


/**
 * The class LODParserFunctions contains all parser functions of the LinkedData
 * extension. The following functions are parsed:
 * - ...
 *
 * @author Thomas Schweitzer
 *
 */
class LODParserFunctions {

	//--- Constants ---

	//--- Private fields ---

	// LODParserFunctions: The only instance of this class
	private static $mInstance = null;


	/**
	 * Constructor for HACLParserFunctions. This object is a singleton.
	 */
	public function __construct() {
	}


	//--- Public methods ---

	public static function getInstance() {
		if (is_null(self::$mInstance)) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}

	/**
	 * Resets the singleton instance of this class. Normally this instance is
	 * only used for parsing ONE article. If several articles are parsed in
	 * one invokation of the wiki system, this singleton has to be reset (e.g.
	 * for unit tests).
	 *
	 */
	public function reset() {
	}


//	/**
//	 * Callback for parser function "#access:".
//	 * This parser function defines an access control entry (ACE) in form of an
//	 * inline right definition. It can appear several times in an article and
//	 * has the following parameters:
//	 * assigned to: This is a comma separated list of user groups and users whose
//	 *              access rights are defined. The special value stands for all
//	 *              anonymous users. The special value user stands for all
//	 *              registered users.
//	 * actions: This is the comma separated list of actions that are permitted.
//	 *          The allowed values are read, edit, formedit, create, move,
//	 *          annotate and delete. The special value comprises all of these actions.
//	 * description:This description in prose explains the meaning of this ACE.
//	 * name: (optional) A short name for this inline right
//	 *
//	 * @param Parser $parser
//	 * 		The parser object
//	 *
//	 * @return string
//	 * 		Wikitext
//	 *
//	 * @throws
//	 * 		HACLException(HACLException::INTERNAL_ERROR)
//	 * 			... if the parser function is called for different articles
//	 */
//	public static function access(&$parser) {
//		$params = self::$mInstance->getParameters(func_get_args());
//		$fingerprint = self::$mInstance->makeFingerprint("access", $params);
//		$title = $parser->getTitle();
//		if (self::$mInstance->mTitle == null) {
//			self::$mInstance->mTitle = $title;
//		} else if ($title->getArticleID() != self::$mInstance->mTitle->getArticleID()) {
//			throw new HACLException(HACLException::INTERNAL_ERROR,
//                "The parser functions are called for different articles.");
//		}
//
//		// handle the parameter "assigned to".
//		list($users, $groups, $em1, $warnings) = self::$mInstance->assignedTo($params);
//
//		// handle the parameter 'action'
//		list($actions, $em2) = self::$mInstance->actions($params);
//
//		// handle the (optional) parameter 'description'
//		global $haclgContLang;
//		$descPN = $haclgContLang->getParserFunctionParameter(HACLLanguage::PFP_DESCRIPTION);
//		$description = array_key_exists($descPN, $params)
//						? $params[$descPN]
//						: "";
//		// handle the (optional) parameter 'name'
//		$namePN = $haclgContLang->getParserFunctionParameter(HACLLanguage::PFP_NAME);
//		$name = array_key_exists($namePN, $params)
//					? $params[$namePN]
//					: "";
//
//		$errMsgs = $em1 + $em2;
//
//		if (count($errMsgs) == 0) {
//			// no errors
//			// => create and store the new right for later use.
//			if (!in_array($fingerprint, self::$mInstance->mFingerprints)) {
//				$ir = new HACLRight(self::$mInstance->actionNamesToIDs($actions), $groups, $users, $description, $name);
//				self::$mInstance->mInlineRights[] = $ir;
//				self::$mInstance->mFingerprints[] = $fingerprint;
//			}
//		} else {
//			self::$mInstance->mDefinitionValid = false;
//		}
//
//		// Format the defined right in Wikitext
//		if (!empty($name)) {
//			$text = wfMsgForContent('hacl_pf_rightname_title', $name)
//			.wfMsgForContent('hacl_pf_rights', implode(' ,', $actions));
//		} else {
//			$text = wfMsgForContent('hacl_pf_rights_title', implode(' ,', $actions));
//		}
//		$text .= self::$mInstance->showAssignees($users, $groups);
//		$text .= self::$mInstance->showDescription($description);
//		$text .= self::$mInstance->showErrors($errMsgs);
//		$text .= self::$mInstance->showWarnings($warnings);
//		
//		return $text;
//
//	}



	//--- Private methods ---
	
	/**
	 * Returns the parser function parameters that were passed to the parser-function
	 * callback.
	 *
	 * @param array(mixed) $args
	 * 		Arguments of a parser function callback
	 * @return array(string=>string)
	 * 		Array of argument names and their values.
	 */
	private function getParameters($args) {
		$parameters = array();

		foreach ($args as $arg) {
			if (!is_string($arg)) {
				continue;
			}
			if (preg_match('/^\s*(.*?)\s*=\s*(.*?)\s*$/', $arg, $p) == 1) {
				$parameters[strtolower($p[1])] = $p[2];
			}
		}

		return $parameters;
	}


}
