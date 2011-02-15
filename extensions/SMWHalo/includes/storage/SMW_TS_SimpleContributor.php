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
	$tsNamespace = TSNamespaces::getInstance();
	$subj_iri = $tsNamespace->getFullIRI($data->getSubject()->getTitle());
	$allProperties = $data->getProperties();
	$dbkeys = $property->getDBkeys();
	$propertyName = array_shift($dbkeys);
	if (smwfGetSemanticStore()->inverseOf->getDBkey() == $propertyName) {
		foreach($propertyValueArray as $inv) {
			if (count($propertyValueArray) == 1) {
				$invprop_iri = $tsNamespace->getFullIRI($inv->getTitle());
				$triplesFromHook[] = array($subj_iri, "owl:inverseOf", $invprop_iri);
			}
		}
	} elseif (smwfGetSemanticStore()->domainRangeHintProp->getDBkey() == $propertyName) {

		if (count($propertyValueArray) > 0) {
			$datavalues = reset($propertyValueArray);
			$dvs = $datavalues->getDVs();
			$domain = reset($dvs);
			$domain_iri = $tsNamespace->getFullIRI($domain->getTitle());
			$triplesFromHook[] = array($subj_iri, "haloprop:domainAndRange", "_:1");
			$triplesFromHook[] = array("_:1", "haloprop:domain", $domain_iri);
			$range = next($dvs);
				
			if ($range !== false) {
				$range_iri = $tsNamespace->getFullIRI($range->getTitle());
				$triplesFromHook[] = array("_:1", "haloprop:range", $range_iri);
			}
		}

	} elseif ($property->getPropertyID() == "_TYPE") {

		$hasType_iri = $tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "Has_type");

		foreach($propertyValueArray as $value) {
			$dbkeys = $value->getDBkeys();
			$typeID = array_shift($dbkeys);
			if ($typeID != '_wpg') {
				$triplesFromHook[] = array($subj_iri, $hasType_iri, WikiTypeToXSD::getXSDType($typeID));
			} elseif ($typeID == '_wpg' || $typeID == '_wpp' || $typeID == '_wpc' ||$typeID == '_wpf') {
				$triplesFromHook[] = array($subj_iri, $hasType_iri, "tsctype:page");
			} else if ($typeID === '_rec') {
				$triplesFromHook[] = array($subj_iri, $hasType_iri, "tsctype:record");
			}

		}

	}
	return true;
}

/**
 * Called when category annotations get updated in a triple store.
 *
 * @param $subject SMWWikiPageValue
 * @param $c Title of category
 * @param $triplesFromHook Triples which are returned.
 *
 * @return Array of triples or false
 */
function smwfTripleStoreCategoryUpdate(& $subject, & $c, & $triplesFromHook) {
	global $smwgTripleStoreGraph;
	$tsNamespace = TSNamespaces::getInstance();
	$subj_iri = $tsNamespace->getFullIRI($subject->getTitle());
	// serialize transitive or symetric property triples
	$ns = $subject->getTitle()->getNamespace();
	if ($ns == SMW_NS_PROPERTY && smwfGetSemanticStore()->transitiveCat->equals($c)) {
		$triplesFromHook[] = array($subj_iri, "rdf:type", "owl:TransitiveProperty");
	} elseif ($ns == SMW_NS_PROPERTY && smwfGetSemanticStore()->symetricalCat->equals($c)) {
		$triplesFromHook[] = array($subj_iri, "rdf:type", "owl:SymmetricProperty");
	}
	return true;
}



