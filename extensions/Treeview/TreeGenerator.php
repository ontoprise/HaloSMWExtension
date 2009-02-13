<?php
global $wgHooks;
$wgHooks['LanguageGetMagic'][] = 'wfTreeGeneratorLanguageGetMagic';

// name of tree generator parser function
define ('GENERATE_TREE_PF', 'generateTree');

class TreeGenerator {
    private $json;

	/**
	 * Register parser function for tree generation
	 *
	 */
	public function __construct() {
		global $wgTreeViewMagic, $wgParser;
		$wgParser->setFunctionHook( GENERATE_TREE_PF, array($this,'generateTree'));

	}

	/**
	 * Entry point for parser function
	 *
	 * @param unknown_type $parser
	 * @return String Wiki-Tree
	 */
	public function generateTree(&$parser) {
		global $wgUser;
		$params = func_get_args();
		array_shift( $params ); // we already know the $parser ...
		$genTreeParameters = array();
		foreach($params as $p) {
			$keyValue = explode("=", $p);
			if (count($keyValue) != 2) continue;
			$genTreeParameters[$keyValue[0]] = $keyValue[1];
		}
		if (!array_key_exists('property', $genTreeParameters)) return "";
		$relationName = Title::newFromText($genTreeParameters['property'], SMW_NS_PROPERTY);
		if (array_key_exists('category', $genTreeParameters)) {
			$genTreeParameters['category'] = str_replace("{{{USER-NAME}}}", $wgUser != NULL ? $wgUser->getName() : "", $genTreeParameters['category']);
			$categoryName = Title::newFromText($genTreeParameters['category'], NS_CATEGORY);
		} else {
			$categoryName = NULL;
		}
		$start = array_key_exists('start', $genTreeParameters) ? Title::newFromText($genTreeParameters['start']) : NULL;
		$displayProperty = array_key_exists('display', $genTreeParameters)
		                   ? Title::newFromText($genTreeParameters['display'], SMW_NS_PROPERTY)
		                   : NULL;
		$tv_store = TreeviewStorage::getTreeviewStorage();

		// setup some settings
		$maxDepth = array_key_exists('maxDepth', $genTreeParameters) ? $genTreeParameters['maxDepth'] : NULL;
		$redirectPage = ($maxDepth > 0) ? Title::newFromText($genTreeParameters['redirectPage']) : NULL;
		// check for dynamic expansion via Ajax
		if (array_key_exists('dynamic', $genTreeParameters)) {
		    $useAjaxExpansion = 1;
		    // set maxlevel depth to 1 if it is not set
		    if (!$maxDepth) $maxDepth = 1;
	    } else {
	        $useAjaxExpansion = NULL;
	    }

	    // start level of tree
		$hchar = array_key_exists('level', $genTreeParameters)
		         ? str_repeat("*", $genTreeParameters['level']) : "*";
		$tv_store->setup($maxDepth, $redirectPage, $displayProperty, $hchar, $this->json);
		
		$tree = $tv_store->getHierarchyByRelation($relationName, $categoryName, $start);
		
		// check if we have to return certain parameter with the result set
		if (!$this->json && $useAjaxExpansion) {
		    $returnPrefix= "\x7f"."dynamic=1&property=".$genTreeParameters['property']."&";
		    if ($categoryName) $returnPrefix .= "category=".$genTreeParameters['category']."&";
		    if ($displayProperty) $returnPrefix .= "display=".$genTreeParameters['display']."&";
		    if ($genTreeParameters['refresh']) $returnPrefix .= "refresh=1&";
		    return $returnPrefix."\x7f".$tree;
		}
		return $tree;
	}
	
    public function setJson() {
        $this->json = true;
    }
}

abstract class TreeviewStorage {
    
