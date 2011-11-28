<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup SMWHaloTriplestore
 *
 * Simple contributor only updates the 'has type' annotations.
 *
 * Called when property annotations get updated in a triple store.
 *
 * @param SemanticData $semData All semantic data (for context)
 * @param SMWDIProperty $property  Currently processed property
 * @param SMWDataItem [] $propertyValueArray Values of current property
 * @param $triplesFromHook Triples which are returned.
 *
 * @return Array of triples or false. If return value is a non-empty array processing stops for this property. Same if it is explicitly false.
 * Otherwise normal processing goes on.
 */
function smwfTripleStorePropertyUpdate(& $data, & $property, & $propertyValueArray, & $triplesFromHook) {
	global $smwgHaloTripleStoreGraph;
	if (!($property instanceof SMWDIProperty)) {
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
	global $smwgHaloContLang;
	$sspa = $smwgHaloContLang->getSpecialSchemaPropertyArray();

	if (SMWHaloPredefinedPages::$IS_INVERSE_OF->getDBkey() == $property->getKey()) {
		foreach($propertyValueArray as $inv) {
			if (count($propertyValueArray) == 1) {
				$invprop_iri = $tsNamespace->getFullIRI($inv->getTitle());
				$triplesFromHook[] = array($subj_iri, "owl:inverseOf", $invprop_iri);
			}
		}
	} elseif (SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getDBkey() == $property->getKey()) {

		if (count($propertyValueArray) > 0) {
			$dataItemContainer = reset($propertyValueArray);
			$sd = $dataItemContainer->getSemanticData();
			$domainCatValues = $sd->getPropertyValues(SMWDIProperty::newFromUserLabel($sspa[SMW_SSP_HAS_DOMAIN]));
			$rangeCatValues = $sd->getPropertyValues(SMWDIProperty::newFromUserLabel($sspa[SMW_SSP_HAS_RANGE]));
			if (count($domainCatValues) > 0) {
				$domain = reset($domainCatValues);
				$domain_iri = $tsNamespace->getFullIRI($domain->getTitle());
				$triplesFromHook[] = array($subj_iri, "haloprop:domainAndRange", "_:1");
				$triplesFromHook[] = array("_:1", "haloprop:domain", $domain_iri);
			}

			if (count($rangeCatValues) > 0) {
				$range = reset($rangeCatValues);
				$range_iri = $tsNamespace->getFullIRI($range->getTitle());
				$triplesFromHook[] = array("_:1", "haloprop:range", $range_iri);
			}
		}

	} elseif ($property->getKey() == "_TYPE") {

		$hasType_iri = $tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "Has_type");

		foreach($propertyValueArray as $value) {
			$typeID = $value->getFragment();
			switch($typeID) {
				case '_wpg':
					$triplesFromHook[] = array($subj_iri, $hasType_iri, "tsctype:page");
					break;
				case '_rec':
					$triplesFromHook[] = array($subj_iri, $hasType_iri, "tsctype:record");
					break;
				default:
					$triplesFromHook[] = array($subj_iri, $hasType_iri, WikiTypeToXSD::getXSDType($typeID));
					break;
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
	global $smwgHaloTripleStoreGraph;
	$tsNamespace = TSNamespaces::getInstance();
	$subj_iri = $tsNamespace->getFullIRI($subject->getTitle());
	// add triples, if page is a property and contains annotations of the predefined 
	// marker categories Category:Transitive properties or Category:Symmetrical properties 
	$ns = $subject->getTitle()->getNamespace();
	if ($ns == SMW_NS_PROPERTY && SMWHaloPredefinedPages::$TRANSITIVE_PROPERTY->equals($c)) {
		$triplesFromHook[] = array($subj_iri, "rdf:type", "owl:TransitiveProperty");
	} elseif ($ns == SMW_NS_PROPERTY && SMWHaloPredefinedPages::$SYMMETRICAL_PROPERTY->equals($c)) {
		$triplesFromHook[] = array($subj_iri, "rdf:type", "owl:SymmetricProperty");
	}
	return true;
}



