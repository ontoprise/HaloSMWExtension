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



class OB_Storage {
	public function getRootCategories($p_array) {
		// param0 : limit
		// param1 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->limit =  intval($p_array[0]);
		$reqfilter->sort = true;
		$reqfilter->offset = intval($p_array[1])*$reqfilter->limit;
		$rootcats = smwfGetSemanticStore()->getRootCategories($reqfilter);
		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($rootcats, $p_array[0] + 0, $p_array[1] + 0, true);
	}

	public function getSubCategory($p_array) {
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

	}

	public function getInstance($p_array) {
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

	}

	public function getAnnotations($p_array) {
		//param0: prefixed title
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$propertyAnnotations = array();

		$instance = Title::newFromText($p_array[0]);

		$properties = smwfGetStore()->getProperties($instance, $reqfilter);
		foreach($properties as $a) {
			if (!$a->isShown() || !$a->isVisible()) continue;
			$values = smwfGetStore()->getPropertyValues($instance, $a);
			$propertyAnnotations[] = array($a, $values);
		}


		return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($propertyAnnotations, $instance);

	}

	public function getProperties($p_array) {
		//param0: category name
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$cat = Title::newFromText($p_array[0], NS_CATEGORY);
		$onlyDirect = $p_array[1] == "true";
		$properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($cat, $onlyDirect, $reqfilter);

		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyList($properties);

	}

	public function getRootProperties($p_array) {
		// param0 : limit
		// param1 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  intval($p_array[0]);
		$reqfilter->offset = intval($p_array[1])*$reqfilter->limit;
		$rootatts = smwfGetSemanticStore()->getRootProperties($reqfilter);

		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($rootatts, $p_array[0] + 0, $p_array[1] + 0, true);
	}

	public function getSubProperties($p_array) {
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

	}

	public function getInstancesUsingProperty($p_array) {
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
	}

	public function getCategoryForInstance($p_array) {
		$browserFilter = new SMWOntologyBrowserFilter();
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$instanceTitle = Title::newFromText($p_array[0]);
		return $browserFilter->filterForCategoriesWithInstance($instanceTitle, $reqfilter);
	}