	private static $store;
	/**
	 * Returns hierrachy of Titles connected by given relation.
	 *
	 * @param Title $relation Connector relation
	 * @param Title $category Category constraint (optional)
	 * @param Title $start Article to start (optional)
	 * @return Tree of TreeNode objects
	 */
	public abstract function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL);
	
    public static function getTreeviewStorage() {
        global $smwgHaloIP;
        if (self::$store == NULL) {
            global $smwgDefaultStore;
            switch ($smwgDefaultStore) {
                case (SMW_STORE_TESTING):
                    self::$store = null; // not implemented yet
                    trigger_error('Testing store not implemented for HALO extension.');
                    break;
                case ('SMWHaloStore2'):
                    self::$store = new TreeviewStorageSQL2();
                    break;
            }
        }
        return self::$store;
    }
}

class TreeviewStorageSQL2 extends TreeviewStorage {
  
    private $maxDepth;
    private $redirectPage;
    private $displayProperty;
    private $hchar;
    private $json;

    // for the conditions, that can be used for generating the
    // tree, the smw_id will be fetched and stored here
    private $smw_relation_id;
    private $smw_category_id;
    private $smw_start_id;
  
    public function setup($maxDepth, $redirectPage, $displayProperty, $hchar, $jsonOutput) {
        $this->maxDepth = ($maxDepth) ? $maxDepth + 1 : NULL; // use absolute depth 
        $this->redirectPage = $redirectPage;
        $this->displayProperty = $displayProperty;
        $this->hchar = $hchar;
        $this->json = $jsonOutput;
    }

