<?php
/*
 * Created on 26.04.2007
 *
 * Author: kai
 */

require_once($smwgHaloIP . '/includes/SMW_ChemistryParser.php');

class SMWOntologyBrowserXMLGenerator {
 public static function encapsulateAsConceptPartition($titles, $limit, $partitionNum, $rootLevel = false) {
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
	foreach($titles as $t) { 
		if (SMWOntologyBrowserXMLGenerator::isPredefinedProperty($t)) {
			continue;
		}
		$result = $result."<conceptTreeElement title=\"".$t->getDBkey()."\" img=\"concept.gif\" id=\"ID_$id$count\"></conceptTreeElement>";
		$count++;
	}
	if ($rootLevel) {
		return $result == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_categories')."\"/>" : "<result>$result</result>";
	} else {
		return $result == "" ? "noResult" : "<result>$result</result>";
 	}
}

public static function encapsulateAsInstancePartition($directInstances, $inheritedInstances, $limit, $partitionNum) {
	$id = uniqid (rand());
	$count = 0;
	$result = "";
	if (count($directInstances) == $limit || count($inheritedInstances) >= $limit) { 
		if ($partitionNum == 0) { 
			$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\"/>";
		} else {
			$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\"/>";
		}
	}
	if (count($directInstances) < $limit && count($inheritedInstances) < $limit && $partitionNum > 0) {
		$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
	}
	$count++;
	foreach($directInstances as $t) { 
		$title = preg_replace("/\"/", "&quot;", $t->getDBkey());
		$result = $result."<instance title=\"".$title."\" img=\"instance.gif\" id=\"ID_$id$count\"></instance>";
		$count++;
	}
	foreach($inheritedInstances as $t) { 
		$instanceTitle = preg_replace("/\"/", "&quot;", $t[0]->getDBkey());
		$categoryTitle = preg_replace("/\"/", "&quot;", $t[1]->getDBkey());
		$result = $result."<instance title=\"".$instanceTitle."\" superCat=\"$categoryTitle\" img=\"instance.gif\" id=\"ID_$id$count\" inherited=\"true\"></instance>";
		$count++;
	}
	return $result == '' ? "<instanceList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_instances')."\"/>" : "<instanceList>$result</instanceList>";
}

public static function encapsulateAsPropertyPartition($titles, $limit, $partitionNum, $rootLevel = false) {
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
	foreach($titles as $t) { 
		if (SMWOntologyBrowserXMLGenerator::isPredefinedProperty($t)) {
			continue;
		}
		$title = preg_replace("/\"/", "&quot;", $t->getDBkey());
		$result = $result."<propertyTreeElement title=\"".$title."\" img=\"attribute.gif\" id=\"ID_$id$count\"></propertyTreeElement>";
		$count++;
	}
	if ($rootLevel) { 
		return $result == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_attributes')."\"/>" : "<result>$result</result>";
	} else { 
		return $result == '' ? "noResult" : "<result>$result</result>";
	}
}

public static function encapsulateAsAnnotationList($attributeAnnotations) {
	$result = "";
	foreach($attributeAnnotations as $a) {
		list($attribute, $values) = $a;
		$result .= SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotation($attribute, $values);
	}
		
	return $result == '' ? "<annotationsList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_annotations')."\"/>" : "<annotationsList>".$result."</annotationsList>";
}



public static function encapsulateAsPropertyList($directProperties, $inheritedProperties) {
	$id = uniqid (rand());
	$count = 0;
	$propertiesXML = "";

	foreach($directProperties as $t) {
		if ($t instanceof Title) { 
			$propertiesXML .= SMWOntologyBrowserXMLGenerator::encapsulateAsProperty($t, $count);
			$count++;
		}
	}
	foreach($inheritedProperties as $t) {
		if ($t instanceof Title) { 
			$propertiesXML .= SMWOntologyBrowserXMLGenerator::encapsulateAsProperty($t, $count);
			$count++;
		}
	}
	return $propertiesXML == '' ? "<propertyList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_properties')."\"/>" : "<propertyList>".$propertiesXML."</propertyList>";
}

private static function encapsulateAsProperty(Title $t, $count) {
		$content = "";
		$img = "";
		// read type of property
		$typesOfAttribute = smwfGetStore()->getSpecialValues($t, SMW_SP_HAS_TYPE);
		if (count($typesOfAttribute) == 0) {
			// no 'has type' annotation -> it's a binary relation by default
			$relationTarget = smwfGetStore()->getPropertyValues($t, smwfGetSemanticStore()->rangeHintRelation);
			$img = "relation.gif";
			if (count($relationTarget) == 0) {
				$content = "<rangeType>".wfMsg('smw_ob_undefined_type')."</rangeType>";
			} else { 
				foreach($relationTarget as $rt) {
					$title = preg_replace("/\"/", "&quot;", $rt->getXSDValue());
					$content .= "<rangeType isLink=\"true\">".$title."</rangeType>";
				}
				
			}
					
		} else { 
			// may be a binary relation if 'has type:=Type:Page', an attribute or n-ary relation otherwise.
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
			
		return "<property title=\"".$t->getText()."\" img=\"$img\" id=\"ID_$id$count\" $minCardText $maxCardText $isSymetricalText $isTransitiveText $numberOfUsageAtt>$content</property>";
	
}

private static function encapsulateAsAnnotation(Title $annotationTitle, $smwValues) {
	$id = uniqid (rand());
	$count = 0;
	$singleProperties = "";
	$multiProperties = "";
	$isFormula = false;
	$chemistryParser = new ChemEqParser();
	foreach($smwValues as $smwValue) {
		if ($smwValue instanceof SMWNAryValue) { // n-ary property
		
			$needRepaste = false;
			$parameters = "";
			foreach($smwValue->getDVs() as $params) {
				
				if ($params->getTypeID() == 'chemicalequation' || $params->getTypeID() == 'chemicalformula') {
					$isFormula = true;
					$formulaAsHTML = SMWOntologyBrowserXMLGenerator::getChemicalFormulaOrEquationAsHTML($params->getXSDValue(), $chemistryParser);
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
			$title = preg_replace("/\"/", "&quot;", $annotationTitle->getDBkey());
			$multiProperties .= "<annotation title=\"".$title."\" img=\"attribute.gif\" id=\"ID_$id$count\" $repasteMarker>".$parameters."</annotation>";
	
		} else if ($smwValue instanceof SMWWikiPageValue) { // relation
		
			$title = preg_replace("/\"/", "&quot;", $annotationTitle->getDBkey());
			$singleProperties .= "<annotation title=\"".$title."\" img=\"relation.gif\" id=\"ID_$id$count\" $markChemicalForEq><param isLink=\"true\">".$smwValue->getXSDValue()."</param></annotation>";
			
		} else { // normal attribute
			if ($smwValue->getTypeID() == 'chemicalequation' || $smwValue->getTypeID() == 'chemicalformula') {
				$isFormula = true;
				$formulaAsHTML = SMWOntologyBrowserXMLGenerator::getChemicalFormulaOrEquationAsHTML($smwValue->getXSDValue(), $chemistryParser);
				$value = "<![CDATA[".($formulaAsHTML)."]]>";
			} else { 
				// escape potential HTML in a CDATA section
				$value = "<![CDATA[".html_entity_decode($smwValue->getXSDValue())." ".$smwValue->getUnit()."]]>";
			}
			//special attribute mark for all things needed to get re-pasted in FF.
			$repasteMarker = $isFormula || html_entity_decode($smwValue->getXSDValue()) != $smwValue->getXSDValue() || $smwValue->getUnit() != '' ? "chemFoEq=\"true\"" : "";
		
			$title = preg_replace("/\"/", "&quot;", $annotationTitle->getDBkey());
			$singleProperties .= "<annotation title=\"".$title."\" img=\"attribute.gif\" id=\"ID_$id$count\" $repasteMarker><param>".$value."</param></annotation>";
		}
		$count++;
	}
	return $singleProperties.$multiProperties;
}



private static function getChemicalFormulaOrEquationAsHTML($text, &$chemistryParser) {
	$value = $text;
	$chemistryParser->checkEquation($text);
	if ($chemistryParser->getError() == '') {
		// value is equation
		$value = $chemistryParser->getHtmlFormat();
	}
	$chemistryParser->checkFormula($text);
	if ($chemistryParser->getError() == '') {
		// value is formula
		$value = $chemistryParser->getHtmlFormat();
	}
	return $value;
}





/**
 * returns true, if the property is a pre-defined schema property
 */
private static function isPredefinedProperty($prop) {
	return ($prop->getDBkey()== smwfGetSemanticStore()->domainHintRelation->getDBkey()) 
		||  ($prop->getDBkey()== smwfGetSemanticStore()->rangeHintRelation->getDBkey())
		||  ($prop->getDBkey()== smwfGetSemanticStore()->minCard->getDBkey()) 
		|| 	($prop->getDBkey()== smwfGetSemanticStore()->maxCard->getDBkey())
		|| ($prop->getDBkey()== smwfGetSemanticStore()->transitiveCat->getDBkey()) 
		|| ($prop->getDBkey()== smwfGetSemanticStore()->symetricalCat->getDBkey()); 
} 	
}
?>
