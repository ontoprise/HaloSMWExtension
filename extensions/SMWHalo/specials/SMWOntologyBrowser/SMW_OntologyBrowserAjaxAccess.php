<?php
/**
 * Created on 26.02.2007
 *
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloOntologyBrowser
 *
 * @author Kai K�hn
 *
 * Delegates AJAX calls to database and encapsulate the results as XML.
 * This allows easy transformation to HTML on client side.
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMWH_OB_DEFAULT_PARTITION_SIZE', 40);

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
require_once("$smwgHaloIP/includes/SMW_OntologyManipulator.php");



class OB_Storage {

	/**
	 * Datasource according to the LOD metamodel
	 *
	 * @var string (URI)
	 */
	protected $dataSource;

	/**
	 * Bundle ID
	 *
	 * @var string
	 */
	protected $bundleID;

	public function __construct($dataSource = '', $bundleID = '') {
		$this->dataSource = $dataSource;
		$this->bundleID = $bundleID;
	}


	public function getRootCategories($p_array) {
		// param0 : limit
		// param1 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->limit =  intval($p_array[0]);
		$reqfilter->sort = true;
		$partitionNum = isset($p_array[1]) ? intval($p_array[1]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;

		$rootcats = smwfGetSemanticStore()->getRootCategories($reqfilter, $this->bundleID);
		$resourceAttachments = array();
		wfRunHooks('smw_ob_attachtoresource', array($rootcats, & $resourceAttachments, NS_CATEGORY));

		$ts = TSNamespaces::getInstance();
		$categoryTreeElements = array();
		foreach ($rootcats as $rc) {
			list($t, $isLeaf) = $rc;
			$categoryTreeElements[] = new CategoryTreeElement($t, NULL, $isLeaf);
		}
		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($categoryTreeElements, $resourceAttachments, $reqfilter->limit, $partitionNum, true);
	}

	public function getSubCategory($p_array) {
		// param0 : category
		// param1 : limit
		// param2 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->limit =  intval($p_array[1]);
		$reqfilter->sort = true;
		$partitionNum = isset($p_array[2]) ? intval($p_array[2]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$supercat = Title::newFromText($p_array[0], NS_CATEGORY);


		$directsubcats = smwfGetSemanticStore()->getDirectSubCategories($supercat, $reqfilter, $this->bundleID);
		$resourceAttachments = array();
		wfRunHooks('smw_ob_attachtoresource', array($directsubcats, & $resourceAttachments, NS_CATEGORY));

		$ts = TSNamespaces::getInstance();
		$categoryTreeElements = array();
		foreach ($directsubcats as $rc) {
			list($t, $isLeaf) = $rc;
			$categoryTreeElements[] = new CategoryTreeElement($t, NULL, $isLeaf);
		}
		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($categoryTreeElements, $resourceAttachments, $reqfilter->limit, $partitionNum, false);

	}

	public function getInstance($p_array) {
		// param0 : category
		// param1 : limit
		// param2 : partitionNum
		// param3 : onlyDirect
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  intval($p_array[1]);
		$partitionNum = isset($p_array[2]) ? intval($p_array[2]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$onlyAssertedCategories = $p_array[3] == 'true';
		$cat = Title::newFromText($p_array[0], NS_CATEGORY);


		$instances = smwfGetSemanticStore()->getAllInstances($cat,  $reqfilter);
			


		$ts = TSNamespaces::getInstance();
		$results = array();
		foreach($instances as $i) {
			if (is_array($i) && !is_null($i[1])) {
				$c = new CategoryTreeElement($i[1], NULL);
					
				if (!array_key_exists($i[0]->getPrefixedText(), $results)) {
					$results[$i[0]->getPrefixedText()] = new InstanceListElement($i[0], NULL);
					$results[$i[0]->getPrefixedText()]->addCategoryTreeElement($c);
				} else {
					$instanceElement = $results[$i[0]->getPrefixedText()];
					$instanceElement->addCategoryTreeElement($c);
				}
			} else {
				$instanceTitle = is_array($i) ? reset($i) : $i;
				if (!array_key_exists($instanceTitle->getPrefixedText(), $results)) {
					$results[$instanceTitle->getPrefixedText()] = new InstanceListElement($instanceTitle, NULL);
					$results[$instanceTitle->getPrefixedText()]->addCategoryTreeElement(NULL);
				}
			}
		}

		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition(count($instances), $results, $reqfilter->limit, $partitionNum);

	}

	public function getAnnotations($p_array) {
		//param0: prefixed title
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$propertyAnnotations = array();

		$instance = Title::newFromText($p_array[0]);
		$instanceDi = SMWDIWikiPage::newFromTitle($instance);

		$properties = smwfGetStore()->getProperties($instanceDi, $reqfilter, true);

		$ts = TSNamespaces::getInstance();
		$instanceListElement = new InstanceListElement($instance,  $ts->getFullURI($instance), NULL);
			
		foreach($properties as $property) {
			if (!$property->isShown() || !$property->isUserDefined()) continue;
			$values = smwfGetStore()->getPropertyValues($instanceDi, $property, $reqfilter, '', true);
			$values_tuple = array();
			foreach($values as $di) {
				$dv = SMWDataValueFactory::newDataItemValue($di, null);
				$values_tuple[] = array($dv, NULL);
			}
			$propertyElement = new PropertySchemaElement($property, NULL, NULL);
			$propertyAnnotations[] = new Annotation($propertyElement, $values_tuple);
		}

		return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($propertyAnnotations, $instanceListElement);

	}

	public function getProperties($p_array) {
		//param0: category name
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$cat = Title::newFromText($p_array[0], NS_CATEGORY);
		$onlyDirect = $p_array[1] == "true";
		$domainOrRange = $p_array[2];
		$domainOrRange = $domainOrRange == "domain" ? SMW_SSP_HAS_DOMAIN : SMW_SSP_HAS_RANGE;

		$properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($cat, $onlyDirect, $domainOrRange, $reqfilter, $this->bundleID);

		$propertySchemaElement = array();
		foreach($properties as $p) {
			$schemaData = new SchemaData($p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7] == true);
			$propertySchemaElement[] = new PropertySchemaElement(SMWDIProperty::newFromUserLabel($p[0]->getText()), NULL, $schemaData);
		}

		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyList($propertySchemaElement);

	}

	public function getRootProperties($p_array) {
		// param0 : limit
		// param1 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  isset($p_array[0]) ? intval($p_array[0]) : SMWH_OB_DEFAULT_PARTITION_SIZE;
		$partitionNum = isset($p_array[1]) ? intval($p_array[1]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;

		$rootatts = smwfGetSemanticStore()->getRootProperties($reqfilter, $this->bundleID);
		$resourceAttachments = array();
		wfRunHooks('smw_ob_attachtoresource', array($rootatts, & $resourceAttachments, SMW_NS_PROPERTY));

		$ts = TSNamespaces::getInstance();
		$propertyTreeElements = array();
		foreach ($rootatts as $rc) {
			list($t, $isLeaf) = $rc;
			$propertyTreeElements[] = new PropertyTreeElement($t, NULL, $isLeaf);
		}

		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($propertyTreeElements, $resourceAttachments, $reqfilter->limit, $partitionNum, true);
	}

	public function getSubProperties($p_array) {
		// param0 : attribute
		// param1 : limit
		// param2 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  intval($p_array[1]);
		$partitionNum = isset($p_array[2]) ? intval($p_array[2]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$superatt = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
			
		$directsubatts = smwfGetSemanticStore()->getDirectSubProperties($superatt, $reqfilter, $this->bundleID);
		$resourceAttachments = array();
		wfRunHooks('smw_ob_attachtoresource', array($directsubatts, & $resourceAttachments, SMW_NS_PROPERTY));
		$ts = TSNamespaces::getInstance();
		$propertyTreeElements = array();
		foreach ($directsubatts as $rc) {
			list($t, $isLeaf) = $rc;
			$propertyTreeElements[] = new PropertyTreeElement($t, NULL, $isLeaf);
		}
		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($propertyTreeElements, $resourceAttachments, $reqfilter->limit, $partitionNum, false);

	}

	public function getInstancesUsingProperty($p_array) {
		// param0 : property
		// param1 : limit
		// param2 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  intval($p_array[1]);
		$partitionNum = isset($p_array[2]) ? intval($p_array[2]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$prop = Title::newFromText($p_array[0], SMW_NS_PROPERTY);

		if (smwf_om_userCan($p_array[0], 'propertyread', SMW_NS_PROPERTY) === "true") {
			$attinstances = smwfGetStore()->getAllPropertySubjects(SMWDIProperty::newFromUserLabel($prop->getDBkey()),  $reqfilter, true);
		} else {
			$attinstances = array();
		}

		$results = array();
		foreach($attinstances as $i) {
			$results[] = new InstanceListElement($i->getTitle(), NULL, NULL);
		}

		$propertyName_xml = str_replace( array('"'),array('&quot;'),$prop->getDBkey());
		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition(count($attinstances), $results, $reqfilter->limit, $partitionNum, 'getInstancesUsingProperty,'.$propertyName_xml);
	}

	public function getInstanceUsingPropertyValue($p_array) {
		// param0 : property
		// param1 : limit
		// param2 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  intval($p_array[2]);
		$partitionNum = isset($p_array[3]) ? intval($p_array[3]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$prop = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
		$property = SMWDIProperty::newFromUserLabel($prop->getDBkey());
		$value = SMWDataValueFactory::newPropertyObjectValue($property, $p_array[1]);

		if (smwf_om_userCan($p_array[0], 'propertyread', SMW_NS_PROPERTY) === "true") {
			$attinstances = smwfGetStore()->getPropertySubjects($property, $value, $reqfilter, true);
		} else {
			$attinstances = array();
		}


		$results = array();
		foreach($attinstances as $i) {
			$results[] = new InstanceListElement($i->getTitle(), NULL, NULL);
		}

		$propertyName_xml = str_replace( array('"'),array('&quot;'),$prop->getDBkey());
		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition(count($attinstances), $results, $reqfilter->limit, $partitionNum, 'getInstanceUsingPropertyValue,'.$propertyName_xml);
	}

	public function getPropertyValues($p_array) {
		// param0 : property
		// param1 : limit
		// param2 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  intval($p_array[1]);
		$partitionNum = isset($p_array[2]) ? intval($p_array[2]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$prop = Title::newFromText($p_array[0], SMW_NS_PROPERTY);

		if (smwf_om_userCan($p_array[0], 'propertyread', SMW_NS_PROPERTY) === "true") {
			$property = SMWDIProperty::newFromUserLabel($prop->getDBkey());
			$attvalues = smwfGetStore()->getPropertyValues(NULL, $property ,  $reqfilter, '', true);

		} else {
			$attvalues = array();
		}


		$propertyAnnotations = array();
		foreach($attvalues as $di) {
			$dv = SMWDataValueFactory::newDataItemValue($di, null);
			$attvalues_tuple = array();
			$attvalues_tuple[] = array($dv, NULL);
			$propertyElement = new PropertySchemaElement($property, NULL, NULL);
			$propertyAnnotations[] = new Annotation($propertyElement, $attvalues_tuple);
		}
			
		return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($propertyAnnotations, NULL);
	}

	public function getCategoryForInstance($p_array) {

		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$instanceTitle = Title::newFromText($p_array[0]);
		return $this->filterForCategoriesWithInstance($instanceTitle, $reqfilter);
	}

	public function getCategoryForProperty($p_array) {

		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$propertyTitle = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
		return $this->filterForCategoriesWithProperty($propertyTitle, $reqfilter);
	}
	public function filterBrowse($p_array) {

		$type = $p_array[0];
		$hint = explode(" ", $p_array[1]);

		if ($type == 'category') {
			return $this->filterForCategories($hint);
		} else if ($type == 'instance') {
			return $this->filterForInstances($hint);
		} else if ($type == 'propertyTree') {
			return $this->filterForPropertyTree($hint);
		} else if ($type == 'property') {
			return $this->filterForProperties($hint);
		}
	}

	/**
	 * Filters for categories containg the given hint as substring (case-insensitive)
	 * Returns the category tree from root to this found entities as xml string.
	 *
	 * @return xml string (category tree)
	 */
	protected function filterForCategories($categoryHints) {

		$reqfilter = new SMWAdvRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->disjunctiveStrings = true;
		if (count($categoryHints) == 0) {
			return "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_categories')."\"/>";
		}

		foreach($categoryHints as $hint) {
			$reqfilter->addStringCondition($hint, SMWStringCondition::STRCOND_MID);
		}
		$reqfilter->isCaseSensitive = false;


		$foundCategories = smwfGetSemanticStore()->getPages(array(NS_CATEGORY), $reqfilter, true, $this->bundleID);

		return $this->getCategoryTree($foundCategories);
	}

	/**
	 * Returns the category tree of all categories the given article is instance of.
	 *
	 * @param $articleTitle article title
	 * @return xml string (category tree)
	 */
	protected function filterForCategoriesWithInstance(Title $articleTitle, $reqfilter) {

		$categories = smwfGetSemanticStore()->getCategoriesForInstance($articleTitle, $reqfilter, $this->bundleID);
		return $this->getCategoryTree($categories);
	}

	/**
	 * Returns the category tree of all categories the given property has a domain of.
	 *
	 * @param $propertyTitle property title
	 * @return xml string (category tree)
	 */
	protected function filterForCategoriesWithProperty(Title $propertyTitle, $reqfilter) {

		$categories = smwfGetSemanticStore()->getDomainCategories($propertyTitle, $reqfilter, $this->bundleID);
		return $this->getCategoryTree($categories);
	}

	/**
	 * Filters for instances containg the given hint as substring (case-insensitive)
	 * Returns an instance list with all entities found
	 *
	 * @return xml string
	 */
	protected function filterForInstances($instanceHints) {
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;

		if (count($instanceHints) == 0) {
			return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition(0, array(), $reqfilter->limit, 0);
		}
		foreach($instanceHints as $hint) {
			$reqfilter->addStringCondition($hint, SMWStringCondition::STRCOND_MID);
		}

		$reqfilter->isCaseSensitive = false;
		$foundInstances = smwfGetSemanticStore()->getPages(array(-NS_CATEGORY), $reqfilter, true);

		$result = "";
		$id = uniqid (rand());
		$count = 0;
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();


		$results = array();
		foreach($foundInstances as $i) {
			$results[] = new InstanceListElement($i, NULL, NULL);
		}

		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition(count($foundInstances), $results, $reqfilter->limit, 0);

	}


	/**
	 * Filters for attribute containg the given hint as substring (case-insensitive)
	 * Returns the attribute tree from root to this found entities as xml string.
	 *
	 * @return xml string (attribute tree)
	 */
	protected function filterForPropertyTree($propertyHints) {
		$reqfilter = new SMWAdvRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->disjunctiveStrings = true;

		if (count($propertyHints) == 0) {
			return "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_attributes')."\"/>";
		}
		foreach($propertyHints as $hint) {
			$reqfilter->addStringCondition($hint, SMWStringCondition::STRCOND_MID);
		}

		$reqfilter->isCaseSensitive = false;


		$foundAttributes = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY), $reqfilter, false, $this->bundleID);


		// create root object
		$root = new XMLTreeObject(null);

		// get all paths to the root
		$allPaths = array();
		$visitedNodes = array();
		foreach($foundAttributes as $cat) {
			$init_path = array();
			$this->getAllPropertyPaths($cat, $init_path, $allPaths, $visitedNodes);
		}

		// reverse paths
		$reversedPaths = array();
		foreach($allPaths as $path) {
			$reversedPaths[] = array_reverse($path);
		}
			
		// build tree of XMLTreeObjects
		foreach($reversedPaths as $path) {
			$node = $root;
			$nodeIndex=0;
			foreach($path as $p) {
				$hasChild = $nodeIndex < count($path)-1 ? "true" : count(smwfGetSemanticStore()->getDirectSubProperties($p)) > 0;
				$node = $node->addChild($p, $hasChild);
				$nodeIndex++;
			}
		}

		// sort first level
		$root->sortChildren();

		// serialize tree as XML
		$serializedXML = $root->serializeAsXML('propertyTreeElement');
		return $serializedXML == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_attributes')."\"/>" : '<result>'.$serializedXML.'</result>';
			
	}



	/**
	 * Filters for properties containg the given hint as substring (case-insensitive)
	 * Returns an property list with all entities found
	 *
	 * @return xml string
	 */
	protected function filterForProperties($propertyHints) {
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		//$reqfilter->limit = MAX_RESULTS;

		if (count($propertyHints) == 0) {
			return "<propertyList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_properties')."\"/>";
		}

		foreach($propertyHints as $hint) {
			$reqfilter->addStringCondition($hint, SMWStringCondition::STRCOND_MID);
		}

		$reqfilter->isCaseSensitive = false;
		$foundProperties = smwfGetSemanticStore()->getPropertiesWithSchemaByName($reqfilter);
		$propertySchemaElement = array();
		foreach($foundProperties as $p) {
			$schemaData = new SchemaData($p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7] == true);
			$propertySchemaElement[] = new PropertySchemaElement(SMWDIProperty::newFromUserLabel($p[0]->getText()), NULL, $schemaData);
		}
		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyList($propertySchemaElement);
	}

	/**
	 * Returns the category tree for the given array of categories as XML
	 * @return xml
	 */
	protected function getCategoryTree($categories) {
		// create root object
		$root = new XMLTreeObject(null);

		// get all paths to the root
		$allPaths = array();
		$vistedNodes = array(); // used internally to prevent infinite cycles
		foreach($categories as $cat) {
			$init_path = array();
			$this->getAllCategoryPaths($cat, $init_path, $allPaths, $vistedNodes);
		}

		// reverse paths
		$reversedPaths = array();
		foreach($allPaths as $path) {
			$reversedPaths[] = array_reverse($path);
		}
			
		// build tree of XMLTreeObjects
		foreach($reversedPaths as $path) {
			$node = $root;
			$nodeIndex = 0;
			foreach($path as $c) {
				$hasChild = $nodeIndex < count($path)-1 ? "true" : count(smwfGetSemanticStore()->getDirectSubCategories($c)) > 0;
				$node = $node->addChild($c, $hasChild);
				$nodeIndex++;
			}
		}

		// sort first level
		$root->sortChildren();

		// serialize tree as XML
		$serializedXML = $root->serializeAsXML('conceptTreeElement');
		return $serializedXML == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_categories')."\"/>"  : '<result>'.$serializedXML.'</result>';
	}


	/**
	 * Detrmines all category paths from root to the given entity.
	 * May be more than one in case of multiple inheritance.
	 *
	 * @param $cat The category to determine path for
	 * @param $path Must be an empty array
	 * @param $allPaths Must be an empty array
	 */
	protected function getAllCategoryPaths($cat, & $path, & $allPaths, & $visitedNodes) {
		$path[] = $cat;
		$superCats = smwfGetSemanticStore()->getDirectSuperCategories($cat);
		array_push($visitedNodes, $cat->getDBkey());
		$cycleFound = false;
		foreach($superCats as $superCat) {
			if (in_array($superCat->getDBkey(), $visitedNodes)) {
				$cycleFound = true;
				break;
			}
			$cloneOfPath = array_clone($path);
			$this->getAllCategoryPaths($superCat, $cloneOfPath, $allPaths, $visitedNodes);
		}
		if (count($superCats) == 0 || $cycleFound) $allPaths[] = $path;
		array_pop($visitedNodes);
	}

	/**
	 * Detrmines all attribute paths from root to the given entity.
	 * May be more than one in case of multiple inheritance.
	 *
	 * @param $cat The category to determine path for
	 * @param $path Must be an empty array
	 * @param $allPaths Must be an empty array
	 */
	protected function getAllPropertyPaths($att, & $path, & $allPaths, & $visitedNodes) {
		$path[] = $att;
		$superProps = smwfGetSemanticStore()->getDirectSuperProperties($att);
		array_push($visitedNodes, $att->getDBkey());
		$cycleFound = false;
		foreach($superProps as $superProp) {
			if (in_array($superProp->getDBkey(), $visitedNodes)) {
				$cycleFound = true;
				break;
			}
			$cloneOfPath = array_clone($path);
			$this->getAllPropertyPaths($superProp, $cloneOfPath, $allPaths, $visitedNodes);
		}
		if (count($superProps) == 0 || $cycleFound) $allPaths[] = $path;
		array_pop($visitedNodes);
	}

	protected function createErrorMessage($errorCode, $errorMessage) {
		return "<?xml version='1.0' encoding='UTF-8'?><errorMessage>".
		"<errorCode>$errorCode</errorCode>".
		"<errorMessage>$errorMessage</errorMessage>".
		"</errorMessage>";
	}

}





function smwf_ob_OntologyBrowserAccess($method, $params, $dataSource = '', $bundleID = '') {

	$browseWiki = wfMsg("smw_ob_source_wiki");
	global $smwgHaloQuadMode, $smwgHaloWebserviceEndpoint;
	if (isset($smwgHaloWebserviceEndpoint) && $smwgHaloQuadMode && !empty($dataSource) && $dataSource != $browseWiki) {
		// dataspace parameter. so assume quad driver is installed
		$storage = new OB_StorageTSQuad($dataSource, $bundleID);
	} else if (isset($smwgHaloWebserviceEndpoint)) {
		// assume normal (non-quad) TSC is running
		$storage = new OB_StorageTS($dataSource, $bundleID);
	} else {
		// no TSC installed
		$storage = new OB_Storage($dataSource, $bundleID);
	}


	$p_array = explode("##", $params);
	$method = new ReflectionMethod(get_class($storage), $method);
	return $method->invoke($storage, $p_array, $dataSource);

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
 * Represents OntologyBrowser category tree element (1st column)
 *
 *@author kuehn
 *
 */
class CategoryTreeElement {

	/*
	 * Title
	 * MW Title object
	 */
	private $title;

	/*
	 * string
	 * TSC URI
	 */
	private $uri;

	/* string
	 * MW URL
	 */
	private $url;

	/*
	 * boolean
	 * Leaf category or not.
	 */
	private $leaf;

	public function __construct($title, $uri, $isLeaf = false) {
		$this->title = $title;
		$this->uri = $uri;
		$this->url = $title->getFullURL();
		$this->isLeaf = $isLeaf;
	}



	public function getTitle() {
		return $this->title;
	}
	public function getURI() {
		return $this->uri;
	}
	public function getURL() {
		return $this->url;
	}
	public function isLeaf() {
		return $this->isLeaf;
	}
}

/**
 * Represents OntologyBrowser instance list element (2nd column)
 *
 *@author kuehn
 *
 */
class InstanceListElement {

	/*
	 * Title
	 * MW Title object
	 */
	private $title;

	/*
	 * string
	 * TSC URI
	 */
	private $uri;

	/* string
	 * MW URL
	 */
	private $url;

	/*
	 * CategoryTreeElement
	 * member category
	 */
	private $categoryTreeElements;


	private $metadata;

	public function __construct($title, $uri, $metadata = NULL) {
		$this->title = $title;
		$this->uri = $uri;
		$this->url = $title->getFullURL();
		$this->categoryTreeElements = array();
		$this->metadata = $metadata;
	}


	public function getTitle() {
		return $this->title;
	}
	public function getURI() {
		return $this->uri;
	}
	public function getURL() {
		return $this->url;
	}

	public function addCategoryTreeElement($categoryTreeElement) {
		$this->categoryTreeElements[] = $categoryTreeElement;
	}
	public function getCategoryTreeElements() {
		return $this->categoryTreeElements;
	}
	public function getMetadata() {
		return $this->metadata;
	}
}

/**
 * Represents OntologyBrowser property tree element (1st column)
 *
 *@author kuehn
 *
 */
class PropertyTreeElement {

	/*
	 * Title
	 * MW Title object
	 */
	private $title;

	/*
	 * string
	 * TSC URI
	 */
	private $uri;

	/* string
	 * MW URL
	 */
	private $url;

	/*
	 * boolean
	 * Leaf category or not.
	 */
	private $leaf;

	public function __construct($title, $uri, $isLeaf) {
		$this->title = $title;
		$this->uri = $uri;
		$this->url = $title->getFullURL();
		$this->isLeaf = $isLeaf;
	}



	public function getTitle() {
		return $this->title;
	}
	public function getURI() {
		return $this->uri;
	}
	public function getURL() {
		return $this->url;
	}
	public function isLeaf() {
		return $this->isLeaf;
	}
}

/**
 * Represents OntologyBrowser schema data of a property (3rd column)
 *
 *@author kuehn
 *
 */
class SchemaData {
	/*
	 * Property Title
	 * MW Title object
	 */
	private $title;
	private $min_card;
	private $max_card;
	private $type;
	private $is_sym;
	private $is_trans;
	private $range;
	private $inherited;

	public function __construct($title, $min_card, $max_card, $type, $is_sym, $is_trans, $range, $inherited) {
		$this->title = $title;
		$this->min_card = $min_card;
		$this->max_card = $max_card;
		$this->type = $type;
		$this->is_sym = $is_sym;
		$this->is_trans = $is_trans;
		$this->range = $range;
		$this->inherited = $inherited;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getMinCard() {
		return $this->min_card;
	}
	public function getMaxCard() {
		return $this->max_card;
	}
	public function getType() {
		return $this->type;
	}
	public function isSymetrical() {
		return $this->is_sym;
	}
	public function isTransitive() {
		return $this->is_trans;
	}
	public function getRange() {
		return $this->range;
	}
	public function isInherited() {
		return $this->inherited;
	}
}

/**
 * Represents a OntologyBrowser property (3rd column)
 *
 * A property with schema information (when clicking on a category)
 * A property as part of an annotation.
 *
 * @author kuehn
 *
 */
class PropertySchemaElement {

	/*
	 * SMWDIProperty
	 * property
	 */
	private $property;

	/*
	 * string
	 * TSC URI
	 */
	private $uri;

	/* string
	 * MW URL
	 */
	private $url;

	/*
	 * SchemaData
	 *
	 */
	private $schemadata;

	public function __construct($property, $uri, $schemadata = NULL) {
		$this->property = $property;
		$this->uri = $uri;
		$this->url = $property->getDiWikiPage()->getTitle()->getFullURL();
		$this->schemadata = $schemadata;
	}



	public function getPropertyValue() {
		return $this->property;
	}

	public function getPropertyTitle() {
		return $this->property->getDiWikiPage()->getTitle();
	}

	public function getURI() {
		return $this->uri;
	}
	public function getURL() {
		return $this->url;
	}
	public function getSchemaData() {
		return $this->schemadata;
	}
}

/**
 * Represents a OntologyBrowser annotation (3rd column)
 *
 * @author kuehn
 *
 */
class Annotation {

	/*
	 * PropertySchemaElement
	 * (schema data is usually null here)
	 */
	private $property;

	/*
	 * array of SMWDataValue
	 */
	private $smw_values;
	private $smw_inferredvalues;

	public function __construct($property, $smw_values, $smw_inferredvalues =  array()) {

		$this->property = $property;
		$this->smw_values = $smw_values;
		$this->smw_inferredvalues = $smw_inferredvalues;
	}


	public function getProperty() {
		return $this->property;
	}
	public function getValues() {
		return $this->smw_values;
	}

	public function getInferredValues() {
		return $this->smw_inferredvalues;
	}

}


/**
 * Makes a shallow copy of the given source array.
 */
function array_clone(& $src) {
	$dst = array();
	foreach($src as $e) {
		$dst[] = $e;
	}
	return $dst;
}
