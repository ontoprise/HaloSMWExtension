<?php

/*
 * Provides some static methods that are used by several DAL modules
 */
class DIDALHelper {

		/**
	 * Parses the input policy in the XML string <$inputPolicy>. The policy
	 * specifies concrete terms, regular expression for terms to import and
	 * the properties of the terms to import.
	 *
	 * @param string $inputPolicy
	 * 		An XML string that contains the input policy.
	 * @return array(array<string>)
	 * 		An array with three arrays (keys: "terms", "regex", "properties")
	 * 		that contain the values from the XML string or an error message
	 * 		if the XML is not valid.
	 */
	public static function parseInputPolicy(&$inputPolicy) {
		$parser = new DIXMLParser($inputPolicy);
		$result = $parser->parse();
		if ($result !== TRUE) {
			return $result;
		}
		 
		$policy = array();
		$policy['terms'] = $parser->getValuesOfElement(array('terms', 'term'));
		$policy['regex'] = $parser->getValuesOfElement(array('terms', 'regex'));
		$policy['properties'] = $parser->getValuesOfElement(array('properties', 'property'));
		return $policy;
	}

	/**
	 * Checks if a term (that may belong to an import set) matches the restriction
	 * of import sets and the input policy.
	 *
	 * @param string $impSet
	 * 		The name of the import that the term belongs to. Can be <null>.
	 * @param string $term
	 * 		The name of the term.
	 * @param array<string> $importSets
	 * 		An array of allowed import sets.
	 * @param array(array<string>) $policy
	 * 		An array with the keys 'terms', 'regex' and 'properties'. The value for
	 * 		each key is an array of strings with terms, regular expressions and
	 * 		properties, respectively.
	 * @return boolean
	 * 		<true>, if the term matches the rules and should be imported
	 * 		<false> otherwise
	 */
	public static function termMatchesRules($impSet, $term,
			&$importSets, &$policy) {

		//deal with this one
		return true;
				
		// Check import set
		if ($impSet != null && strlen(trim($importSets)) > 0) {
			
			if (trim($impSet) == trim($importSets)) {
				// Term belongs to the wrong import set.
				return false;
			}
		}

		// Check term policy
		$terms = &$policy['terms'];
		if (in_array($term, $terms)) {
			return true;
		}

		// Check regex policy
		$regex = &$policy['regex'];
		foreach ($regex as $re) {
			$re = trim($re);
			if (preg_match('/'.$re.'/', $term)) {
				return true;
			}
		}

		return false;
	}
	
}