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
 */

/**
 * @file
 * @ingroup DITermImport
 * 
 * @author Ingo Steinbauer
 */

/**
 * This group contains all parts of the Data Import extension that deal with term imports.
 * @defgroup DITermImport
 * @ingroup DataImport
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


/**
 * This class does some initialisation for the Term Import framework
 *
 */

class TermImportManager {

	//	static function showTermImportPage(&$title, &$article) {
	//		global $smwgDIIP, $wgNamespaceAliases;
	//		if ($title->getNamespace() == SMW_NS_TERM_IMPORT) {
	//			require_once("$smwgDIIP/specials/TermImport/SMW_TermImportPage.php");
	//			$article = new SMWTermImportPage($title);
	//		}
	//		return true;
	//	}

	/**
	 * Initialized the Term Import framework
	 */
	static function initTermImportFramework() {
		global $wgRequest, $wgHooks, $wgParser;
		$action = $wgRequest->getVal('action');

		if ($action == 'ajax') {
			//return;
		}
			
		//$wgHooks['ArticleFromTitle'][] = 'TermImportManager::showTermImportPage';
		$wgParser->setHook('ImportSettings', 'termImportParserHook');
	}
}

/**
 * This function is called, when a <ImportSettings>-tag for a Term Import
 * has been found in an article.
 *
 */
function termImportParserHook($input, $args, $parser) {
	require_once("SMW_TermImportDefinitionValidator.php");

	$attr = "";
	foreach ($args as $k => $v) {
		$attr .= " ". $k . '="' . $v . '"';
	}
	$completeImportSettings = "<ImportSettings$attr>".$input."</ImportSettings>\n";

	$messages = "";
	$tiDV = new SMWTermImportDefinitionValidator($completeImportSettings);
	if(!$tiDV->isValidXML()){
		$messages .= "\n* Invalid XML";
	} else {
		if(!$tiDV->isValidModuleConfiguration())
		$messages .= "\n* Invalid ModuleConfiguration.";
		if(!$tiDV->isValidDataSource())
		$messages .= "\n* Invalid data source definition.";
		if(!$tiDV->isValidConflictPolicy())
		$messages .= "\n* Invalid conflict policy.";
		if(!$tiDV->isValidMappingPolicy())
		$messages .= "\n* Invalid mapping policy.";
		if(!$tiDV->isValidImportSet())
		$messages .= "\n* Invalid import set.";
		if(!$tiDV->isValidInputPolicy())
		$messages .= "\n* Invalid Input Policy.";
		if(!$tiDV->isValidUpdatePolicy())
		$messages .= "\n* Invalid update policy.";
	}

	if(strlen($messages) > 0){
		$messages = '<h4><span class="mw-headline">The Term Import Definition is erronious</span></h4>'.$messages;
	} else {
		global $wgArticlePath;
		if(strpos($wgArticlePath, "?") > 0){
			$url = Title::makeTitleSafe(NS_SPECIAL, "TermImport")->getFullURL()."&tiname=".$parser->getTitle()->getText();
		} else {
			$url = Title::makeTitleSafe(NS_SPECIAL, "TermImport")->getFullURL()."?tiname=".$parser->getTitle()->getText();
		}
		$messages = '<h4><a href="'.$url.'">Click here to edit the Term Import definition in the GUI</a></h4>';
	}
	$completeImportSettings = '<h4><span class="mw-headline">Term Import definition</span></h4>'
	.'<pre>'.trim(htmlspecialchars($completeImportSettings)).'</pre>';
	return  $completeImportSettings.$messages;
}