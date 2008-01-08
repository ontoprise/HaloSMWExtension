<?php
/*
 * Created on 26.02.2007
 * Author: KK
 * 
 * Delegates AJAX calls to database and encapsulate the results as XML.
 * This allows easy transformation to HTML on client side. 
 */
global $smwgIP, $smwgHaloIP, $wgAjaxExportList;
$wgAjaxExportList[] = 'smwfOntologyBrowserAccess';

require_once($smwgIP . "/includes/storage/SMW_Store.php");
require_once($smwgHaloIP . "/specials/SMWGardening/SMW_Gardening.php");
require_once("SMW_OntologyBrowserXMLGenerator.php");
require_once("SMW_OntologyBrowserFilter.php" );
 
function smwfOntologyBrowserAccess($method, $params) {
	
 	$p_array = explode(",", $params);
 	
 	if ($method == 'getRootCategories') {
 		// param0 : limit
 		// param1 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->limit =  $p_array[0] + 0;
 		$reqfilter->sort = true;
 		$reqfilter->offset = ($p_array[1] + 0)*$reqfilter->limit;
		$rootcats = smwfGetSemanticStore()->getRootCategories($reqfilter);
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($rootcats, $p_array[0] + 0, $p_array[1] + 0, true);
 		
 	} else if ($method == 'getSubCategory') {
 		// param0 : category
 		// param1 : limit
 		// param2 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->limit =  $p_array[1] + 0;
 		$reqfilter->sort = true;
 		$reqfilter->offset = ($p_array[2] + 0)*$reqfilter->limit;
 		$supercat = Title::newFromText($p_array[0], NS_CATEGORY);
 		$directsubcats = smwfGetSemanticStore()->getDirectSubCategories($supercat, $reqfilter);
 		 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($directsubcats, $p_array[1] + 0, $p_array[2] + 0, false);
 		
 	}  else if ($method == 'getInstance') {
 		// param0 : category
 		// param1 : limit
 		// param2 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$reqfilter->limit =  $p_array[1] + 0;
 		$reqfilter->offset = ($p_array[2] + 0)*$reqfilter->limit;
 		$cat = Title::newFromText($p_array[0], NS_CATEGORY);
 		$instances = smwfGetSemanticStore()->getInstances($cat,  $reqfilter);
 		 		 		 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($instances, $p_array[1] + 0, $p_array[2] + 0);
 		
 	} else if ($method == 'getAnnotations') {
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$propertyAnnotations = array();
 		
 		$instance = Title::newFromText($p_array[0]);
 		$properties = smwfGetStore()->getProperties($instance, $reqfilter);
 		foreach($properties as $a) { 
 			$values = smwfGetStore()->getPropertyValues($instance, $a);
 			$propertyAnnotations[] = array($a, $values); 
 		}
 	 	
 	 	
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($propertyAnnotations, $instance);
 		
 	} else if ($method == 'getProperties') {
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$cat = Title::newFromText($p_array[0], NS_CATEGORY);
 		$properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($cat, $reqfilter);
 		
 	 	return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyList($properties);
 		
 	} else if ($method == 'getRootProperties') {
 		// param0 : limit
 		// param1 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$reqfilter->limit =  $p_array[0] + 0;
 		$reqfilter->offset = ($p_array[1] + 0)*$reqfilter->limit;
 		$rootatts = smwfGetSemanticStore()->getRootProperties($reqfilter);
 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($rootatts, $p_array[0] + 0, $p_array[1] + 0, true);
 	} else if ($method == 'getSubProperties') {
		// param0 : attribute
 		// param1 : limit
 		// param2 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$reqfilter->limit =  $p_array[1] + 0;
 		$reqfilter->offset = ($p_array[2] + 0)*$reqfilter->limit;
 		$superatt = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
 		$directsubatts = smwfGetSemanticStore()->getDirectSubProperties($superatt, $reqfilter);
 		 		 		
 		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($directsubatts, $p_array[1] + 0, $p_array[2] + 0, false);
 		
 	} else if ($method == 'getInstancesUsingProperty') {
 		// param0 : property
 		// param1 : limit
 		// param2 : partitionNum
 		$reqfilter = new SMWRequestOptions();
 		$reqfilter->sort = true;
 		$reqfilter->limit =  $p_array[1] + 0;
 		$reqfilter->offset = ($p_array[2] + 0)*$reqfilter->limit;
 		$prop = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
 		$attinstances = smwfGetStore()->getAllPropertySubjects($prop,  $reqfilter);
 		
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

?>
