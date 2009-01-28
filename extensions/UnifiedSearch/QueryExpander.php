<?php
define('US_SYNOMYM_EXP', 0);
define('US_SYNOMYM_BROAD_EXP', 1);
define('US_SYNOMYM_NARROW_EXP', 2);


/**
 * Query Expander augments search terms according to a given (SKOS-)ontology. It
 * uses synonyms, shortcuts, broader or narrower, subproperties or subcategories
 * to enrich a full-text query string.
 *
 * Example: gears engine
 *  Gets expanded to:
 *   (Shifting gear OR Automatic gear) AND (V8 engine OR V10 engine OR V8 motor OR V10 motor)
 *
 */
class QueryExpander {

       
	
	
	public function __construct() {
       
	}
	
	

	/**
	 * Expands a search term according to specified mode.
	 *
	 * @param string $term
	 * @param Enum $mode
	 * @return string
	 */
	public static function expand($terms, $mode = 0) {
	
		$query = array();
		foreach($terms as $term) {
			$title = Title::newFromText($term);
			if ($title->exists()) {

				$values = array($term);
				$values = array_merge($values, self::getSKOSPropertyValues($title, $mode));
				$query[]= self::opTerms($values, "OR");
								
			} 

			$title = Title::newFromText($term, NS_CATEGORY);
			if ($title->exists()) {
				$subcategories = smwfGetSemanticStore()->getDirectSubCategories($title);
				$categoryQuery = self::opTerms($subcategories, "OR");
				$values = self::getSKOSPropertyValues($title, $mode);
				$skos_query = self::opTerms($values, "OR");
				$query[]= self::opTerms(array($term, $categoryQuery, $skos_query), "OR");
				continue;
			}

			$title = Title::newFromText($term, SMW_NS_PROPERTY);
			if ($title->exists()) {
				$subproperties = smwfGetSemanticStore()->getDirectSubProperties($title);
				$propertyQuery = self::opTerms($subproperties, "OR");
				$values = self::getSKOSPropertyValues($title, $mode);
				$skos_query = self::opTerms($values, "OR");
				$query[]= self::opTerms(array($term, $propertyQuery, $skos_query), "OR");
				continue;
			}

			$title = Title::newFromText($term, NS_TEMPLATE);
			if ($title->exists()) {
				$values = array($term);
				$values = array_merge($values, self::getSKOSPropertyValues($title, $mode));
				$query[]= self::opTerms($values, "OR");
				continue;
			}

			// title does not exist as instance, category, property or template
			// so check if it appears in the SKOS ontology
			$titles = self::lookupSKOS($term, $mode);
			$query[]= self::opTerms(array_merge(array($term), $titles), "OR");
		}
		$totalQuery = self::opTerms($query, "AND");
		return $totalQuery;
	}

	private static function getSKOSPropertyValues($title, $mode) {
		$result = array();
		switch($mode) {
			case US_SYNOMYM_EXP:
				
				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$LABEL);
				$result = array_merge($result, $values);

				
				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$SYNONYM);
				$result = array_merge($result, $values);

				
				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$HIDDEN);
				$result = array_merge($result, $values);
				break;

			case US_SYNOMYM_BROAD_EXP:
				
				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$BROADER);
				$result = array_merge($result, $values);
				break;

			case US_SYNOMYM_NARROW_EXP:
				
				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$NARROWER);
				$result = array_merge($result, $values);
				break;
		}
		return $result;
	}
	
	private static function lookupSKOS($term, $mode) {
		$result = array();
		switch($mode) {
            case US_SYNOMYM_EXP:
            	$requestoptions = new SMWRequestOptions();
            	$requestoptions->addStringCondition($term, SMWStringCondition::STRCOND_MID);
            	
            	
            	$titles = smwfGetStore()->getPropertySubjects(SKOSVocabulary::$LABEL, NULL, $requestoptions);
            	$result = array_merge($result, $titles);
            	
            	
                $titles = smwfGetStore()->getPropertySubjects(SKOSVocabulary::$SYNONYM, NULL, $requestoptions);
                $result = array_merge($result, $titles);
                
               
                $titles = smwfGetStore()->getPropertySubjects(SKOSVocabulary::$HIDDEN, NULL, $requestoptions);
                $result = array_merge($result, $titles);
		}
		
		return $result;
	}
    
	/**
	 * Connects terms by the specified operator.
	 *
	 * @param array $terms Can be titles, property values or strings
	 * @param string $operator
	 * @return string
	 */
	private static function opTerms(array $terms, $operator) {
		$i = 0;
		$connectedParts = 0; // number of _actually_ connected $terms
		$result = "";
		foreach($terms as $t) {
			if ($t instanceof Title) {
				if ($i === 0) $result .= $t->getText(); else $result .= " $operator ".$t->getText();
				$connectedParts++;
			} else if ($t instanceof SMWPropertyValue ) {
				if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".$t->getXSDValue();
				$connectedParts++;
			} else if ($t instanceof SMWStringValue ) {
                if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".$t->getXSDValue();
                $connectedParts++;
            } else if (is_string($t) && strlen($t) > 0) {
				if ($i === 0) $result .= $t; else $result .= " $operator ".$t;
				$connectedParts++;
			}
			$i++;
		}
		return $connectedParts <= 1 ? $result : "(".$result.")";
	}
	
	
}

?>