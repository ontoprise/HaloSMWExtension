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
 require_once( "SMW_GraphHelper.php");
 require_once("SMW_SemanticStore.php");

 
 define('MAX_RECURSION_DEPTH', 10);
 
 class SMWSemanticStoreSQL extends SMWSemanticStore {
 		
	public function SMWSemanticStoreSQL() {
		global $smwgHaloContLang;
		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$smwSpecialCategories = $smwgHaloContLang->getSpecialCategoryArray();
		$domainHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_HINT], SMW_NS_PROPERTY);
		$rangeHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_RANGE_HINT], SMW_NS_PROPERTY);
		$minCard = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MIN_CARD], SMW_NS_PROPERTY);
		$maxCard = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MAX_CARD], SMW_NS_PROPERTY);
		$transitiveCat = Title::newFromText($smwSpecialCategories[SMW_SC_TRANSITIVE_RELATIONS], NS_CATEGORY);
		$symetricalCat = Title::newFromText($smwSpecialCategories[SMW_SC_SYMMETRICAL_RELATIONS], NS_CATEGORY);
		$inverseOf = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_IS_INVERSE_OF], SMW_NS_PROPERTY);
		parent::SMWSemanticStore($domainHintRelation, $rangeHintRelation, $minCard, $maxCard, $transitiveCat, $symetricalCat, $inverseOf);
	}
	
	function setup($verbose) {
 		$this->setupGardening($verbose);
		
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
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND NOT EXISTS (SELECT cl_from FROM categorylinks WHERE cl_from = page_id)'.
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
		$sql = 'page_namespace=' . SMW_NS_PROPERTY .
			   ' AND NOT EXISTS (SELECT subject_title FROM smw_subprops WHERE subject_title = page_title)'.
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
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND page_title =' . $db->addQuotes($categoryTitle->getDBkey()) . ' AND cl_from = page_id AND cl_to IN (SELECT page_title FROM page WHERE page_title=cl_to)'.
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
		
		$allInstances = array();
		$directInstances = $this->getDirectInstances($categoryTitle, $requestoptions);
		
		$subCategories = $this->getDirectSubCategories($categoryTitle);
		foreach($subCategories as $cat) {
			$this->_getInstances($cat, $requestoptions, $allInstances);
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
		// Warning: Category graph MUST NOT contain cycles. Otherwise system crashes in an endless loop.
		$allProperties = array();
		$directProperties = $this->getDirectPropertiesOfCategory($categoryTitle, $requestoptions);
		
		$subCategories = $this->getDirectSuperCategories($categoryTitle);
		foreach($subCategories as $cat) {
			$this->_getPropertiesOfCategory($cat, $requestoptions, $allProperties);
		}
		return array($directProperties, $allProperties);
	}
	
	
			
	function getDirectPropertiesOfCategory(Title $categoryTitle, $requestoptions = NULL) {
		$value = SMWDataValueFactory::newTypeIDValue('_wpg');
  		$value->setValues($categoryTitle->getDBKey(), $categoryTitle->getNamespace());
		return smwfGetStore()->getPropertySubjects($this->domainHintRelation, $value);
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
		
		$res = $db->query('SELECT COUNT(subject_title) AS numOfSubjects FROM smw_attributes s WHERE attribute_title = '.$db->addQuotes($property->getDBKey()).' GROUP BY attribute_title ' .
						  ' UNION SELECT COUNT(subject_title) AS numOfSubjects FROM smw_nary s WHERE attribute_title = '.$db->addQuotes($property->getDBKey()).' GROUP BY attribute_title' .
						  ' UNION SELECT COUNT(subject_title) AS numOfSubjects FROM smw_relations s WHERE relation_title = '.$db->addQuotes($property->getDBKey()).' GROUP BY relation_title;');
		
		if($db->numRows( $res ) > 0) {
			$row = $db->fetchObject($res);
			$num = $row->numOfSubjects;
		} 
		$db->freeResult($res);
		return $num;
	}
	
	
 	public function getDomainsOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if (count($superAttributes) > 0) {
 			$superAttribute = Title::newFromID($superAttributes[0]->to);
 			$domainCategories = smwfGetStore()->getPropertyValues($superAttribute, $this->domainHintRelation);
 			if (count($domainCategories) > 0) {
 				return $domainCategories;
 			} else {
 				return $this->getDomainsOfSuperProperty($inheritanceGraph, $superAttribute);
 			}
 			
 		} else {
 			return array();
 		}
 		
 	}
 	
 		
 	public function getRangesOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if (count($superAttributes) > 0) {
 			$superAttribute = Title::newFromID($superAttributes[0]->to);
 			$rangeCategories = smwfGetStore()->getPropertyValues($superAttribute, $this->rangeHintRelation);
 			if (count($rangeCategories) > 0) {
 				return $rangeCategories;
 			} else {
 				return $this->getRangesOfSuperProperty($inheritanceGraph, $superAttribute);
 			}
 			
 		} else {
 			return array();
 		}
 		
 	}
 	
 
	public function getMinCardinalityOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if (count($superAttributes) > 0) {
 			$superAttribute = Title::newFromID($superAttributes[0]->to);
 			$minCards = smwfGetStore()->getPropertyValues($superAttribute, $this->minCard);
 			if (count($minCards) > 0) {
 				
 				return $minCards[0]->getXSDValue() + 0;
 			} else {
 				return $this->getMinCardinalityOfSuperProperty($inheritanceGraph, $superAttribute);
 			}
 			
 		} else {
 			return CARDINALITY_MIN;
 		}
 	}
 	
 	
 	public function getMaxCardinalityOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if (count($superAttributes) > 0) {
 			$superAttribute = Title::newFromID($superAttributes[0]->to);
 			$maxCards = smwfGetStore()->getPropertyValues($superAttribute, $this->maxCard);
 			if (count($maxCards) > 0) {
 				
 				return trim($maxCards[0]->getXSDValue()) == '*' ? CARDINALITY_UNLIMITED : $maxCards[0]->getXSDValue() + 0;
 			} else {
 				
 				return $this->getMaxCardinalityOfSuperProperty($inheritanceGraph, $superAttribute);
 			}
 			
 		} else {
 			
 			return CARDINALITY_UNLIMITED;
 		}
 	}
 	
 	
	
	public function getTypeOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if (count($superAttributes) > 0) {
 			$superAttribute = Title::newFromID($superAttributes[0]->to);
 			$types = smwfGetStore()->getSpecialValues($superAttribute, SMW_SP_HAS_TYPE);
 			if (count($types) > 0) {
 				return $types;
 			} else {
 				return $this->getTypeOfSuperProperty($inheritanceGraph, $superAttribute);
 			}
 			
 		} else {
 			return array();
 		}
 		
 	}
 	
 	
 	public function getCategoriesOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if (count($superAttributes) > 0) {
 			$superAttribute = Title::newFromID($superAttributes[0]->to);
 			$categories = $this->getCategoriesForInstance($superAttribute);
 			if (count($categories) > 0) {
 				return $categories;
 			} else {
 				return $this->getCategoriesOfSuperProperty($inheritanceGraph, $superAttribute);
 			}
 			
 		} else {
 			return array();
 		}
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
		
		 $res = $db->query('SELECT p1.page_id AS sub, p2.page_id AS sup FROM smw_subprops, page p1, page p2 WHERE p1.page_namespace = '.SMW_NS_PROPERTY.
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

	
 	private function _getInstances(Title $categoryTitle, $requestoptions = NULL, & $allInstances, $depth = 0) {
		// Warning: Category graph MUST NOT contain cycles.
		// Otherwise system crashes in an endless loop.		
		$depth++;
		if ($depth >= MAX_RECURSION_DEPTH) return;
		
		$directInstances = $this->getDirectInstances($categoryTitle, $requestoptions);
		foreach($directInstances as $inst) {
			$allInstances[] = array($inst, $categoryTitle);
		}
		$subCategories = $this->getDirectSubCategories($categoryTitle);
		foreach($subCategories as $cat) {
			$this->_getInstances($cat, $requestoptions, $allInstances, $depth);
		}
	}
	
	private function _getPropertiesOfCategory(Title $categoryTitle, $requestoptions = NULL, & $allProperties, $depth = 0) {
		// Warning: Category graph MUST NOT contain cycles.
		// Otherwise system crashes in an endless loop.		
		
		$depth++;
		if ($depth >= MAX_RECURSION_DEPTH) return;
		
		$directProperties = $this->getDirectPropertiesOfCategory($categoryTitle, $requestoptions);
		foreach($directProperties as $inst) {
			$allProperties[] = $inst;
		}
		$subCategories = $this->getDirectSuperCategories($categoryTitle);
		foreach($subCategories as $cat) {
			$this->_getPropertiesOfCategory($cat, $requestoptions, $allProperties, $depth);
		}
	}
	
	
 	/**
	 * Creates some predefined pages
	 */
	protected function createPreDefinedPages($verbose) {
		global $smwgHaloContLang;
		$this->reportProgress("Creating predefined pages...\n",$verbose);
		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		foreach($ssp as $key => $value) {
			$t = Title::newFromText($value, SMW_NS_PROPERTY);
			if (!$t->exists()) {
				$article = new Article($t);
				$article->insertNewArticle(wfMsg('smw_predefined_props', $t->getText()), "", false, false);
				$this->reportProgress(" Create page ".$t->getNsText().":".$t->getText()."...\n",$verbose);
			}
		}
		
		$scs = $smwgHaloContLang->getSpecialCategoryArray();
		foreach($scs as $key => $value) {
			$t = Title::newFromText($value, NS_CATEGORY);
			if (!$t->exists()) {
				$article = new Article($t);
				$article->insertNewArticle(wfMsg('smw_predefined_cats', $t->getText()), "", false, false);
				$this->reportProgress(" Create page ".$t->getNsText().":".$t->getText()."...\n",$verbose);
			}
		}
		
		$this->createHelpAttributes($verbose);
		
		$this->reportProgress("Predefined pages created successfully.\n",$verbose);
	}
	
	private function createHelpAttributes($verbose){
		$title = Title::newFromText("Question", SMW_NS_PROPERTY);
		if (!($title->exists())){
			$articleContent = "[[has type::Type:String]]";
			$wgArticle = new Article( $title );
			$wgArticle->doEdit( $articleContent, "New attribute added", EDIT_NEW);
			$this->reportProgress(" Create page ".$title->getNsText().":".$title->getText()."...\n",$verbose);
		}
		$title = Title::newFromText("Description", SMW_NS_PROPERTY);
		if (!($title->exists())){
			$articleContent = "[[has type::Type:String]]";
			$wgArticle = new Article( $title );
			$wgArticle->doEdit( $articleContent, "New attribute added", EDIT_NEW);
			$this->reportProgress(" Create page ".$title->getNsText().":".$title->getText()."...\n",$verbose);
		}
		$title = Title::newFromText("DiscourseState", SMW_NS_PROPERTY);
		if (!($title->exists())){
			$articleContent = "[[has type::Type:String]]";
			$wgArticle = new Article( $title );
			$wgArticle->doEdit( $articleContent, "New attribute added", EDIT_NEW);
			$this->reportProgress(" Create page ".$title->getNsText().":".$title->getText()."...\n",$verbose);
		}
	}
	
	/**
	 * Initializes the gardening component
	 */
	protected function setupGardening($verbose) {
			global $wgDBname, $smwgDefaultCollation;
			$db =& wfGetDB( DB_MASTER );

			// create gardening table
			$smw_gardening = $db->tableName('smw_gardening');
			$fname = 'SMW::initGardeningLog';
			
			if (!isset($smwgDefaultCollation)) {
				$collation = '';
			} else {
				$collation = 'COLLATE '.$smwgDefaultCollation;
			}
		
			// create relation table
			$this->setupTable($smw_gardening, array(
				  'id'				=>	'INT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY' ,
				  'user'      		=>  'VARCHAR(255) '.$smwgDefaultCollation.' NOT NULL' ,
				  'gardeningbot'	=>	'VARCHAR(255) '.$smwgDefaultCollation.' NOT NULL' ,
				  'starttime'  		=> 	'DATETIME NOT NULL',
				  'endtime'     	=> 	'DATETIME',
				  'timestamp_start'	=>	'VARCHAR(14) '.$smwgDefaultCollation.' NOT NULL',
				  'timestamp_end' 	=>	'VARCHAR(14) '.$smwgDefaultCollation.'',
				  'useremail'   	=>  'VARCHAR(255) '.$smwgDefaultCollation.'',
				  'log'				=>	'VARCHAR(255) '.$smwgDefaultCollation.'',
				  'progress'		=>	'DOUBLE'), $db, $verbose);


			// create GardeningLog category
			$this->reportProgress("Setting up GardeningLog category ...\n",$verbose);
			$gardeningLogCategoryTitle = Title::newFromText(wfMsg('smw_gardening_log'), NS_CATEGORY);
 			$gardeningLogCategory = new Article($gardeningLogCategoryTitle);
 			if (!$gardeningLogCategory->exists()) {
 				$gardeningLogCategory->insertNewArticle(wfMsg('smw_gardening_log_exp'), wfMsg('smw_gardening_log_exp'), false, false);
 			}
 			$this->reportProgress("   ... GardeningLog category created.\n",$verbose);


 			// fetch all user IDs and add group SMW_GARD_ALL_USERS
 			$this->reportProgress("Add exsiting users to gardening groups ...\n",$verbose);
			$res = $db->select( $db->tableName('user'),
		             array('user_id'),
		             array(),
		             "SMW::initGardeningLog",array());
		    if($db->numRows( $res ) > 0) {
				while ($row = $db->fetchObject($res)) {
					$user = User::newFromId($row->user_id);
					$user->addGroup(SMW_GARD_ALL_USERS);
				}
			}
			$db->freeResult($res);

			// fetch all sysop IDs and add group SMW_GARD_GARDENERS
			$res = $db->select( $db->tableName('user_groups'),
		             array('ug_user'),
		             array('ug_group' => 'sysop'),
		             "SMW::initGardeningLog",array());
		    if($db->numRows( $res ) > 0) {
				while ($row = $db->fetchObject($res)) {
					$user = User::newFromId($row->ug_user);
					$user->addGroup(SMW_GARD_GARDENERS);
				}
			}
			$db->freeResult($res);
			$this->reportProgress("   ... done!\n",$verbose);
	}
	
	/**
	 * Initializes the logging component
	 */
	protected function setupLogging($verbose) {
			global $smwhgEnableLogging; 
			$this->reportProgress("Setting up logging ...\n",$verbose);
			$this->reportProgress("   ... Creating logging database \n",$verbose);
			global $wgDBname;
			$db =& wfGetDB( DB_MASTER );

			// create gardening table
			$smw_logging = $db->tableName('smw_logging');
			$fname = 'SMW::setupLogging';

			// create relation table
			$this->setupTable($smw_logging, array(
				  'id'				=>	'INT(10) UNSIGNED NOT NULL auto_increment PRIMARY KEY' ,
				  'timestamp'      	=>  'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' ,
				  'user'      		=>  'VARCHAR(255)' ,
				  'location'		=>	'VARCHAR(255)' ,
				  'type'			=>	'VARCHAR(255)' ,
				  'function'		=>	'VARCHAR(255)' ,
				  'remotetimestamp'	=>	'VARCHAR(255)' ,
				  'text'			=>  'LONGTEXT' 
				  ), $db, $verbose);

			$this->reportProgress("   ... done!\n",$verbose);
}
 	/**
	 * Make sure the table of the given name has the given fields, provided
	 * as an array with entries fieldname => typeparams. typeparams should be
	 * in a normalised form and order to match to existing values.
	 *
	 * The function returns an array that includes all columns that have been
	 * changed. For each such column, the array contains an entry 
	 * columnname => action, where action is one of 'up', 'new', or 'del'
	 * If the table was already fine or was created completely anew, an empty 
	 * array is returned (assuming that both cases require no action).
	 *
	 * NOTE: the function partly ignores the order in which fields are set up.
	 * Only if the type of some field changes will its order be adjusted explicitly.
	 */
	protected function setupTable($table, $fields, $db, $verbose) {
		global $wgDBname;
		$this->reportProgress("Setting up table $table ...\n",$verbose);
		if ($db->tableExists($table) === false) { // create new table
			$sql = 'CREATE TABLE ' . $wgDBname . '.' . $table . ' (';
			$first = true;
			foreach ($fields as $name => $type) {
				if ($first) {
					$first = false;
				} else {
					$sql .= ',';
				}
				$sql .= $name . '  ' . $type;
			}
			$sql .= ') TYPE=innodb';
			$db->query( $sql, 'SMWSQLStore::setupTable' );
			$this->reportProgress("   ... new table created\n",$verbose);
			return array();
		} else { // check table signature
			$this->reportProgress("   ... table exists already, checking structure ...\n",$verbose);
			$res = $db->query( 'DESCRIBE ' . $table, 'SMWSQLStore::setupTable' );
			$curfields = array();
			$result = array();
			while ($row = $db->fetchObject($res)) {
				$type = strtoupper($row->Type);
				if ($row->Null != 'YES') {
					$type .= ' NOT NULL';
				}
				$curfields[$row->Field] = $type;
			}
			$position = 'FIRST';
			foreach ($fields as $name => $type) {
				if ( !array_key_exists($name,$curfields) ) {
					$this->reportProgress("   ... creating column $name ... ",$verbose);
					$db->query("ALTER TABLE $table ADD `$name` $type $position", 'SMWSQLStore::setupTable');
					$result[$name] = 'new';
					$this->reportProgress("done \n",$verbose);
				} elseif ($curfields[$name] != $type && stripos("auto_increment", $type) == -1) {
					$this->reportProgress("   ... changing type of column $name from '$curfields[$name]' to '$type' ... ",$verbose);
					$db->query("ALTER TABLE $table CHANGE `$name` `$name` $type $position", 'SMWSQLStore::setupTable');
					$result[$name] = 'up';
					$curfields[$name] = false;
					$this->reportProgress("done.\n",$verbose);
				} else {
					$this->reportProgress("   ... column $name is fine\n",$verbose);
					$curfields[$name] = false;
				}
				$position = "AFTER $name";
			}
			foreach ($curfields as $name => $value) {
				if ($value !== false) { // not encountered yet --> delete
					$this->reportProgress("   ... deleting obsolete column $name ... ",$verbose);
					$db->query("ALTER TABLE $table DROP COLUMN `$name`", 'SMWSQLStore::setupTable');
					$result[$name] = 'del';
					$this->reportProgress("done.\n",$verbose);
				}
			}
			$this->reportProgress("   ... table $table set up successfully.\n",$verbose);
			return $result;
		}
	}
	
	/**
	 * Make sure that each of the column descriptions in the given array is indexed by *one* index
	 * in the given DB table.
	 */
	protected function setupIndex($table, $columns, $db) {
		$table = $db->tableName($table);
		$res = $db->query( 'SHOW INDEX FROM ' . $table , 'SMW::SetupIndex');
		if ( !$res ) {
			return false;
		}
		$indexes = array();
		while ( $row = $db->fetchObject( $res ) ) {
			if (!array_key_exists($row->Key_name, $indexes)) {
				$indexes[$row->Key_name] = array();
			}
			$indexes[$row->Key_name][$row->Seq_in_index] = $row->Column_name;
		}
		foreach ($indexes as $key => $index) { // clean up existing indexes
			$id = array_search(implode(',', $index), $columns );
			if ( $id !== false ) {
				$columns[$id] = false;
			} else { // duplicate or unrequired index
				$db->query( 'DROP INDEX ' . $key . ' ON ' . $table, 'SMW::SetupIndex');
			}
		}

		foreach ($columns as $column) { // add remaining indexes
			if ($column != false) {
				$db->query( "ALTER TABLE $table ADD INDEX ( $column )", 'SMW::SetupIndex');
			}
		}
		return true;
	}

	/**
	 * Print some output to indicate progress. The output message is given by
	 * $msg, while $verbose indicates whether or not output is desired at all.
	 */
	protected function reportProgress($msg, $verbose) {
		if (!$verbose) {
			return;
		}
		if (ob_get_level() == 0) { // be sure to have some buffer, otherwise some PHPs complain
			ob_start();
		}
		print $msg;
		ob_flush();
		flush();
	}
	
 }
?>
