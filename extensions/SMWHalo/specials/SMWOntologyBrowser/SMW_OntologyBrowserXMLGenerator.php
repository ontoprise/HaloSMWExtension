<?php
/*
 * Created on 26.04.2007
 *
 * Author: kai
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

require_once($smwgHaloIP . '/includes/SMW_ChemistryParser.php');
require_once("SMW_OntologyBrowserErrorHighlighting.php");

class SMWOntologyBrowserXMLGenerator {
	
/**
 * Encapsulate an array of categories as a category partition in XML.
 * 
 * @param array & $titles. Category titles
 * @param $limit Max number of categories per partition
 * @param $partitionNum Number of partition (0 <= $partitionNum <= total number / limit)
 * @param $rootLevel True, if partition on root level. Otherwise false.
 * 
 * @return XML string
 */
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
	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
	foreach($titles as $t) { 
		if (SMWOntologyBrowserXMLGenerator::isPredefined($t)) {
			continue;
		}
		$title_esc = htmlspecialchars($t->getDBkey()); 
		$titleURLEscaped = htmlspecialchars(self::urlescape($t->getDBkey()));
		$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t);
		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
		$result = $result."<conceptTreeElement title_url=\"$titleURLEscaped\" title=\"".$title_esc."\" img=\"concept.gif\" id=\"ID_$id$count\">$gi_issues</conceptTreeElement>";
		$count++;
	}
	if ($rootLevel) {
		return $result == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_categories')."\"/>" : "<result>$result</result>";
	} else {
		return $result == "" ? "noResult" : "<result>$result</result>";
 	}
}

/**
 * Encapsulate an array of instances as an instance partition in XML.
 * 
 * @param array & $titles. Instance titles
 * @param $limit Max number of instances per partition
 * @param $partitionNum Number of partition (0 <= $partitionNum <= total number / limit)
 * 
 * @return XML string
 */
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
	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
	foreach($instances as $t) { 
		$instWithCat = is_array($t);
		$instanceTitle =  $instWithCat ?  $t[0] : $t;
		if ($instanceTitle instanceof SMWWikiPageValue) { // also accept SMW datavalue here
			$instanceTitle = $instanceTitle->getTitle();
		}
		$titleEscaped = htmlspecialchars($instanceTitle->getDBkey()); 
		$titleURLEscaped = htmlspecialchars(self::urlescape($instanceTitle->getDBkey()));
		$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $instanceTitle);
 		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
 		if ($instWithCat && $t[1] != NULL) {
 			$categoryTitle = htmlspecialchars($t[1]->getDBkey());
 			$result = $result."<instance title_url=\"$titleURLEscaped\" title=\"".$titleEscaped."\" superCat=\"$categoryTitle\" img=\"instance.gif\" id=\"ID_$id$count\" inherited=\"true\">$gi_issues</instance>";
 		} else {
 			$result = $result."<instance title_url=\"$titleURLEscaped\" title=\"".$titleEscaped."\" img=\"instance.gif\" id=\"ID_$id$count\">$gi_issues</instance>";
 		}
		$count++;
	}
	
	return $result == '' ? "<instanceList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_instances')."\"/>" : "<instanceList>$result</instanceList>";
}

/**
 * Encapsulate an array of properties as a property partition in XML.
 * 
 * @param array & $titles. Property titles
 * @param $limit Max number of properties per partition
 * @param $partitionNum Number of partition (0 <= $partitionNum <= total number / limit)
 * @param $rootLevel True, if partition on root level. Otherwise false
 * 
 * @return XML string
 */
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
	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
	foreach($titles as $t) { 
		if (SMWOntologyBrowserXMLGenerator::isPredefined($t)) {
			continue;
		}
		$title = htmlspecialchars($t->getDBkey());
		$titleURLEscaped = htmlspecialchars(self::urlescape($t->getDBkey()));
		$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t);
		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
		$result = $result."<propertyTreeElement title_url=\"$titleURLEscaped\" title=\"".$title."\" img=\"attribute.gif\" id=\"ID_$id$count\">$gi_issues</propertyTreeElement>";
		$count++;
	}
	if ($rootLevel) { 
		return $result == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_attributes')."\"/>" : "<result>$result</result>";
	} else { 
		return $result == '' ? "noResult" : "<result>$result</result>";
	}
}