	public function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL) {
	  
		$db =& wfGetDB( DB_MASTER );
		$categoryConstraintTable;
		$categoryConstraintWhere;
		
	    // relation must be set -> we fetch here the smw_id of the requested relation
		if (! $this->getRelationId($relation)) return ($this->json) ? array() : "";
		// if category is set, we will fetch the id of the category
		if ($category) {
		    if (! $this->getCategoryId($category)) return ($this->json) ? array() : "";
		    $smw_inst2 = $db->tableName('smw_inst2');
		    $categoryConstraintTable = ",$smw_inst2 i ";
		    $categoryConstraintWhere = " AND i.o_id = ".$this->smw_category_id." AND r.o_id = i.s_id";    
		}
				
		$smw_rels2 = $db->tableName('smw_rels2');

		// match all triples that are of the requested relation
		$query = "SELECT r.s_id as s_id, r.o_id as o_id FROM $smw_rels2 r $categoryConstraintTable"
		         ."WHERE r.p_id = ".$this->smw_relation_id.$categoryConstraintWhere;
		
		if ($start) {
		    if (!$this->getStartId($start))
		        return ($this->json)
		            ? array ("name" => $start->getDBKey(), "link" => $start->getDBKey())
		            : $this->hchar."[[".$start->getDBKey()."]]\n";
		    $query.= " AND r.o_id = ".$this->smw_start_id;  
		}
		elseif ($this->maxDepth && $this->maxDepth == 2) { // only root and one level below
		    $query.= " AND r.o_id NOT in (SELECT s_id FROM $smw_rels2 WHERE p_id = ".$this->smw_relation_id.")";
		}    
		$res = $db->query($query);
		$elementProperties= array();
		$sIds = array();
		while ($row = $db->fetchObject($res)) {
			if (!isset($sIds[$row->s_id]))
				$sIds[$row->s_id]= array($row->o_id);
			else
				$sIds[$row->s_id][]= $row->o_id;
			// merge all id's into one list to fetch title, links or a
			// certain property of these pages later only once
			if (!isset($elementProperties[$row->s_id]))
			    $elementProperties[$row->s_id]= array();
			if (!isset($elementProperties[$row->o_id]))
			    $elementProperties[$row->o_id]= array();
		}
		if (count($sIds) == 0) return;

		// fetch propeties of that smw_ids found (both s_id and o_id)
		$this->getElementProperties($elementProperties);
		$treeList = $this->generateTreeDeepFirstSearch($sIds, $elementProperties);
        if ($this->json)
            return $this->formatTreeToJson($treeList, $elementProperties);
		return $this->formatTreeToText($treeList, $elementProperties);
	}
	
	/**
	 * Get title, category and other detailed information of node elements.
	 * The smw_id will be the key. Each value consists of an array itself
	 * that stores (smw_sortkey, category, smw_title [,property value])
	 * The property value is set only if the given property exists for that
	 * node and if it's supposed to be displayed in the tree afterwards.
	 *  
	 * @param array &$dataArr which is data[smw_id]= array(data)
	 */
	private function getElementProperties(&$dataArr) {
	    $db =& wfGetDB( DB_MASTER );
	    $smw_ids = $db->tableName('smw_ids');
	    $smw_inst2 = $db->tableName('smw_inst2');
	    $limit = 50;   // fetch only that many items at once from the db
	    $i = 0;        // counter when building query to add only limit elements to one query where clause
	    $pos = 0;      // counter when building query to check when all elements have been fetched
	    $sizeOfData = count($dataArr);  // size of $dataArr
	    // query for title, link and category for each smw_id
	    $query1= "SELECT s.smw_id as smw_id, s.smw_sortkey as title, s.smw_title as link, a.o_id as category ".
	             "FROM $smw_ids s, $smw_inst2 a ".
	             "WHERE s.smw_id in (%s) and s.smw_id = a.s_id";
	    // query for fetching title and link for each smw_id indepentend of category for those
	    // pages that have anotations that do not lead to an existing page 
	    $query2= "SELECT smw_id, smw_sortkey as title, smw_title as link ".
	             "FROM $smw_ids WHERE smw_id in (%s)";
	    $query_add = ""; // list of ids
	    foreach (array_keys($dataArr) as $id) {
	        $i++;
	        $pos++;
	        $query_add.= $id.",";
	        if ($i == $limit || $pos == $sizeOfData) {
	            $query_add = substr($query_add, 0, -1);
	            $res = $db->query(sprintf($query1, $query_add));
	            $fids = array_flip(explode(",", $query_add));
	            while ($row = $db->fetchObject($res)) {
	                unset($fids[$row->smw_id]);
	                $dataArr[$row->smw_id]= array($row->title, $row->category, $row->link);
                    $this->postProcessingForElement($dataArr, $row);
	            }
	            $db->freeResult($res);
	            
	            // if we had less results than ids in where clause, fetch the rest with query2
	            if (count($fids) > 0) {
	                $res = $db->query(sprintf($query2, implode(",", array_keys($fids))));
	                while ($row = $db->fetchObject($res)) {
	                    $dataArr[$row->smw_id]= array($row->title, NULL, $row->link);
	                    $this->postProcessingForElement($dataArr, $row);
	                }
	                $db->freeResult($res);
	            }
	            // if we have already that many elements processed as are in sIds
	            // then we are done and quit the loop here.
	            if ($pos == $sizeOfData)
	                break;
	            // otherwise flush variables for next query
	            $i = 0;  
	            $query_add = "";
	        }
	    }
	}

	/**
	 * Receiving a smw_id from the database can origin from various queries.
	 * To fetch details for an element or set some member variables based on
	 * the configuration, this is the same for all elements. These post
	 * processing is done here after the raw data is received from the db.  
	 *
	 * @param array &$dataArr which is data[smw_id]= array(data)
	 * @param Database row Object &$row
	 */
	function postProcessingForElement(&$dataArr, &$row) {
	    // add property value if choosen    
	    if ($this->displayProperty) {
	        $smwValues = smwfGetStore()->getPropertyValues($row->title, $this->displayProperty);
	        if (count($smwValues) > 0) 
    		    $dataArr[$row->smw_id][] = $smwValues[0]->getXSDValue();
    	    }
	}
	
	/**
	 * Fetch a smw_id by it's title. This function is generic for
	 * fetching smw_ids of categories, properties and pages.
	 *
	 * @param Title $title object of element (category, property ...)
	 * @param integer $ns namespace of element
	 * @return integer $smw_id or NULL on failure
	 */
	private function getSmwIdByTitle(Title &$title, $ns) {
		$db =& wfGetDB( DB_MASTER );
		$smw_ids = $db->tableName('smw_ids');
		$query = "SELECT smw_id FROM $smw_ids WHERE ".
		         "smw_title = ".$db->addQuotes($title->getDBKey()).
		         " AND smw_namespace = ".$ns;
		$res = $db->query($query);
		$s = $db->fetchObject($res);
		$db->freeResult($res);
		if (!$s)
			return NULL;
		return $s->smw_id;
	}
	
	/**
	 * Get smw_id of property for which relations are searched in triples
	 * and stores this id in smw_relation_id
	 *
	 * @param Title $relation
	 * @return Boolean true on success or false on error
	 */
	private function getRelationId(Title &$relation) {
		if ($this->smw_relation_id = $this->getSmwIdByTitle($relation, SMW_NS_PROPERTY)) 
		    return true;
		return false;
	}

	/**
	 * Get smw_id of category for which tree is started to create
	 * and stores this id in smw_category_id
	 *
	 * @param Title $category
	 * @return Boolean true on success or false on error
	 */
	private function getCategoryId(Title $category) {
		if ($this->smw_category_id = $this->getSmwIdByTitle($category, NS_CATEGORY))
		    return true;
		return false;	  
	}

	/**
	 * Get smw_id of article which is defined in start
	 * and stores this id in smw_start_id
	 *
	 * @param Title $start
	 * @return Boolean true on success or false on error
	 */	
	private function getStartId(Title $start) {
		if ($this->smw_start_id = $this->getSmwIdByTitle($start, NS_MAIN))
		    return true;
		return false;	  
	}
	
	/**
	 * get all root categories. These do not have any parents, hence
	 * are not defined in the sIds array. The result is an array of
	 * the smw_id of root element(s).
	 *
	 * @param array &$sIds list of subjects with key = s_id, value = array(parents)
	 * @param array &$elementProperties list of elements with key = smw_id, value = name
	 * @return array $rootCats list of element ids of the root category
	 */
	private function getRootCategories(&$sIds, &$elementProperties) {
	    $rootCats = array();
	    foreach (array_keys($elementProperties) as $id) {
 		    if (!isset($sIds[$id]))
		        $rootCats[]= $id;
	    }
	    return $rootCats;
	}
	
	/**
	 * Create a list of elements ordered hierarchical. This list ist stored in an
	 * array. Each element represents one item. The item has an id (smw_ids.smw_id)
	 * and a depth (level in hierarchy).
	 * An element proceding another is either a parent (depth is one number less) or
	 * a sibling (same depth). The input is taken from the function getHierarchyByRelation
	 * that fetches a list of relations from the db.
	 * While traversing the list of fetched elements from the db, this list is reduced by
	 * each element which has been processed. This reduces memory usage and runing time
	 * when iterating over the array.
	 *
	 * @param array &$sIds list of subjects with key = s_id, value = array(parents)
	 * @param array &$elementProperties list of elements with key = smw_id, values as array
	 * @return array $tree list of elements ordered hierarchical as array(smw_id, depth)
	 */
	private function generateTreeDeepFirstSearch(&$sIds, &$elementProperties) {
	    $tree = new ChainedList(); // store each element array(0=>id, 1=>depth) in a chained list
	    $findSubTree = array();    // stack for elements that have several parents

	    // save here all root categories
	    if ($this->smw_start_id)
	        $rootCats= array($this->smw_start_id);
	    else
	        $rootCats = $this->getRootCategories($sIds, $elementProperties);
	    	    
	    // now search for all elements below a root category and create
	    // one list of entries in hierarchic order
	    foreach ($rootCats as &$id) {
		    $depth = 1;                    // current depth of element, start by one (root)            
    		
		    $e = array($id, $depth);
		    $tree->insertTail($e);  // add current root to tree
    		
        	$parents[] = $id;              // add current root as parent to stack 

        	// get last parent of stack to look for it's children
	        while ($currParent = end($parents)) {
	            foreach (array_keys($sIds) as $s_id) {
                    // check all elements to look for nodes that have the current parent
       		    	foreach (array_keys($sIds[$s_id]) as $item) {

       		    	    // add redirect page if it is set and maxdepth is reached
       		    	    if ($this->redirectPage && $this->maxDepth && 
       		    	        $this->maxDepth == $depth) {
       		    	        // make sure to add only one redirect page for all children
       		    	        // of the current node
       		    	        $last = $tree->getLast();
       		    	        if ($last[0] != -1) {
       		    	            $e = array(-1, $depth + 1);
                                $tree->insertTail($e);
       		    	        }
                            continue 2;
                        }
       		    	  
       		    	    if ($sIds[$s_id][$item] == $currParent) {

       		    	        // stop descending any further in this subtree IF:
       		    	        // - a category is set and the parent matches this category but
	                        //   not this child OR
	                        // - maxDepth is set and already reached 
	                        // (TODO check if unset this node is really ok)
             		    	if ($this->smw_category_id &&
             		    	    ($this->smw_category_id == $elementProperties[$currParent][1]) &&
             		    	    ($this->smw_category_id != $elementProperties[$s_id][1]) ||
             		    	    ($this->maxDepth && $this->maxDepth == $depth)) {
                                unset($sIds[$s_id]);
             		    	    continue 2;
             		    	}
             		    	
             		    	// increase depth by one and add element to tree
           				    $depth++;
           				    $e = array($s_id, $depth);
    		            	$tree->insertTail($e);

    		            	// If this element was already processed but exists several
	    	            	// times, findSubTree contains the number of additional parents.
               				// The subtree was created at the first time of occurence
           	    			// The children must now be copied to the current position.
           		    		// Then continue with next sibling.
    		            	if (isset($findSubTree[$s_id])) {
           				        $this->addSubTree($tree);
           				        $depth--;
		            	        $occurence= count($findSubTree[$s_id]);
               				    if ($occurence == 1)
    		                		unset($findSubTree[$s_id]);
	    		                else
		    		                $findSubTree[$s_id]--;
			                    continue 2;
			                }

               				// The element is traversed the first time, remember current id
    		            	// as parent and look for children. Also if this element has
	    		            // several parents itself, add the id and number of occurences
		    	            // (besides this one) to the list findSubTree. If the element is
			                // traversed again as a child of another parent, then the subtree
			                // which will be composed now can be copied at the new position.
   				            // Then continue with next iteration one level down. 
    			            else { 
   	    			            $parents[] = $s_id;
		    	                unset($sIds[$s_id][$item]);
			                    if (count($sIds[$s_id]) == 0) {
				                    unset($sIds[$s_id]);
			                    } else {
			                        if (!isset($findSubTree[$s_id]))
				                        $findSubTree[$s_id]= count($sIds[$s_id]);
   				                }
    			                continue 3; 
	    		            }
		                }
		            }
	            }
       		    // all children of the current parent have been traversed.
        	    // Continue now one level above (with the sibling of the current parent)
	            array_pop($parents);
	            $depth--;
   		    }
	    }
	    return $tree;
	}
	
	/**
	 * Takes the tree created by function getTreeFirstDepthSearch. The last element
	 * might be already in the tree with another parent. It's children must be copied
	 * to the end below the second instance of that element, ad children must be ajusted
	 * to the current depth.
	 *
	 * @param array &$tree list of elements ordered hierarchical as array(smw_id, depth)
	 */
	function addSubTree(&$tree) {
        $subtree = new ChainedList();
	    $last= $tree->getLast();
	    if (! $last) return;
	    $tree->rewind();
	    while ($item = $tree->getCurrent()) {
	        $tree->next();
    		if ($item[0] == $last[0]) {
	    	    $depth = $item[1];          // remember depth found element
		        $diff= $last[1] - $depth;   // calulate difference to current depth
		        while ($item = $tree->getCurrent()) {
		            $tree->next();
			        if ($item[1] > $depth) {
			            $e = array($item[0], $item[1] + $diff);
			            $subtree->insertTail($e);
			        }
        			else {
        			    $tree->forward();
        			    $tree->insertTreeBehind($subtree);
	        		    return;
        			}
		        }
		    }
	    }
	}

	/**
     * Formats the list of elements from an array to the final ascii output
     * that can be used by the wiki parser. Input is the previously generated
     * tree as elements of an array. To save memory the tree array is reduced
     * by it's elements as soon as they are converted to an ascii string.
     *
     * @param ChainedList &$treeList of elements array(smw_id, depth) that
     * 					  are ordered hierarchical
     * @param array 	  &$elementProperties list of elements consisting of:
     *					  key = smw_id, array(title, link, [property value])
     * @return string	  $tree 
     */
	function formatTreeToText(&$treeList, &$elementProperties) {
	    if ($this->redirectPage)
	        $elementProperties[-1] = 
	            array($this->redirectPage->getDBkey()."|...", NULL);
	    $fillchar = $this->hchar{0};
	    $prefix = substr($this->hchar, 1);
	    $treeList->rewind();
	    while ($item = $treeList->getCurrent()) {
	        $treeList->next();
		    $tree.= $prefix.str_repeat($fillchar, $item[1])."[["
		            .(isset($elementProperties[$item[0]][3])
		             ? $elementProperties[$item[0]][0]."|".$elementProperties[$item[0]][3]
		             : ( $elementProperties[$item[0]][0] != str_replace("_", " ", $elementProperties[$item[0]][2]))
		               ? $elementProperties[$item[0]][2]."|".$elementProperties[$item[0]][0]
		               : $elementProperties[$item[0]][0] )
		            ."]]\n";
		    unset($item);
	    }
	    unset($treeList);
	    return $tree;
	}
    /**
     * works the same as function formatTreeToText() except that no string
     * is returned but an array of elements as associative arrays. This is
     * later used to be converted into a json compatible string that can be
     * easily parsed by javascript.
     *
     * @param ChainedList &$treeList of elements array(smw_id, depth) that
     * 					  are ordered hierarchical
     * @param array 	  &$elementProperties list of elements consisting of:
     *					  key = smw_id, array(title, link, [property value])
     * @return array	  $tree of elements as an asoc array (name, link) 
     */
	function formatTreeToJson(&$treeList, &$elementProperties) {
	    $tree = array();
	    if ($this->redirectPage)
	        $elementProperties[-1] = 
	            array("...", NULL, $this->redirectPage->getDBkey());
	    $treeList->rewind();
	    while ($item = $treeList->getCurrent()) {
	        $treeList->next();
		    $tree[]= array(
		      'name' => isset($elementProperties[$item[0]][3]) 
		                ? $elementProperties[$item[0]][3]
		                : $elementProperties[$item[0]][0],
		      'link' => $elementProperties[$item[0]][2],
		      'depth' => $item[1],
		    );
		    unset($item);
	    }
	    unset($treeList);
	    return $tree;
	}
}
/**
 * Class for storing one element in a chain.
 * Each element has a pointer to the predecessor
 * and the successor and another pointer to
 * the element data itself
 */

