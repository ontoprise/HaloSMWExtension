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
 * @ingroup DITermImport
 * 
 * @author Ingo Steinbauer
 */

/**
 * This group contains all parts of the Data Import extension that deal with term imports.
 * @defgroup DITermImport
 * @ingroup DataImport
 */
 
/*
 * Renders a page in the TermImport namespace
 */
class DITermImportPage {
	
	/**
	 * This function is called, when a <ImportSettings>-tag for a Term Import
	 * has been found in an article.
	 *
	 */
	public static function renderTermImportDefinition($input, $args, $parser) {
		
		//todo: language
		
		$attr = "";
		foreach ($args as $k => $v) {
			$attr .= " ". $k . '="' . $v . '"';
		}
		
		//this is necessary, since someone (I really do not who)
		//replaces ]]> with ] which is really strange
		$input = str_replace('] ] >', ']]>', $input);
		
		$completeImportSettings = "<ImportSettings$attr>".$input."</ImportSettings>\n";
	
		$messages = "";
		$tiDV = new DITermImportDefinitionValidator($completeImportSettings);
		if(!$tiDV->isValidXML()){
			$messages .= "\n* Invalid XML";
		} else {
			if(!$tiDV->isValidModuleConfiguration())
			$messages .= "\n* Invalid ModuleConfiguration.";
			if(!$tiDV->isValidDataSource())
			$messages .= "\n* Invalid data source definition.";
			if(!$tiDV->isValidConflictPolicy())
			$messages .= "\n* Invalid conflict policy.";
			if(!$tiDV->isValidCreationPattern())
			$messages .= "\n* Invalid creation pattern.";
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
}