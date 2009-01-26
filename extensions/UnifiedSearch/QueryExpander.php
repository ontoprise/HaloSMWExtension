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

    // SKOS terms
	private  static $LABEL = 'us_skos_preferedLabel';
	private  static $SYNONYM = 'us_skos_altLabel';
	private  static $HIDDEN = 'us_skos_hiddenLabel';
	private  static $BROADER = 'us_skos_broader';
	private  static $NARROWER = 'us_skos_narrower';
	private  static $DESCRIPTION = 'us_skos_description';
	private  static $EXAMPLE = 'us_skos_example';
	private  static $TERM = 'us_skos_term';
    
	private $terms;
	
	public function __construct() {
        $this->terms = array();
	}
	
	public function getTerms() {
		return $this->terms;
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
        
		$this->terms = self::parseTerms($terms);
		$query = array();
		foreach($this->terms as $term) {
			
			$title = Title::newFromText($term);
			if ($title->exists()) {
				$values = array($term);
				$values = array_merge($values, $this->getSKOSPropertyValues($title, $mode));
				$query[]= self::opTerms($values, "OR");
				continue;
			} 

			$title = Title::newFromText($term, NS_CATEGORY);
			if ($title->exists()) {
				
				$subcategories = smwfGetSemanticStore()->getDirectSubCategories($title);
				$categoryQuery = self::opTerms($subcategories, "OR");
				$values = $this->getSKOSPropertyValues($title, $mode);
				$skos_query = self::opTerms($values, "OR");
				$query[]= self::opTerms(array($term, $categoryQuery, $skos_query), "OR");
				continue;
			}

			$title = Title::newFromText($term, SMW_NS_PROPERTY);
			if ($title->exists()) {
				$subproperties = smwfGetSemanticStore()->getDirectSubProperties($title);
				$propertyQuery = self::opTerms($subproperties, "OR");
				$values = $this->getSKOSPropertyValues($title, $mode);
				$skos_query = self::opTerms($values, "OR");
				$query[]= self::opTerms(array($term, $propertyQuery, $skos_query), "OR");
				continue;
			}

			$title = Title::newFromText($term, NS_TEMPLATE);
			if ($title->exists()) {
				$values = array($term);
				$values = array_merge($values, $this->getSKOSPropertyValues($title, $mode));
				$query[]= self::opTerms($values, "OR");
				continue;
			}

			// title does not exist as instance, category, property or template
			// so check if it appears in the SKOS ontology
			$titles = $this->lookupSKOS($term, $mode);
			$query[]= self::opTerms(array_merge(array($term), $titles), "OR");
		}
		$totalQuery = self::opTerms($query, "AND");
		return $totalQuery;
	}

	private function getSKOSPropertyValues($title, $mode) {
		$result = array();
		switch($mode) {
			case US_SYNOMYM_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$LABEL));
				$values = smwfGetStore()->getPropertyValues($title, $prop);
				$result = array_merge($result, $values);

				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$SYNONYM));
				$values = smwfGetStore()->getPropertyValues($title, $prop);
				$result = array_merge($result, $values);

				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$HIDDEN));
				$values = smwfGetStore()->getPropertyValues($title, $prop);
				$result = array_merge($result, $values);
				break;

			case US_SYNOMYM_BROAD_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$BROADER));
				$values = smwfGetStore()->getPropertyValues($title, $prop);
				$result = array_merge($result, $values);
				break;

			case US_SYNOMYM_NARROW_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg(self::$NARROWER));
				$values = smwfGetStore()->getPropertyValues($title, $prop);
				$result = array_merge($result, $values);
				break;
		}
		return $result;
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
			} else if (is_string($t) && strlen($t) > 0) {
				if ($i === 0) $result .= $t; else $result .= " $operator ".$t;
				$connectedParts++;
			}
			$i++;
		}
		return $connectedParts <= 1 ? $result : "(".$result.")";
	}
	
	public static function parseTerms($termString) {
		$terms = array();
	    // split terms at whitespaces unless they are quoted
        preg_match_all('/([^\s"]+|"[^"]+")+/', $termString, $matches);
                
        foreach($matches[0] as $term) {
            // unquote if necessary
            if (substr($term, 0, 1) == '"' && substr($term, strlen($term)-1, 1) == '"') {
                $term = substr($term, 1, strlen($term)-2);
                $term = str_replace(' ','_',$term);
            }
            $terms[] = $term;
        }
        return $terms;
	}
}

?>