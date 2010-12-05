<?php
/**
 * @file
 * @ingroup SMWHaloTriplestore
 * 
 * Schema contributor tries to add all schema information from the wiki
 * Warning: may created complex models.
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

	if (smwfGetSemanticStore()->domainRangeHintRelation->getDBkey() == array_shift($property->getDBkeys())) {

		foreach($propertyValueArray as $domRange) {
			if (!$domRange instanceof SMWRecordValue) continue; // occurs if 'has domain and range' is not n-ary
			if (count($domRange->getDVs()) == 2) {
				$dvs = $domRange->getDVs();
				if ($dvs[0] != NULL && $dvs[1] != NULL && $dvs[0]->isValid() && $dvs[1]->isValid()) { // domain and range
					$minCard = $data->getPropertyValues(smwfGetSemanticStore()->minCardProp);
					$maxCard = $data->getPropertyValues(smwfGetSemanticStore()->maxCardProp);
						
					// insert RDFS
					$triplesFromHook[] = array("<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">", "rdfs:domain", "cat:".$dvs[0]->getDBkey());
					$triplesFromHook[] = array("<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">", "rdfs:range", "cat:".$dvs[1]->getDBkey());
						
					// insert OWL
					$triplesFromHook[] = array("<$smwgTripleStoreGraph/category/".$dvs[0]->getDBkey().">", "rdfs:subClassOf", "_:1");
					$triplesFromHook[] = array("_:1", "owl:Restriction", "_:2");
					$triplesFromHook[] = array("_:2", "owl:onProperty", "<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">");
					$triplesFromHook[] = array("_:2", "owl:allValuesFrom", "<$smwgTripleStoreGraph/category/".$dvs[1]->getDBkey().">");
					foreach($minCard as $value) {
						if (array_shift($value->getDBkeys()) !== false)
						$triplesFromHook[] = array("_:2", "owl:minCardinality", "\"".array_shift($value->getDBkeys())."\"");
					}
					foreach($maxCard as $value) {
						if (array_shift($value->getDBkeys()) !== false)
						$triplesFromHook[] = array("_:2", "owl:minCardinality", "\"".array_shift($value->getDBkeys())."\"");
					}
				} elseif ($dvs[0] != NULL && $dvs[0]->isValid()) { // only domain
					$typeValues = $data->getPropertyValues(SMWPropertyValue::makeProperty("_TYPE"));
					$minCard = $data->getPropertyValues(smwfGetSemanticStore()->minCardProp);
					$maxCard = $data->getPropertyValues(smwfGetSemanticStore()->maxCardProp);
						
					// insert RDFS
					$triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "rdfs:domain", "<$smwgTripleStoreGraph/category/".$dvs[0]->getDBkey().">");
					foreach($typeValues as $value) {
						if (array_shift($value->getDBkeys()) !== false) {
							$typeID = array_shift($value->getDBkeys());
							if ($typeID != '_wpg') $triplesFromHook[] = array("<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">", "rdfs:range", WikiTypeToXSD::getXSDType($typeID));
						}
					}

					// insert OWL
					$triplesFromHook[] = array("<$smwgTripleStoreGraph/category/".$dvs[0]->getDBkey().">", "rdfs:subClassOf", "_:1");
					$triplesFromHook[] = array("_:1", "owl:Restriction", "_:2");
					$triplesFromHook[] = array("_:2", "owl:onProperty", "<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">");
					foreach($typeValues as $value) {
						if (array_shift($value->getDBkeys()) !== false) {
							$triplesFromHook[] = array("_:2", "owl:allValuesFrom", WikiTypeToXSD::getXSDType(array_shift($value->getDBkeys())));
						}
					}
					foreach($minCard as $value) {
						if (array_shift($value->getDBkeys()) !== false)
						$triplesFromHook[] = array("_:2", "owl:minCardinality", "\"".array_shift($value->getDBkeys())."\"");
					}
					foreach($maxCard as $value) {
						if (array_shift($value->getDBkeys()) !== false)
						$triplesFromHook[] = array("_:2", "owl:maxCardinality", "\"".array_shift($value->getDBkeys())."\"");
					}
				}
			}
				
		}
	} elseif (smwfGetSemanticStore()->inverseOf->getDBkey() == array_shift($property->getDBkeys())) {
		foreach($propertyValueArray as $inverseProps) {
			if (count($propertyValueArray) == 1) {
				
				$triplesFromHook[] = array("<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">", "owl:inverseOf", "<$smwgTripleStoreGraph/property/".$inverseProps->getDBkey().">");
			}
		}
	} elseif (smwfGetSemanticStore()->minCard->getDBkey() == array_shift($property->getDBkeys())) {
		// do nothing
		$triplesFromHook = false;
	} elseif (smwfGetSemanticStore()->maxCard->getDBkey() == array_shift($property->getDBkeys())) {
		// do nothing
		$triplesFromHook = false;
	} elseif ($property->getPropertyID() == "_TYPE") {

		// serialize type only if there is no domain and range annotation
		$domRanges = $data->getPropertyValues(smwfGetSemanticStore()->domainRangeHintProp);

		if (count($domRanges) == 0) { // insert only if domain and range annotation does not exist

			// insert OWL restrictions
			$minCard = $data->getPropertyValues(smwfGetSemanticStore()->minCardProp);
			$maxCard = $data->getPropertyValues(smwfGetSemanticStore()->maxCardProp);
			$triplesFromHook[] = array("<$smwgTripleStoreGraph/property/DefaultRootCategory>", "rdfs:subClassOf", "_:1");
			$triplesFromHook[] = array("_:1", "owl:Restriction", "_:2");
			$triplesFromHook[] = array("_:2", "owl:onProperty", "<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">");
			foreach($propertyValueArray as $value) {
				if (array_shift($value->getDBkeys()) !== false) {
					$typeID = array_shift($value->getDBkeys());
					$triplesFromHook[] = array("_:2", "owl:allValuesFrom", WikiTypeToXSD::getXSDType($typeID));
				}
			}
			foreach($minCard as $value) {
				if (array_shift($value->getDBkeys()) !== false)
				$triplesFromHook[] = array("_:2", "owl:minCardinality", "\"".array_shift($value->getDBkeys())."\"");
			}
			foreach($maxCard as $value) {
				if (array_shift($value->getDBkeys()) !== false)
				$triplesFromHook[] = array("_:2", "owl:maxCardinality", "\"".array_shift($value->getDBkeys())."\"");
			}

			// insert RDFS range/domain
			foreach($propertyValueArray as $value) {
				$typeID = array_shift($value->getDBkeys());
				//$triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "rdfs:domain", "cat:DefaultRootCategory");
				if ($typeID != '_wpg') $triplesFromHook[] = array("<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">", "rdfs:range", WikiTypeToXSD::getXSDType($typeID));
					
			}
		}
		// insert Has type
		foreach($propertyValueArray as $value) {
			$typeID = array_shift($value->getDBkeys());
			if ($typeID != '_wpg') {
				$triplesFromHook[] = array("<$smwgTripleStoreGraph/property/".$data->getSubject()->getDBkey().">", "Has_type", WikiTypeToXSD::getXSDType($typeID));
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
		$triplesFromHook[] = array("<$smwgTripleStoreGraph/property/".$subject->getDBkey().">", "rdf:type", "owl:TransitiveProperty");
	} elseif ($subject->getNamespace() == SMW_NS_PROPERTY && smwfGetSemanticStore()->symetricalCat->equals($c)) {
		$triplesFromHook[] = array("<$smwgTripleStoreGraph/property/".$subject->getDBkey().">", "rdf:type", "owl:SymmetricProperty");
	}
	return true;
}



