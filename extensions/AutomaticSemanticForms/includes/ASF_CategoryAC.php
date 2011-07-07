<?php

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
		} else {
			$categoryCandidates = self::getSubCategoryCandidates($rootCategory);
		}
		
		$textTitles = array();
		
		foreach($categoryCandidates as $c) {
			if (empty($userInput) || stripos(str_replace(" ", "_", (string) $c[0]), $userInput) !== false) {
				$titleText = (string)TSHelper::getTitleFromURI((string)$c[0], true);
				if(Title::newFromText($titleText)->exists()){
					$textTitles[] = (string)$c[0];
					if (count($textTitles) >= $maxResults) break;
				}
			}
		}
		
		$textTitles = array_unique($textTitles);
		$titles = array();
		foreach($textTitles as $r) {
			$titles[] = TSHelper::getTitleFromURI($r, true);
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
		
		global $smwgTripleStoreGraph;
		$resultSelector = 'not( ./sparqlxml:binding[@name ="'.ASF_PROP_NO_AUTOMATIC_FORMEDIT
				.'" and (./sparqlxml:uri/text() = "'.$smwgTripleStoreGraph.'/a/True" or ./sparqlxml:literal/text() = "1 ")])'
			.' and not( ./sparqlxml:binding[@name ="Has_default_form"])';
		$bindingSelector = '@name ="_var0"';
		$queryResults = $dom->xpath('//sparqlxml:result['.$resultSelector.']/sparqlxml:binding['.$bindingSelector.']/sparqlxml:uri');
		
		return $queryResults;
	}
	
	
	private static function getSubCategoryCandidates($category){
		global $wgLang;
		if(strpos($category, $wgLang->getNSText(NS_CATEGORY).':') === 0){
				$category = substr($category, strpos($category, ":") +1);
		}
		$category = Title::newFromText($category, NS_CATEGORY);
		
		if(!($category instanceof Title)){
			return array();
		}
		
		//Get category candidates
		$store = smwfGetSemanticStore();
		$categoryCandidates = $store->getSubCategories($category);
		$categoryCandidates[] = array($category);
		
		//filter categories
		$store = smwfNewBaseStore();
		$categories = array();
		foreach($categoryCandidates as $candidate){
			$semanticData = $store->getSemanticData($candidate[0]);
			
			$noASF = ASFFormGeneratorUtils::getPropertyValue($semanticData, ASF_PROP_NO_AUTOMATIC_FORMEDIT);
			$hasDefaultForm = ASFFormGeneratorUtils::getPropertyValue($semanticData, 'Has_default_form');
			if(strtolower($noASF) != 'true' && strlen($hasDefaultForm) == 0){
				$categories[$candidate[0]->getText()] = array($candidate[0]->getText()); 
			}
		}
		
		ksort($categories);
		
		return $categories;
	}
}













