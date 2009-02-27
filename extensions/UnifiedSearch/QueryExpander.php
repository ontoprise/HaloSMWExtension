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
 *  @author: Kai K�hn
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
	public static function expandForFulltext($terms, $mode = 0) {
			
		if ($mode == US_EXACTMATCH) {
			// do not expand, just use the given terms
			return self::opTerms($terms, "AND");
		}
		$query = array();
		foreach($terms as $term) {
			$found = false;

			$title = Title::newFromText($term, NS_CATEGORY);
			if ($title == NULL) continue;
			if ($title->exists()) {
				$subcategories = USStore::getStore()->getDirectSubCategories($title);
				$skos_values = self::getSKOSPropertyValues($title, $mode);
				$redirects = USStore::getStore()->getRedirects($title);
				$query[]= self::opTerms(array_merge(array($term), $subcategories, $skos_values, $redirects), "OR");
				$found = true;
			}
			$title = Title::newFromText($term);
			if ($title == NULL) continue;
			if ($title->exists()) {

				$skos_values = self::getSKOSPropertyValues($title, $mode);
				$skos_subjects = strlen($term) < 3 ? array() : self::lookupSKOSForFulltext($term, $mode);
				$redirects = USStore::getStore()->getRedirects($title);
				$query[]= self::opTerms(array_merge(array($term),$skos_subjects, $skos_values, $redirects), "OR");
				$found = true;
			}

			global $usgAllNamespaces;
			$extraNamespace = array_diff(array_keys($usgAllNamespaces), array(NS_MAIN, SMW_NS_PROPERTY, NS_TEMPLATE, NS_CATEGORY, NS_HELP));

			foreach($extraNamespace as $ns) {
				$title = Title::newFromText($term, $ns);
				if ($title == NULL) continue;
				if ($title->exists()) {

					$skos_values = self::getSKOSPropertyValues($title, $mode);
					$skos_subjects = strlen($term) < 3 ? array() : self::lookupSKOSForFulltext($term, $mode);
					$redirects = USStore::getStore()->getRedirects($title);
					$query[]= self::opTerms(array_merge(array($term),$skos_subjects, $skos_values, $redirects), "OR");
					$found = true;
				}
			}

			$title = Title::newFromText($term, SMW_NS_PROPERTY);
			if ($title == NULL) continue;
			if ($title->exists()) {
				$subproperties = $mode == US_HIGH_TOLERANCE ? USStore::getStore()->getDirectSubProperties($title) : array();
				$skos_values = self::getSKOSPropertyValues($title, $mode);
				$redirects = USStore::getStore()->getRedirects($title);
				$query[]= self::opTerms(array_merge(array($term), $subproperties, $skos_values, $redirects), "OR");
				$found = true;
			}

			$title = Title::newFromText($term, NS_TEMPLATE);
			if ($title == NULL) continue;
			if ($title->exists()) {
				$values = array($term);
				$values = $mode == US_HIGH_TOLERANCE ? array_merge($values, self::getSKOSPropertyValues($title, $mode)) : array();

				$query[]= self::opTerms($values, "OR");
				$found = true;
			}

			if (!$found) {
				// do not look in SKOS ontology if term has less than 3 chars
				$skos_subjects = strlen($term) < 3 ? array() : self::lookupSKOSForFulltext($term, $mode);

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

	private static function lookupSKOSForFulltext($term, $mode) {
		$result = array();
		$requestoptions = new SMWRequestOptions();
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
	 * Returns articles which are subjects from SKOS properties
	 *
	 * @param array of string $terms Terms the user entered (unquoted, no special syntax, may contain significant whitespaces)
	 * @param array $namespaces Namespace indexes
	 * @param int $tolerance tolerance level (0 = tolerant, 1 = semi-tolerant, 2 = exact)
	 * @param int $limit Limit of matches in SKOS ontology
	 * @return array of Title
	 */
	public function expandForTitles($terms, array $namespaces, $tolerance = 0, $limit=10) {

		$db =& wfGetDB( DB_SLAVE );
		// create virtual tables
		$requestoptions = new SMWRequestOptions();
		$requestoptions->isCaseSensitive = false;
		$requestoptions->limit = $limit;

		$termsAnded = self::opTerms($terms, "AND");
		if ($tolerance == US_EXACTMATCH) {
			$subjectTerms = array();
			 
		}

		if ($tolerance == US_LOWTOLERANCE) {
			$properties = array(SKOSVocabulary::$LABEL, SKOSVocabulary::$SYNONYM, SKOSVocabulary::$HIDDEN);
			$requestoptions->disjunctiveStrings = false;
			foreach($terms as $t) {
				if (strlen($t) < 3) continue; // do not add SKOS elements for matches with less than 3 letters .
				$t = str_replace(" ", "_", $t);
				$requestoptions->addStringCondition($t, SMWStringCondition::STRCOND_MID);
			}
			$subjects = USStore::getStore()->getPropertySubjects($properties, $namespaces, $requestoptions); // add all matches with all terms matching
			$subjectTerms = self::opTerms($subjects, "OR");
				
		}

		if ($tolerance == US_HIGH_TOLERANCE) {
			$properties = array(SKOSVocabulary::$LABEL, SKOSVocabulary::$SYNONYM, SKOSVocabulary::$HIDDEN, SKOSVocabulary::$BROADER, SKOSVocabulary::$NARROWER);
			$requestoptions->disjunctiveStrings = true;
			foreach($terms as $t) {
				if (strlen($t) < 3) continue; // do not add SKOS elements for matches with less than 3 letters .
				$t = str_replace(" ", "_", $t);
				$requestoptions->addStringCondition($t, SMWStringCondition::STRCOND_MID);
			}
			$subjects = USStore::getStore()->getPropertySubjects($properties, $namespaces, $requestoptions); // add all matches with all terms matching
			$subjectTerms = self::opTerms($subjects, "OR");
		}


		$totalQuery = self::opTerms(array($termsAnded, $subjectTerms), "OR");
		return $totalQuery;
	}

	public function findAggregatedTerms($terms) {
		$titles = USStore::getStore()->getPageTitles($terms);
		
		$sm = new SmithWaterman();
		$seqB = implode(' ', $terms);
		$maximums = array();
		foreach($titles as $seqA) {
			
			$matches = $sm->getBestMatches(strtolower($seqA->getText()), $seqB);
			
			if (count($matches) > 0) {
				$m = trim(reset($matches));
				if (!array_key_exists($m, $maximums)) $maximums[$m] = 1; else $maximums[$m]++;
			}
			//if ($max != '' && !in_array($max, $maximums)) $maximums[] = $max;
		}
		return $maximums;
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
					if ($i === 0) $result .= $t->getText(); else $result .= " $operator ".self::quoteIfNecessary($t->getText());
					$connectedParts++;
				} else if ($t instanceof SMWPropertyValue ) {
					if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".self::quoteIfNecessary($t->getXSDValue());
					$connectedParts++;
				} else if ($t instanceof SMWStringValue ) {
					if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".self::quoteIfNecessary($t->getXSDValue());
					$connectedParts++;
				} else if ($t instanceof SMWWikiPageValue ) {
					if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".self::quoteIfNecessary($t->getXSDValue());
					$connectedParts++;
				} else if (is_string($t) && strlen($t) > 0) {
					if ($i === 0) $result .= $t; else $result .= " $operator ".self::quoteIfNecessary($t);
					$connectedParts++;
				}
				$i++;
			}
			return $connectedParts <= 1 ? $result : "(".$result.")";
		}

		private static function quoteIfNecessary($str) {
			$containsOperators = strpos($str, " AND ") || strpos($str, " OR ");
			return !$containsOperators && strpos($str, " ") !== false ? "\"$str\"" : $str;
		}
	}

	?>