class ListItem { 
    public $_data; 
    public $_prev; 
    public $_next; 

    public function __construct(&$item) { 
        $this->_prev = $this->_next = NULL; 
        $this->_data = $item; 
    } 
} 
/**
 * Class for storing a list of elements. List
 * elements are of the type ListItem. This
 * class provides access to the elements stored
 * in the list. 
 */
class ChainedList { 
 
    private $_head = NULL; 
    private $_tail = NULL; 
    private $_current = NULL;
    
    public function rewind() {
        $this->_current = $this->_head;
    }

    public function forward() {
        $this->_current = $this->_tail;
    }
    
    public function next() {
        $this->_current = $this->_current->_next;
    }
    
    public function prev() {
        $this->_current = $this->_current->_prev;
    }
    
    public function getFirst() { 
        if (!$this->_head) 
            return NULL; 
        $this->_current = $this->_head; 
        return $this->_current->_data; 
    } 

    public function getLast() { 
        if (!$this->_tail) 
            return NULL; 
        $this->_current = $this->_tail; 
        return $this->_current->_data; 
    } 

    public function getNext() { 
        if (!$this->_current) 
            return NULL; 
        $this->_current = $this->_current->_next; 
        if (!$this->_current) 
            return NULL; 
        return $this->_current->_data; 
    } 

