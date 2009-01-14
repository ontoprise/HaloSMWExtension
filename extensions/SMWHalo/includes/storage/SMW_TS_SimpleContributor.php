<?php
/**
 * Simple contributor only updates the 'has type' annotations.
 *
 * Called when property annotations get updated in a triple store.
 *
 * @param $semData All semantic data (for context)
 * @param $property Currently processed property
 * @param $propertyValueArray Values of current property
 * @param $triplesFromHook Triples which are returned.
 *
 * @return Array of triples or false. If return value is a non-empty array processing stops for this property. Same if it is explicitly false.
 * Otherwise normal processing goes on.
 */
function smwfTripleStorePropertyUpdate(& $data, & $property, & $propertyValueArray, & $triplesFromHook) {

	if (!($property instanceof SMWPropertyValue)) {
		// error. should not happen
		trigger_error("Triple store update: property is not SMWPropertyValue");
		return true;
	}

	// check if it is a property with special semantics
	// check for 'has domain, range' and 'is inverse of' and 'has type'
	// 'has min cardinality' and 'has max cardinality are read implictly when processing 'has domain and range'
	// and therefore ignored.
	$allProperties = $data->getProperties();

	if ($property->getPropertyID() == "_TYPE") {

		 
		// insert RDFS range/domain
		foreach($propertyValueArray as $value) {
			$typeID = $value->getXSDValue();
			if ($typeID != '_wpg') {
				$triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "prop:Has_type", WikiTypeToXSD::getXSDType($typeID));
			}

		}

	}
	return true;
}

/**
 * Called when category annotations get updated in a triple store.
 *
 * @param $subject Title with category link
 * @param $c Title of category
 * @param $triplesFromHook Triples which are returned.
 *
 * @return Array of triples or false
 */
function smwfTripleStoreCategoryUpdate(& $subject, & $c, & $triplesFromHook) {
	// serialize transitive or symetric property triples
	if ($subject->getNamespace() == SMW_NS_PROPERTY && smwfGetSemanticStore()->transitiveCat->equals($c)) {
		$triplesFromHook[] = array("prop:".$subject->getDBkey(), "rdf:type", "owl:TransitiveProperty");
	} elseif ($subject->getNamespace() == SMW_NS_PROPERTY && smwfGetSemanticStore()->symetricalCat->equals($c)) {
		$triplesFromHook[] = array("prop:".$subject->getDBkey(), "rdf:type", "owl:SymmetricProperty");
	}
	return true;
}


class WikiTypeToXSD {

	/**
	 * Map primitve types or units to XSD values
	 *
	 * @param unknown_type $wikiTypeID
	 * @return unknown
	 */
	public static function getXSDType($wikiTypeID) {
		switch($wikiTypeID) {
			case '_str' : return 'xsd:string';
			case '_num' : return 'xsd:float';
			case '_boo' : return 'xsd:boolean';
			case '_dat' : return 'xsd:dateTime';
			case '_wpg' : return 'cat:DefaultRootCategory';
			default: return 'xsd:unit';
		}

	}
}
?>