	public function getCategoryForProperty($p_array) {
		$browserFilter = new SMWOntologyBrowserFilter();
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$propertyTitle = Title::newFromText($p_array[0], SMW_NS_PROPERTY);
		return $browserFilter->filterForCategoriesWithProperty($propertyTitle, $reqfilter);
	}
	public function filterBrowse($p_array) {
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


class OB_StorageTS extends OB_Storage {

	private $tsNamespaceHelper;

	public function __construct() {
		$this->tsNamespaceHelper = new TSNamespaces(); // initialize namespaces
	}

	public function getInstance($p_array) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion;
		if (!isset($smwgDeployVersion) || !$smwgDeployVersion) ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
		$client = new SoapClient("$wgServer$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_sparql", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));

		try {
			global $smwgTripleStoreGraph;

			$categoryName = $p_array[0];
			$limit =  intval($p_array[1]);
			$partition =  intval($p_array[2]);
			$offset = $partition * $limit;

			// query
			$response = $client->query("[[Category:$categoryName]]", $smwgTripleStoreGraph, "?Category|limit=$limit|offset=$offset");

			global $smwgSPARQLResultEncoding;
			// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
			// another charset.
			if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
				$response = utf8_decode($response);
			}

		 $dom = simplexml_load_string($response);

		 $titles = array();
		 $results = $dom->xpath('//result');
		 foreach ($results as $r) {

		 	$children = $r->children(); // binding nodes
		 	$b = $children->binding[0]; // instance
		 	 
		 	$sv = $b->children()->uri[0];
		 	$instance = $this->getTitleFromURI((string) $sv);

		 	$categories = array();
		 	$b = $children->binding[1]; // categories
		 	 
		 	foreach($b->children()->uri as $sv) {
		 		$category = $this->getTitleFromURI((string) $sv);
		 		if (!is_null($category)) {
		 			$titles[] = array($instance, $this->getTitleFromURI((string) $sv));
		 		} else {
		 			$titles[] = $instance;
		 		}
		 	}

		 	 
		 }


		} catch(Exception $e) {
			return "Internal error: ".$e->getMessage();
		}

		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($titles, $limit, $partition);
	}

	private function getTitleFromURI($sv) {

		foreach (TSNamespaces::$ALL_NAMESPACES as $nsIndsex => $ns) {
			if (stripos($sv, $ns) === 0) {
				$local = substr($sv, strlen($ns));
				return Title::newFromText($local, $nsIndsex);
					
			}
		}

			

		// result with unknown namespace
		if (stripos($sv, TSNamespaces::$UNKNOWN_NS) === 0) {


			$startNS = strlen(TSNamespaces::$UNKNOWN_NS);
			$length = strpos($sv, "#") - $startNS;
			$ns = intval(substr($sv, $startNS, $length));

			$local = substr($sv, strpos($sv, "#")+1);

			return Title::newFromText($local, $ns);



		}

		return NULL;
	}


	private function getLiteral($literal) {
		list($literalValue, $literalType) = $literal;
		if (!empty($literalValue)) {

			// create SMWDataValue either by property or if that is not possible by the given XSD type
			if ($property instanceof SMWPropertyValue ) {
				$value = SMWDataValueFactory::newPropertyObjectValue($prs->getData(), $literalValue);
			} else {
				$value = SMWDataValueFactory::newTypeIDValue(WikiTypeToXSD::getWikiType($literalType));
			}
			if ($value->getTypeID() == '_dat') { // exception for dateTime
				if ($literalValue != '') $value->setXSDValue($$literalValue);
			} if ($value->getTypeID() == '_ema') { // exception for email
				$value->setXSDValue($literalValue);
			} else {
				$value->setUserValue($literalValue);
			}
		} else {
			$property = $prs->getData();
			if ($property instanceof SMWPropertyValue ) {
				$value = SMWDataValueFactory::newPropertyObjectValue($property);
			} else {
				$value = SMWDataValueFactory::newTypeIDValue('_wpg');

			}

		}
		return $value;
	}


	public function getAnnotations($p_array) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion;
		if (!isset($smwgDeployVersion) || !$smwgDeployVersion) ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
		$client = new SoapClient("$wgServer$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_sparql", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));

		try {
			global $smwgTripleStoreGraph;

			$instanceName = $p_array[0];
			$instance = Title::newFromText($instanceName);
			$instanceName = $instance->getDBkey();
			
			$limit =  intval($p_array[1]);
			$partition =  intval($p_array[2]);
			$offset = $partition * $limit;

			// query
			$nsPrefix = $this->tsNamespaceHelper->getNSPrefix($instance->getNamespace());
			$response = $client->query("SELECT ?p ?o WHERE { <$smwgTripleStoreGraph/$nsPrefix#$instanceName> ?p ?o. }", $smwgTripleStoreGraph, "limit=$limit|offset=$offset");

			global $smwgSPARQLResultEncoding;
			// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
			// another charset.
			if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
				$response = utf8_decode($response);
			}
            //echo print_r($response, true);die();
			$dom = simplexml_load_string($response);

			$annotations = array();
			$results = $dom->xpath('//result');
			foreach ($results as $r) {

				$children = $r->children(); // binding nodes
				$b = $children->binding[0]; // predicate

				$sv = $b->children()->uri[0];
				$title = $this->getTitleFromURI((string) $sv);
				if (is_null($title)) continue;
				$predicate = SMWPropertyValue::makeUserProperty($title->getText());

				$categories = array();
				$b = $children->binding[1]; // categories
				$values = array();
				foreach($b->children()->uri as $sv) {
					$object = $this->getTitleFromURI((string) $sv);
					$value = SMWDataValueFactory::newPropertyObjectValue($predicate, $object);
					$values[] = $value;
						
				}
				foreach($b->children()->literal as $sv) {
					$literal = array((string) $sv, $sv->attributes()->datatype);
					$value = $this->getLiteral($literal);
					$values[] = $value;
				}


				$annotations[] = array($predicate, $values);	
			}


		} catch(Exception $e) {
			return "Internal error: ".$e->getMessage();
		}

		return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($annotations, $instance);

	}
}

