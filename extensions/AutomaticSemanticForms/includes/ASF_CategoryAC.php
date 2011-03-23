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
	public static function getCategories($userInput){
		$categoryCandidates = self::getCategoryCandidates();
		
		$textTitles = array();

		foreach($categoryCandidates as $c) {
			if (empty($userInput) || stripos(str_replace(" ", "_", (string) $c[0]), $userInput) !== false) {
				$textTitles[] = (string) $c[0];
				if (count($textTitles) >= SMW_AC_MAX_RESULTS) break;
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
	private static function getCategoryCandidates(){
		global $smwgResultFormats, $smwgHaloIP;
		require_once "$smwgHaloIP/includes/queryprinters/SMW_QP_XML.php";
		$smwgResultFormats['xml'] = 'SMWXMLResultPrinter';

		//Ask for all categories and their 'No automatic form creation annotation
		
		$rawParams[] = '[[:Category:+]]';
		$rawParams[] = '?'.ASF_PROP_NO_AUTOMATIC_FORMEDIT;
		$rawParams[] = "?Has_default_form";

		SMWQueryProcessor::processFunctionParams($rawParams,$querystring,$params,$printouts);
		$params['format'] = "xml";
		$params['limit'] = 400;
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
}













