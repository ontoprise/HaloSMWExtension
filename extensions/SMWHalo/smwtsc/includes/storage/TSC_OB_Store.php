<?php
global $tscgIP;
require_once( "$tscgIP/includes/triplestore_client/TSC_Helper.php" );

class OB_StorageTS extends OB_Storage {

    private $tsNamespaceHelper;

    public function __construct($dataSource = null, $bundleID = '') {
        parent::__construct($dataSource, $bundleID);
        $this->tsNamespaceHelper = TSNamespaces::getInstance(); // initialize namespaces
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
            $categoryTreeElements[] = new CategoryTreeElement($t, $ts->getFullURI($t), $isLeaf);
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
            $categoryTreeElements[] = new CategoryTreeElement($t, $ts->getFullURI($t), $isLeaf);
        }
        return SMWOntologyBrowserXMLGenerator::encapsulateAsConceptPartition($categoryTreeElements, $resourceAttachments, $reqfilter->limit, $partitionNum, false);

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
            $propertyTreeElements[] = new PropertyTreeElement($t, $ts->getFullURI($t), $isLeaf);
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
            $propertyTreeElements[] = new PropertyTreeElement($t, $ts->getFullURI($t), $isLeaf);
        }
        return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyPartition($propertyTreeElements, $resourceAttachments, $reqfilter->limit, $partitionNum, false);

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

        $ts = TSNamespaces::getInstance();
        $propertySchemaElement = array();
        foreach($properties as $p) {
            $schemaData = new SchemaData($p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7] == true);
            $propertySchemaElement[] = new PropertySchemaElement(SMWDIProperty::newFromUserLabel($p[0]->getText()), $ts->getFullURI($p[0]), $schemaData);
        }

        return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyList($propertySchemaElement);

    }

    public function getInstance($p_array) {

        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();

        try {
            global $smwgHaloTripleStoreGraph;

            $categoryName = $p_array[0];
            $limit =  intval($p_array[1]);
            $partition =  intval($p_array[2]);
            $offset = $partition * $limit;
            $onlyAssertedCategories = $p_array[3] == 'true';
            $metadata = isset($p_array[4]) ? $p_array[4] : false;
            $metadataRequest = $metadata != false ? "|metadata=$metadata" : "";

            $dataSpace = $this->getDataSourceParameters();

            // query
            if ($onlyAssertedCategories) {
                $allSubCategories = smwfGetSemanticStore()->getSubCategories(Title::newFromText($categoryName, NS_CATEGORY));
                $categoryDisjuction = $categoryName;
                foreach($allSubCategories as $sc) {
                    list($c, $isLeaf) = $sc;
                    $categoryDisjuction .= '||'.$c->getText();
                }
                $response = $client->query("[[Category:$categoryDisjuction]]", "?Category|limit=$limit|offset=$offset|merge=false|sort=_X_|infer=false$dataSpace$metadataRequest", $smwgHaloTripleStoreGraph);
            } else {
                $response = $client->query("[[Category:$categoryName]]", "?Category|limit=$limit|offset=$offset|merge=false|sort=_X_$dataSpace$metadataRequest", $smwgHaloTripleStoreGraph);
            }

            $categoryTitle = Title::newFromText($categoryName, NS_CATEGORY);
            $titles = array();
            $numOfresults = $this->parseInstances($response, $titles, $categoryTitle);


        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }

        return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($numOfresults, $titles, $limit, $partition);
    }


    /**
     * Parses an SPARQL-XML result containing instances with their parent categories (including inferred).
     *
     * @param XML $response
     * @param Tuple $titles
     *         ((instanceTitle, $instanceURI, $localInstanceURL, $metadataMap) , ($localCategoryURL, $categoryTitle))
     * @param Title $categoryTitle
     */
    protected function parseInstances($response, &$titles, $categoryTitle) {
        global $smwgSPARQLResultEncoding;
        // PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
        // another charset.
        if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
            $response = utf8_decode($response);
        }


        $dom = simplexml_load_string($response);
        $dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
            
        $results = $dom->xpath('//sparqlxml:result');
        foreach ($results as $r) {

            $children = $r->children(); // binding nodes
            $b = $children->binding[0]; // instance

            $sv = $b->children()->uri[0];
            if (is_null($sv)) $sv = $b->children()->bnode[0];
            if (is_null($sv)) continue;


            $metadataMap = $this->parseMetadata($sv->metadata);
            list($url, $title) = TSHelper::makeLocalURL((string) $sv);
            $instance = new InstanceListElement($title, (string) $sv, $metadataMap);

            $categories = array();
            $assertedCategories = smwfGetSemanticStore()->getCategoriesForInstance($title);

            if (count($children->binding) > 1) {
                // category binding node exists
                $b = $children->binding[1]; // categories
                $inherited = true;
                foreach($b->children()->uri as $sv) {
                    $category = TSHelper::getTitleFromURI((string) $sv);
                    if (!is_null($instance) && !is_null($category)) {
                        if (!array_key_exists($title->getPrefixedText(), $titles)) {
                            $titles[$title->getPrefixedText()] = $instance;
                        } else {
                            $instance = $titles[$title->getPrefixedText()];
                        }

                        if ($this->containsTitle($assertedCategories, $categoryTitle)) {
                            $instance->addCategoryTreeElement(NULL);
                        } else {
                            $c = new CategoryTreeElement($category, (string) $sv);
                            $instance->addCategoryTreeElement($c);
                        }
                    }

                }

            } else {
                $titles[$title->getPrefixedText()] = $instance;
            }


        }
        return count($results);
    }

    private function containsTitle($titleSet, $title) {
        if (! is_object($title)) return false;
        foreach($titleSet as $t) {
            if ($title->equals($t)) return true;
        }
        return false;
    }


    private function getLiteral($literal, $predicate) {
        list($literalValue, $literalType) = $literal;
        if (trim($literalValue) !== '') {

            // create SMWDataValue either by property or if that is not possible by the given XSD type
            if ($predicate instanceof SMWDIProperty ) {
                $value = SMWDataValueFactory::newPropertyObjectValue($predicate, $literalValue)->getDataItem();
                
            } else {
                $value = SMWDataValueFactory::newTypeIdValue(WikiTypeToXSD::getWikiType($literalType), $literalValue)->getDataItem();
            }
                
        } else {
            $value = SMWDataValueFactory::newTypeIdValue("_str", "!!empty value!!")->getDataItem();
        }
        return $value;
    }


    public function getAnnotations($p_array) {
        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;
            $instanceURI = $p_array[0];

            // actually limit and offset is not used
            $limit =  isset($p_array[1]) && is_numeric($p_array[1]) ? $p_array[1] : 500;
            $partition = isset($p_array[2]) && is_numeric($p_array[2]) ? $p_array[2] : 0;
            $offset = $partition * $limit;
            $metadata = isset($p_array[3]) ? $p_array[3] : false;
            $metadataRequest = $metadata != false ? "|metadata=$metadata" : "";
            $onlyDirect = $p_array[4] == "true";
            global $smwgHaloTripleStoreGraph;
            $response = $client->query("SELECT ?p ?o WHERE { <$instanceURI> ?p ?o. }",  "limit=$limit|offset=$offset$metadataRequest", $smwgHaloTripleStoreGraph);
            $annotations = array();
            $this->parseAnnotations($response, $instanceURI, $annotations, $onlyDirect);


        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }

        return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($annotations, NULL);

    }



    protected function parseAnnotations($response, $instanceURI, & $annotations, $onlyDirect = false) {
        global $smwgSPARQLResultEncoding;
        // PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
        // another charset.
        if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
            $response = utf8_decode($response);
        }

        $dom = simplexml_load_string($response);
        $dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");

        $results = $dom->xpath('//sparqlxml:result');

        $instanceTitle = TSHelper::getTitleFromURI($instanceURI);
        if ($instanceTitle instanceof Title) {
            $semData = smwfGetStore()->getSemanticData(SMWDIWikiPage::newFromTitle($instanceTitle));

        } else {
            $assertedValues = array();
        }
        
        foreach ($results as $r) {

            $children = $r->children(); // binding nodes
            $b = $children->binding[0]; // predicate

            $sv = $b->children()->uri[0];
            if (TSNamespaces::$RDF_NS."type" === (string) $sv) continue; // ignore rdf:type annotations
            $title = TSHelper::getTitleFromURI((string) $sv);
            if (is_null($title)) continue;
            $predicate = SMWDIProperty::newFromUserLabel($title->getText());
            $predicateURI=(string) $sv;

            $categories = array();
            $b = $children->binding[1]; // values
            $values = array();
            foreach($b->children()->uri as $sv) {
                $object = TSHelper::getTitleFromURI((string) $sv);
                if (TSHelper::isLocalURI((string) $sv) || TSHelper::isFunctionalOBLTerm((string) $sv)) {
                    $value = SMWDIWikiPage::newFromTitle($object);
                    $uri = (string) $sv;
                } else {
                    $value = SMWDIUri::doUnserialize((string) $sv);
                    $uri = (string) $sv;
                }
                // add metadata
                $metadataMap = $this->parseMetadata($sv->metadata);
                foreach($metadataMap as $mdProperty => $mdValue) {
                    $value->setMetadata(strtoupper($mdProperty), NULL, $mdValue);
                }

                $values[] = array($value,$uri);

            }
            foreach($b->children()->literal as $sv) {
                $literal = array((string) $sv, $sv->attributes()->datatype);
                $value = $this->getLiteral($literal, $predicate);

                // add metadata
                $metadataMap = $this->parseMetadata($sv->metadata);
                foreach($metadataMap as $mdProperty => $mdValue) {
                    $value->setMetadata(strtoupper($mdProperty), NULL, $mdValue);
                }
                $values[] = array($value, NULL);
            }

            
            $assertedValues = $semData->getPropertyValues($predicate);
            $derivedValuesResult=array();
            $assertedValuesResult=array();
            SMWFullSemanticData::getDataValueDiff($values, $assertedValues, $derivedValuesResult, $assertedValuesResult);

            if ($onlyDirect) {
                $derivedValuesResult=array();
            }
                

            $annotations[] = new Annotation(new PropertySchemaElement($predicate, $predicateURI), $assertedValuesResult, $derivedValuesResult);
        }

    }

    protected function parseValues($response, $propertyElement, & $annotations) {
        global $smwgSPARQLResultEncoding;
        // PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
        // another charset.
        if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
            $response = utf8_decode($response);
        }

        $dom = simplexml_load_string($response);
        $dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");

        $results = $dom->xpath('//sparqlxml:result');
        $predicate = $propertyElement->getPropertyValue();
        foreach ($results as $r) {

            $children = $r->children(); // binding nodes


            $categories = array();
            $b = $children->binding[0]; // values
            $values = array();
            foreach($b->children()->uri as $sv) {
                $object = TSHelper::getTitleFromURI((string) $sv);
                if (TSHelper::isLocalURI((string) $sv)) {
                    $value = SMWDataValueFactory::newPropertyObjectValue($predicate, $object);
                    $uri = (string) $sv;
                } else {
                    $value = SMWDataValueFactory::newTypeIDValue('_uri', (string) $sv);
                    $uri = (string) $sv;
                }
                // add metadata
                $metadataMap = $this->parseMetadata($sv->metadata);
                foreach($metadataMap as $mdProperty => $mdValue) {
                    $value->setMetadata(strtoupper($mdProperty), NULL, $mdValue);
                }

                $values[] = array($value, $uri) ;

            }
            foreach($b->children()->literal as $sv) {
                $literal = array((string) $sv, $sv->attributes()->datatype);
                $value = $this->getLiteral($literal, $predicate);

                // add metadata
                $metadataMap = $this->parseMetadata($sv->metadata);
                foreach($metadataMap as $mdProperty => $mdValue) {
                    $value->setMetadata(strtoupper($mdProperty), NULL, $mdValue);
                }
                $values[] = array($value, $NULL);
            }


            $annotations[] = new Annotation($propertyElement, $values);
        }

    }

    public function getInstancesUsingProperty($p_array) {
        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;

            $propertyName = $p_array[0];
            $limit =  intval($p_array[1]);
            $partition =  intval($p_array[2]);
            $offset = $partition * $limit;

            // query
            $response = $client->query("[[$propertyName::+]]",  "?Category|limit=$limit|offset=$offset|merge=false", $smwgHaloTripleStoreGraph);

            $titles = array();
            $numOfResults = $this->parseInstances($response, $titles, NULL);

        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }

        $propertyName_xml = str_replace( array('"'),array('&quot;'),$propertyName);
        return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($numOfResults, $titles, $limit, $partition, 'getInstancesUsingProperty,'.$propertyName_xml);
    }

    public function getInstanceUsingPropertyValue($p_array) {
        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;

            $propertyName = $p_array[0];
            $value = $p_array[1];
            $limit =  intval($p_array[2]);
            $partition =  intval($p_array[3]);
            $offset = $partition * $limit;

            // query
            $response = $client->query("[[$propertyName::$value]]",  "?Category|limit=$limit|offset=$offset|merge=false", $smwgHaloTripleStoreGraph);

            $titles = array();
            $numOfResults = $this->parseInstances($response, $titles, NULL);

        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }

        $propertyName_xml = str_replace( array('"'),array('&quot;'),$propertyName);
        return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($numOfResults, $titles, $limit, $partition, 'getInstanceUsingPropertyValue,'.$propertyName_xml);
    }

    public function getPropertyValues($p_array) {
        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;
            $propertyURI = $p_array[0];

            // actually limit and offset is not used
            $limit =  isset($p_array[1]) && is_numeric($p_array[1]) ? $p_array[1] : 500;
            $partition = isset($p_array[2]) && is_numeric($p_array[2]) ? $p_array[2] : 0;
            $offset = $partition * $limit;
            $metadata = isset($p_array[3]) ? $p_array[3] : false;
            $metadataRequest = $metadata != false ? "|metadata=$metadata" : "";


            $response = $client->query("SELECT ?o WHERE { ?s <$propertyURI> ?o. }",  "limit=$limit|offset=$offset$metadataRequest", $smwgHaloTripleStoreGraph);
            $annotations = array();
            $property = TSHelper::getTitleFromURI($p_array[0]);


            $propertyElement = new PropertySchemaElement(SMWDIProperty::newFromUserLabel($property->getText()), $propertyURI);
            $this->parseValues($response, $propertyElement , $annotations);


        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }

        return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($annotations, NULL);
    }




    public function getCategoryForInstance($p_array) {
        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;

            $instanceURI = $p_array[0];
            $bundleSPARQL = "";
            if ($this->bundleID != '') {
                global $dfgLang;
                $partOfBundleURI = TSHelper::getUriFromTitle(Title::newFromText($dfgLang->getLanguageString("df_partofbundle"), SMW_NS_PROPERTY));
                $bundleURI = TSHelper::getUriFromTitle(Title::newFromText($this->bundleID, NS_MAIN));
                $bundleSPARQL = " ?cat <$partOfBundleURI> <$bundleURI> .";
            }
            // query
            $response = $client->query(TSNamespaces::getW3CPrefixes()." SELECT ?cat WHERE { <$instanceURI> rdf:type ?cat. $bundleSPARQL }",  "", $smwgHaloTripleStoreGraph);

            $categories = array();
            $this->parseCategories($response, $categories);


        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }


        return $this->getCategoryTree($categories);
    }

    protected function parseCategories($response, & $categories) {
        global $smwgSPARQLResultEncoding;
        // PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
        // another charset.
        if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
            $response = utf8_decode($response);
        }

        $dom = simplexml_load_string($response);
        $dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
        $titles = array();
        $results = $dom->xpath('//sparqlxml:result');


        foreach ($results as $r) {

            //$children = $r->children(); // binding nodes
            $b = $r->binding[0]; // categories

            foreach($b->children()->uri as $sv) {
                $category = TSHelper::getTitleFromURI((string) $sv);
                if (!is_null($category)) {

                    $categories[] = $category;
                }
            }


        }
    }

    public function filterBrowse($p_array) {
            

        $type = $p_array[0];
        $hint = explode(" ", $p_array[1]);

        if ($type != 'instance') return parent::filterBrowse($p_array);

        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;

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


            $response = $client->query(TSNamespaces::getW3CPrefixes()." SELECT ?s ?cat WHERE { ?s ?p ?o. OPTIONAL { ?s rdf:type ?cat. } $filter }",  "limit=1000", $smwgHaloTripleStoreGraph);


            $titles = array();
            $numOfResults = $this->parseInstances($response, $titles, NULL);


        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }

        // do not show partitions. 1000 instances is maximum here.
        return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($numOfResults, $titles, 1001, 0);
    }

    /**
     * Creates the data source parameters for the query.
     * The field $this->dataSource is a comma separated list of data source names.
     * A special name for the wiki may be among them. In this case, the graph
     * for the wiki is added to the parameters.
     *
     * @return string
     *  The data source parameters for the query.
     */
    protected function getDataSourceParameters() {
        if (!isset($this->dataSource)) {
            // no dataspace parameters
            return "";
        }
        // Check if the wiki is among the data sources
        $dataSpace = "";
        $sources = explode(',', $this->dataSource);
        $graph = "";
        $wikiID = wfMsg("smw_ob_source_wiki");
        foreach ($sources as $key => $source) {
            if (trim($source) == $wikiID) {
                global $smwgHaloTripleStoreGraph;
                $graph = "|graph=$smwgHaloTripleStoreGraph";
                unset ($sources[$key]);
                break;
            }
        }
        $dataSources = implode(',', $sources);
        $dataSpace = "|dataspace=$dataSources$graph";
        return $dataSpace;
    }

    protected function parseMetadata($metadataNode) {
        $result = array();
        if (!is_null($metadataNode) && $metadataNode !== '') {
            foreach($metadataNode as $m) {
                $name = (string) $m->attributes()->name;
                $datatype = (string) $m->attributes()->datatype;
                $mdValues = array();
                foreach($m->value as $mdValue) {
                    $mdValues[] = (string) $mdValue;
                }
                $result[strtoupper($name)] = $mdValues;
            }
        }
        return $result;
    }
}