function smwf_ob_OntologyBrowserAccess($method, $params) {
	$storage = new OB_Storage();
	$p_array = explode("##", $params);
	$method = new ReflectionMethod(get_class($storage), $method);
	return $method->invoke($storage, $p_array);

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

class TSNamespaces {

	public static $CAT_NS;
	public static $PROP_NS;
	public static $INST_NS;
	public static $TYPE_NS;
	public static $IMAGE_NS;
	public static $HELP_NS;
	public static $TEMPLATE_NS;
	public static $USER_NS;
	public static $UNKNOWN_NS;

	public static $ALL_NAMESPACES;

	// general namespace suffixes for different namespaces
	public static $CAT_NS_SUFFIX = "/category#";
	public static $PROP_NS_SUFFIX = "/property#";
	public static $INST_NS_SUFFIX = "/a#";
	public static $TYPE_NS_SUFFIX = "/type#";
	public static $IMAGE_NS_SUFFIX = "/image#";
	public static $HELP_NS_SUFFIX = "/help#";
	public static $TEMPLATE_NS_SUFFIX = "/template#";
	public static $USER_NS_SUFFIX = "/user#";
	public static $UNKNOWN_NS_SUFFIX = "/ns_"; // only fragment. # is missing!

	function __construct() {
		global $smwgTripleStoreGraph;
		self::$CAT_NS = $smwgTripleStoreGraph.self::$CAT_NS_SUFFIX;
		self::$PROP_NS = $smwgTripleStoreGraph.self::$PROP_NS_SUFFIX;
		self::$INST_NS = $smwgTripleStoreGraph.self::$INST_NS_SUFFIX;
		self::$TYPE_NS = $smwgTripleStoreGraph.self::$TYPE_NS_SUFFIX;
		self::$IMAGE_NS = $smwgTripleStoreGraph.self::$IMAGE_NS_SUFFIX;
		self::$HELP_NS = $smwgTripleStoreGraph.self::$HELP_NS_SUFFIX;
		self::$TEMPLATE_NS = $smwgTripleStoreGraph.self::$TEMPLATE_NS_SUFFIX;
		self::$USER_NS = $smwgTripleStoreGraph.self::$USER_NS_SUFFIX;
		self::$UNKNOWN_NS = $smwgTripleStoreGraph.self::$UNKNOWN_NS_SUFFIX;

		self::$ALL_NAMESPACES = array(NS_MAIN=>self::$INST_NS, NS_CATEGORY => self::$CAT_NS, SMW_NS_PROPERTY => self::$PROP_NS,
		SMW_NS_TYPE => self::$TYPE_NS_SUFFIX, NS_IMAGE => self::$IMAGE_NS, NS_HELP => self::$HELP_NS, NS_TEMPLATE => self::$TEMPLATE_NS,
		NS_USER => self::$USER_NS);
	}

	public function getNSPrefix($namespace) {
		if ($namespace == SMW_NS_PROPERTY) return "prop";
		elseif ($namespace == NS_CATEGORY) return "cat";
		elseif ($namespace == NS_MAIN) return "a";
		elseif ($namespace == SMW_NS_TYPE) return "type";
		elseif ($namespace == NS_IMAGE) return "image";
		elseif ($namespace == NS_TEMPLATE) return "template";
		elseif ($namespace == NS_USER) return "user";
		elseif ($namespace == NS_HELP) return "help";
		else return "ns_$namespace";
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


