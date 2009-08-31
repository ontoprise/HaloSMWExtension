<?php
/*
 * Created on 26.02.2007
 * Author: KK
 * 
 * Delegates AJAX calls to database and encapsulate the results as XML.
 * This allows easy transformation to HTML on client side. 
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $smwgHaloIP, $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ob_OntologyBrowserAccess';
$wgAjaxExportList[] = 'smwf_ob_PreviewRefactoring';

if (defined("SGA_GARDENING_EXTENSION_VERSION")) {
	global $sgagIP;
    require_once($sgagIP . "/specials/Gardening/SGA_Gardening.php");
} else {
	require_once("SMW_GardeningIssueStoreDummy.php");
}
require_once("SMW_OntologyBrowserXMLGenerator.php");
require_once("SMW_OntologyBrowserFilter.php" );
require_once("$smwgHaloIP/includes/SMW_OntologyManipulator.php");

function smwf_ob_OntologyBrowserAccess($method, $params) {
	
 	$p_array = explode("##", $params);
 	
 	if ($method == 'getRootCategories') {
 		// param0 : limit
 		// param1 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->limit =  intval($p_array[0]);
 		$reqfilter->sort = true;
 		$reqfilter->offset = intval($p_array[1])*$reqfilter->limit;
		$rootcats = smwfGetSemanticStore()->getRootCategories($reqfilter);
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($rootcats, $p_array[0] + 0, $p_array[1] + 0, true);
 		
 	} else if ($method == 'getSubCategory') {
 		// param0 : category
 		// param1 : limit
 		// param2 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->limit =  intval($p_array[1]);
 		$reqfilter->sort = true;
 		$reqfilter->offset = intval($p_array[2])*$reqfilter->limit;
 		$supercat = Title::newFromText($p_array[0], NS_CATEGORY);
 		$directsubcats = smwfGetSemanticStore()->getDirectSubCategories($supercat, $reqfilter);
 		 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($directsubcats, $p_array[1] + 0, $p_array[2] + 0, false);
 		
 	}  else if ($method == 'getInstance') {
 		// param0 : category
 		// param1 : limit
 		// param2 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$reqfilter->limit =  intval($p_array[1]);
 		$reqfilter->offset = intval($p_array[2])*$reqfilter->limit;
 		$cat = Title::newFromText($p_array[0], NS_CATEGORY);
 		$instances = smwfGetSemanticStore()->getAllInstances($cat,  $reqfilter);
 		 		 		 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($instances, $p_array[1] + 0, $p_array[2] + 0);
 		
 	} else if ($method == 'getAnnotations') {
 		//param0: prefixed title
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$propertyAnnotations = array();
 		
 		$instance = Title::newFromText($params);
 		
 		$properties = smwfGetStore()->getProperties($instance, $reqfilter);
 		foreach($properties as $a) { 
 			if (!$a->isShown() || !$a->isVisible()) continue;
 			$values = smwfGetStore()->getPropertyValues($instance, $a);
 			$propertyAnnotations[] = array($a, $values); 
 		}
 	 	
 	 	
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($propertyAnnotations, $instance);
 		
 	} else if ($method == 'getProperties') {
 		//param0: category name
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$cat = Title::newFromText($p_array[0], NS_CATEGORY);
 		$onlyDirect = $p_array[1] == "true";
 		$properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($cat, $onlyDirect, $reqfilter);
 		
 	 	return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyList($properties);
 		
 	} else if ($method == 'getRootProperties') {
 		// param0 : limit
 		// param1 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$reqfilter->limit =  intval($p_array[0]);
 		$reqfilter->offset = intval($p_array[1])*$reqfilter->limit;
 		$rootatts = smwfGetSemanticStore()->getRootProperties($reqfilter);
 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($rootatts, $p_array[0] + 0, $p_array[1] + 0, true);
 	} else if ($method == 'getSubProperties') {
		// param0 : attribute
 		// param1 : limit
 		// param2 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$reqfilter->limit =  intval($p_array[1]);
 		$reqfilter->offset = intval($p_array[2])*$reqfilter->limit;
 		$superatt = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
 		$directsubatts = smwfGetSemanticStore()->getDirectSubProperties($superatt, $reqfilter);
 		 		 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($directsubatts, $p_array[1] + 0, $p_array[2] + 0, false);
 		
 	} else if ($method == 'getInstancesUsingProperty') {
 		// param0 : property
 		// param1 : limit
 		// param2 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$reqfilter->limit =  intval($p_array[1]);
 		$reqfilter->offset = intval($p_array[2])*$reqfilter->limit;
 		$prop = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
 		
 		if (smwf_om_userCan($p_array[0], 'propertyread', SMW_NS_PROPERTY) === "true") {
 			$attinstances = smwfGetStore()->getAllPropertySubjects(SMWPropertyValue::makeUserProperty($prop->getDBkey()),  $reqfilter);
 		} else {
 			$attinstances = array();
 		}
 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($attinstances, $p_array[1] + 0, $p_array[2] + 0);
 	} else if ($method == 'getCategoryForInstance') {
 		$browserFilter = new SMWOntologyBrowserFilter();
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$instanceTitle = Title::newFromText($p_array[0]);
		return $browserFilter->filterForCategoriesWithInstance($instanceTitle, $reqfilter);
 	} else if ($method == 'getCategoryForProperty') {
 		$browserFilter = new SMWOntologyBrowserFilter();
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$propertyTitle = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
		return $browserFilter->filterForCategoriesWithProperty($propertyTitle, $reqfilter);
 	}  else if ($method == 'filterBrowse') {
 		$browserFilter = new SMWOntologyBrowserFilter();
 		$type = $p_array[0];
 		$hint = explode(" ", $p_array[1]);
 		$hint = smwfEliminateStopWords($hint);
 		if ($type == 'category') {
 			/*STARTLOG*/
 				smwLog($p_array[1],"OB","searched categories", "Special:OntologyBrowser");
			/*ENDLOG*/
 			return $browserFilter->filterForCategories($hint);
 		} else if ($type == 'instance') {
 			/*STARTLOG*/
 				smwLog($p_array[1],"OB","searched instances", "Special:OntologyBrowser");
			/*ENDLOG*/
 			return $browserFilter->filterForInstances($hint);
 		} else if ($type == 'propertyTree') {
 			/*STARTLOG*/
 				smwLog($p_array[1],"OB","searched property tree", "Special:OntologyBrowser");
			/*ENDLOG*/
 			return $browserFilter->filterForPropertyTree($hint);
 		} else if ($type == 'property') {
 			/*STARTLOG*/
 				smwLog($p_array[1],"OB","searched properties", "Special:OntologyBrowser");
			/*ENDLOG*/
 			return $browserFilter->filterForProperties($hint);
 		} 
 		
 	}
 	 	
}

