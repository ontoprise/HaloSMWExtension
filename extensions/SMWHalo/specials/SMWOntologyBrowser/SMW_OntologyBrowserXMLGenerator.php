<?php
/*
 * Created on 26.04.2007
 *
 * Author: kai
 */

require_once($smwgHaloIP . '/includes/SMW_ChemistryParser.php');
require_once("SMW_OntologyBrowserErrorHighlighting.php");

class SMWOntologyBrowserXMLGenerator {
 public static function encapsulateAsConceptPartition(array & $titles, $limit, $partitionNum, $rootLevel = false) {
	$id = uniqid (rand());
	$count = 0;
	$result = "";
	if (count($titles) == $limit) { 
		if ($partitionNum == 0) { 
			$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\"/>";
		} else {
			$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\"/>";
		}
	}
	if (count($titles) < $limit && $partitionNum > 0) {
		$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
	}
	$count++;
	$gi_store = SMWGardening::getGardeningIssuesAccess();
	foreach($titles as $t) { 
		if (SMWOntologyBrowserXMLGenerator::isPredefinedProperty($t)) {
			continue;
		}
		$title_esc = htmlspecialchars($t->getDBkey()); 
		$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t);
		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
		$result = $result."<conceptTreeElement title=\"".$title_esc."\" img=\"concept.gif\" id=\"ID_$id$count\">$gi_issues</conceptTreeElement>";
		$count++;
	}
	if ($rootLevel) {
		return $result == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_categories')."\"/>" : "<result>$result</result>";
	} else {
		return $result == "" ? "noResult" : "<result>$result</result>";
 	}
}

public static function encapsulateAsInstancePartition(array & $instances, $limit, $partitionNum) {
	$id = uniqid (rand());
	$count = 0;
	$result = "";
	if (count($instances) == $limit) { 
		if ($partitionNum == 0) { 
			$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\"/>";
		} else {
			$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\"/>";
		}
	}
	if (count($instances) < $limit && $partitionNum > 0) {
		$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
	}
	$count++;
	$gi_store = SMWGardening::getGardeningIssuesAccess();
	foreach($instances as $t) { 
		$instWithCat = is_array($t);
		$instanceTitle =  $instWithCat ?  $t[0] : $t;
		$titleEscaped = htmlspecialchars($instanceTitle->getDBkey()); 
		$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $instanceTitle);
 		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
 		if ($instWithCat && $t[1] != NULL) {
 			$categoryTitle = htmlspecialchars($t[1]->getDBkey());
 			$result = $result."<instance title=\"".$titleEscaped."\" superCat=\"$categoryTitle\" img=\"instance.gif\" id=\"ID_$id$count\" inherited=\"true\">$gi_issues</instance>";
 		} else {
 			$result = $result."<instance title=\"".$titleEscaped."\" img=\"instance.gif\" id=\"ID_$id$count\">$gi_issues</instance>";
 		}
		$count++;
	}
	
	return $result == '' ? "<instanceList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_instances')."\"/>" : "<instanceList>$result</instanceList>";
}

public static function encapsulateAsPropertyPartition(array & $titles, $limit, $partitionNum, $rootLevel = false) {
	$id = uniqid (rand());
	$count = 0;
	$result = "";
	if (count($titles) == $limit) { 
		if ($partitionNum == 0) { 
			$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\"/>";
		} else {
			$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\"/>";
		}
	}
	if (count($titles) < $limit && $partitionNum > 0) {
		$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
	}
	$count++;
	$gi_store = SMWGardening::getGardeningIssuesAccess();
	foreach($titles as $t) { 
		if (SMWOntologyBrowserXMLGenerator::isPredefinedProperty($t)) {
			continue;
		}
		$title = htmlspecialchars($t->getDBkey());
		$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t);
		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
		$result = $result."<propertyTreeElement title=\"".$title."\" img=\"attribute.gif\" id=\"ID_$id$count\">$gi_issues</propertyTreeElement>";
		$count++;
	}
	if ($rootLevel) { 
		return $result == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_attributes')."\"/>" : "<result>$result</result>";
	} else { 
		return $result == '' ? "noResult" : "<result>$result</result>";
	}
}

public static function encapsulateAsAnnotationList(array & $attributeAnnotations, $instance) {
	$result = "";
	$gi_store = SMWGardening::getGardeningIssuesAccess();
	foreach($attributeAnnotations as $a) {
		list($attribute, $values) = $a;
		$result .= SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotation($instance, $attribute, $values);
	}
	// get low cardinality issues and "highlight" missing annotations. This is an exception because missing annotations do not exist.
	$issues = $gi_store->getGardeningIssues('smw_consistencybot', SMW_GARDISSUE_TOO_LOW_CARD, NULL, $instance);
	$result .= SMWOntologyBrowserErrorHighlighting::getMissingAnnotations($issues);	
	return $result == '' ? "<annotationsList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_annotations')."\"/>" : "<annotationsList>".$result."</annotationsList>";
}



