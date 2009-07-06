<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *
 * @author Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

###
# If you already have custom namespaces on your site, insert
# $smwgTINamespaceIndex = ???;
# into your LocalSettings.php *before* including this file.
# The number ??? must be the smallest even namespace number
# that is not in use yet. However, it must not be smaller
# than 100. Semantic MediaWiki normally uses namespace numbers from 100 upwards.
##

// Register additional namespaces
if (!isset($smwgTINamespaceIndex)) {
	TermImportManager::initTINamespaces(202);
} else {
	TermImportManager::initTINamespaces();
}

global $wgLanguageCode, $smwgNamespacesWithSemanticLinks;
$smwgNamespacesWithSemanticLinks[SMW_NS_TERM_IMPORT] = true;

/**
 * This class does some initialisation for the Term Import framework
 *
 */

class TermImportManager {

	static function showTermImportPage(&$title, &$article) {
		global $smwgDIIP, $wgNamespaceAliases;
		if ($title->getNamespace() == SMW_NS_TERM_IMPORT) {
			require_once("$smwgDIIP/specials/TermImport/SMW_TermImportPage.php");
			$article = new SMWTermImportPage($title);
		}
		return true;
	}

	/**
	 * Initializes the namespaces that are used by the Term Import framework
	 * Normally the base index starts at 202. It must be an even number greater than
	 * than 100. However, by default Semantic MediaWiki uses the namespace indexes
	 * from 100 upwards.
	 *
	 * @param int $baseIndex
	 * 		Optional base index for all Term Import namespaces. The default is 202.
	 */
	static function initTINamespaces($baseIndex = 200) {
		global $smwgTINamespaceIndex;
		if (!isset($smwgTINamespaceIndex)) {
			$smwgTINamespaceIndex = $baseIndex;
		}

		if (!defined('SMW_NS_TERM_IMPORT')) {
			define('SMW_NS_TERM_IMPORT',       $smwgTINamespaceIndex);
			define('SMW_NS_TERM_IMPORT_TALK',  $smwgTINamespaceIndex+1);
		}
	}

	/**
	 * Initialized the Term Import framework
	 */
	static function initTermImportFramework() {
		global $wgRequest, $wgHooks, $wgParser;
		$action = $wgRequest->getVal('action');

		if ($action == 'ajax') {
			// Do not install the extension for ajax calls
			return;
		}
			
		$wgHooks['ArticleFromTitle'][] = 'TermImportManager::showTermImportPage';
		$wgParser->setHook('ImportSettings', 'termImportParserHook');
	}
}

/**
 * This function is called, when a <ImportSettings>-tag for a Term Import
 * has been found in an article. 
 * 		
 */
function termImportParserHook($input, $args, $parser) {
	$attr = "";
	foreach ($args as $k => $v) {
		$attr .= " ". $k . '="' . $v . '"';
	}
	$completeImportSettings = "<ImportSettings$attr>".$input."</ImportSettings>\n";
	
	return  "<pre>\n".htmlspecialchars($completeImportSettings)."</pre>";
}

?>