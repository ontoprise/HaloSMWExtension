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
require_once("SMW_OntologyBrowserFilter.php" );
require_once("$smwgHaloIP/includes/SMW_OntologyManipulator.php");
require_once( "$smwgHaloIP/includes/storage/SMW_TS_Helper.php" );


class OB_Storage {
	
	protected $dataSource;
	
	public function __construct($dataSource = null) {
		$this->dataSource = $dataSource;
	}
	
	
	public function getRootCategories($p_array) {
		// param0 : limit
		// param1 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->limit =  intval($p_array[0]);
		$reqfilter->sort = true;
		$partitionNum = isset($p_array[1]) ? intval($p_array[1]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$rootcats = smwfGetSemanticStore()->getRootCategories($reqfilter);
		$resourceAttachments = array();
		wfRunHooks('smw_ob_attachtoresource', array($rootcats, & $resourceAttachments, NS_CATEGORY));
		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($rootcats, $resourceAttachments, $reqfilter->limit, $partitionNum, true);
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
		$directsubcats = smwfGetSemanticStore()->getDirectSubCategories($supercat, $reqfilter);
		$resourceAttachments = array();
		wfRunHooks('smw_ob_attachtoresource', array($rootcats, & $resourceAttachments, NS_CATEGORY));
		return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($directsubcats, $resourceAttachments, $reqfilter->limit, $partitionNum, false);

	}

	public function getInstance($p_array) {
		// param0 : category
		// param1 : limit
		// param2 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  intval($p_array[1]);
		$partitionNum = isset($p_array[2]) ? intval($p_array[2]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$cat = Title::newFromText($p_array[0], NS_CATEGORY);
		$instances = smwfGetSemanticStore()->getAllInstances($cat,  $reqfilter);

		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($instances, $reqfilter->limit, $partitionNum);

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
		$dIndex = $p_array[2];
		$properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($cat, $onlyDirect, $dIndex, $reqfilter);

		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyList($properties);

	}

	public function getRootProperties($p_array) {
		// param0 : limit
		// param1 : partitionNum
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->limit =  isset($p_array[0]) ? intval($p_array[0]) : SMWH_OB_DEFAULT_PARTITION_SIZE;
		$partitionNum = isset($p_array[1]) ? intval($p_array[1]) : 0;
		$reqfilter->offset = $partitionNum*$reqfilter->limit;
		$rootatts = smwfGetSemanticStore()->getRootProperties($reqfilter);

		$resourceAttachments = array();
		wfRunHooks('smw_ob_attachtoresource', array($rootatts, & $resourceAttachments, SMW_NS_PROPERTY));

		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($rootatts, $resourceAttachments, $reqfilter->limit, $partitionNum, true);
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
		$directsubatts = smwfGetSemanticStore()->getDirectSubProperties($superatt, $reqfilter);
		$resourceAttachments = array();
		wfRunHooks('smw_ob_attachtoresource', $directsubatts, $resourceAttachments, SMW_NS_PROPERTY);
		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($directsubatts, $resourceAttachments, $reqfilter->limit, $partitionNum, false);

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
			$attinstances = smwfGetStore()->getAllPropertySubjects(SMWPropertyValue::makeUserProperty($prop->getDBkey()),  $reqfilter);
		} else {
			$attinstances = array();
		}

		$propertyName_xml = str_replace( array('"'),array('&quot;'),$prop->getDBkey());
		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($attinstances, $reqfilter->limit, $partitionNum, 'getInstancesUsingProperty,'.$propertyName_xml);
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

	public function __construct($dataSource = null) {
		parent::__construct($dataSource);
		$this->tsNamespaceHelper = new TSNamespaces(); // initialize namespaces
	}

	public function getInstance($p_array) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion, $smwgUseLocalhostForWSDL;
		$client = TSConnection::getConnector();
		$client->connect();

		try {
			global $smwgTripleStoreGraph;

			$categoryName = $p_array[0];
			$limit =  intval($p_array[1]);
			$partition =  intval($p_array[2]);
			$offset = $partition * $limit;

			$dataSpace = "";
			if (isset($this->dataSource)) {
				$dataSpace = "|dataspace={$this->dataSource}";
			}
			
			// query
			$response = $client->query("[[Category:$categoryName]]", "?Category|limit=$limit|offset=$offset|merge=false$dataSpace");

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
		 	$sv = str_replace("__", "//", $sv); // XXX: hack for Ultrapedia
		 	$instance = $this->getTitleFromURI((string) $sv);

		 	$categories = array();
		 	$b = $children->binding[1]; // categories
		 	 
		 	foreach($b->children()->uri as $sv) {
		 		$category = $this->getTitleFromURI((string) $sv);
		 		if (!is_null($instance) && !is_null($category)) {
		 			$titles[] = array($instance, $this->getTitleFromURI((string) $sv));
		 		} else  {
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


	private function getLiteral($literal, $predicate) {
		list($literalValue, $literalType) = $literal;
		if (!empty($literalValue)) {

			// create SMWDataValue either by property or if that is not possible by the given XSD type
			if ($predicate instanceof SMWPropertyValue ) {
				$value = SMWDataValueFactory::newPropertyObjectValue($predicate, $literalValue);
			} else {
				$value = SMWDataValueFactory::newTypeIDValue(WikiTypeToXSD::getWikiType($literalType));
			}
			if ($value->getTypeID() == '_dat') { // exception for dateTime
				if ($literalValue != '') $value->setXSDValue(str_replace("-","/", $literalValue));
			} else if ($value->getTypeID() == '_ema') { // exception for email
				$value->setXSDValue($literalValue);
			} else {
				$value->setUserValue($literalValue);
			}
		} else {

			if ($predicate instanceof SMWPropertyValue ) {
				$value = SMWDataValueFactory::newPropertyObjectValue($predicate);
			} else {
				$value = SMWDataValueFactory::newTypeIDValue('_wpg');

			}

		}
		return $value;
	}


	public function getAnnotations($p_array) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion, $smwgUseLocalhostForWSDL;
		$client = TSConnection::getConnector();
		$client->connect();
		try {
			global $smwgTripleStoreGraph, $smwgTripleStoreQuadMode;

			$instanceName = $p_array[0];
			$instance = Title::newFromText($instanceName);
			$instanceName = str_replace("//","__",$instance->getDBkey()); //XXX: hack for ultrapedia

			$limit =  isset($p_array[1]) ? intval($p_array[1]) : SMWH_OB_DEFAULT_PARTITION_SIZE;
			$partition =   isset($p_array[2]) ? intval($p_array[2]) : 0;
			$offset = $partition * $limit;

			$dataSpace = "";
			if (isset($this->dataSource)) {
				$dataSpace = "|dataspace={$this->dataSource}";
			}
			
			// query
			$nsPrefix = $this->tsNamespaceHelper->getNSPrefix($instance->getNamespace());
			if (isset($smwgTripleStoreQuadMode) && $smwgTripleStoreQuadMode == true) {
				$response = $client->query("SELECT ?p ?o WHERE { GRAPH ?g { <$smwgTripleStoreGraph/$nsPrefix#$instanceName> ?p ?o. } }",  "limit=$limit|offset=$offset$dataSpace");
			} else {
				$response = $client->query("SELECT ?p ?o WHERE { <$smwgTripleStoreGraph/$nsPrefix#$instanceName> ?p ?o. }",  "limit=$limit|offset=$offset$dataSpace");
			}

			global $smwgSPARQLResultEncoding;
			// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
			// another charset.
			if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
				$response = utf8_decode($response);
			}
				
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
					$value = $this->getLiteral($literal, $predicate);
					$values[] = $value;
				}


				$annotations[] = array($predicate, $values);
			}


		} catch(Exception $e) {
			return "Internal error: ".$e->getMessage();
		}

		return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($annotations, $instance);

	}

	public function getInstancesUsingProperty($p_array) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion, $smwgUseLocalhostForWSDL;
		$client = TSConnection::getConnector();
		$client->connect();
		try {
			global $smwgTripleStoreGraph;

			$propertyName = $p_array[0];
			$limit =  intval($p_array[1]);
			$partition =  intval($p_array[2]);
			$offset = $partition * $limit;

			$dataSpace = "";
			if (isset($this->dataSource)) {
				$dataSpace = "|dataspace={$this->dataSource}";
			}
			
			// query
			$response = $client->query("[[$propertyName::+]]",  "?Category|limit=$limit|offset=$offset$dataSpace|merge=false");

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
				$sv = str_replace("__", "//", $sv); // XXX: hack for Ultrapedia
				$instance = $this->getTitleFromURI((string) $sv);

				$categories = array();
				$b = $children->binding[1]; // categories
					
				foreach($b->children()->uri as $sv) {
					$category = $this->getTitleFromURI((string) $sv);
					if (!is_null($instance) && !is_null($category)) {
						$titles[] = array($instance, $this->getTitleFromURI((string) $sv));
					} else if (!is_null($instance)) {
						$titles[] = $instance;
					}
				}

					
			}


		} catch(Exception $e) {
			return "Internal error: ".$e->getMessage();
		}

