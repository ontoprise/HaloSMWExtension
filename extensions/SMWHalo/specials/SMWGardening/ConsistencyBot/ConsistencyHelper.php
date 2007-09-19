<?php
/*
 * Created on 23.05.2007
 *
 * Author: kai
 */
 
 class ConsistencyHelper {
 	
 	// schema property titles
 	public $domainHintRelation;
 	public $rangeHintRelation;
 	public $inverseOf;
 	public $equalTo;
	public $minCard;
	public $maxCard;
	public $transitiveCat;
	public $symetricalCat;
	
	
	public function ConsistencyHelper() {
		// intialize schemaproperties
 		global $smwgContLang, $smwgHaloContLang;
		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$smwSpecialCategories = $smwgHaloContLang->getSpecialCategoryArray();
		$this->domainHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_HINT], SMW_NS_PROPERTY);
		$this->rangeHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_RANGE_HINT], SMW_NS_PROPERTY);
		$this->inverseOf = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_IS_INVERSE_OF], SMW_NS_PROPERTY);
		$this->equalTo = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_IS_EQUAL_TO], SMW_NS_PROPERTY);
		$this->minCard = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MIN_CARD], SMW_NS_PROPERTY);
		$this->maxCard = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MAX_CARD], SMW_NS_PROPERTY);
		$this->transitiveCat = Title::newFromText($smwSpecialCategories[SMW_SC_TRANSITIVE_RELATIONS], NS_CATEGORY);
		$this->symetricalCat = Title::newFromText($smwSpecialCategories[SMW_SC_SYMMETRICAL_RELATIONS], NS_CATEGORY);
		
	}
	
	// data model helper functions
	
 	/**
 	 * Returns a sorted array of (category,supercategory) page_id tuples
 	 * representing an category inheritance graph. 
 	 * 
 	 * @return array of GraphEdge objects;
 	 */
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
 	
 	/**
 	 * Returns a sorted array of (attribute,superattribute) page_id tuples
 	 * representing an attribute inheritance graph. 
 	 * 
 	 *  @return array of GraphEdge objects;
 	 */
 	public function getPropertyInheritanceGraph() {
 		global $smwgContLang;
  		$namespaces = $smwgContLang->getNamespaceArray();
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
 	
 	 	
 	/**
 	 * Returns the domains of the first super property which has defined some
 	 */
 	public function getDomainsOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = $this->searchInSortedGraph($inheritanceGraph, $attributeID);
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
 	
 	/**
 	 * Returns the ranges of the first super property which has defined some
 	 */ 	
 	 public function getRangesOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = $this->searchInSortedGraph($inheritanceGraph, $attributeID);
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
 	
 	/**
 	 * Determines minimum cardinality of an attribute,
 	 * which may be inherited.
 	 */
 	public function getMinCardinalityOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = $this->searchInSortedGraph($inheritanceGraph, $attributeID);
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
 	
 	/**
 	 * Determines minimum cardinality of an attribute,
 	 * which may be inherited.
 	 */
 	public function getMaxCardinalityOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = $this->searchInSortedGraph($inheritanceGraph, $attributeID);
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
 	
 	
 	public function getCategoriesForInstance(Title $instanceTitle) {
		$db =& wfGetDB( DB_MASTER ); // TODO: can we use SLAVE here? Is '=&' needed in PHP5?

		$sql = 'page_title=' . $db->addQuotes($instanceTitle->getDBkey()) . ' AND page_id = cl_from';

		$res = $db->select( array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    'DISTINCT cl_to',
		                    $sql, 'SMW::getCategoriesForInstance', NULL);
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
	
	public function getTypeOfSuperProperty(& $inheritanceGraph, $a) {
 		$attributeID = $a->getArticleID();
 		$superAttributes = $this->searchInSortedGraph($inheritanceGraph, $attributeID);
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
 		$superAttributes = $this->searchInSortedGraph($inheritanceGraph, $attributeID);
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
 	
 	/**
 	 * Returns Title object of pages from the given namespace(s)
 	 * 
 	 * @param $namspaces array of namespaces or NULL
 	 */
 	public function getPages($namespaces = NULL) {
		
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
		
				
		$result = array();
		
	
		$res = $db->select( $db->tableName('page'), 
		                    array('page_title','page_namespace'),
		                    $sql, 'SMW::getPages', NULL );
		
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	
	// various helper functions
	
 	/**
 	 * Checks if there is a path from $c_id1 to $c_id2
 	 * 
 	 * @param $graph
 	 * @param $c_id1 categoryID
 	 * @param $c_id2 categoryID
 	 * 
 	 * @return true, if $c_id1 is subcategory of $c_id2
 	 */
 	public function checkForPath(& $graph, $c_id1, $c_id2) {
 		if ($c_id1 == $c_id2) {
 			return true;
 		}
 		$nextEdges = $this->searchInSortedGraph($graph, $c_id1);
 		if ($nextEdges == null) {
 			return false;
 		}
 		foreach($nextEdges as $e) {
 			if ($e->to == $c_id2) {
 				return true;
 			}
 			$finished = $this->checkForPath($graph, $e->to, $c_id2);
 			if ($finished) {
 				return true;
 			}
 		}
 		return false;
 	}
 	
 	public function isCardinalityValue($s) {
 		// card must be either an integer >= 0 or *
		return preg_match("/\\d+/", trim($s)) > 0;
 	}
 	
 	/**
 	* Searches a value in a sorted array of GraphEdges (sorted for from property). 
 	* If the array is unsorted the result is undefined.
 	* Complexity: O(log(n))
	 *
 	* @return array of all edges: forall x,y <- (x,y) and x = $from, otherwise null.
 	*/
	public function searchInSortedGraph( & $sortedGraph, $from) {
 	 $lowerBound = 0;
 	 $upperBound = count($sortedGraph)-1;
 	 do {
 		$diff = $upperBound - $lowerBound;
 		$diff = $diff % 2 == 0 ? $diff/2 : intval($diff/2);
 		$cs = $lowerBound + $diff;
 		if ($sortedGraph[$cs]->from == $from) {
 			return $this->getAllEdges($sortedGraph, $cs);
	 	} else {
 			if ($sortedGraph[$cs]->from < $from) {
 				$lowerBound = $cs;
 			} else {
 				$upperBound = $cs;
 			}
 		}
 	 } while($lowerBound < $upperBound && $diff > 0);
 	 return $sortedGraph[$upperBound]->from == $from ? $this->getAllEdges($sortedGraph, $upperBound) : null;
	}
	
	private function getAllEdges( & $sortedGraph, $index) {
		$result = array($sortedGraph[$index]);
		$value = $sortedGraph[$index]->from;
		
		$indexUp = $index+1;
		while($sortedGraph[$indexUp]->from == $value) {
			$result[] = $sortedGraph[$indexUp];
			$indexUp++;
		}
		
		$indexDown = $index-1;
		while($sortedGraph[$indexDown]->from == $value) {
			$result[] = $sortedGraph[$indexDown];
			$indexDown--;
		}
		 
		return $result;
	}
	
	public function searchBoundInSortedGraph( & $sortedGraph, $from) {
 	 $lowerBound = 0;
 	 $upperBound = count($sortedGraph)-1;
 	 do {
 		$diff = $upperBound - $lowerBound;
 		$diff = $diff % 2 == 0 ? $diff/2 : intval($diff/2);
 		$cs = $lowerBound + $diff;
 		if ($sortedGraph[$cs]->from == $from) {
 			return $this->getAllEdgeBounds($sortedGraph, $cs);
	 	} else {
 			if ($sortedGraph[$cs]->from < $from) {
 				$lowerBound = $cs;
 			} else {
 				$upperBound = $cs;
 			}
 		}
 	 } while($lowerBound < $upperBound && $diff > 0);
 	 return $sortedGraph[$upperBound]->from == $from ? $this->getAllEdgeBounds($sortedGraph, $upperBound) : null;
	}
	
	private function getAllEdgeBounds( & $sortedGraph, $index) {
		
		$value = $sortedGraph[$index]->from;
		
		$indexUp = $index+1;
		while($sortedGraph[$indexUp]->from == $value) {
			
			$indexUp++;
		}
		
		$indexDown = $index-1;
		while($sortedGraph[$indexDown]->from == $value) {
			
			$indexDown--;
		}
		 
		return array($indexUp-1, $indexDown+1);
	}
 }
?>