/**
 * Encapsulate an array of annotations as XML.
 * 
 * @param array & $propertyAnnotations: Tuple of ($property, $value)
 * @param Title $instance
 * 
 * @return XML string
 */
public static function encapsulateAsAnnotationList(array & $propertyAnnotations, Title $instance) {
	$result = "";
	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
	foreach($propertyAnnotations as $a) {
		list($property, $values) = $a;
		$result .= SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotation($instance, $property, $values);
	}
	// get low cardinality issues and "highlight" missing annotations. This is an exception because missing annotations do not exist.
	$issues = $gi_store->getGardeningIssues('smw_consistencybot', SMW_GARDISSUE_MISSING_ANNOTATIONS, NULL, $instance);
	$result .= SMWOntologyBrowserErrorHighlighting::getMissingAnnotations($issues);	
	$instanceTitleEscaped = htmlspecialchars($instance->getDBkey()); 
	$titleURLEscaped = htmlspecialchars(self::urlescape($instance->getDBkey()));
	return $result == '' ? "<annotationsList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_annotations')."\" title_url=\"$titleURLEscaped\" title=\"$instanceTitleEscaped\"/>" : "<annotationsList>".$result."</annotationsList>";
}


/**
 * Encapsulate an array of properties as XML
 * 
 * @param array & $properties: Tuple of (title, minCard, maxCard, type, isSym, isTrans, range)
 * 
 * @return XML string
 */
public static function encapsulateAsPropertyList(array & $properties) {
	
	$count = 0;
	$propertiesXML = "";
	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
	foreach($properties as $t) {
			$directIssues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t[0]);
 			$propertiesXML .= SMWOntologyBrowserXMLGenerator::encapsulateAsProperty($t, $count, $directIssues);
			$count++;
	}
	
	return $propertiesXML == '' ? "<propertyList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_properties')."\"/>" : "<propertyList>".$propertiesXML."</propertyList>";
}

/**
 * Returns an XML represenatation of a schema property
 * 
 * @param array & schemaData. Tuple of (title, minCard, maxCard, type, isSym, isTrans, range)
 * @param count continuous number for generating new IDs
 * @param array & issues Gardening issues for that property
 * 
 * @return XML string (fragment)
 */
private static function encapsulateAsProperty(array & $schemaData, $count, array & $issues) {
		$id = uniqid (rand());
		$content = "";
		
		// unpack schemaData array
		$title = $schemaData[0];
		$minCardinality = $schemaData[1];
		$maxCardinality = $schemaData[2];
		$type = $schemaData[3];
		$isMemberOfSymCat = $schemaData[4];
		$isMemberOfTransCat = $schemaData[5];
		$range = $schemaData[6];
		
		if ($type == '_wpg') { // binary relation?
			if ($range == NULL) {
				$content .= "<rangeType>".wfMsg('smw_ob_undefined_type')."</rangeType>";
			} else {
				$content .= "<rangeType isLink=\"true\">".$range."</rangeType>";
			}
		} else {
			// it must be an attribute or n-ary relation otherwise.
			$v = SMWDataValueFactory::newSpecialValue(SMW_SP_HAS_TYPE);
			$v->setXSDValue($type);
			$typesOfAttributeAsString = $v->getTypeLabels();
			foreach($typesOfAttributeAsString as $typeOfAttributeAsString) {
				$content .= "<rangeType>".$typeOfAttributeAsString."</rangeType>";
			}
			
			
		}
		
		// generate attribute strings
		$maxCardText = $maxCardinality != CARDINALITY_UNLIMITED ? "maxCard=\"".$maxCardinality."\"" : "maxCard=\"*\"";
		$minCardText = $minCardinality != CARDINALITY_MIN ? "minCard=\"".$minCardinality."\"" : "minCard=\"0\"";
		$isSymetricalText = $isMemberOfSymCat ? "isSymetrical=\"true\"" : "";
		$isTransitiveText = $isMemberOfTransCat ? "isTransitive=\"true\"" : "";
		$title_esc = htmlspecialchars($title->getDBkey());
		$titleURLEscaped = htmlspecialchars(self::urlescape($title->getDBkey()));
		$numberofUsage = smwfGetSemanticStore()->getNumberOfUsage($title);
		$numberOfUsageAtt = 'num="'.$numberofUsage.'"';	
		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
		return "<property title_url=\"$titleURLEscaped\" title=\"".$title_esc."\" id=\"ID_".$id.$count."\" " .
					"$minCardText $maxCardText $isSymetricalText $isTransitiveText $numberOfUsageAtt>".
					$content.$gi_issues.
				"</property>";
	
}

