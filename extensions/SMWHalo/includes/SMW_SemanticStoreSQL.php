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
	
	function setup($verbose) {
		$this->setupLogging($verbose);
		$this->createPreDefinedPages($verbose);
 	}
	 	
	public function getPages($namespaces = NULL, $requestoptions = NULL, $ignoreRedirects = false) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		
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
		
		$sql .= $this->getSQLConditions($requestoptions,'page_title','page_title');
		
		$result = array();
		
		if (!$ignoreRedirects) {
			$res = $db->select( $db->tableName('page'), 
		               array('page_title','page_namespace'),
		               $sql, 'SMW::getPages', $this->getSQLOptions($requestoptions,'page_namespace') );
		} else {
			$sql_options = $this->getSQLOptions($requestoptions,'page_namespace');
			$limit = $sql_options['LIMIT'] != NULL ? $sql_options['LIMIT'] : "";
			$offset = $sql_options['OFFSET'] != NULL ? $sql_options['OFFSET'] : "";
			$orderby = $sql_options['ORDER BY'] != NULL ? $sql_options['ORDER BY'] : "";
			$res = $db->query('SELECT page_title, page_namespace FROM '.$db->tableName('page').' LEFT JOIN '.$db->tableName('redirect').' ON page_id=rd_from WHERE '.$sql.' AND rd_title IS NULL '.$limit.' '.$offset.' '.$orderby);
		}
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$db->freeResult($res);
		return $result;
	}
		
	function getRootCategories($requestoptions = NULL) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$categorylinks = $db->tableName('categorylinks');
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND NOT EXISTS (SELECT cl_from FROM '.$categorylinks.' WHERE cl_from = page_id)'.
		       $this->getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select( $db->tableName('page'), 
		                    'page_title',
		                    $sql, 'SMW::getRootCategories', $this->getSQLOptions($requestoptions,'page_title') );
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
		$sql = 'page_namespace=' . SMW_NS_PROPERTY .
			   ' AND NOT EXISTS (SELECT subject_title FROM '.$smw_subprops.' WHERE subject_title = page_title)'.
		       $this->getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select( $db->tableName('page'), 
		                    'page_title',
		                    $sql, 'SMW::getRootProperties', $this->getSQLOptions($requestoptions,'page_title') );
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
			   ' AND cl_to =' . $db->addQuotes($categoryTitle->getDBkey()) . ' AND cl_from = page_id'.
		       $this->getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select(  array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    'page_title',
		                    $sql, 'SMW::getDirectSubCategories', $this->getSQLOptions($requestoptions,'page_title') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, NS_CATEGORY);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	function getDirectSuperCategories(Title $categoryTitle, $requestoptions = NULL) {
		
		$db =& wfGetDB( DB_MASTER );
		$mw_page = $db->tableName('page');
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND page_title =' . $db->addQuotes($categoryTitle->getDBkey()) . ' AND cl_from = page_id AND cl_to IN (SELECT page_title FROM '.$mw_page.' WHERE page_title=cl_to)'.
		       $this->getSQLConditions($requestoptions,'cl_to','cl_to');

		$res = $db->select(  array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    'cl_to',
		                    $sql, 'SMW::getDirectSuperCategories', $this->getSQLOptions($requestoptions,'cl_to') );
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
		
		$db =& wfGetDB( DB_MASTER ); // TODO: can we use SLAVE here? Is '=&' needed in PHP5?

		$sql = 'page_title=' . $db->addQuotes($instanceTitle->getDBkey()) . ' AND page_id = cl_from'.//AND page_namespace = '.NS_MAIN.  
			$this->getSQLConditions($requestoptions,'cl_to','cl_to');

		$res = $db->select( array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    'DISTINCT cl_to',
		                    $sql, 'SMW::getCategoriesForInstance',  $this->getSQLOptions($requestoptions,'cl_to'));
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
		$this->createVirtualTableForInstances($categoryTitle, $db);
		
		$res = $db->select( 'smw_ob_instances', 
		                    'DISTINCT instance, category',
		                    array(), 'SMW::getInstances', $this->getSQLOptions($requestoptions,'category'));
		
		// rewrite result as array
		$result = array();
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(Title::newFromText($row->instance, NS_MAIN), Title::newFromText($row->category, NS_CATEGORY));
			}
		}
		$db->freeResult($res);
		
		$this->dropVirtualTableForInstances($db);
		return $result;
	}
	
	/**
	 * Creates a virtual table and adds all (direct and indirect) instances of
	 * $categoryTitle
	 * 
	 * @param Title $categoryTitle
	 * @param & $db DB reference
	 */
	private function createVirtualTableForInstances($categoryTitle, & $db) {
		global $smwgDefaultCollation;
		$visitedNodes = array();
		$allInstances = array();
		
		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_instances ( instance VARCHAR(255) '.$collation.' NOT NULL, category VARCHAR(255) '.$collation.')
		            TYPE=MEMORY', 'SMW::getInstances' );
		$this->_addDirectInstances($categoryTitle, false, $db);
		
		$numCategories = 0;
		$subCategories = $this->getDirectSubCategories($categoryTitle);
		$numCategories = count($subCategories);
		foreach($subCategories as $cat) {
			$numCategories += $this->_addInstances($cat, $visitedNodes, $db);
		}
		return $numCategories;
	}
	
	/**
	 * Drops virtual table for instances.
	 * 
	 * @param & $db DB reference
	 */
	private function dropVirtualTableForInstances(& $db) {
		$db->query('DROP TABLE smw_ob_instances');
	}
	/**
	 * Adds direct instances of a category to the virtual table 'smw_ob_instances'.
	 * 
	 * @param Title $categoryTitle
	 * @param bool $addCategory If true, categoryTitle is added, otherwise NULL
	 * @param & $db DB reference
	 */
	private function _addDirectInstances($categoryTitle, $addCategory, & $db) {
		$page = $db->tableName('page');
	 	$categorylinks = $db->tableName('categorylinks');
		$superCategory = $addCategory ? $db->addQuotes($categoryTitle->getDBkey()) : "NULL";
		$db->query("INSERT INTO smw_ob_instances (instance, category) " .
				"SELECT page_title AS instance, $superCategory AS category FROM $page, $categorylinks " .
					"WHERE cl_to = ". $db->addQuotes($categoryTitle->getDBkey()). " AND page_id = cl_from AND page_namespace = ". NS_MAIN, 
			           'SMW::_addDirectInstances');
	}
	
	/**
	 * Adds all instances of subcategories of $categoryTitle recursivly to 
	 * virtual table 'smw_ob_instances'. Can handle cycles in category graph.
	 * 
	 * @param Title $categoryTitle 
	 * @param & $visitedNodes
	 * @param & $db DB reference
	 */
	private function _addInstances($categoryTitle, & $visitedNodes, & $db) {
		array_push($visitedNodes, $categoryTitle->getArticleID());		
		$this->_addDirectInstances($categoryTitle, true, $db);
		$numCategories = 0;
		$subCategories = $this->getDirectSubCategories($categoryTitle);
		$numCategories = count($subCategories);
		foreach($subCategories as $cat) {
			if (!in_array($cat->getArticleID(), $visitedNodes)) { 
				$numCategories += $this->_addInstances($cat, $visitedNodes, $db);
			}
		}
		array_pop($visitedNodes);
		return $numCategories;
	}
	
	function getDirectInstances(Title $categoryTitle, $requestoptions = NULL) {
		$db =& wfGetDB( DB_MASTER ); 

		$sql = 'cl_to=' . $db->addQuotes($categoryTitle->getDBkey()) . 
			' AND page_id = cl_from AND page_namespace = '.NS_MAIN.
			$this->getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select( array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    'DISTINCT page_title',
		                    $sql, 'SMW::getDirectInstances', $this->getSQLOptions($requestoptions,'page_title'));
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
	
	
	function getPropertiesOfCategory(Title $categoryTitle, $requestoptions = NULL) {
		$db =& wfGetDB( DB_MASTER ); 
		$this->createVirtualTableForProperties($categoryTitle, $db);
		$res = $db->select( 'smw_ob_properties', 
		                    'DISTINCT property',
		                    array(), 'SMW::getPropertiesOfCategory', $this->getSQLOptions($requestoptions,'property'));
		
		// rewrite result as array
		$result = array();
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->property, SMW_NS_PROPERTY);
			}
		}
		$db->freeResult($res);
		$this->dropVirtualTableForProperties($db);
		return $result;
	}
	
	private function createVirtualTableForProperties(Title $categoryTitle, & $db) {
		global $smwgDefaultCollation;
		$visitedNodes = array();
		$allInstances = array();
		
		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties ( property VARCHAR(255) '.$collation.' NOT NULL)
		            TYPE=MEMORY', 'SMW::getPropertiesOfCategory' );
		$this->_addDirectProperties($categoryTitle, $db);
		
		$subCategories = $this->getDirectSuperCategories($categoryTitle);
		foreach($subCategories as $cat) {
			$this->_addProperties($cat, $visitedNodes, $db);
		}
	}
	
	private function dropVirtualTableForProperties(& $db) {
		$db->query('DROP TABLE smw_ob_properties');
	}
	
	private function _addDirectProperties($categoryTitle, & $db) {
		$smw_nary = $db->tableName('smw_nary');
		$smw_nary_relations = $db->tableName('smw_nary_relations');
		$db->query("INSERT INTO smw_ob_properties (property) " .
				"SELECT n.subject_title AS property FROM $smw_nary n, $smw_nary_relations r" .
					" WHERE n.subject_id = r.subject_id AND r.nary_pos = 0 AND n.attribute_title = ". $db->addQuotes($this->domainRangeHintRelation->getDBkey()). " AND r.object_title = " .$db->addQuotes($categoryTitle->getDBkey()), 
			           'SMW::_addDirectProperties');
	}
	
	private function _addProperties($categoryTitle, & $visitedNodes, & $db) {
		array_push($visitedNodes, $categoryTitle->getArticleID());		
		$this->_addDirectProperties($categoryTitle, $db);
	
		$subCategories = $this->getDirectSuperCategories($categoryTitle);
		foreach($subCategories as $cat) {
			if (!in_array($cat->getArticleID(), $visitedNodes)) { 
				$this->_addProperties($cat, $visitedNodes, $db);
			}
		}
		array_pop($visitedNodes);
	}
			
	function getDirectPropertiesOfCategory(Title $categoryTitle, $requestoptions = NULL) {
		$dv_container = SMWDataValueFactory::newTypeIDValue('__nry');
		$dv = SMWDataValueFactory::newTypeIDValue('_wpg');
  		$dv->setValues($categoryTitle->getDBKey(), $categoryTitle->getNamespace());
  		$dv_container->setDVs(array($dv, NULL));
		return smwfGetStore()->getPropertySubjects($this->domainRangeHintRelation, $dv_container, NULL, 0);
	}
	
	function getDirectSubProperties(Title $attribute, $requestoptions = NULL) {
	 	
	 	$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = 'object_title = ' . $db->addQuotes($attribute->getDBkey());

		$res = $db->select(  $db->tableName('smw_subprops'), 
		                    'subject_title',
		                    $sql, 'SMW::getDirectSubProperties', $this->getSQLOptions($requestoptions,'subject_title') );
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
		$sql = 'subject_title = ' . $db->addQuotes($attribute->getDBkey());

		$res = $db->select(  $db->tableName('smw_subprops'), 
		                    'object_title',
		                    $sql, 'SMW::getDirectSuperProperties', $this->getSQLOptions($requestoptions,'object_title') );
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
		$numCategories = $this->createVirtualTableForInstances($category, $db);
		
		$res = $db->select( 'smw_ob_instances', 
		                    'COUNT(DISTINCT instance) AS numOfInstances',
		                    array(), 'SMW::getNumberOfInstances', array() );
		
		// rewrite result as array
		$numOfInstances = 0;
		
		if($db->numRows( $res ) > 0) {
			$row = $db->fetchObject($res);
			$numOfInstances = $row->numOfInstances;
			
		}
		$db->freeResult($res);
		
		$this->dropVirtualTableForInstances($db);
		return array($numOfInstances, $numCategories);
	}
	
	public function getNumberOfProperties(Title $category) {
		$db =& wfGetDB( DB_MASTER ); 
		$this->createVirtualTableForProperties($category, $db);
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
					 				'JOIN '.$smw_nary.' n ON CONTAINS(s.value_string, '.$db->addQuotes($type->getDBkey()).') AND s.subject_title=n.attribute_title ' .
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
 	
 	
 	public function getDomainsAndRangesOfSuperProperty(& $inheritanceGraph, $p) {
 		$visitedNodes = array();
 		return $this->_getDomainsAndRangesOfSuperProperty($inheritanceGraph, $p, $visitedNodes);
 		
 	}
 	
 	private function _getDomainsAndRangesOfSuperProperty(& $inheritanceGraph, $p, & $visitedNodes) {
 		$results = array();
 		$propertyID = $p->getArticleID();
 		array_push($visitedNodes, $propertyID);
 		$superProperties = GraphHelper::searchInSortedGraph($inheritanceGraph, $propertyID);
 		if ($superProperties == null) return $results;
 		foreach($superProperties as $sp) {
 			$spTitle = Title::newFromID($sp->to);
 			$domainRangeCategories = smwfGetStore()->getPropertyValues($spTitle, $this->domainRangeHintRelation);
 			if (count($domainRangeCategories) > 0) {
 				return $domainRangeCategories;
 			} else {
 				if (!in_array($sp->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getDomainsAndRangesOfSuperProperty($inheritanceGraph, $spTitle, $visitedNodes));
 				} 
 			}
 			
 		} 
 		array_pop($visitedNodes);
 		return $results;
 	}
 	
 
	public function getMinCardinalityOfSuperProperty(& $inheritanceGraph, $a) {
 		$visitedNodes = array();
 		$minCards = $this->_getMinCardinalityOfSuperProperty($inheritanceGraph, $a, $visitedNodes);
 		return max($minCards); // return highest min cardinality
 	}
 	
 	private function _getMinCardinalityOfSuperProperty(& $inheritanceGraph, $a, & $visitedNodes) {
 		$results = array(CARDINALITY_MIN);
 		$attributeID = $a->getArticleID();
 		array_push($visitedNodes, $attributeID);
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if ($superAttributes == null) return $results;
 		foreach($superAttributes as $sa) {
 			$saTitle = Title::newFromID($sa->to);
 			$minCards = smwfGetStore()->getPropertyValues($saTitle, $this->minCard);
 			if (count($minCards) > 0) {
 				
 				return array($minCards[0]->getXSDValue() + 0);
 			} else {
 				if (!in_array($sa->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getMinCardinalityOfSuperProperty($inheritanceGraph, $saTitle, $visitedNodes));
 				} 
 			}
 			
 		} 
		array_pop($visitedNodes);
 		return $results;
 	}
 	
 	
 	public function getMaxCardinalityOfSuperProperty(& $inheritanceGraph, $a) {
 		$visitedNodes = array();
 		$maxCards = $this->_getMaxCardinalityOfSuperProperty($inheritanceGraph, $a, $visitedNodes);
 		return min($maxCards); // return smallest max cardinality
 	}
 	
 	private function _getMaxCardinalityOfSuperProperty(& $inheritanceGraph, $a, & $visitedNodes) {
 		$results = array(CARDINALITY_UNLIMITED);
 		$attributeID = $a->getArticleID();
 		array_push($visitedNodes, $attributeID);
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if ($superAttributes == null) return $results;
 		foreach($superAttributes as $sa) {
 			$saTitle = Title::newFromID($sa->to);
 			$maxCards = smwfGetStore()->getPropertyValues($saTitle, $this->maxCard);
 			if (count($maxCards) > 0) {
 				
 				return array($maxCards[0]->getXSDValue() + 0);
 			} else {
 				if (!in_array($sa->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getMaxCardinalityOfSuperProperty($inheritanceGraph, $saTitle, $visitedNodes));
 				} 
 			}
 			
 		}
 		array_pop($visitedNodes);
 		return $results;
 	}
 	
 	
	
	public function getTypeOfSuperProperty(& $inheritanceGraph, $a) {
 		$visitedNodes = array();
 		return $this->_getTypeOfSuperProperty($inheritanceGraph, $a, $visitedNodes);
 		
 	}
 	
 	private function _getTypeOfSuperProperty(& $inheritanceGraph, $a, & $visitedNodes) {
 		$results = array();
 		$attributeID = $a->getArticleID();
 		array_push($visitedNodes, $attributeID);
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if ($superAttributes == null) return $results;
 		foreach($superAttributes as $sa) {
 			$saTitle = Title::newFromID($sa->to);
 			$types = smwfGetStore()->getSpecialValues($saTitle, SMW_SP_HAS_TYPE);
 			if (count($types) > 0) {
 				return $types;
 			} else {
 				if (!in_array($sa->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getTypeOfSuperProperty($inheritanceGraph, $saTitle, $visitedNodes));
 				} 
 			}
 			
 		}
 		array_pop($visitedNodes);
 		return $results;
 	}
 	
 	
 	public function getCategoriesOfSuperProperty(& $inheritanceGraph, $a) {
 		$visitedNodes = array();
 		return $this->_getCategoriesOfSuperProperty($inheritanceGraph, $a, $visitedNodes);
 	}
 	
 	private function _getCategoriesOfSuperProperty(& $inheritanceGraph, $a, & $visitedNodes) {
 		$results = array();
 		$attributeID = $a->getArticleID();
 		array_push($visitedNodes, $attributeID);
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if ($superAttributes == null) return $results;
 		foreach($superAttributes as $sa) {
 			$saTitle = Title::newFromID($sa->to);
 			$categories = $this->getCategoriesForInstance($saTitle);
 			if (count($categories) > 0) {
 				return $categories;
 			} else {
 				if (!in_array($sa->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getCategoriesOfSuperProperty($inheritanceGraph, $saTitle, $visitedNodes));
 				} 
 			}
 			
 		} 
 		array_pop($visitedNodes);
 		return $results;
 	}
 	
 	
 	public function getCategoryInheritanceGraph() {
 		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND cl_to = page_title';
		$sql_options = array();
		$sql_options['ORDER BY'] = 'cl_from';
		$res = $db->select(  array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    array('cl_from','page_id', 'page_title'),
		                    $sql, 'SMW::getCategoryInheritanceGraph', $sql_options);
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = new GraphEdge($row->cl_from, $row->page_id);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	
 	public function getPropertyInheritanceGraph() {
 		global $smwgContLang;
  		$namespaces = $smwgContLang->getNamespaces();
 		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$smw_subprops = $db->tableName('smw_subprops');
		 $res = $db->query('SELECT p1.page_id AS sub, p2.page_id AS sup FROM '.$smw_subprops.', page p1, page p2 WHERE p1.page_namespace = '.SMW_NS_PROPERTY.
							' AND p2.page_namespace = '.SMW_NS_PROPERTY.' AND p1.page_title = subject_title AND p2.page_title = object_title ORDER BY p1.page_id');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = new GraphEdge($row->sub, $row->sup);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	///// Private methods /////

	/**
	 * Transform input parameters into a suitable array of SQL options.
	 * The parameter $valuecol defines the string name of the column to which
	 * sorting requests etc. are to be applied.
	 */
	protected function getSQLOptions($requestoptions, $valuecol = NULL) {
		$sql_options = array();
		if ($requestoptions !== NULL) {
			if ($requestoptions->limit >= 0) {
				$sql_options['LIMIT'] = $requestoptions->limit;
			}
			if ($requestoptions->offset > 0) {
				$sql_options['OFFSET'] = $requestoptions->offset;
			}
			if ( ($valuecol !== NULL) && ($requestoptions->sort) ) {
				$sql_options['ORDER BY'] = $requestoptions->ascending ? $valuecol : $valuecol . ' DESC';
			}
		}
		return $sql_options;
	}

	/**
	 * Transform input parameters into a suitable string of additional SQL conditions.
	 * The parameter $valuecol defines the string name of the column to which
	 * value restrictions etc. are to be applied.
	 * @param $requestoptions object with options
	 * @param $valuecol name of SQL column to which conditions apply
	 * @param $labelcol name of SQL column to which string conditions apply, if any
	 */
	protected function getSQLConditions($requestoptions, $valuecol, $labelcol = NULL) {
		$sql_conds = '';
		if ($requestoptions !== NULL) {
			$db =& wfGetDB( DB_MASTER ); // TODO: use slave?
			if ($requestoptions->boundary !== NULL) { // apply value boundary
				if ($requestoptions->ascending) {
					if ($requestoptions->include_boundary) {
						$op = ' >= ';
					} else {
						$op = ' > ';
					}
				} else {
					if ($requestoptions->include_boundary) {
						$op = ' <= ';
					} else {
						$op = ' < ';
					}
				}
				$sql_conds .= ' AND ' . $valuecol . $op . $db->addQuotes($requestoptions->boundary);
			}
			if ($labelcol !== NULL) { // apply string conditions
				foreach ($requestoptions->getStringConditions() as $strcond) {
					$string = str_replace(array('_', ' '), array('\_', '\_'), $strcond->string);
					switch ($strcond->condition) {
						case SMW_STRCOND_PRE:
							$string .= '%';
							break;
						case SMW_STRCOND_POST:
							$string = '%' . $string;
							break;
						case SMW_STRCOND_MID:
							$string = '%' . $string . '%';
							break;
					}
					if ($requestoptions->isCaseSensitive) { 
						$sql_conds .= ' AND ' . $labelcol . ' LIKE ' . $db->addQuotes($string);
					} else {
						$sql_conds .= ' AND UPPER(' . $labelcol . ') LIKE UPPER(' . $db->addQuotes($string).')';
					}
				}
			}
		}
		return $sql_conds;
	}

	
 		
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
	
	private function createHelpAttributes($verbose){
		$title = Title::newFromText("Question", SMW_NS_PROPERTY);
		if (!($title->exists())){
			$articleContent = "[[has type::Type:String]]";
			$wgArticle = new Article( $title );
			$wgArticle->doEdit( $articleContent, "New attribute added", EDIT_NEW);
			DBHelper::reportProgress(" Create page ".$title->getNsText().":".$title->getText()."...\n",$verbose);
		}
		$title = Title::newFromText("Description", SMW_NS_PROPERTY);
		if (!($title->exists())){
			$articleContent = "[[has type::Type:String]]";
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
			
		foreach($matches[1] as $m) {
			$repl = "[[".str_replace("_", " ",$targetProperty)."::".$m."]]";
			$newtext = preg_replace('/\[\[\s*'.preg_quote(str_replace("_", " ",$redirectProperty)).'\s*:[:|=]'.preg_quote($m).'\]\]/i', $repl, $text);
		}
			
		if ($text != $newtext) {
			if ($verbose) echo "\n - Replacing annotated redirects on ".$title->getText()."...";
			
			$a->doEdit($newtext, $rev->getComment(), EDIT_UPDATE);
			$wgParser->parse($newtext, $title, $options, true, true, $rev->getID());
			SMWFactbox::storeData($title, true);
			if ($verbose) echo "done!";
		}
 	}
 }
?>