/**
 * Returns semantic statistics about the page.
 * 
 * @param $titleText Title string
 * @param $ns namespace
 * 
 * @return HTML table content (but no table tags!)
 */
function smwf_ob_PreviewRefactoring($titleText, $ns) {
	
	$tableContent = "";
	$title = Title::newFromText($titleText, $ns);
	switch($ns) {
 			case NS_CATEGORY: {
 				$numOfCategories = count(smwfGetSemanticStore()->getSubCategories($title));
		 		$numOfInstances = smwfGetSemanticStore()->getNumberOfInstancesAndSubcategories($title);
		 		$numOfProperties = smwfGetSemanticStore()->getNumberOfProperties($title);
		 		$tableContent .= '<tr><td>'.wfMsg('smw_ob_hasnumofsubcategories').'</td><td>'.$numOfCategories.'</td></tr>';
		 		$tableContent .= '<tr><td>'.wfMsg('smw_ob_hasnumofinstances').'</td><td>'.$numOfInstances.'</td></tr>';
		 		$tableContent .= '<tr><td>'.wfMsg('smw_ob_hasnumofproperties').'</td><td>'.$numOfProperties.'</td></tr>';
 				break;
 			}
 			case SMW_NS_PROPERTY: {
 				$numberOfUsages = smwfGetSemanticStore()->getNumberOfUsage($title);
 				$tableContent .= '<tr><td>'.wfMsg('smw_ob_hasnumofpropusages', $numberOfUsages).'</td></tr>';
 				break;
 			}
 			case NS_MAIN: {
 				$numOfTargets = smwfGetSemanticStore()->getNumberOfPropertiesForTarget($title);
 				$tableContent .= '<tr><td>'.wfMsg('smw_ob_hasnumoftargets', $numOfTargets).'</td></tr>';
 				break;
 			}
 			case NS_TEMPLATE: {
 				$numberOfUsages = smwfGetSemanticStore()->getNumberOfUsage($title);
 				$tableContent .= '<tr><td>'.wfMsg('smw_ob_hasnumoftempuages', $numberOfUsages).'</td></tr>';
 				break;
 			}
 		}
 
 	return $tableContent;
}

/**
 * Eliminates common prefixes/suffixes from $hints array
 * 
 * @param array of string
 * @return array of string
 */
function smwfEliminateStopWords($hints) {
 		$stopWords = array('has', 'of', 'in', 'by', 'is');
 		$result = array();
 		foreach($hints as $h) {
 			if (!in_array(strtolower($h), $stopWords)) {
 				$result[] = $h;
 			}
 		}
 		return $result;
 	}


