<?php

// $Id$

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
		/*$displayProperty = array_key_exists('display', $genTreeParameters)
		                   ? Title::newFromText($genTreeParameters['display'], SMW_NS_PROPERTY)
		                   : NULL;
		                   */
		$displayProperty = array_key_exists('display', $genTreeParameters) ? $genTreeParameters['display'] : NULL;
		$tv_store = TreeviewStorage::getTreeviewStorage();
		if (is_null($tv_store)) return "";

		// setup some settings
		$maxDepth = array_key_exists('maxDepth', $genTreeParameters) ? $genTreeParameters['maxDepth'] : NULL;
		$redirectPage = (($maxDepth > 0) &&  isset($genTreeParameters['redirectPage']))
		                ? Title::newFromText($genTreeParameters['redirectPage']) : NULL;
		// check for dynamic expansion via Ajax
		if (array_key_exists('dynamic', $genTreeParameters)) {
		    $useAjaxExpansion = 1;
	    } else {
	        $useAjaxExpansion = NULL;
	    }

	    // start level of tree
		$hchar = array_key_exists('level', $genTreeParameters)
		         ? str_repeat("*", $genTreeParameters['level']) : "*";
		$tv_store->setup(($useAjaxExpansion) ? 1 : $maxDepth, $redirectPage, $displayProperty, $hchar, $this->json);
		
		$tree = $tv_store->getHierarchyByRelation($relationName, $categoryName, $start);

		// check if we have to return certain parameter with the result set
		if (!$this->json && $useAjaxExpansion) {
		    $returnPrefix= "\x7f"."dynamic=1&property=".$genTreeParameters['property']."&";
		    if ($categoryName) $returnPrefix .= "category=".$genTreeParameters['category']."&";
		    if ($displayProperty) $returnPrefix .= "display=".$genTreeParameters['display']."&";
			if ($start) $returnPrefix .= "start=".$genTreeParameters['start']."&";
		    if ($maxDepth) $returnPrefix .= "maxDepth=".$maxDepth."&";
		    if (isset($genTreeParameters['refresh'])) $returnPrefix .= "refresh=1&";
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
                case ('SMWHaloStore'):
                	self::$store = null; // not supported anymore
                    trigger_error("Old 'SMWHaloStore' is not supported anymore. Please upgrade to 'SMWHaloStore2'");
                    break;
                default:
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
    
    // information about the tree is stored here
   	private $elementProperties;
   	private $sIds;
    private $treeList;
      
    public function setup($maxDepth, $redirectPage, $displayProperty, $hchar, $jsonOutput) {
        $this->maxDepth = ($maxDepth) ? $maxDepth + 1 : NULL; // use absolute depth 
        $this->redirectPage = $redirectPage;
        /*$this->displayProperty = ($displayProperty != NULL)
                                 ? SMWPropertyValue::makeUserProperty($displayProperty->getDBkey())
                                 : NULL;*/
        $this->displayProperty = $displayProperty;
        $this->hchar = $hchar;
        $this->json = $jsonOutput;
        
		// empty all class variables, that will store information about this tree
		$this->elementProperties= array();
		$this->sIds = array();
		$this->treeList = new ChainedList(); // store each element array(0=>id, 1=>depth) in a chained list

        $this->smw_relation_id = NULL;
     	$this->smw_category_id = NULL;
		$this->smw_start_id = NULL;
    }

	public function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL) {
		// empty all class variables, that will store information about this tree
		$this->elementProperties= array();
		$this->sIds = array();
		$this->treeList = new ChainedList(); // store each element array(0=>id, 1=>depth) in a chained list

	  	$db = NULL;
		$categoryConstraintTable = '';
		$categoryConstraintWhere = '';
		$categoryConstraintGroupBy = '';
		$startRefine = '';
		
	    // relation must be set -> we fetch here the smw_id of the requested relation
		if (! $this->getRelationId($relation)) return ($this->json) ? array() : "";
		// if category is set, we will fetch the id of the category
		if ($category) {
		    if (! $this->getCategoryId($category)) return ($this->json) ? array() : "";
		    if (!$db) $db =& wfGetDB( DB_SLAVE );
		    $smw_inst2 = $db->tableName('smw_inst2');
		    $categoryConstraintTable = ",$smw_inst2 i ";
		    $categoryConstraintWhere = " AND i.o_id = ".$this->smw_category_id.
									    " AND (r.o_id = i.s_id OR r.s_id = i.s_id)";
			$categoryConstraintGroupBy = " GROUP BY s_id, o_id";
		}
		
		if (!$db) $db =& wfGetDB( DB_SLAVE );		
		$smw_rels2 = $db->tableName('smw_rels2');

		// match all triples that are of the requested relation
		$query = "SELECT r.s_id as s_id, r.o_id as o_id FROM $smw_rels2 r $categoryConstraintTable"
		         ."WHERE r.p_id = ".$this->smw_relation_id.$categoryConstraintWhere;

		if ($start && (!$this->getStartId($start)))
			return ($this->json)
		            ? array ("name" => $start->getDBKey(), "link" => $start->getDBKey())
		            : $this->hchar."[[".$start->getDBKey()."]]\n";
		
		if ($this->maxDepth && $this->maxDepth == 2) { // only root and one level below
			if ($start)
				$query.= " AND r.o_id = ".$this->smw_start_id;
			else
		    	$query.= " AND r.o_id NOT in (SELECT r.s_id FROM $smw_rels2 $categoryConstraintTable ".
		    			 " WHERE r.p_id = ".$this->smw_relation_id.$categoryConstraintWhere.")";
		}
		$query.= $categoryConstraintGroupBy;
		$res = $db->query($query);
		
		while ($row = $db->fetchObject($res)) {
			if (!isset($this->sIds[$row->s_id]))
				$this->sIds[$row->s_id]= array($row->o_id);
			else
				$this->sIds[$row->s_id][]= $row->o_id;
			// merge all id's into one list to fetch title, links or a
			// certain property of these pages later only once
			if (!isset($this->elementProperties[$row->s_id]))
			    $this->elementProperties[$row->s_id]= array();
			if (!isset($this->elementProperties[$row->o_id]))
			    $this->elementProperties[$row->o_id]= array();
		}
		$db->freeResult($res);
		if (count($this->sIds) == 0) return;
				
		// fetch properties of that smw_ids found (both s_id and o_id)
		$this->getElementProperties();

		// if start is set but not found in element properties, then it doesn't belong to the desired subset
		// make an exception for the main page as this normaly doesn't belong to any category nor as any properties set
		if ($start && !isset($this->elementProperties[$this->smw_start_id])) { 
			if ($start != wfMsg("mainpage")) return;
			$this->elementProperties[$this->smw_start_id] = array(wfMsg("mainpage"), NULL, wfMsg("mainpage"));
		}
		
		$treeList = $this->generateTreeDeepFirstSearch();
        if ($this->json)
            return $this->formatTreeToJson();
		return $this->formatTreeToText();
	}
	
	/**
	 * Get title, category and other detailed information of node elements.
	 * The smw_id will be the key. Each value consists of an array itself
	 * that stores (smw_sortkey, category, smw_title [,property value])
	 * The property value is set only if the given property exists for that
	 * node and if it's supposed to be displayed in the tree afterwards.
	 * 
	 */
	private function getElementProperties() {
	    $db =& wfGetDB( DB_SLAVE );
	    $smw_ids = $db->tableName('smw_ids');
	    $smw_inst2 = $db->tableName('smw_inst2');
	    $limit = 50;   // fetch only that many items at once from the db
	    $i = 0;        // counter when building query to add only limit elements to one query where clause
	    $pos = 0;      // counter when building query to check when all elements have been fetched
	    $sizeOfData = count($this->elementProperties);  // size of $dataArr
	    // query for title, link and category for each smw_id
	    $query1= "SELECT s.smw_id as smw_id, s.smw_sortkey as title, s.smw_title as link, s.smw_namespace as ns, a.o_id as category ".
	             "FROM $smw_ids s, $smw_inst2 a ".
	             "WHERE s.smw_id in (%s) and s.smw_id = a.s_id";
	    // query for fetching title and link for each smw_id that has no category assigned
	    $query2= "SELECT smw_id, smw_sortkey as title, smw_title as link, smw_namespace as ns ".
	             "FROM $smw_ids WHERE smw_id in (%s)";
	    $query_add = ""; // list of ids
	    foreach (array_keys($this->elementProperties) as $id) {
	        $i++;
	        $pos++;
	        $query_add.= $id.",";
	        if ($i == $limit || $pos == $sizeOfData) {
	            $query_add = substr($query_add, 0, -1);
	            $res = $db->query(sprintf($query1, $query_add));
	            $fids = array_flip(explode(",", $query_add));
	            while ($row = $db->fetchObject($res)) {
	                unset($fids[$row->smw_id]);
	                if (is_null($this->smw_category_id) || $this->smw_category_id == $row->category) {
	               		$this->elementProperties[$row->smw_id]= array($row->title, $row->category, $row->link);
                   		$this->postProcessingForElement($row);
	                }
                   	else {
                   		unset($this->elementProperties[$row->smw_id]);
                   		unset($this->sIds[$row->smw_id]);
                   	}
	            }
	            $db->freeResult($res);
	            
	            // if we had less results than ids in where clause, fetch the rest with query2
	            // this needs only be done, if no category is defined where the nodes must belong to
	            // however if a category is defined, the remaining sIds do not belong to the category,
	            // therefore delete them 
	            if (count($fids) > 0) {
	            	if ($this->smw_category_id != NULL) {
	            		foreach (array_keys($fids) as $id) unset($this->elementProperties[$id]);
	            	}
	            	else {
		                $res = $db->query(sprintf($query2, implode(",", array_keys($fids))));
		                while ($row = $db->fetchObject($res)) {
	    	                $this->elementProperties[$row->smw_id]= array($row->title, NULL, $row->link);
	        	            $this->postProcessingForElement($row);
	            	    }
	                	$db->freeResult($res);
	            	}
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
	 * @param Database row Object &$row
	 */
	function postProcessingForElement(&$row) {
		static $prop;
	    // add property value if choosen    
	    if ($this->displayProperty) {
	        $title = Title::newFromText($row->title, $row->ns);
	        if ($prop == NULL) {	        
	        	$pname = Title::newFromText($this->displayProperty, SMW_NS_PROPERTY);
	        	$prop = SMWPropertyValue::makeUserProperty($pname->getDBkey());
	        }
 			$smwValues = smwfGetStore()->getPropertyValues($title, $prop);
 		    if (count($smwValues) > 0) {
	        	$propValue = str_replace("_", " ", $smwValues[0]->getXSDValue());
    		    if (strlen(trim($propValue)) > 0) $this->elementProperties[$row->smw_id][] = $propValue;
 		    }
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
		static $db;
		if (!$db) $db =& wfGetDB( DB_SLAVE );
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
	 * If search was narrowed by a category, maybe the parents in
	 * elementProperties have been deleted aready because they didn't
	 * belong to the desired category. Therefore we also have to check
	 * the sIds array, to look for elements that have a parents which
	 * is not yet in the elementProperties anymore. Hence this element
	 * is a root node as well.
	 *
	 * @return array $rootCats list of element ids of the root category
	 */
	private function getRootCategories() {
	    $rootCats = array();
   		foreach (array_keys($this->elementProperties) as $id) {
    		if (!isset($this->sIds[$id]))
	       		$rootCats[]= $id;
	    }
	    if (!is_null($this->smw_category_id)) {
	    	foreach (array_keys($this->sIds) as $id) {
	    		foreach ($this->sIds[$id] as $item) {
	    			if (!isset($this->sIds[$item]) &&			// parent doesn' exist
	    				isset($this->elementProperties[$id]) &&	// node exists in props -> correct cat
	    				 !in_array($item, $rootCats) &&			// and parent is not yet a root
	    				 !in_array($id, $rootCats)) 			// node itself is not yet a root
	    				$rootCats[]= $id;
	    		}
	    	}
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
	 */
	private function generateTreeDeepFirstSearch() {
	    
	    $findSubTree = array();    // stack for elements that have several parents
		
	    // save here all root categories
	    if ($this->smw_start_id)
	        $rootCats= array($this->smw_start_id);
	    else
	        $rootCats = $this->getRootCategories();

	    // now search for all elements below a root category and create
	    // one list of entries in hierarchic order
	    foreach ($rootCats as $id) {
		    $depth = 1;                    // current depth of element, start by one (root)            
    		
		    $e = array($id, $depth);
		    $this->treeList->insertTail($e);  // add current root to tree
    		
        	$parents[] = $id;              // add current root as parent to stack 

        	// get last parent of stack to look for it's children
	        while ($currParent = end($parents)) {
	            foreach (array_keys($this->sIds) as $s_id) {
                    // check all elements to look for nodes that have the current parent
       		    	foreach (array_keys($this->sIds[$s_id]) as $item) {

       		    	    // add redirect page if it is set and maxdepth is reached
       		    	    if ($this->redirectPage && $this->maxDepth && 
       		    	        $this->maxDepth == $depth) {
       		    	        // make sure to add only one redirect page for all children
       		    	        // of the current node
       		    	        $last = $this->treeList->getLast();
       		    	        if ($last[0] != -1) {
       		    	            $e = array(-1, $depth + 1);
                                $this->treeList->insertTail($e);
       		    	        }
                            continue 2;
                        }
       		    	  
       		    	    if ($this->sIds[$s_id][$item] == $currParent) {

       		    	        // stop descending any further in this subtree IF:
       		    	        // - a category is set and the parent matches this category but
	                        //   not this child OR
	                        // - maxDepth is set and already reached 
	                        // (TODO check if unset this node is really ok)
             		    	if ($this->smw_category_id &&
             		    	    ($this->smw_category_id == $this->elementProperties[$currParent][1]) &&
             		    	    ($this->smw_category_id != $this->elementProperties[$s_id][1]) ||
             		    	    ($this->maxDepth && $this->maxDepth == $depth)) {
                                unset($this->sIds[$s_id]);
             		    	    continue 2;
             		    	}
             		    	
             		    	// increase depth by one and add element to tree
           				    $depth++;
           				    $e = array($s_id, $depth);
    		            	$this->treeList->insertTail($e);

    		            	// If this element was already processed but exists several
	    	            	// times, findSubTree contains the number of additional parents.
               				// The subtree was created at the first time of occurence
           	    			// The children must now be copied to the current position.
           		    		// Then continue with next sibling.
    		            	if (isset($findSubTree[$s_id])) {
           				        $this->addSubTree();
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
		    	                unset($this->sIds[$s_id][$item]);
			                    if (count($this->sIds[$s_id]) == 0) {
				                    unset($this->sIds[$s_id]);
			                    } else {
			                        if (!isset($findSubTree[$s_id]))
				                        $findSubTree[$s_id]= count($this->sIds[$s_id]);
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
	}
	
	/**
	 * Takes the tree created by function getTreeFirstDepthSearch. The last element
	 * might be already in the tree with another parent. It's children must be copied
	 * to the end below the second instance of that element, ad children must be ajusted
	 * to the current depth.
	 *
	 */
	function addSubTree() {
        $subtree = new ChainedList();
	    $last= $this->treeList->getLast();
	    if (! $last) return;
	    $this->treeList->rewind();
	    while ($item = $this->treeList->getCurrent()) {
	        $this->treeList->next();
    		if ($item[0] == $last[0]) {
	    	    $depth = $item[1];          // remember depth found element
		        $diff= $last[1] - $depth;   // calulate difference to current depth
		        while ($item = $this->treeList->getCurrent()) {
		            $this->treeList->next();
			        if ($item[1] > $depth) {
			            $e = array($item[0], $item[1] + $diff);
			            $subtree->insertTail($e);
			        }
        			else {
        			    $this->treeList->forward();
        			    $this->treeList->insertTreeBehind($subtree);
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
     * @return string	  $tree that can be used by the parser function #tree 
     */
	function formatTreeToText() {
		$tree = '';
	    if ($this->redirectPage)
	        $this->elementProperties[-1] = 
	            array($this->redirectPage->getDBkey()."|...", NULL);
	    $fillchar = $this->hchar{0};
	    $prefix = substr($this->hchar, 1);
	    $this->treeList->rewind();
	    while ($item = $this->treeList->getCurrent()) {
	    	$this->treeList->next();
		    $tree.= $prefix.str_repeat($fillchar, $item[1])."[[";
		    if (isset($this->elementProperties[$item[0]][3]))
		        $tree.= $this->elementProperties[$item[0]][0]."|".$this->elementProperties[$item[0]][3];
		    else {
		         if ($this->elementProperties[$item[0]][0] != str_replace("_", " ", $this->elementProperties[$item[0]][2]))
		             $tree.= $this->elementProperties[$item[0]][2]."|".$this->elementProperties[$item[0]][0];
		         else
		             $tree.= $this->elementProperties[$item[0]][0];
		    }
		    $tree.= "]]\n";

	    }
	    $this->treeList = NULL;
	    return $tree;
	}
	
    /**
     * works the same as function formatTreeToText() except that no string
     * is returned but an array of elements as associative arrays. This is
     * later used to be converted into a json compatible string that can be
     * easily parsed by javascript.
     *
     * @return array	  $tree of elements as an asoc array (name, link) 
     */
	function formatTreeToJson() {
	    $tree = array();
	    if ($this->redirectPage)
	        $this->elementProperties[-1] = 
	            array("...", NULL, $this->redirectPage->getDBkey());
	    $this->treeList->rewind();
	    while ($item = $this->treeList->getCurrent()) {
	        $this->treeList->next();
		    $tree[]= array(
		      'name' => isset($this->elementProperties[$item[0]][3]) 
		                ? $this->elementProperties[$item[0]][3]
		                : $this->elementProperties[$item[0]][0],
		      'link' => $this->elementProperties[$item[0]][2],
		      'depth' => $item[1],
		    );
		    unset($item);
	    }
	    $this->treeList = NULL;
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

    public function __construct($item) { 
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
 
    private $_head; 
    private $_tail;
    private $_current;
    
    public function __construct() {
    	$this->_head = NULL;
    	$this->_tail = NULL;
    	$this->_current = NULL;
    }

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