class OB_StorageTSQuad extends OB_StorageTS {
    public function getAnnotations($p_array) {
        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;

            $instanceURI = $p_array[0];

            // actually limit and offset is not used
            $limit =  isset($p_array[1]) && is_numeric($p_array[1]) ? $p_array[1] : 500;
            $partition = isset($p_array[2]) && is_numeric($p_array[2]) ? $p_array[2] : 0;
            $offset = $partition * $limit;
            $metadata = isset($p_array[3]) ? $p_array[3] : false;
            $metadataRequest = $metadata != false ? "|metadata=$metadata" : "";

            $dataSpace = $this->getDataSourceParameters();



            $response = $client->query("SELECT ?p ?o WHERE { <$instanceURI> ?p ?o. }",  "limit=$limit|offset=$offset$dataSpace$metadataRequest");
            $annotations = array();
            $this->parseAnnotations($response, $instanceURI, $annotations);


        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }

        return SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotationList($annotations, NULL);

    }

    public function getInstancesUsingProperty($p_array) {
        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;

            $propertyURI = TSNamespaces::getInstance()->getNSURI(SMW_NS_PROPERTY).$p_array[0];
            $limit =  intval($p_array[1]);
            $partition =  intval($p_array[2]);
            $offset = $partition * $limit;
            $metadata = isset($p_array[3]) ? $p_array[3] : false;
            $metadataRequest = $metadata != false ? "|metadata=$metadata" : "";
            $dataSpace = $this->getDataSourceParameters();

            // query
            $response = $client->query(TSNamespaces::getW3CPrefixes()." SELECT ?s ?cat WHERE { ?s <$propertyURI> ?o. OPTIONAL { ?s rdf:type ?cat. } }",  "limit=$limit|offset=$offset$dataSpace$metadataRequest");

            $titles = array();
            $numOfResults = $this->parseInstances($response, $titles, NULL);

        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }
            
        $propertyURI = str_replace( array('"'),array('&quot;'),$propertyURI);
        return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($numOfResults, $titles, $limit, $partition, 'getInstancesUsingProperty,'.$propertyURI);
    }

    public function getCategoryForInstance($p_array) {
        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;

            $instanceURI = $p_array[0];

            $dataSpace = $this->getDataSourceParameters();

            // query
            $response = $client->query(TSNamespaces::getW3CPrefixes()." SELECT ?cat WHERE { <$instanceURI> rdf:type ?cat.  }",  "$dataSpace");

            $categories = array();
            $this->parseCategories($response, $categories);


        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }


        return $this->getCategoryTree($categories);
    }

    public function filterBrowse($p_array) {


        $type = $p_array[0];
        $hint = explode(" ", $p_array[1]);

        if ($type != 'instance') return parent::filterBrowse($p_array);

        global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion;
        $client = TSConnection::getConnector();
        $client->connect();
        try {
            global $smwgHaloTripleStoreGraph;

            $dataSpace = $this->getDataSourceParameters();

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


            $response = $client->query(TSNamespaces::getW3CPrefixes()." SELECT ?s ?cat WHERE { ?s ?p ?o. OPTIONAL { ?s rdf:type ?cat. } $filter }",  "limit=1000$dataSpace");


            $titles = array();
            $numOfResults = $this->parseInstances($response, $titles, NULL);


        } catch(Exception $e) {
            global $smwgHaloWebserviceEndpoint;
            return $this->createErrorMessage($e->getCode(), "Error accessing the TSC at $smwgHaloWebserviceEndpoint");
        }

        // do not show partitions. 1000 instances is maximum here.
        return SMWOntologyBrowserXMLGenerator::encapsulateAsInstancePartition($numOfResults, $titles, 1001, 0);
    }
}