    public function getPrev() { 
        if (!$this->_current) 
            return NULL; 
        $this->_current = $this->_current->_prev; 
        if (!$this->_current) 
            return NULL; 
        return $this->_current->_data; 
    } 

    public function getCurrent() {
        if (!$this->_current)
            return NULL;
        return $this->_current->_data;
    }
    
    public function insertBefore(&$item) { 
        $tmp = new ListItem($item); 

        /* add to head if no current element */ 
        if (!$this->_current) { 
            $this->_current = $this->_head; 
        } 
        /* list empty? */ 
        if (!$this->_current) { 
            $this->_head = $this->_tail = $tmp; 
            $tmp->_prev = $tmp->_next = NULL; 
        } else { 
            $tmp->_prev = $this->_current->_prev; 
            $tmp->_next = $this->_current; 
            $this->_current->_prev = $tmp; 

            if ($tmp->_prev)	/* is there a proceeding element? */ 
                $tmp->_prev->_next = $tmp; 
            else   /* nothing proceeding - set head to first element */ 
                $this->_head = $tmp; 
        } 
        $this->_current = $tmp; 
        return TRUE; 
    }
    
    public function insertBehind(&$item) { 
        $tmp = new ListItem($item); 

        /* add at the top if there is no current element */ 
        if (!$this->_current) { 
            $this->_current = $this->_tail; 
        } 
        /* list empty? */ 
        if (!$this->_current) { 
            $this->_head = $this->_tail = $tmp; 
            $tmp->_prev = $tmp->_next = NULL; 
        } else { 
            $tmp->_prev = $this->_current; 
            $tmp->_next = $this->_current->_next; 
            $this->_current->_next = $tmp; 

            /* is there a proceeding element? */ 
            if ($tmp->_next)
                $tmp->_next->_prev = $tmp; 
            else {   /* no procedor - set current element to head */ 
                $this->_tail = $tmp; 
                $tmp->_next = NULL; 
            } 
        } 
        $this->_current = $tmp; 
        return TRUE; 
    } 

    public function insertTreeBehind(&$tree) {
    	/* list to insert is empty? */
    	if (!$tree->_head)
    		return NULL;
    
    	/* add at the top if there is no current element */
        if (!$this->_current) { 
            $this->_current = $this->_head; 
        }
        /* list empty? */
        if (!$this->_current) {
        	$this->_head = $tree->_head;
        	$this->_tail = $tree->_tail;
        } else {
            $tree->_head->_prev = $this->_current;
        	$tree->_tail->_next = $this->_current->_next;
        	$this->_current->_next = $tree->_head;
            if (!$tree->_tail->_next)
                $this->_tail = $tree->_tail;
        }
        $this->_current = $tree->_head;
        return TRUE;
    }
    
    public function insertHead(&$item) { 
        $this->_current = $this->_head; 
        return $this->insertBefore($item); 
    } 

    public function insertTail(&$item) { 
        $this->_current = $this->_tail; 
        return $this->insertBehind($item); 
    } 
} 

function wfTreeGeneratorLanguageGetMagic(&$magicWords,$langCode = 0) {
	$magicWords[GENERATE_TREE_PF] = array( 0, GENERATE_TREE_PF );
	return true;
}
?>