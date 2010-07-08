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
	global $wgParser, $lodgContLang;
	
	LODParserFunctions::getInstance();

	$wgParser->setHook($lodgContLang->getParserFunction(LODLanguage::PF_MAPPING), 
	                   array('LODParserFunctions', 'mapping'));
	
//	$wgParser->setFunctionHook('haclaccess', 'HACLParserFunctions::access');

}

function lodfLanguageGetMagic( &$magicWords, $langCode ) {
	global $lodgContLang;
//	$magicWords['lodmapping']
//		= array( 0, $lodgContLang->getParserFunction(LODLanguage::PF_MAPPING));
	return true;
}


/**
 * The class LODParserFunctions contains all parser functions of the LinkedData
 * extension. The following functions are parsed:
 * - mapping (as tag i.e. <mapping>
 *
 * @author Thomas Schweitzer
 *
 */
class LODParserFunctions {

	//--- Constants ---

	//--- Private fields ---

	// LODParserFunctions: The only instance of this class
	private static $mInstance = null;
	
	// bool: <true>, if the first mapping on a page is parsed
	private static $mFirstMapping = true;
	
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
	 * Parses the mapping tag <mapping>
	 *
	 * @param string $text
	 * 		The content of the <mapping> tag
	 * @param array $params
	 * 		Parameters
	 * @param Parser $parser
	 * 		The parser
	 */
	public static function mapping($text, $params, &$parser)  {
		// The mapping function is only allowed in namespace "Mapping".
		$title = $parser->getTitle();
		$ns = $title->getNamespace();
		$msg = "";
		if ($ns != LOD_NS_MAPPING) {
			// Wrong namespace => add a warning
			$msg = wfMsg('lod_mapping_tag_ns');
		} else {
			// If this is the first mapping on the page, all mappings for the page
			// must be deleted.
			$store = LODMappingStore::getStore();
			if (self::$mFirstMapping) {
				$store->removeAllMappings($title->getText());
				self::$mFirstMapping = false;
			}
			
			// Get the "target" parameter
			global $lodgContLang;
			$targetName = $lodgContLang->getParserFunctionParameter(LODLanguage::PFP_MAPPING_TARGET);
			$target = null;
			if (array_key_exists($targetName, $params)) {
				$target = $params[$targetName];
			}
			// Store this mapping.
			$mapping = new LODMapping($text, $title->getText(), $target);
			$success = true;
			try {
				$success = $store->addMapping($mapping);
			} catch (Exception $e) {
				$success = false;
			}
			if (!$success) {
				$msg = wfMsg("lod_saving_mapping_failed.");
			}
			
		}
		return "$msg\n\n<pre>$text</pre>";
	}
	
	/**
	 * This method is called, when an article is deleted. If the article
	 * belongs to the namespace "Mapping", the LOD mappings for the article are
	 * deleted.
	 *
	 * @param Article $article
	 * 		The article that will be deleted.
	 * @param User $user
	 * 		The user who deletes the article.
	 * @param string $reason
	 * 		The reason, why the article is deleted.
	 */
	public static function articleDelete(&$article, &$user, &$reason) {
		if ($article->getTitle()->getNamespace() == LOD_NS_MAPPING) {
			// The article is in the "Mapping" namespace. 
			// => delete the mappings that are defined in the article.
			$source = $article->getTitle()->getText();
			$store = LODMappingStore::getStore();
			$store->removeAllMappings($source);
		}
		return true;
	}
	

	/**
	 * This function checks for articles in the namespace "Mapping", if the 
	 * database contains a corresponding mapping. If not, an error message is 
	 * displayed on the page.
	 *
	 * @param OutputPage $out
	 * @param string $text
	 * @return bool true
	 */
	public static function outputPageBeforeHTML(&$out, &$text) {
		global $wgTitle;
		$title = $wgTitle;
		if (isset($title) && $title->getNamespace() == LOD_NS_MAPPING) {
			$store = LODMappingStore::getStore();
			if (count($store->getAllMappings($title->getText())) == 0) {
				$msg = wfMsg("lod_no_mapping_in_ns");
				$out->addHTML("<div><b>$msg</b></div>");
			}
		}
		return true;
	}
	
	/**
	 * Resets the singleton instance of this class. Normally this instance is
	 * only used for parsing ONE article. If several articles are parsed in
	 * one invokation of the wiki system, this singleton has to be reset (e.g.
	 * for unit tests).
	 *
	 */
	public function reset() {
		self::$mFirstMapping = true;
	}


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
