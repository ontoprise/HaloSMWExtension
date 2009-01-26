<?php


class WikiTitleSearch {
	
	private static $STORE;

	public function lookupTitles($termString, array $namespaces, $limit=10, $offset=0) {

		$terms = QueryExpander::parseTerms($termString);
		

		// get titles containing all terms (case-insensitive)
		$requestoptions = new SMWAdvRequestOptions();
		$requestoptions->limit = $limit;
		$requestoptions->offset = $offset;
		$requestoptions->isCaseSensitive = false;
		foreach($terms as $term) {
			$requestoptions->addStringCondition($term, SMWStringCondition::STRCOND_MID);
		}
		$allTermMatches = smwfGetSemanticStore()->getPages($namespaces, $requestoptions);
		

		$unifiedSearchResults = array();
		// calculate score
		
		foreach($allTermMatches as $m) {
			$sc = 0;
			foreach($terms as $t) {
				$titleLength = strlen($m->getText());
				$sc += strlen($t) / $titleLength;
			}
			
			$unifiedSearchResults[] = UnifiedSearchResult::newFromWikiTitleResult($m, $sc);
		}

		if (count($allTermMatches) > 0) {
			return $unifiedSearchResults;
		}


		$requestoptions = new SMWAdvRequestOptions();
		$requestoptions->limit = $limit;
		$requestoptions->offset = $offset;
		$requestoptions->isCaseSensitive = false;
		$requestoptions->disjunctiveStrings = true;
		foreach($terms as $term) {
			$requestoptions->addStringCondition($term, SMWStringCondition::STRCOND_MID);
		}
		$singleTermMatches = smwfGetSemanticStore()->getPages($namespaces, $requestoptions);

		
		foreach($singleTermMatches as $m) {
			$sc = 0;
			foreach($terms as $t) {
				if (stripos($m->getText(), $t) !== false) {
					$titleLength = strlen($m->getText());
					$sc += strlen($t) / $titleLength;
				}
			}
			$unifiedSearchResults[] = UnifiedSearchResult::newFromWikiTitleResult($m, $sc);
		}
        return $unifiedSearchResults;
	}
	
	public static function &getStore() {
	    global $IP;
	    if (self::$STORE == NULL) {
	        if ($smwgBaseStore != 'SMWHaloStore' && $smwgBaseStore != 'SMWHaloStore2') {
	            trigger_error("The store '$smwgBaseStore' is not implemented for the HALO extension. Please use 'SMWHaloStore2'.");
	        } elseif ($smwgBaseStore == 'SMWHaloStore2') {
	            require_once($IP . '/extensions/storage/US_StoreSQL2.php');
	            self::$STORE = new USStoreSQL();
	        }  else {
	            trigger_error("The store '$smwgBaseStore' is deprecated. You must use 'SMWHaloStore2'.");
	        }
	    }
	    return self::$STORE;
	}
}




?>