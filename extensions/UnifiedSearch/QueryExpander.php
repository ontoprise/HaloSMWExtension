<?php
define('US_SYNOMYM_EXP', 0);
define('US_SYNOMYM_BROAD_EXP', 1);
define('US_SYNOMYM_NARROW_EXP', 2);
define('US_SYNOMYM_EXP', 0);

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

    // SKOS terms
	private final static $LABEL = 'us_skos_preferedLabel';
	private final static $SYNONYM = 'us_skos_altLabel';
	private final static $HIDDEN = 'us_skos_hiddenLabel';
	private final static $BROADER = 'us_skos_broader';
	private final static $NARROWER = 'us_skos_narrower';
	private final static $DESCRIPTION = 'us_skos_description';
	private final static $EXAMPLE = 'us_skos_example';
	private final static $TERM = 'us_skos_term';
    
	
	public function __construct() {

	}

	/**
	 * Expands a search term according to specified mode.
	 *
	 * @param string $term
	 * @param Enum $mode
	 * @return string
	 */
	public function expand($terms, $mode = 0) {
		
		// split terms at whitespaces unless they are quoted
		preg_match_all('/([^\s]+|"[^"]+")+/', $terms, $matches);
        
		$query = array();
		foreach($matches[0] as $term) {
			$title = Title::newFromText($term);
			if ($title->exists()) {
				$values = $this->getSKOSPropertyValues($title, $mode);
				$query[]= self::opTerms($values, "OR");
				continue;
			} 

			$title = Title::newFromText($term, NS_CATEGORY);
			if ($title->exists()) {
				$subcategories = smwfGetSemanticStore()->getDirectSubCategories();
				$categoryQuery .= self::opTerms($subcategories, "OR");
				$values = $this->getSKOSPropertyValues($title, $mode);
				$skos_query .= self::opTerms($values, "OR");
				$query[]= self::opTerms(array($categoryQuery, $skos_query), "AND");
				continue;
			}

			$title = Title::newFromText($term, SMW_NS_PROPERTY);
			if ($title->exists()) {
				$subproperties = smwfGetSemanticStore()->getDirectSubProperties();
				$propertyQuery .= self::opTerms($subproperties, "OR");
				$values = $this->getSKOSPropertyValues($title, $mode);
				$skos_query .= self::opTerms($values, "OR");
				$query[]= self::opTerms(array($propertyQuery, $skos_query), "AND");
				continue;
			}

			$title = Title::newFromText($term, NS_TEMPLATE);
			if ($title->exists()) {
				$values = $this->getSKOSPropertyValues($title, $mode);
				$query[]= self::opTerms($values, "OR");
				continue;
			}

			// title does not exist as instance, category, property or template
			// so check if it appears in the SKOS ontology
			$titles = $this->lookupSKOS($term, $mode);
			$query[]= self::opTerms($titles, "OR");
		}
		$totalQuery = self::opTerms($query, "AND");
		return $totalQuery;
	}

	private function getSKOSPropertyValues($title, $mode) {
		$result = array();
		switch($mode) {
			case US_SYNOMYM_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$LABEL));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);

				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$SYNONYM));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);

				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$HIDDEN));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);
				break;

			case US_SYNOMYM_BROAD_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$BROADER));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);
				break;

			case US_SYNOMYM_NARROW_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$NARROWER));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);
				break;
		}
	}
	
	private function lookupSKOS($term, $mode) {
		$result = array();
		switch($mode) {
            case US_SYNOMYM_EXP:
            	$requestoptions = new SMWRequestOptions();
            	$requestoptions->addStringCondition($term, SMWStringCondition::STRCOND_MID);
            	
            	$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$LABEL));
            	$titles = smwfGetStore()->getPropertySubjects($prop, NULL, $requestoptions);
            	$result = array_merge($result, $titles);
            	
            	$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$SYNONYM));
                $titles = smwfGetStore()->getPropertySubjects($prop, NULL, $requestoptions);
                $result = array_merge($result, $titles);
                
                $prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$HIDDEN));
                $titles = smwfGetStore()->getPropertySubjects($prop, NULL, $requestoptions);
                $result = array_merge($result, $titles);
		}
	}
    
	/**
	 * Connects terms by the specified operator.
	 *
	 * @param array $terms Can be titles, property values or strings
	 * @param string $operator
	 * @return string
	 */
	private static function opTerms(array & $terms, $operator) {
		$i = 0;
		$result = "";
		foreach($terms as $t) {
			if ($t instanceof Title) {
				if ($i === 0) $result .= $t->getText(); else $result .= " $operator ".$t->getText();
			} else if ($t instanceof SMWPropertyValue ) {
				if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".$t->getXSDValue();
			} else if (is_string($t)) {
				if ($i === 0) $result .= $t; else $result .= " $operator ".$t;
			}
			$i++;
		}
		return "(".$result.")";
	}
}

?>