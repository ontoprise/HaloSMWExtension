<?php
/*
 * Created on 17.04.2007
 * Author: kai
 * 
 * Database access class (mainly) for OntologyBrowser.
 */
 global $smwgIP,$smwgHaloIP;
 require_once( "$smwgIP/includes/storage/SMW_Store.php" );
 require_once( "$smwgIP/includes/SMW_DV_WikiPage.php" );
 require_once( "$smwgIP/includes/SMW_DataValueFactory.php" );
 require_once  "$smwgHaloIP/includes/SMW_DBHelper.php";
 require_once( "SMW_GraphHelper.php");
 require_once( "SMW_SemanticStore.php");

 // max depth of category graph
 define('SMW_MAX_CATEGORY_GRAPH_DEPTH', 10);
  
 class SMWSemanticStoreSQL extends SMWSemanticStore {
 		
	public function SMWSemanticStoreSQL() {
		global $smwgHaloContLang;
		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$smwSpecialCategories = $smwgHaloContLang->getSpecialCategoryArray();
		$domainRangeHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT], SMW_NS_PROPERTY);
	
		$minCard = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MIN_CARD], SMW_NS_PROPERTY);
		$maxCard = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MAX_CARD], SMW_NS_PROPERTY);
		$transitiveCat = Title::newFromText($smwSpecialCategories[SMW_SC_TRANSITIVE_RELATIONS], NS_CATEGORY);
		$symetricalCat = Title::newFromText($smwSpecialCategories[SMW_SC_SYMMETRICAL_RELATIONS], NS_CATEGORY);
		$inverseOf = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_IS_INVERSE_OF], SMW_NS_PROPERTY);
		parent::SMWSemanticStore($domainRangeHintRelation, $minCard, $maxCard, $transitiveCat, $symetricalCat, $inverseOf);
	}
	
	/**
	 * Checks if $title is a redirect page.
	 * 
	 * @param $title
	 * @param $pagetable Name of MW's 'page' table (for efficiency)
	 * @param & $db reference for database (for efficiency)
	 */
	private static function isRedirect(Title $title, $pagetable, & $db) {
		return $db->selectRow($pagetable, 'page_is_redirect', array('page_title' => $title->getDBkey(), 'page_namespace' => $title->getNamespace(), 'page_is_redirect' => 1)) !== false;
	}
	
	function setup($verbose) {
		$this->setupLogging($verbose);
		$this->createPreDefinedPages($verbose);
 	}
	 	
	public function getPages($namespaces = NULL, $requestoptions = NULL, $addRedirectTargets = false) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = "";
		if ($namespaces != NULL) {
			$sql .= '(';
			for ($i = 0, $n = count($namespaces); $i < $n; $i++) { 
				if ($i > 0) $sql .= ' OR ';
				$sql .= 'page_namespace='.$db->addQuotes($namespaces[$i]);
			}
			if (count($namespaces) == 0) $sql .= 'true';
			$sql .= ') ';
		} else  {
			$sql = 'true';
		}
		
		$sql .= DBHelper::getSQLConditions($requestoptions,'page_title','page_title');
		
		$result = array();
		
		if (!$addRedirectTargets) {
			$res = $db->select( $db->tableName('page'), 
		               array('page_title','page_namespace'),
		               $sql.'  AND page_is_redirect = 0', 'SMW::getPages', DBHelper::getSQLOptions($requestoptions,'page_namespace') );
		    if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = Title::newFromText($row->page_title, $row->page_namespace);
				}
			}
		} else {
						
		   $res = $db->query( '(SELECT page_title AS title, page_namespace AS ns FROM page WHERE '.$sql.' AND page_is_redirect = 0) ' .
		   					'UNION DISTINCT ' .
		   					  '(SELECT rd_title AS title, rd_namespace AS ns FROM page JOIN redirect ON page_id = rd_from WHERE '.$sql.' AND page_is_redirect = 1)  '.
		   					 DBHelper::getSQLOptionsAsString($requestoptions,'ns'));
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = Title::newFromText($row->title, $row->ns);
				}
			}        
		}
		
		
		$db->freeResult($res);
		return $result;
	}
		
	function getRootCategories($requestoptions = NULL) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$categorylinks = $db->tableName('categorylinks');
		$page = $db->tableName('page');
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND page_is_redirect = 0 AND NOT EXISTS (SELECT cl_from FROM '.$categorylinks.' WHERE cl_from = page_id)'.
		       DBHelper::getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select( $page, 
		                    'page_title',
		                    $sql, 'SMW::getRootCategories', DBHelper::getSQLOptions($requestoptions,'page_title') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, NS_CATEGORY);
				 
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	function getRootProperties($requestoptions = NULL) {
		
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$smw_subprops = $db->tableName('smw_subprops');
		$page = $db->tableName('page');
		$sql = 'page_namespace=' . SMW_NS_PROPERTY .
			   ' AND page_is_redirect = 0 AND NOT EXISTS (SELECT subject_title FROM '.$smw_subprops.' WHERE subject_title = page_title)'.
		       DBHelper::getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select( $page, 
		                    'page_title',
		                    $sql, 'SMW::getRootProperties', DBHelper::getSQLOptions($requestoptions,'page_title') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, SMW_NS_PROPERTY);
			}
		}
		$db->freeResult($res);
		return $result;
	}

	
	function getDirectSubCategories(Title $categoryTitle, $requestoptions = NULL) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND page_is_redirect = 0 AND cl_to =' . $db->addQuotes($categoryTitle->getDBkey()) . ' AND cl_from = page_id'.
		       DBHelper::getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select(  array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    'page_title',
		                    $sql, 'SMW::getDirectSubCategories', DBHelper::getSQLOptions($requestoptions,'page_title') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, NS_CATEGORY);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	public function getSubCategories(Title $category) {
		$visitedNodes = array();
		return $this->_getSubCategories($category, $visitedNodes);
	}
	
	private function _getSubCategories(Title $category, & $visitedNodes) {
		$subCategories = $this->getDirectSubCategories($category);
		$result = array();
		foreach($subCategories as $subCat) {
			if (in_array($subCat, $visitedNodes)) {
				continue;
			}
			array_push($visitedNodes, $subCat);
			$result = array_merge($result, $this->_getSubCategories($subCat, $visitedNodes));
		}
		array_pop($visitedNodes);
		return array_merge($result, $subCategories);
	}
	
	function getDirectSuperCategories(Title $categoryTitle, $requestoptions = NULL) {
		
		$db =& wfGetDB( DB_MASTER );
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND page_title =' . $db->addQuotes($categoryTitle->getDBkey()) . ' AND cl_from = page_id AND cl_to IN (SELECT page_title FROM '.$page.' WHERE page_title=cl_to AND page_is_redirect = 0)'.
		       DBHelper::getSQLConditions($requestoptions,'cl_to','cl_to');

		$res = $db->select(  array($page, $categorylinks), 
		                    'cl_to',
		                    $sql, 'SMW::getDirectSuperCategories', DBHelper::getSQLOptions($requestoptions,'cl_to') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->cl_to, NS_CATEGORY);
				
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	function getCategoriesForInstance(Title $instanceTitle, $requestoptions = NULL) {
		
		$db =& wfGetDB( DB_MASTER ); 
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$sql = 'p1.page_title=' . $db->addQuotes($instanceTitle->getDBkey()) . ' AND p1.page_id = cl_from AND p2.page_is_redirect = 0 AND cl_to = p2.page_title'.
			DBHelper::getSQLConditions($requestoptions,'cl_to','cl_to');

		$res = $db->select( array($page.' p1', $categorylinks, $page.' p2'), 
		                    'DISTINCT cl_to',
		                    $sql, 'SMW::getCategoriesForInstance',  DBHelper::getSQLOptions($requestoptions,'cl_to'));
		// rewrite result as array
		$result = array();
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->cl_to, NS_CATEGORY);
				
			}
		}
		$db->freeResult($res);

		return $result;
	}
	
	function getInstances(Title $categoryTitle, $requestoptions = NULL) {
		$db =& wfGetDB( DB_MASTER ); 
		$this->createVirtualTableWithInstances($categoryTitle, $db);
		
		
		$res = $db->select('smw_ob_instances', array('instance', 'category'), array(), 'SMW::getInstances', DBHelper::getSQLOptions($requestoptions,'instance'));
		$results = array();
		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$instance = Title::newFromText($row->instance, NS_MAIN);
				$category = Title::newFromText($row->category, NS_CATEGORY);
				$results[] = array($instance, $category);
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		
		// drop virtual tables
		$this->dropVirtualTableWithInstances($db);
		return $results;
	}
	
	/**
	 * Creates a virtual table and adds all (direct and indirect) instances of
	 * $categoryTitle
	 * 
	 * @param Title $categoryTitle
	 * @param & $db DB reference
	 */
	private function createVirtualTableWithInstances($categoryTitle, & $db) {
		global $smwgDefaultCollation;
		
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
	
		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_instances (instance VARCHAR(255), category VARCHAR(255) '.$collation.')
		            TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );
		
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_instances_sub (category VARCHAR(255) '.$collation.' NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_instances_super (category VARCHAR(255) '.$collation.' NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );
		
		// initialize with direct instances
				           
		$db->query('INSERT INTO smw_ob_instances (SELECT page_title AS instance, NULL AS category FROM '.$page.' ' .
						'JOIN '.$categorylinks.' ON page_id = cl_from ' .
						'WHERE page_is_redirect = 0 AND page_namespace = '.NS_MAIN.' AND cl_to = '.$db->addQuotes($categoryTitle->getDBkey()).')');
	
		$db->query('INSERT INTO smw_ob_instances_super VALUES ('.$db->addQuotes($categoryTitle->getDBkey()).')');
		
		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		// maximum iteration length is maximum category tree depth.
		do  {
			$maxDepth--;
			
			// get next subcategory level
			$db->query('INSERT INTO smw_ob_instances_sub (SELECT DISTINCT page_title AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_id = cl_from WHERE page_namespace = '.NS_CATEGORY.' AND cl_to IN (SELECT * FROM smw_ob_instances_super))');
			
			// insert direct instances of current subcategory level
			$db->query('INSERT INTO smw_ob_instances (SELECT page_title AS instance, cl_to AS category FROM '.$page.' ' .
						'JOIN '.$categorylinks.' ON page_id = cl_from ' .
						'WHERE page_is_redirect = 0 AND page_namespace = '.NS_MAIN.' AND cl_to IN (SELECT * FROM smw_ob_instances_sub))');
			
			// copy subcatgegories to supercategories of next iteration
			$db->query('TRUNCATE TABLE smw_ob_instances_super');
			$db->query('INSERT INTO smw_ob_instances_super (SELECT * FROM smw_ob_instances_sub)');
			
			// check if there was least one more subcategory. If not, all instances were found.
			$res = $db->query('SELECT COUNT(category) AS numOfSubCats FROM smw_ob_instances_super');
			$numOfSubCats = $db->fetchObject($res)->numOfSubCats;
			$db->freeResult($res);
			
			$db->query('TRUNCATE TABLE smw_ob_instances_sub');
			
		} while ($numOfSubCats > 0 && $maxDepth > 0);
		
	
		$db->query('DROP TABLE smw_ob_instances_super');
		$db->query('DROP TABLE smw_ob_instances_sub');
	}
	
	/**
	 * Drops virtual table for instances.
	 * 
	 * @param & $db DB reference
	 */
	private function dropVirtualTableWithInstances(& $db) {
			$db->query('DROP TABLE smw_ob_instances');
	}
	
	
	function getDirectInstances(Title $categoryTitle, $requestoptions = NULL) {
		$db =& wfGetDB( DB_MASTER ); 

		$sql = 'cl_to=' . $db->addQuotes($categoryTitle->getDBkey()) . 
			' AND page_is_redirect = 0 AND page_id = cl_from AND page_namespace = '.NS_MAIN.
			DBHelper::getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select( array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    'DISTINCT page_title',
		                    $sql, 'SMW::getDirectInstances', DBHelper::getSQLOptions($requestoptions,'page_title'));
		// rewrite result as array
		$result = array();
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, NS_MAIN);
			}
		}
		$db->freeResult($res);

		return $result;
	}
	
	
	function getPropertiesWithSchemaByCategory(Title $categoryTitle, $requestoptions = NULL) {
		$db =& wfGetDB( DB_MASTER ); 
		$page = $db->tableName('page');
		$this->createVirtualTableWithPropertiesByCategory($categoryTitle, $db);
		$res = $db->select( 'smw_ob_properties', 
		                    'DISTINCT property',
		                    array(), 'SMW::getPropertiesOfCategory', DBHelper::getSQLOptions($requestoptions,'property'));
		$properties = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$properties[] = Title::newFromText($row->property, SMW_NS_PROPERTY);
				 
			}
		}
		$db->freeResult($res);
		
		$result = $this->getSchemaPropertyTuple($properties, $db);
		$this->dropVirtualTableForProperties($db);
		return $result;
	}
	
	public function getPropertiesWithSchemaByName($requestoptions) {
		$db =& wfGetDB( DB_MASTER ); 
		$this->createVirtualTableWithPropertiesByName($requestoptions, $db);
		
		$res = $db->select( 'smw_ob_properties', 
		                    'DISTINCT property',
		                    array(), 'SMW::getPropertiesOfCategory', DBHelper::getSQLOptions($requestoptions,'property'));
		$properties = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				// do not check for redirect, because it's done it the query
				$properties[] = Title::newFromText($row->property, SMW_NS_PROPERTY);
			}
		}
		$db->freeResult($res);
		
		$result = $this->getSchemaPropertyTuple($properties, $db);
		$this->dropVirtualTableForProperties($db);
		return $result;
	}
	/**
	 * Returns property schema data for a given array of property titles.
	 * Needs the virtual table 'smw_ob_properties' initialized with the 
	 * data given in properties.
	 * 
	 * @return array of tuples: (title, minCard, maxCard, type, isSym, isTrans, range)
	 */
	private function getSchemaPropertyTuple(array & $properties, & $db) {
		$resMinCard = $db->query('SELECT property, value_xsd AS minCard FROM smw_ob_properties  JOIN '.$db->tableName('smw_attributes').
							 ' ON subject_title = property WHERE attribute_title = '.$db->addQuotes($this->minCard->getDBKey()). ' GROUP BY property ORDER BY property');
		$resMaxCard = $db->query('SELECT property, value_xsd AS maxCard FROM smw_ob_properties  JOIN '.$db->tableName('smw_attributes').
							 ' ON subject_title = property WHERE attribute_title = '.$db->addQuotes($this->maxCard->getDBKey()). ' GROUP BY property ORDER BY property');
		$resTypes = $db->query('SELECT property, value_string AS type FROM smw_ob_properties  JOIN '.$db->tableName('smw_specialprops').
							 ' ON subject_title = property WHERE property_id = '.SMW_SP_HAS_TYPE. '  GROUP BY property ORDER BY property');
		$resSymCats = $db->query('SELECT property, cl_to AS minCard FROM smw_ob_properties  JOIN '.$db->tableName('categorylinks').
							 ' ON cl_from = id WHERE cl_to = '.$db->addQuotes($this->symetricalCat->getDBKey()). ' GROUP BY property ORDER BY property');
		$resTransCats = $db->query('SELECT property, cl_to AS minCard FROM smw_ob_properties  JOIN '.$db->tableName('categorylinks').
							 ' ON cl_from = id WHERE cl_to = '.$db->addQuotes($this->transitiveCat->getDBKey()). ' GROUP BY property ORDER BY property');
		$resRanges = $db->query('SELECT property, object_title AS range FROM smw_ob_properties  JOIN '.$db->tableName('smw_nary_relations'). 'r ON r.subject_id = id JOIN '.$db->tableName('smw_nary').'n ON n.subject_id = r.subject_id '.
							 ' WHERE attribute_title = '.$db->addQuotes($this->domainRangeHintRelation->getDBKey()). ' AND nary_pos = 1 GROUP BY property ORDER BY property');					 
		// rewrite result as array
		$result = array();
		
		$rowMinCard = $db->fetchObject($resMinCard);
		$rowMaxCard = $db->fetchObject($resMaxCard);
		$rowType = $db->fetchObject($resTypes);
		$rowSymCat = $db->fetchObject($resSymCats);
		$rowTransCats = $db->fetchObject($resTransCats);
		$rowRanges = $db->fetchObject($resRanges);
		foreach($properties as $p) {
				$minCard = CARDINALITY_MIN;
				if ($rowMinCard != NULL && $rowMinCard->property == $p->getDBkey()) {
					$minCard = $rowMinCard->minCard;
					$rowMinCard = $db->fetchObject($resMinCard);
				}
				$maxCard = CARDINALITY_UNLIMITED;
				if ($rowMaxCard != NULL && $rowMaxCard->property == $p->getDBkey()) {
					$maxCard = $rowMaxCard->maxCard;
					$rowMaxCard = $db->fetchObject($resMaxCard);
				}
				$type = '_wpg';
				
				if ($rowType != NULL && $rowType->property == $p->getDBkey()) {
					$type = $rowType->type; 
					$rowType = $db->fetchObject($resTypes);
				}
				$symCat = false;
				if ($rowSymCat != NULL && $rowSymCat->property == $p->getDBkey()) {
					$symCat = true;
					$rowSymCat = $db->fetchObject($resSymCats);
				}
				$transCat = false;
				if ($rowTransCats != NULL && $rowTransCats->property == $p->getDBkey()) {
					$transCat = true;
					$rowTransCats = $db->fetchObject($resTransCats);
				}
				$range = NULL;
				if ($rowRanges != NULL && $rowRanges->property == $p->getDBkey()) {
					$range = $rowRanges->range;
					$rowRanges = $db->fetchObject($resRanges);
				}
				$result[] = array($p, $minCard, $maxCard, $type, $symCat, $transCat, $range);
			
		}
		$db->freeResult($resMinCard);
		$db->freeResult($resMaxCard);
		$db->freeResult($resTypes);
		$db->freeResult($resSymCats);
		$db->freeResult($resTransCats);
		$db->freeResult($resRanges);
		return $result;
 	}	
	
	/**
	 * Returns a virtual 'smw_ob_properties' table with properties matching $stringConditions
	 * 
	 */
	private function createVirtualTableWithPropertiesByName($requestoptions, & $db) {
		global $smwgDefaultCollation;
		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		
		$page = $db->tableName('page');
		$redirects = $db->tableName('redirect');
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties (id INT(8) NOT NULL, property VARCHAR(255) '.$collation.')
		            TYPE=MEMORY', 'SMW::createVirtualTableForInstances' );
		$sql = DBHelper::getSQLConditions($requestoptions,'page_title','page_title');
		// add properties which match and which are no redirects 
		$db->query('INSERT INTO smw_ob_properties (SELECT page_id, page_title FROM '.$page.' WHERE page_is_redirect = 0 AND page_namespace = '.SMW_NS_PROPERTY.' '. $sql.')'); 
		$sql = DBHelper::getSQLConditions($requestoptions,'p1.page_title','p1.page_title');
		// add targets of matching redirects
		$db->query('INSERT INTO smw_ob_properties (SELECT p2.page_id, p2.page_title FROM page p1 JOIN redirect ON p1.page_id = rd_from JOIN page p2 ON p2.page_title = rd_title AND p2.page_namespace = rd_namespace WHERE p1.page_namespace = '.SMW_NS_PROPERTY.' '. $sql.')');             
	}
	/**
	 * Creates 'smw_ob_properties' and fills it with all properties (including inherited)
	 * of a category.
	 * 
	 * @param Title $category
	 * @param & $db 
	 */
	private function createVirtualTableWithPropertiesByCategory(Title $categoryTitle, & $db) {
		global $smwgDefaultCollation;
		
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$smw_nary = $db->tableName('smw_nary');
		$smw_nary_relations = $db->tableName('smw_nary_relations');
		
		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties (id INT(8) NOT NULL, property VARCHAR(255) '.$collation.')
		            TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
		
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_sub (category INT(8) NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_super (category INT(8) NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
		            
		$db->query('INSERT INTO smw_ob_properties (SELECT n.subject_id AS id, n.subject_title AS property FROM '.$smw_nary.' n JOIN '.$smw_nary_relations.' r ON n.subject_id = r.subject_id JOIN '.$page.' p ON n.subject_id = p.page_id '.
					' WHERE r.nary_pos = 0 AND n.attribute_title = '. $db->addQuotes($this->domainRangeHintRelation->getDBkey()). ' AND r.object_title = ' .$db->addQuotes($categoryTitle->getDBkey()).' AND p.page_is_redirect = 0)');
	
		$db->query('INSERT INTO smw_ob_properties_sub VALUES ('.$db->addQuotes($categoryTitle->getArticleID()).')');    
		
		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		// maximum iteration length is maximum category tree depth.
		do  {
			$maxDepth--;
			
			// get next supercategory level
			$db->query('INSERT INTO smw_ob_properties_super (SELECT DISTINCT page_id AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_title = cl_to WHERE page_namespace = '.NS_CATEGORY.' AND cl_from IN (SELECT * FROM smw_ob_properties_sub))');
			
			// insert direct properties of current supercategory level
			$db->query('INSERT INTO smw_ob_properties (SELECT n.subject_id AS id, n.subject_title AS property FROM '.$smw_nary.' n JOIN '.$smw_nary_relations.' r ON n.subject_id = r.subject_id JOIN '.$page.' p ON n.subject_id = p.page_id '.
					' WHERE r.nary_pos = 0 AND n.attribute_title = '. $db->addQuotes($this->domainRangeHintRelation->getDBkey()). ' AND p.page_is_redirect = 0 AND r.object_id IN (SELECT * FROM smw_ob_properties_super))');
	
			
			// copy supercatgegories to subcategories of next iteration
			$db->query('DELETE FROM smw_ob_properties_sub');
			$db->query('INSERT INTO smw_ob_properties_sub (SELECT * FROM smw_ob_properties_super)');
			
			// check if there was least one more supercategory. If not, all properties were found.
			$res = $db->query('SELECT COUNT(category) AS numOfSuperCats FROM smw_ob_properties_sub');
			$numOfSuperCats = $db->fetchObject($res)->numOfSuperCats;
			$db->freeResult($res);
			
			$db->query('DELETE FROM smw_ob_properties_super');
			
		} while ($numOfSuperCats > 0 && $maxDepth > 0);   
		     
		$db->query('DROP TABLE smw_ob_properties_super');
		$db->query('DROP TABLE smw_ob_properties_sub');
	}
	
	/**
	 * Drops table 'smw_ob_properties'.
	 */
	private function dropVirtualTableForProperties(& $db) {
		$db->query('DROP TABLE smw_ob_properties');
	}
	
	
	// can return directs, but it is not used anywhere		
	/*function getDirectPropertiesByCategory(Title $categoryTitle, $requestoptions = NULL) {
		$dv_container = SMWDataValueFactory::newTypeIDValue('__nry');
		$dv = SMWDataValueFactory::newTypeIDValue('_wpg');
  		$dv->setValues($categoryTitle->getDBKey(), $categoryTitle->getNamespace());
  		$dv_container->setDVs(array($dv, NULL));
		return smwfGetStore()->getPropertySubjects($this->domainRangeHintRelation, $dv_container, NULL, 0);
	}*/
	
	function getPropertiesWithDomain(Title $category) {
		return $this->getNarySubjects($category, 0);
	}
	
	function getPropertiesWithRange(Title $category) {
		return $this->getNarySubjects($category, 1);
	}
	
	private function getNarySubjects(Title $object, $pos) {
		$db =& wfGetDB( DB_MASTER );
		$smw_nary = $db->tableName('smw_nary');
		$smw_nary_relations = $db->tableName('smw_nary_relations');
 	 	$domainRangeRelation = smwfGetSemanticStore()->domainRangeHintRelation;
 	 	$results = array();
 	 	$res = $db->query('SELECT subject_title, subject_namespace FROM '.$smw_nary.' n JOIN '.$smw_nary_relations.' r ON n.subject_id = r.subject_id ' .
 	 						'WHERE n.attribute_title = '.$db->addQuotes($domainRangeRelation->getDBkey()).
 	 						' AND r.object_title = '.$db->addQuotes($object->getDBkey()).
							' AND r.object_namespace = '.NS_CATEGORY. 
							' AND r.nary_pos = '.mysql_real_escape_string($pos));
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				
				$results[] = Title::newFromText($row->subject_title, $row->subject_namespace);
			}
		}
		$db->freeResult($res);
		return $results;
	}
	
	/**
 	  * Returns all domain categories for a given property.
 	  */
 	 function getDomainCategories($propertyTitle, $reqfilter) {
 	 	$db =& wfGetDB( DB_MASTER );
		$page = $db->tableName('page');
 	 	$domainRangeRelation = smwfGetSemanticStore()->domainRangeHintRelation;
 	    $categories = smwfGetStore()->getPropertyValues($propertyTitle, $domainRangeRelation, $reqfilter);
 	    $result = array();
 	    foreach($categories as $value) {
 	    	$dvs = $value->getDVs();
 	    	if ($dvs[0] instanceof SMWWikiPageValue) {
 	    		$t = $dvs[0]->getTitle();
 	    		if (!SMWSemanticStoreSQL::isRedirect($t, $page, $db)) $result[] = $t;
 	    	}
 	    }
 	    return $result;
 	 }
	
	function getDirectSubProperties(Title $attribute, $requestoptions = NULL) {
	 	
	 	$result = "";
		$db =& wfGetDB( DB_MASTER );
		$page = $db->tableName('page');
		$smw_subprops = $db->tableName('smw_subprops');
		$sql = 'object_title = ' . $db->addQuotes($attribute->getDBkey()).' AND page_is_redirect = 0 AND subject_title = page_title AND page_namespace = '.SMW_NS_PROPERTY;

		$res = $db->select( array($smw_subprops, $page), 
		                    'subject_title',
		                    $sql, 'SMW::getDirectSubProperties', DBHelper::getSQLOptions($requestoptions,'subject_title') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->subject_title, SMW_NS_PROPERTY);
				
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	function getDirectSuperProperties(Title $attribute, $requestoptions = NULL) {
	 	
	 	$result = "";
		$db =& wfGetDB( DB_MASTER );
		$page = $db->tableName('page');
		$smw_subprops = $db->tableName('smw_subprops');
		$sql = 'subject_title = ' . $db->addQuotes($attribute->getDBkey()).' AND page_is_redirect = 0 AND object_title = page_title AND page_namespace = '.SMW_NS_PROPERTY;

		$res = $db->select(   array($smw_subprops, $page), 
		                    'object_title',
		                    $sql, 'SMW::getDirectSuperProperties', DBHelper::getSQLOptions($requestoptions,'object_title') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->object_title, SMW_NS_PROPERTY);
				
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	public function getRedirectPages(Title $title) {
		$db =& wfGetDB( DB_MASTER );
		$page = $db->tableName('page');
	 	$redirect = $db->tableName('redirect');
	 	$res = $db->query('SELECT page_title, page_namespace FROM '.$page.', '.$redirect.' WHERE '.$db->addQuotes($title->getDBkey()).' = rd_title AND '.$title->getNamespace().' = rd_namespace AND page_id = rd_from');
	 	$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	public function getRedirectTarget(Title $title) {
		$db =& wfGetDB( DB_MASTER );
		$redirect = $db->tableName('redirect');
	 	$res = $db->query('SELECT rd_namespace, rd_title FROM '.$redirect.' WHERE rd_from = '.$title->getArticleID());
	 	
	 	if ($db->numRows( $res ) == 0) {
	 		$db->freeResult($res);
	 		return $title;
	 	}
		$row = $db->fetchObject($res);
		$result = Title::newFromText($row->rd_title, $row->rd_namespace);
		$db->freeResult($res);
		return $result;
	}
	
	
	
	public function getNumberOfUsage(Title $title) {
		$num = 0;
		$db =& wfGetDB( DB_MASTER );
		if ($title->getNamespace() == NS_TEMPLATE) {
			$templatelinks = $db->tableName('templatelinks');
			$res = $db->query('SELECT COUNT(tl_from) AS numOfSubjects FROM '.$templatelinks.' s WHERE tl_title = '.$db->addQuotes($title->getDBKey()).' GROUP BY tl_title ');
		} else if ($title->getNamespace() == SMW_NS_PROPERTY) {
			$smw_attributes = $db->tableName('smw_attributes');
		 	$smw_relations = $db->tableName('smw_relations');
		 	$smw_nary = $db->tableName('smw_nary');	
			$res = $db->query('SELECT COUNT(subject_title) AS numOfSubjects FROM '.$smw_attributes.' s WHERE attribute_title = '.$db->addQuotes($title->getDBKey()).' GROUP BY attribute_title ' .
							  ' UNION SELECT COUNT(subject_title) AS numOfSubjects FROM '.$smw_nary.' s WHERE attribute_title = '.$db->addQuotes($title->getDBKey()).' GROUP BY attribute_title' .
							  ' UNION SELECT COUNT(subject_title) AS numOfSubjects FROM '.$smw_relations.' s WHERE relation_title = '.$db->addQuotes($title->getDBKey()).' GROUP BY relation_title;');
		}
		if($db->numRows( $res ) > 0) {
			$row = $db->fetchObject($res);
			$num = $row->numOfSubjects;
		} 
		$db->freeResult($res);
		return $num;
	}
	
	public function getNumberOfInstancesAndSubcategories(Title $category) {
		$db =& wfGetDB( DB_MASTER ); 
		$this->createVirtualTableWithInstances($category, $db);
		
		$res = $db->select( 'smw_ob_instances', 
		                    'COUNT(DISTINCT instance) AS numOfInstances, COUNT(DISTINCT category) AS numOfCategories',
		                    array(), 'SMW::getNumberOfInstancesAndSubcategories', array() );
		
		// rewrite result as array
		$numOfInstances = 0;
		
		if($db->numRows( $res ) > 0) {
			$row = $db->fetchObject($res);
			$numOfInstances = $row->numOfInstances;
			$numCategories =$row->numOfCategories;
		}
		$db->freeResult($res);
		
		$this->dropVirtualTableWithInstances($db);
		return array($numOfInstances, $numCategories);
	}
	
	public function getNumberOfProperties(Title $category) {
		$db =& wfGetDB( DB_MASTER ); 
		$this->createVirtualTableWithPropertiesByCategory($category, $db);
		$res = $db->select( 'smw_ob_properties', 
		                    'COUNT(DISTINCT property) AS numOfProperties',
		                    array(), 'SMW::getNumberOfProperties', array() );
		
		// rewrite result as array
		$result = 0;
		
		if($db->numRows( $res ) > 0) {
			$row = $db->fetchObject($res);
			$result = $row->numOfProperties;
			
		}
		$db->freeResult($res);
		$this->dropVirtualTableForProperties($db);
		return $result;
	}
	
	public function getNumberOfPropertiesForTarget(Title $target) {
		$db =& wfGetDB( DB_MASTER ); 
		$result = 0;
		$res = $db->select( $db->tableName('smw_relations'), 
		                    'COUNT(DISTINCT relation_title) AS numOfProperties',
		                    array('object_title' => $target->getDBkey()), 'SMW::getNumberOfPropertiesForTarget', array() );
		if($db->numRows( $res ) > 0) {
			  $row = $db->fetchObject($res);
			  $result += $row->numOfProperties;
		}           
		$db->freeResult($res);
		
		$res = $db->select( array($db->tableName('smw_nary_relations'). " r", $db->tableName('smw_nary'). " n"), 
		                    'COUNT(DISTINCT object_title) AS numOfProperties',
		                    array('r.subject_id' => 'n.subject_id', 'object_title' => $target->getDBkey()), 'SMW::getNumberOfPropertiesForTarget', array() );
		if($db->numRows( $res ) > 0) {
			  $row = $db->fetchObject($res);
			  $result += $row->numOfProperties;
		}           
		$db->freeResult($res);
		return $result;
	}
	
	public function getDistinctUnits(Title $type) {
		$db =& wfGetDB( DB_MASTER );
		$smw_attributes = $db->tableName('smw_attributes');
		$smw_nary = $db->tableName('smw_nary');
		$smw_nary_attributes = $db->tableName('smw_nary_attributes');
		$smw_specialprops = $db->tableName('smw_specialprops');
	 	
		$res = $db->query(	'(SELECT DISTINCT value_unit FROM '.$smw_attributes.' WHERE value_datatype = '.$db->addQuotes($type->getDBkey()).') '.
						 ' UNION ' .
					 		'(SELECT DISTINCT value_unit FROM '.$smw_specialprops.' s ' .
					 				'JOIN '.$smw_nary.' n ON LOCATE('.$db->addQuotes($type->getDBkey()).', s.value_string) > 0 AND s.subject_title=n.attribute_title ' .
					 				'JOIN '.$smw_nary_attributes.' a ON n.nary_key=a.nary_key)');
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = $row->value_unit;
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	public function getAnnotationsWithUnit(Title $type, $unit) {
		
		$db =& wfGetDB( DB_MASTER );
		$smw_attributes = $db->tableName('smw_attributes');
		$smw_nary = $db->tableName('smw_nary');
	 	$smw_nary_attributes = $db->tableName('smw_nary_attributes');
	 	$smw_specialprops = $db->tableName('smw_specialprops');
	 	
		$result = array();
		$res = $db->query('SELECT DISTINCT subject_title, subject_namespace, attribute_title FROM '.$smw_attributes.
							' WHERE value_datatype = '.$db->addQuotes($type->getDBkey()).' AND value_unit = '.$db->addQuotes($unit));
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(Title::newFromText($row->subject_title, $row->subject_namespace), Title::newFromText($row->attribute_title, SMW_NS_PROPERTY));
			}
		}
		
		$db->freeResult($res);
		
		$res2 = $db->query('SELECT DISTINCT n.subject_title, n.subject_namespace, attribute_title FROM '.$smw_specialprops.' s ' .
					 				'JOIN '.$smw_nary.' n ON CONTAINS(s.value_string, '.$db->addQuotes($type->getDBkey()).') AND s.subject_title=n.attribute_title ' .
					 				'JOIN '.$smw_nary_attributes.' a ON n.nary_key=a.nary_key ' .
					 	  'WHERE value_unit = '.$db->addQuotes($unit));
		
		
		if($db->numRows( $res2 ) > 0) {
			while($row = $db->fetchObject($res2)) {
				$result[] = array(Title::newFromText($row->subject_title, $row->subject_namespace), Title::newFromText($row->attribute_title, SMW_NS_PROPERTY));
			}
		}
		
		$db->freeResult($res2);
		
		return $result;
	}
	
	
 	
 	
 	
 	
 	///// Private methods /////

		
 		
 	/**
	 * Creates some predefined pages
	 */
	protected function createPreDefinedPages($verbose) {
		global $smwgHaloContLang;
		DBHelper::reportProgress("Creating predefined pages...\n",$verbose);
		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		foreach($ssp as $key => $value) {
			$t = Title::newFromText($value, SMW_NS_PROPERTY);
			if (!$t->exists()) {
				$article = new Article($t);
				if (strtolower($ssp[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT]) == strtolower($t->getText())) { // special handling for SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT. TODO: introduce general mechanism
					$article->insertNewArticle(wfMsg('smw_predefined_props', $t->getText())."\n\n[[has type::Type:Page; Type:Page]]", "", false, false);
				} else {
					$article->insertNewArticle(wfMsg('smw_predefined_props', $t->getText()), "", false, false);
				}
				DBHelper::reportProgress(" Create page ".$t->getNsText().":".$t->getText()."...\n",$verbose);
			}
		}
		
		$scs = $smwgHaloContLang->getSpecialCategoryArray();
		foreach($scs as $key => $value) {
			$t = Title::newFromText($value, NS_CATEGORY);
			if (!$t->exists()) {
				$article = new Article($t);
				$article->insertNewArticle(wfMsg('smw_predefined_cats', $t->getText()), "", false, false);
				DBHelper::reportProgress(" Create page ".$t->getNsText().":".$t->getText()."...\n",$verbose);
			}
		}
		
		$this->createHelpAttributes($verbose);
		
		DBHelper::reportProgress("Predefined pages created successfully.\n",$verbose);
	}
	
	protected function createHelpAttributes($verbose){
		$title = Title::newFromText("Question", SMW_NS_PROPERTY);
		if (!($title->exists())){
			$articleContent = "[[has type::Type:String]]";
			$wgArticle = new Article( $title );
			$wgArticle->doEdit( $articleContent, "New attribute added", EDIT_NEW);
			DBHelper::reportProgress(" Create page ".$title->getNsText().":".$title->getText()."...\n",$verbose);
		}
		$title = Title::newFromText("Description", SMW_NS_PROPERTY);
		if (!($title->exists())){
			$articleContent = "[[has type::Type:Text]]";
			$wgArticle = new Article( $title );
			$wgArticle->doEdit( $articleContent, "New attribute added", EDIT_NEW);
			DBHelper::reportProgress(" Create page ".$title->getNsText().":".$title->getText()."...\n",$verbose);
		}
		$title = Title::newFromText("DiscourseState", SMW_NS_PROPERTY);
		if (!($title->exists())){
			$articleContent = "[[has type::Type:String]]";
			$wgArticle = new Article( $title );
			$wgArticle->doEdit( $articleContent, "New attribute added", EDIT_NEW);
			DBHelper::reportProgress(" Create page ".$title->getNsText().":".$title->getText()."...\n",$verbose);
		}
	}
	
	
	
	/**
	 * Initializes the logging component
	 */
	protected function setupLogging($verbose) {
			global $smwhgEnableLogging; 
			DBHelper::reportProgress("Setting up logging ...\n",$verbose);
			DBHelper::reportProgress("   ... Creating logging database \n",$verbose);
			global $wgDBname;
			$db =& wfGetDB( DB_MASTER );

			// create gardening table
			$smw_logging = $db->tableName('smw_logging');
			$fname = 'SMW::setupLogging';

			// create relation table
			DBHelper::setupTable($smw_logging, array(
				  'id'				=>	'INT(10) UNSIGNED NOT NULL auto_increment PRIMARY KEY' ,
				  'timestamp'      	=>  'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' ,
				  'user'      		=>  'VARCHAR(255)' ,
				  'location'		=>	'VARCHAR(255)' ,
				  'type'			=>	'VARCHAR(255)' ,
				  'function'		=>	'VARCHAR(255)' ,
				  'remotetimestamp'	=>	'VARCHAR(255)' ,
				  'text'			=>  'LONGTEXT' 
				  ), $db, $verbose);

			DBHelper::reportProgress("   ... done!\n",$verbose);
	}
	
	public function rateAnnotation($subject, $predicate, $object, $rating) {
		$db =& wfGetDB( DB_MASTER );
		
		$smw_attributes = $db->tableName('smw_attributes');
		$smw_relations = $db->tableName('smw_relations');
 		
		$res = $db->selectRow($smw_attributes, 'rating', array('subject_title' => $subject, 'attribute_title' => $predicate, 'value_xsd' => $object));
		if ($res !== false) {
			$db->update($smw_attributes, array('rating' => (is_numeric($res) ? $res : 0) + $rating), array('subject_title' => $subject, 'attribute_title' => $predicate, 'value_xsd' => $object));
		} else {
			$res = $db->selectRow($smw_relations, 'rating', array('subject_title' => $subject, 'relation_title' => $predicate, 'object_title' => $object));
			if ($res !== false) {
				$db->update($smw_relations, array('rating' => (is_numeric($res) ? $res : 0) + $rating), array('subject_title' => $subject, 'relation_title' => $predicate, 'object_title' => $object));
			}  
		}
	}
	
	public function getRatedAnnotations($subject) {
		$db =& wfGetDB( DB_MASTER );
		
		$smw_attributes = $db->tableName('smw_attributes');
		$smw_relations = $db->tableName('smw_relations');
 		
		$res = $db->select($smw_attributes, array('attribute_title', 'value_xsd', 'rating'), array('subject_title' => $subject));
		$res2 = $db->select($smw_relations, array('relation_title', 'object_title', 'rating'), array('subject_title' => $subject));
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				
				$result[] = array($row->attribute_title, $row->value_xsd, $row->rating);
			}
		}
		if($db->numRows( $res2 ) > 0) {
			while($row = $db->fetchObject($res2)) {
				$result[] = array($row->relation_title, $row->object_title, $row->rating);
			}
		}
		$db->freeResult($res);
		$db->freeResult($res2);
		return $result;
	}
	
	public function getAnnotationsForRating($limit, $unrated = true) {
 		$db =& wfGetDB( DB_MASTER );
 		$smw_attributes = $db->tableName('smw_attributes');		
		$smw_relations = $db->tableName('smw_relations');
		$smw_nary = $db->tableName('smw_nary');
		if ($unrated) $where = 'WHERE rating IS NULL'; else $where = 'WHERE rating IS NOT NULL';
 		$res = $db->select($smw_attributes, array('subject_title', 'attribute_title', 'value_xsd'), array('rating' => NULL), 'SMW:getAnnotationsWithoutRating', array('ORDER BY' => 'RAND()', 'LIMIT' => $limit));
 		$res = $db->query('(SELECT subject_title AS subject, attribute_title AS predicate, value_xsd AS object FROM '.$smw_attributes. ' '.$where.') ' .
 							'UNION ' .
 						   '(SELECT subject_title AS subject, relation_title AS predicate, object_title AS object FROM '.$smw_relations.' '.$where.') ORDER BY RAND() LIMIT '.$limit);
 		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array($row->subject, $row->predicate, $row->object);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function replaceRedirectAnnotations($verbose = false) {
 		
 		$db =& wfGetDB( DB_MASTER );
 		$page = $db->tableName('page');
 		$redirect = $db->tableName('redirect');
 		$smw_attributes = $db->tableName('smw_attributes');
 		$smw_relations = $db->tableName('smw_relations');
 		$smw_nary = $db->tableName('smw_nary');
 		
 	
 		$result = array();
 		if ($verbose) echo "\n\nSelecting all pages with redirect annotations...\n";
 		
 		// select all annotations of redirect pages
 		$sql = 'page_id = rd_from AND page_title = attribute_title AND page_namespace = 102';
 		$res = $db->select( array($page, $redirect, $smw_attributes), array('rd_title', 'attribute_title', 'subject_title', 'subject_namespace'), 
 							$sql, 'SMW::repairRedirectAnnotations');
 		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array($row->subject_title, $row->subject_namespace, $row->attribute_title, $row->rd_title);
			}
		}
		$db->freeResult($res);
		
		$res = $db->select( array($page, $redirect, $smw_nary), array('rd_title', 'attribute_title', 'subject_title', 'subject_namespace'), 
 							$sql, 'SMW::repairRedirectAnnotations');
 		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array($row->subject_title, $row->subject_namespace, $row->attribute_title, $row->rd_title);
			}
		}
		$db->freeResult($res);
		
		$sql = 'page_id = rd_from AND page_title = relation_title AND page_namespace = 102';
		$res = $db->select( array($page, $redirect, $smw_relations), array('rd_title', 'relation_title', 'subject_title', 'subject_namespace'), 
 							$sql, 'SMW::repairRedirectAnnotations');
 		
 		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array($row->subject_title, $row->subject_namespace, $row->relation_title, $row->rd_title);
			}
		}
		$db->freeResult($res);
 		
 		if ($verbose && count($result) == 0) echo "None found! Go ahead.\n";
 		
		// replace redirect annotation with annotation of redirect target. Also replace it on templates linked with the subject.
		foreach($result as $r) {
		
			$title = Title::newFromText($r[0], $r[1]);
			if ($title == NULL) continue;
			$this->replaceProperties($title, $r[2], $r[3], $verbose);
			$this->replacePropertiesOnTemplates($title, $r[2], $r[3], $verbose, $db);
		}
		
		if ($verbose) echo "\n\n";
 	}
 	
 	/**
 	 * Gets template which are used on $title and replaced the property annotation of
 	 * $redirectProperty with $targetProperty. Usual constraints apply.
 	 * 
 	 * @param $title Title
 	 * @param $redirectProperty string
 	 * @param $targetProperty string
 	 * @param $verbose boolean
 	 * @param & $db database
 	 */
 	private function replacePropertiesOnTemplates(Title $title, $redirectProperty, $targetProperty, $verbose, & $db) {
 		$templatelinks = $db->tableName('templatelinks');
 		$sql = 'tl_from = '.$title->getArticleID();
 		$res = $db->select( $templatelinks, array('DISTINCT tl_title', 'tl_namespace'), 
 							$sql, 'SMW::replacePropertiesOnTemplates');
 		
 		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$title = Title::newFromText($row->tl_title, $row->tl_namespace);
				if ($title == NULL) continue;
				$this->replaceProperties($title, $redirectProperty, $targetProperty, $verbose);
			}
		}
		$db->freeResult($res);
		
 	}
 	
 	/**
 	 * Replaces annotations of $redirectProperty with $targetProperty on page
 	 * $title.
 	 * 
 	 * @param $title Title
 	 * @param $redirectProperty string
 	 * @param $targetProperty string
 	 * @param $verbose boolean
 	 */
 	private function replaceProperties($title, $redirectProperty, $targetProperty, $verbose) {
 		global $wgParser;
 		$options = new ParserOptions();
 		$rev = Revision::newFromTitle( $title );
		if ($rev == NULL) return;
		$a = new Article($title);
		if ($a == NULL) return;
			
		$matches = array();
		$text = $rev->getText();
						
		preg_match_all('/\[\[\s*'.preg_quote(str_replace("_", " ",$redirectProperty)).'\s*:[:|=]([^]]*)\]\]/i', $text, $matches);
		
		$newtext = NULL;	
		foreach($matches[1] as $m) {
			$repl = "[[".str_replace("_", " ",$targetProperty)."::".$m."]]";
			$newtext = preg_replace('/\[\[\s*'.preg_quote(str_replace("_", " ",$redirectProperty)).'\s*:[:|=]'.preg_quote($m).'\]\]/i', $repl, $text);
		}
			
		if ($newtext != NULL && $text != $newtext) {
			if ($verbose) echo "\n - Replacing annotated redirects on ".$title->getText()."...";
			
			$a->doEdit($newtext, $rev->getComment(), EDIT_UPDATE);
			$wgParser->parse($newtext, $title, $options, true, true, $rev->getID());
			SMWFactbox::storeData(true);
			if ($verbose) echo "done!";
		}
 	}
 }
?>
