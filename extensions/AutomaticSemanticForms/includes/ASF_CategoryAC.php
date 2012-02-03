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


/*
 * Provides some static methods for
 * Autocompletion on categories for which
 * ASFs can be created
 */
class ASFCategoryAC {
	
	/*
	 * Get categories for which ASFs can be created.
	 */
public static function getCategories($userInput, $maxResults = SMW_AC_MAX_RESULTS,
			$rootCategory = '_', $queryLimit = 500){
		
		if($rootCategory == '_'){
			$categoryCandidates = self::getCategoryCandidates($queryLimit);
			$dealWithURIs = true;
		} else {
			$dealWithURIs = false;
			$categoryCandidates = self::getSubCategoryCandidates($rootCategory);
		}
		
		$textTitles = array();
		
		foreach($categoryCandidates as $c) {
			if (empty($userInput) || stripos(str_replace(" ", "_", (string) $c[0]), $userInput) !== false) {
				
				if($dealWithURIs){
					$titleText = (string)TSHelper::getTitleFromURI((string)$c[0], true);
				} else {
					$titleText =  (string)$c[0];
				}
				if(Title::newFromText($titleText, NS_CATEGORY)->exists()){
					$textTitles[] = $titleText;
					if (count($textTitles) >= $maxResults) break;
				}
			}
		}
		
		$textTitles = array_unique($textTitles);
		$titles = array();
		foreach($textTitles as $t) {
			$titles[] = Title::newFromText($t);
		}
		
		return $titles;
	}
 
	
	/*
	 * Get Category candidates
	 */
	private static function getCategoryCandidates($queryLimit){
		global $smwgResultFormats, $smwgHaloIP;
		require_once "$smwgHaloIP/includes/queryprinters/SMW_QP_XML.php";
		$smwgResultFormats['xml'] = 'SMWXMLResultPrinter';

		//Ask for all categories and their 'No automatic form creation annotation
		
		$rawParams[] = '[[:Category:+]]';
		$rawParams[] = '?'.ASF_PROP_NO_AUTOMATIC_FORMEDIT;
		$rawParams[] = "?Has_default_form";

		SMWQueryProcessor::processFunctionParams($rawParams,$querystring,$params,$printouts);
		$params['format'] = "xml";
		$params['limit'] = $queryLimit;
		$xmlResult = SMWQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_FILE);
		
		$dom = simplexml_load_string($xmlResult);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		
		global $smwgHaloTripleStoreGraph;
		$resultSelector = 'not( ./sparqlxml:binding[@name ="'.ASF_PROP_NO_AUTOMATIC_FORMEDIT
				.'" and (./sparqlxml:uri/text() = "'.$smwgHaloTripleStoreGraph.'/a/True" or ./sparqlxml:literal/text() = "1 ")])'
			.' and not( ./sparqlxml:binding[@name ="Has_default_form"])';
		$bindingSelector = '@name ="_var0"';
		$queryResults = $dom->xpath('//sparqlxml:result['.$resultSelector.']/sparqlxml:binding['.$bindingSelector.']/sparqlxml:uri');
		
		return $queryResults;
	}
	
	
	private static function getSubCategoryCandidates($category){

		$categories = explode(';', $category);
		
		$results = array();
		foreach($categories as $category){
			$category = trim($category);
		
			global $wgLang;
			if(strpos($category, $wgLang->getNSText(NS_CATEGORY).':') === 0){
				$category = substr($category, strpos($category, ":") +1);
			}
			$category = Title::newFromText($category, NS_CATEGORY);
		
			if(!($category instanceof Title)){
				continue;
			}
		
			//Get category candidates
			$store = smwfGetSemanticStore();
			$categoryCandidates = $store->getSubCategories($category);
			$categoryCandidates[] = array($category);
		
			//filter categories
			$store = smwfGetStore();
			foreach($categoryCandidates as $candidate){
				$semanticData = ASFFormGeneratorUtils::getSemanticData($candidate[0]); 
			
				$noASF = ASFFormGeneratorUtils::getPropertyValue($semanticData, ASF_PROP_NO_AUTOMATIC_FORMEDIT);
				$hasDefaultForm = ASFFormGeneratorUtils::getPropertyValue($semanticData, 'Has_default_form');
			
				if(strtolower($noASF) != 'true' && strlen($hasDefaultForm) == 0){
					$results[$candidate[0]->getText()] = array($candidate[0]->getText()); 
				}
			}
		}
		
		ksort($results);
		
		return $results;
	}
}













