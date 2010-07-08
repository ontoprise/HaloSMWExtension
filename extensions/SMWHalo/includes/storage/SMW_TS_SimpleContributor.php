<?php
/**
 * @file
 * @ingroup SMWHaloTriplestore
 * 
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
    global $smwgTripleStoreGraph;
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

	if (smwfGetSemanticStore()->inverseOf->getDBkey() == array_shift($property->getDBkeys())) {
        foreach($propertyValueArray as $inverseProps) {
            if (count($propertyValueArray) == 1) {
            	
                $triplesFromHook[] = array("<$smwgTripleStoreGraph/property#".$data->getSubject()->getDBkey().">", "owl:inverseOf", "<$smwgTripleStoreGraph/property#".$inverseProps->getDBkey().">");
            }
        }
    } elseif ($property->getPropertyID() == "_TYPE") {

		 
		// insert RDFS range/domain
		foreach($propertyValueArray as $value) {
			$typeID = array_shift($value->getDBkeys());
			if ($typeID != '_wpg') {
				$triplesFromHook[] = array("<$smwgTripleStoreGraph/property#".$data->getSubject()->getDBkey().">", "<$smwgTripleStoreGraph/property#Has_type>", WikiTypeToXSD::getXSDType($typeID));
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
	global $smwgTripleStoreGraph;
	// serialize transitive or symetric property triples
	if ($subject->getNamespace() == SMW_NS_PROPERTY && smwfGetSemanticStore()->transitiveCat->equals($c)) {
		
		$triplesFromHook[] = array("<$smwgTripleStoreGraph/property#".$subject->getDBkey().">", "rdf:type", "owl:TransitiveProperty");
	} elseif ($subject->getNamespace() == SMW_NS_PROPERTY && smwfGetSemanticStore()->symetricalCat->equals($c)) {
		$triplesFromHook[] = array("<$smwgTripleStoreGraph/property#".$subject->getDBkey().">", "rdf:type", "owl:SymmetricProperty");
	}
	return true;
}



