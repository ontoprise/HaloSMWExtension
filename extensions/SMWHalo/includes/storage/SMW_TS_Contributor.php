<?php
/**
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


	// check if it is a property with special semantics
	// check for 'has domain, range' and 'is inverse of' and 'has type'
	// 'has min cardinality' and 'has max cardinality are read implictly when processing 'has domain and range'
	// and therefore ignored.
	$allProperties = $data->getProperties();
	if ($property instanceof Title && smwfGetSemanticStore()->domainRangeHintRelation->equals($property)) {
    
		foreach($propertyValueArray as $domRange) {
			if (count($domRange->getDVs()) == 2) {
				$dvs = $domRange->getDVs();
				if ($dvs[0] != NULL && $dvs[1] != NULL && $dvs[0]->isValid() && $dvs[1]->isValid()) { // domain and range
					$minCard = $data->getPropertyValues(smwfGetSemanticStore()->minCard);
					$maxCard = $data->getPropertyValues(smwfGetSemanticStore()->maxCard);
					
					// insert RDFS
					$triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "rdfs:domain", "cat:".$dvs[0]->getDBkey());
					$triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "rdfs:range", "cat:".$dvs[1]->getDBkey());
					
					// insert OWL
					$triplesFromHook[] = array("cat:".$dvs[0]->getDBkey(), "rdfs:subClassOf", "_:1");
					$triplesFromHook[] = array("_:1", "owl:Restriction", "_:2");
					$triplesFromHook[] = array("_:2", "owl:onProperty", "prop:".$data->getSubject()->getDBkey());
					$triplesFromHook[] = array("_:2", "owl:allValuesFrom", "cat:".$dvs[1]->getDBkey());
					foreach($minCard as $value) {
						if ($value->getXSDValue() !== false)
						$triplesFromHook[] = array("_:2", "owl:minCardinality", "\"".$value->getXSDValue()."\"");
					}
					foreach($maxCard as $value) {
						if ($value->getXSDValue() !== false)
						$triplesFromHook[] = array("_:2", "owl:minCardinality", "\"".$value->getXSDValue()."\"");
					}
				} elseif ($dvs[0] != NULL && $dvs[0]->isValid()) { // only domain
					$typeValues = $data->getPropertyValues(SMW_SP_HAS_TYPE);
					$minCard = $data->getPropertyValues(smwfGetSemanticStore()->minCard);
					$maxCard = $data->getPropertyValues(smwfGetSemanticStore()->maxCard);
					
					// insert RDFS
					$triplesFromHook[] = array($data->getSubject()->getDBkey(), "rdfs:domain", "cat:".$dvs[0]->getDBkey());
				    foreach($typeValues as $value) {
                        if ($value->getXSDValue() !== false) {
                        	$typeID = $value->getXSDValue();
                            if ($typeID != '_wpg') $triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "rdfs:range", WikiTypeToXSD::getXSDType($typeID));
                        }
                    }
                    
					// insert OWL
					$triplesFromHook[] = array("cat:".$dvs[0]->getDBkey(), "rdfs:subClassOf", "_:1");
					$triplesFromHook[] = array("_:1", "owl:Restriction", "_:2");
					$triplesFromHook[] = array("_:2", "owl:onProperty", "prop:".$data->getSubject()->getDBkey());
					foreach($typeValues as $value) {
						if ($value->getXSDValue() !== false) {
							$triplesFromHook[] = array("_:2", "owl:allValuesFrom", WikiTypeToXSD::getXSDType($value->getXSDValue()));
						}
					}
					foreach($minCard as $value) {
						if ($value->getXSDValue() !== false)
						$triplesFromHook[] = array("_:2", "owl:minCardinality", "\"".$value->getXSDValue()."\"");
					}
					foreach($maxCard as $value) {
						if ($value->getXSDValue() !== false)
						$triplesFromHook[] = array("_:2", "owl:maxCardinality", "\"".$value->getXSDValue()."\"");
					}
				}
			}
		}
	} elseif ($property instanceof Title && smwfGetSemanticStore()->inverseOf->equals($property)) {
		foreach($propertyValueArray as $inverseProps) {
			if (count($propertyValueArray) == 1) {
				$triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "owl:inverseOf", "prop:".$inverseProps->getDBkey());
			}
		}
	} elseif ($property instanceof Title && smwfGetSemanticStore()->minCard->equals($property)) {
		// do nothing
		$triplesFromHook = false;
	} elseif ($property instanceof Title && smwfGetSemanticStore()->maxCard->equals($property)) {
		// do nothing
		$triplesFromHook = false;
	} elseif ($property === SMW_SP_HAS_TYPE) {

		// serialize type only if there is no domain and range annotation
		$domRanges = $data->getPropertyValues(smwfGetSemanticStore()->domainRangeHintRelation);
		if (count($domRanges) == 0) {

			// insert OWL restrictions
			$minCard = $data->getPropertyValues(smwfGetSemanticStore()->minCard);
			$maxCard = $data->getPropertyValues(smwfGetSemanticStore()->maxCard);
			$triplesFromHook[] = array("cat:DefaultRootCategory", "rdfs:subClassOf", "_:1");
			$triplesFromHook[] = array("_:1", "owl:Restriction", "_:2");
			$triplesFromHook[] = array("_:2", "owl:onProperty", "prop:".$data->getSubject()->getDBkey());
			foreach($propertyValueArray as $value) {
				if ($value->getXSDValue() !== false) {
					$typeID = $value->getXSDValue();	
					$triplesFromHook[] = array("_:2", "owl:allValuesFrom", WikiTypeToXSD::getXSDType($typeID));
				}
			}
			foreach($minCard as $value) {
				if ($value->getXSDValue() !== false)
				$triplesFromHook[] = array("_:2", "owl:minCardinality", "\"".$value->getXSDValue()."\"");
			}
			foreach($maxCard as $value) {
				if ($value->getXSDValue() !== false)
				$triplesFromHook[] = array("_:2", "owl:maxCardinality", "\"".$value->getXSDValue()."\"");
			}

			// insert RDFS range/domain
			foreach($propertyValueArray as $value) {
				$typeID = $value->getXSDValue();
				//$triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "rdfs:domain", "cat:DefaultRootCategory");
				if ($typeID != '_wpg') $triplesFromHook[] = array("prop:".$data->getSubject()->getDBkey(), "rdfs:range", WikiTypeToXSD::getXSDType($typeID));
					
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
			case '_wpg' : return 'cat:DefaultRootCategory';
			default: return 'xsd:unit';
		}
			
	}
}
?>