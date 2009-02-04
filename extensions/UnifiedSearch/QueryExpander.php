<?php

/**
 * Query Expander augments search terms according to a given (SKOS-)ontology. It
 * uses synonyms, shortcuts, broader or narrower, subproperties or subcategories
 * to enrich a full-text query string.
 *
 * Example: gears engine
 *  Gets expanded to:
 *   (Shifting gear OR Automatic gear) AND (V8 engine OR V10 engine OR V8 motor OR V10 motor)
 *
 *  @author: Kai Kühn
 *
 * Created on: 27.01.2009
 */
class QueryExpander {

	/**
	 * Expands a search term according to specified mode.
	 *
	 * @param string $term
	 * @param Enum $mode
	 * @return string
	 */
	public static function expand($terms, $mode = 0) {
		 
		if ($mode == US_EXACTMATCH) {
			// do not expand, just use the given terms
			return self::opTerms($terms, "AND");
		}
		$query = array();
		foreach($terms as $term) {
			$found = false;

			$title = Title::newFromText($term, NS_CATEGORY);
			if ($title->exists()) {
				$subcategories = smwfGetSemanticStore()->getDirectSubCategories($title);
				$skos_values = self::getSKOSPropertyValues($title, $mode);
				$redirects = USStore::getStore()->getRedirects($title);
				$query[]= self::opTerms(array_merge(array($term), $subcategories, $skos_values, $redirects), "OR");
				$found = true;
			}

			$title = Title::newFromText($term);
			if ($title->exists()) {

				$skos_values = self::getSKOSPropertyValues($title, $mode);
				$skos_subjects = strlen($term) < 3 ? array() : self::lookupSKOS($term, $mode);
				$redirects = USStore::getStore()->getRedirects($title);
				$query[]= self::opTerms(array_merge(array($term),$skos_subjects, $skos_values, $redirects), "OR");
				$found = true;
			}
				
			$extraNamespace = array(NS_PDF, NS_DOCUMENT, NS_AUDIO, NS_VIDEO);
			foreach($extraNamespace as $ns) {
				$title = Title::newFromText($term, $ns);
				if ($title->exists()) {

					$skos_values = self::getSKOSPropertyValues($title, $mode);
					$skos_subjects = strlen($term) < 3 ? array() : self::lookupSKOS($term, $mode);
					$redirects = USStore::getStore()->getRedirects($title);
					$query[]= self::opTerms(array_merge(array($term),$skos_subjects, $skos_values, $redirects), "OR");
					$found = true;
				}
			}

			$title = Title::newFromText($term, SMW_NS_PROPERTY);
			if ($title->exists()) {
				$subproperties = $mode == US_HIGH_TOLERANCE ? smwfGetSemanticStore()->getDirectSubProperties($title) : NULL;
				$skos_values = self::getSKOSPropertyValues($title, $mode);
				$redirects = USStore::getStore()->getRedirects($title);
				$query[]= self::opTerms(array_merge(array($term), $subproperties, $skos_values, $redirects), "OR");
				$found = true;
			}

			$title = Title::newFromText($term, NS_TEMPLATE);
			if ($title->exists()) {
				$values = array($term);
				$values = $mode == US_HIGH_TOLERANCE ? array_merge($values, self::getSKOSPropertyValues($title, $mode)) : NULL;

				$query[]= self::opTerms($values, "OR");
				$found = true;
			}

			if (!$found) {
				// do not look in SKOS ontology if term has less than 3 chars
				$skos_subjects = strlen($term) < 3 ? array() : self::lookupSKOS($term, $mode);

				//echo print_r($skos_subjects, true);
				$query[]= self::opTerms(array_merge(array($term),$skos_subjects), "OR");
					
			}
		}

		$totalQuery = self::opTerms($query, "AND");
		return $totalQuery;
	}

	private static function getSKOSPropertyValues($title, $mode) {
		$result = array();
		switch($mode) {

			case US_HIGH_TOLERANCE:

				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$BROADER);
				$result = array_merge($result, $values);
				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$NARROWER);
				$result = array_merge($result, $values);
					

			case US_LOWTOLERANCE:

				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$LABEL);
				$result = array_merge($result, $values);


				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$SYNONYM);
				$result = array_merge($result, $values);


				$values = smwfGetStore()->getPropertyValues($title, SKOSVocabulary::$HIDDEN);
				$result = array_merge($result, $values);
				break;

		}
		return $result;
	}

	private static function lookupSKOS($term, $mode) {
		$result = array();
		$requestoptions = new SMWAdvRequestOptions();
		$requestoptions->isCaseSensitive = false;
		$requestoptions->addStringCondition($term, SMWStringCondition::STRCOND_MID);
		switch($mode) {
			case US_HIGH_TOLERANCE:
				$result = USStore::getStore()->getPropertySubjects(array(SKOSVocabulary::$LABEL,SKOSVocabulary::$SYNONYM ,
				SKOSVocabulary::$HIDDEN, SKOSVocabulary::$BROADER, SKOSVocabulary::$NARROWER), array(), $requestoptions);
				break;
			case US_LOWTOLERANCE:

				$result = USStore::getStore()->getPropertySubjects(array(SKOSVocabulary::$LABEL,SKOSVocabulary::$SYNONYM ,
				SKOSVocabulary::$HIDDEN), array(), $requestoptions);
				break;
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
			if ($t == NULL) continue;
			if ($t instanceof Title) {
				if ($i === 0) $result .= $t->getText(); else $result .= " $operator ".$t->getText();
				$connectedParts++;
			} else if ($t instanceof SMWPropertyValue ) {
				if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".$t->getXSDValue();
				$connectedParts++;
			} else if ($t instanceof SMWStringValue ) {
				if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".$t->getXSDValue();
				$connectedParts++;
			} else if ($t instanceof SMWWikiPageValue ) {
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