public static function encapsulateAsPropertyList(array & $properties) {
	
	$count = 0;
	$propertiesXML = "";
	$gi_store = SMWGardening::getGardeningIssuesAccess();
	foreach($properties as $t) {
		if ($t instanceof Title) { 
			$directIssues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t);
 			$propertiesXML .= SMWOntologyBrowserXMLGenerator::encapsulateAsProperty($t, $count, $directIssues);
			$count++;
		}
	}
	
	return $propertiesXML == '' ? "<propertyList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_properties')."\"/>" : "<propertyList>".$propertiesXML."</propertyList>";
}

private static function encapsulateAsProperty(Title $t, $count, array & $issues) {
		$id = uniqid (rand());
		$content = "";
		$img = "";
		// read type of property
		$typesOfAttribute = smwfGetStore()->getSpecialValues($t, SMW_SP_HAS_TYPE);
		if (count($typesOfAttribute) == 0 || $typesOfAttribute[0]->getXSDValue() == '_wpg' ) {
			// no 'has type' annotation -> it's a binary relation by default
			$relationTarget = smwfGetStore()->getPropertyValues($t, smwfGetSemanticStore()->domainRangeHintRelation);
			$img = "relation.gif";
			if (count($relationTarget) == 0) {
				$content = "<rangeType>".wfMsg('smw_ob_undefined_type')."</rangeType>";
			} else { 
				foreach($relationTarget as $rt) {
					$dvs = $rt->getDVs();
					if (count($dvs) == 2 && $dvs[1] !== NULL) {
						$title = htmlspecialchars( $dvs[1]->getTitle()->getText()); 
						$content .= "<rangeType isLink=\"true\">".$title."</rangeType>";
					} else {
						$content .= "<rangeType>".wfMsg('smw_ob_undefined_type')."</rangeType>";
					}
				}
				
			}
					
		} else { 
			// it may be an attribute or n-ary relation otherwise.
			// n-ary relations use the attribute icon too.
			$typesOfAttributeAsString = $typesOfAttribute[0]->getTypeLabels();
			foreach($typesOfAttributeAsString as $typeOfAttributeAsString) {
				$content .= "<rangeType>".$typeOfAttributeAsString."</rangeType>";
			}
			$typeLabels = $typesOfAttribute[0]->getTypeLabels();
			$img = (count($typeLabels) == 1 && $typeLabels[0] == 'Page') ? "relation.gif" : "attribute.gif";
		}
		
		$numberofUsage = smwfGetSemanticStore()->getNumberOfUsage($t);
		$numberOfUsageAtt = 'num="'.$numberofUsage.'"';	
		// read min/max cardinality
		// TODO: check value if it is valid.
		$minCard = smwfGetStore()->getPropertyValues($t, smwfGetSemanticStore()->minCard);
		$minCardText = 'minCard="0"'; // default min cardinality
		if (count($minCard) > 0) {
			$minCardText = "minCard=\"".$minCard[0]->getXSDValue()."\"";
		}
		$maxCard = smwfGetStore()->getPropertyValues($t, smwfGetSemanticStore()->maxCard);
		$maxCardText = 'maxCard="*"';// default max cardinality
		if (count($maxCard) > 0) {
			$maxCardText = "maxCard=\"".$maxCard[0]->getXSDValue()."\"";
		}
		
		$catsOfRelation = smwfGetSemanticStore()->getCategoriesForInstance($t);
		$isSymetricalText = '';
		$isTransitiveText = '';
		foreach($catsOfRelation as $c) {
			if ($c->getDBkey() == smwfGetSemanticStore()->symetricalCat->getDBkey()) {
				$isSymetricalText = "isSymetrical=\"true\"";
			} else 	if ($c->getDBkey() == smwfGetSemanticStore()->transitiveCat->getDBkey()) {
				$isTransitiveText = "isTransitive=\"true\"";
			}
		}
		$title_esc = htmlspecialchars($t->getDBkey());
		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
		return "<property title=\"".$title_esc."\" img=\"$img\" id=\"ID_".$id.$count."\" $minCardText $maxCardText $isSymetricalText $isTransitiveText $numberOfUsageAtt>".$content.$gi_issues."</property>";
	
}