		$propertyName_xml = str_replace( array('"'),array('&quot;'),$propertyName);
		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($titles, $limit, $partition, 'getInstancesUsingProperty,'.$propertyName_xml);
	}

	public function getCategoryForInstance($p_array) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion, $smwgUseLocalhostForWSDL;
		$client = TSConnection::getConnector();
		$client->connect();
		try {
			global $smwgTripleStoreGraph;

			$instanceName = substr($p_array[0],1); // remove leading colon
			$instanceName = str_replace("//","__",$instanceName); //XXX: hack for ultrapedia
			
			$dataSpace = "";
			if (isset($this->dataSource)) {
				$dataSpace = "|dataspace={$this->dataSource}";
			}
			
			// query
			$response = $client->query("[[$instanceName]]", "?Category$dataSpace");

			global $smwgSPARQLResultEncoding;
			// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
			// another charset.
			if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
				$response = utf8_decode($response);
			}

			$dom = simplexml_load_string($response);

			$titles = array();
			$results = $dom->xpath('//result');

			$categories = array();
			foreach ($results as $r) {

				//$children = $r->children(); // binding nodes
				$b = $r->binding[0]; // categories
					
				foreach($b->children()->uri as $sv) {
					$category = $this->getTitleFromURI((string) $sv);
					if (!is_null($category)) {
							
						$categories[] = $category;
					}
				}

					
			}


		} catch(Exception $e) {
			return "Internal error: ".$e->getMessage();
		}
			
		$browserFilter = new SMWOntologyBrowserFilter();
		return $browserFilter->getCategoryTree($categories);
	}

	public function filterBrowse($p_array) {
			
		$browserFilter = new SMWOntologyBrowserFilter();
		$type = $p_array[0];
		$hint = explode(" ", $p_array[1]);
		$hint = smwfEliminateStopWords($hint);
		if ($type != 'instance') return parent::filterBrowse($p_array);

		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion, $smwgUseLocalhostForWSDL;
		$client = TSConnection::getConnector();
		$client->connect();
		try {
			global $smwgTripleStoreGraph, $smwgTripleStoreQuadMode;
			
			$dataSpace = "";
			if (isset($this->dataSource)) {
				$dataSpace = "|dataspace={$this->dataSource}";
			}
			
			//query
			for ($i = 0; $i < count($hint); $i++) {
				$hint[$i] = preg_quote($hint[$i]);
				$hint[$i] = str_replace("\\", "\\\\", $hint[$i]);
			}
			$filter = "";
			if (count($hint) > 0) {
				$filter = "FILTER (";
				for ($i = 0; $i < count($hint); $i++) {
					if ($i > 0) $filter .= " && ";
					$filter .= "regex(str(?s), \"$hint[$i]\", \"i\")";
				}
				$filter .= ")";
			}

			if (isset($smwgTripleStoreQuadMode) && $smwgTripleStoreQuadMode == true) {
				$response = $client->query("SELECT ?s WHERE { GRAPH ?g { ?s ?p ?o. $filter } }",  "limit=1000$dataSpace");
			} else {
				$response = $client->query("SELECT ?s WHERE { ?s ?p ?o.  $filter }",  "limit=1000$dataSpace");
			}

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
				$sv = str_replace("__", "//", $sv); // XXX: hack for Ultrapedia
				$instance = $this->getTitleFromURI((string) $sv);
					
				foreach($b->children()->uri as $sv) {
					if (!is_null($instance)) {
						$titles[] = $instance;
					}
				}

					
			}


		} catch(Exception $e) {
			return "Internal error: ".$e->getMessage();
		}

		// do not show partitions. 1000 instances is maximum here.
		return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($titles, 1001, 0);
	}

}


function smwf_ob_OntologyBrowserAccess($method, $params, $dataSource) {
	global $smwgOBInstanceDataFromTriplestore;
	$browseWiki = wfMsg("smw_ob_source_wiki");
 	$storage = (isset($smwgOBInstanceDataFromTriplestore) 
				 && $smwgOBInstanceDataFromTriplestore === true)
			   ||
			   (isset($dataSource) && $dataSource != $browseWiki)
					? new OB_StorageTS($dataSource) 
					: new OB_Storage($dataSource);
					
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


