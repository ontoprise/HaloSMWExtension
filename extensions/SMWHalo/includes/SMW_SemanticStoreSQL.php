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
	 	
	public function getPages($namespaces = NULL, $requestoptions = NULL) {
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
		
	
		$res = $db->select( $db->tableName('page'), 
		                    array('page_title','page_namespace'),
		                    $sql, 'SMW::getPages', $this->getSQLOptions($requestoptions,'page_namespace') );
		
		
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
		$visitedNodes = array();
		$allInstances = array();
		$directInstances = $this->getDirectInstances($categoryTitle, $requestoptions);
		
		$subCategories = $this->getDirectSubCategories($categoryTitle);
		foreach($subCategories as $cat) {
			$this->_getInstances($cat, $requestoptions, $allInstances, $visitedNodes);
		}
		return array($directInstances, $allInstances);
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
		$visitedNodes = array();
		$allProperties = array();
		$directProperties = $this->getDirectPropertiesOfCategory($categoryTitle, $requestoptions);
		
		$subCategories = $this->getDirectSuperCategories($categoryTitle);
		foreach($subCategories as $cat) {
			$this->_getPropertiesOfCategory($cat, $requestoptions, $allProperties, $visitedNodes);
		}
		return array($directProperties, $allProperties);
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
	
	function getNumberOfUsage(Title $property) {
		$num = 0;
		$db =& wfGetDB( DB_MASTER );
		$smw_attributes = $db->tableName('smw_attributes');
	 	$smw_relations = $db->tableName('smw_relations');
	 	$smw_nary = $db->tableName('smw_nary');	
		$res = $db->query('SELECT COUNT(subject_title) AS numOfSubjects FROM '.$smw_attributes.' s WHERE attribute_title = '.$db->addQuotes($property->getDBKey()).' GROUP BY attribute_title ' .
						  ' UNION SELECT COUNT(subject_title) AS numOfSubjects FROM '.$smw_nary.' s WHERE attribute_title = '.$db->addQuotes($property->getDBKey()).' GROUP BY attribute_title' .
						  ' UNION SELECT COUNT(subject_title) AS numOfSubjects FROM '.$smw_relations.' s WHERE relation_title = '.$db->addQuotes($property->getDBKey()).' GROUP BY relation_title;');
		
		if($db->numRows( $res ) > 0) {
			$row = $db->fetchObject($res);
			$num = $row->numOfSubjects;
		} 
		$db->freeResult($res);
		return $num;
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

	
 	private function _getInstances(Title $categoryTitle, $requestoptions = NULL, & $allInstances, & $visitedNodes) {
				
		array_push($visitedNodes, $categoryTitle->getArticleID());		
		$directInstances = $this->getDirectInstances($categoryTitle, $requestoptions);
		foreach($directInstances as $inst) {
			$allInstances[] = array($inst, $categoryTitle);
		}
		$subCategories = $this->getDirectSubCategories($categoryTitle);
		foreach($subCategories as $cat) {
			if (!in_array($cat->getArticleID(), $visitedNodes)) { 
				$this->_getInstances($cat, $requestoptions, $allInstances, $visitedNodes);
			}
		}
		array_pop($visitedNodes);
	}
	
	private function _getPropertiesOfCategory(Title $categoryTitle, $requestoptions = NULL, & $allProperties, & $visitedNodes) {
		array_push($visitedNodes, $categoryTitle->getArticleID());
				
		$directProperties = $this->getDirectPropertiesOfCategory($categoryTitle, $requestoptions);
		foreach($directProperties as $inst) {
			$allProperties[] = $inst;
		}
		$subCategories = $this->getDirectSuperCategories($categoryTitle);
		foreach($subCategories as $cat) {
			if (!in_array($cat->getArticleID(), $visitedNodes)) {
				$this->_getPropertiesOfCategory($cat, $requestoptions, $allProperties, $visitedNodes);
			}
		}
		array_pop($visitedNodes);
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
 	
 }
?>
