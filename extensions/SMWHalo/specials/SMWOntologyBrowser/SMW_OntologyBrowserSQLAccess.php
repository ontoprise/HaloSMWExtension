<?php
/*
 * Created on 17.04.2007
 * Author: kai
 * 
 * Database access class (mainly) for OntologyBrowser.
 */
 global $smwgIP;
 require_once( "$smwgIP/includes/storage/SMW_Store.php" );
 require_once( "$smwgIP/includes/SMW_DV_WikiPage.php" );
 require_once( "$smwgIP/includes/SMW_DataValueFactory.php" );
 
 require_once( "$smwgHaloIP/specials/SMWOntologyBrowser/SMW_OntologyBrowserFilter.php" );
 
 define('MAX_RECURSION_DEPTH', 10);
 
 class SMWOntologyBrowserSQLAccess {
 	
 	/**
 	 * Domain hint relation. 
 	 * Determines the domain of an attribute or relation. 
 	 */
 	public $domainHintRelation;
 	
 	/**
 	 * Range hint relation. 
 	 * Determines the range of a relation. 
 	 */
	public $rangeHintRelation;
	
	/**
	 * Minimum cardinality. 
	 * Determines how often an attribute or relations must be instantiated per instance at least.
	 * Allowed values: 0..n, default is 0.
	 */
	public $minCard;
	
	/**
	 * Maximum cardinality. 
	 * Determines how often an attribute or relations may instantiated per instance at most.
	 * Allowed values: 1..*, default is *, which means unlimited.
	 */
	public $maxCard;
	
	/**
	 * Transitive category
	 * All relations of this category are transitive.
	 */
	public $transitiveCat;
	
	/**
	 * All relations of this category are symetrical.
	 */
	public $symetricalCat;
	
	private $filterBrowsing;
	
	public function SMWOntologyBrowserSQLAccess() {
		global $smwgHaloContLang;
		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$smwSpecialCategories = $smwgHaloContLang->getSpecialCategoryArray();
		$this->domainHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_HINT], SMW_NS_PROPERTY);
		$this->rangeHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_RANGE_HINT], SMW_NS_PROPERTY);
		$this->minCard = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MIN_CARD], SMW_NS_PROPERTY);
		$this->maxCard = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MAX_CARD], SMW_NS_PROPERTY);
		$this->transitiveCat = Title::newFromText($smwSpecialCategories[SMW_SC_TRANSITIVE_RELATIONS], NS_CATEGORY);
		$this->symetricalCat = Title::newFromText($smwSpecialCategories[SMW_SC_SYMMETRICAL_RELATIONS], NS_CATEGORY);
		
		// instantiate the used BrowserFilter
		$this->filterBrowsing = new SMWOntologyBrowserFilter();
	}
	
	/**
	 * Returns the used Browser filter.
	 */
	function getBrowserFilter() {
		return $this->filterBrowsing;
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

	/*function getRootRelations($requestoptions = NULL) {
		
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = 'page_namespace=' . SMW_NS_RELATION .
			   ' AND NOT EXISTS (SELECT subject_id FROM smw_specialprops WHERE subject_id = page_id AND property_id = '.SMW_SP_IS_SUBRELATION_OF.')'.
		       $this->getSQLConditions($requestoptions,'page_title','page_title');

		$res = $db->select( $db->tableName('page'), 
		                    'page_title',
		                    $sql, 'SMW::getRootRelations', $this->getSQLOptions($requestoptions,'page_title') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, SMW_NS_RELATION);
			}
		}
		$db->freeResult($res);
		return $result;
	}*/
	
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
	
	function getDirectSubAttributes(Title $attribute, $requestoptions = NULL) {
	 	
	 	$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = 'object_title = ' . $db->addQuotes($attribute->getDBkey());

		$res = $db->select(  $db->tableName('smw_subprops'), 
		                    'subject_title',
		                    $sql, 'SMW::getDirectSubAttributes', $this->getSQLOptions($requestoptions,'subject_title') );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->subject_title, SMW_NS_PROPERTY);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	function getDirectSuperAttributes(Title $attribute, $requestoptions = NULL) {
	 	
	 	$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = 'subject_title = ' . $db->addQuotes($attribute->getDBkey());

		$res = $db->select(  $db->tableName('smw_subprops'), 
		                    'object_title',
		                    $sql, 'SMW::getDirectSuperAttributes', $this->getSQLOptions($requestoptions,'object_title') );
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
 }
?>