/**
 * Encapsulates an annotation as XML.
 * 
 * @param $instance
 * @param $annotation
 * @param $smwValues
 * 
 * @return XML string (fragment)
 */
private static function encapsulateAsAnnotation(Title $instance, Title $annotationTitle, $smwValues) {
	$id = uniqid (rand());
	$count = 0;
	$singleProperties = "";
	$multiProperties = "";
	$isFormula = false;
	$chemistryParser = new ChemEqParser();
	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
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
			$titleURLEscaped = htmlspecialchars(self::urlescape($annotationTitle->getDBkey()));
			$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_WRONG_DOMAIN_VALUE, SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
 	 		SMW_GARD_ISSUE_MISSING_PARAM, SMW_GARDISSUE_WRONG_TARGET_VALUE), NULL, array($instance, $annotationTitle));
 	 		
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);
			$multiProperties .= "<annotation title_url=\"$titleURLEscaped\" title=\"".$title."\" id=\"ID_$id$count\" $repasteMarker>".$parameters."$gi_issues</annotation>";
	
		} else if ($smwValue instanceof SMWWikiPageValue) { // relation
		
			$title = htmlspecialchars($annotationTitle->getDBkey()); 
			$titleURLEscaped = htmlspecialchars(self::urlescape($annotationTitle->getDBkey()));
			$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_WRONG_DOMAIN_VALUE, SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
 	 		SMW_GARDISSUE_WRONG_TARGET_VALUE), NULL, array($instance, $annotationTitle));
 	 		
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);
			$targetNotExists = $smwValue->getTitle()->exists() ?  "" : "notexists=\"true\"";
			$singleProperties .= "<annotation title_url=\"$titleURLEscaped\" title=\"".$title."\" id=\"ID_$id$count\"><param isLink=\"true\" $targetNotExists>".$smwValue->getTitle()->getPrefixedDBkey()."</param>$gi_issues</annotation>";
			
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
			$titleURLEscaped = htmlspecialchars(self::urlescape($annotationTitle->getDBkey()));
			$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_WRONG_DOMAIN_VALUE, SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
 	 		SMW_GARDISSUE_WRONG_UNIT), NULL, array($instance, $annotationTitle));
 	 		
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);
			$singleProperties .= "<annotation title_url=\"$titleURLEscaped\" title=\"".$title."\" id=\"ID_".$id.$count."\" $repasteMarker><param>".$value."</param>$gi_issues</annotation>";
		}
		$count++;
	}
	return $singleProperties.$multiProperties;
}




/**
 * Returns true, if $t is a pre-defined title.
 */
private static function isPredefined($t) {
	return ($t->getDBkey()== smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()) 
		
		||  ($t->getDBkey()== smwfGetSemanticStore()->minCard->getDBkey()) 
		|| 	($t->getDBkey()== smwfGetSemanticStore()->maxCard->getDBkey())
		|| ($t->getDBkey()== smwfGetSemanticStore()->transitiveCat->getDBkey()) 
		|| ($t->getDBkey()== smwfGetSemanticStore()->symetricalCat->getDBkey()); 
} 	

/**
 * Encode URL, but do not escape slashes (/) 
 *
 * @param unknown_type $url
 * @return unknown
 */
private static function urlescape($url) {
	$url_esc = urlencode($url);
	return str_replace("%2F", "/", $url_esc);
}
}
?>