private static function encapsulateAsAnnotation(Title $instance, Title $annotationTitle, $smwValues) {
	$id = uniqid (rand());
	$count = 0;
	$singleProperties = "";
	$multiProperties = "";
	$isFormula = false;
	$chemistryParser = new ChemEqParser();
	$gi_store = SMWGardening::getGardeningIssuesAccess();
	foreach($smwValues as $smwValue) {
		if ($smwValue instanceof SMWNAryValue) { // n-ary property
		
			$needRepaste = false;
			$parameters = "";
			foreach($smwValue->getDVs() as $params) {
				if ($params == NULL) {
					$parameters .= "<param></param>";
					continue;
				}
				if ($params->getTypeID() == '_che') {
					$isFormula = true;
					$chemistryParser->checkEquation($params->getXSDValue());
					$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
					$value = "<![CDATA[".($formulaAsHTML)."]]>";
				} else if ( $params->getTypeID() == '_chf') {
					$isFormula = true;
					$chemistryParser->checkFormula($params->getXSDValue());
					$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
					$value = "<![CDATA[".($formulaAsHTML)."]]>";
				} else { 
					// escape potential HTML in a CDATA section
					$value = "<![CDATA[".(html_entity_decode($params->getXSDValue()))." ".(html_entity_decode($params->getUnit()))."]]>";
				}
				
				// check if re-paste is needed
				$needRepaste |= html_entity_decode($params->getXSDValue()) != $params->getXSDValue() || $params->getUnit() != '';
				
				// check if target is a wikipage and built param
				$isLink = $params instanceof SMWWikiPageValue ? "isLink=\"true\"" : "";
				$parameters .= "<param $isLink>$value</param>";
			}
			$repasteMarker = $isFormula || $needRepaste ? "chemFoEq=\"true\"" : "";
			$title = htmlspecialchars($annotationTitle->getDBkey()); 
			$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
 	 		SMW_GARD_ISSUE_MISSING_PARAM, SMW_GARDISSUE_WRONG_TARGET_VALUE), NULL, array($instance, $annotationTitle));
 	 		
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);
			$multiProperties .= "<annotation title=\"".$title."\" img=\"attribute.gif\" id=\"ID_$id$count\" $repasteMarker>".$parameters."$gi_issues</annotation>";
	
		} else if ($smwValue instanceof SMWWikiPageValue) { // relation
		
			$title = htmlspecialchars($annotationTitle->getDBkey()); 
			$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
 	 		SMW_GARDISSUE_WRONG_TARGET_VALUE), NULL, array($instance, $annotationTitle));
 	 		
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);
			$singleProperties .= "<annotation title=\"".$title."\" img=\"relation.gif\" id=\"ID_$id$count\"><param isLink=\"true\">".$smwValue->getXSDValue()."</param>$gi_issues</annotation>";
			
		} else if ($smwValue != NULL){ // normal attribute
			if ($smwValue->getTypeID() == '_che') {
				$isFormula = true;
				$chemistryParser->checkEquation($smwValue->getXSDValue());
				$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
				$value = "<![CDATA[".($formulaAsHTML)."]]>";
			} else if ( $smwValue->getTypeID() == '_chf') {
				$isFormula = true;
				$chemistryParser->checkFormula($smwValue->getXSDValue());
				$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
				$value = "<![CDATA[".($formulaAsHTML)."]]>";
			} else { 
				// escape potential HTML in a CDATA section
				$value = "<![CDATA[".html_entity_decode($smwValue->getXSDValue())." ".$smwValue->getUnit()."]]>";
			}
			//special attribute mark for all things needed to get re-pasted in FF.
			$repasteMarker = $isFormula || html_entity_decode($smwValue->getXSDValue()) != $smwValue->getXSDValue() || $smwValue->getUnit() != '' ? "chemFoEq=\"true\"" : "";
		
			$title = htmlspecialchars($annotationTitle->getDBkey()); 
			$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
 	 		SMW_GARDISSUE_WRONG_UNIT), NULL, array($instance, $annotationTitle));
 	 		
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);
			$singleProperties .= "<annotation title=\"".$title."\" img=\"attribute.gif\" id=\"ID_".$id.$count."\" $repasteMarker><param>".$value."</param>$gi_issues</annotation>";
		}
		$count++;
	}
	return $singleProperties.$multiProperties;
}




/**
 * returns true, if the property is a pre-defined schema property
 */
private static function isPredefinedProperty($prop) {
	return ($prop->getDBkey()== smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()) 
		
		||  ($prop->getDBkey()== smwfGetSemanticStore()->minCard->getDBkey()) 
		|| 	($prop->getDBkey()== smwfGetSemanticStore()->maxCard->getDBkey())
		|| ($prop->getDBkey()== smwfGetSemanticStore()->transitiveCat->getDBkey()) 
		|| ($prop->getDBkey()== smwfGetSemanticStore()->symetricalCat->getDBkey()); 
} 	
}
?>
