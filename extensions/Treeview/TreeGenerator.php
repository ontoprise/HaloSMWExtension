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
		// check property, this is the only mandatory parameter, without it we stop right away
		if (!array_key_exists('property', $genTreeParameters)) return "";
		$relationName = Title::newFromText($genTreeParameters['property'], SMW_NS_PROPERTY);
		// parameter category, if pages from a certain category (and it's subcategories) are wanted
		if (array_key_exists('category', $genTreeParameters)) {
			$genTreeParameters['category'] = str_replace("{{{USER-NAME}}}", $wgUser != NULL ? $wgUser->getName() : "", $genTreeParameters['category']);
			$categoryName = Title::newFromText($genTreeParameters['category'], NS_CATEGORY);
		} else {
			$categoryName = NULL;
		}
		// parameter start, shall we start from some page?
		$start = array_key_exists('start', $genTreeParameters) ? Title::newFromText($genTreeParameters['start']) : NULL;
		// parameter display, that must be the name of a property, which value is displayed instead of the default pagename
		$displayProperty = array_key_exists('display', $genTreeParameters) ? $genTreeParameters['display'] : NULL;
		// parameter opento, must contain a pagename down to where the tree is opened
		$openTo = array_key_exists('opento', $genTreeParameters) ? Title::newFromText($genTreeParameters['opento']) : NULL;
		// parameter urlparams
		$urlparams = array_key_exists('urlparams', $genTreeParameters) ? $genTreeParameters['urlparams'] : NULL;
		// parameter check
		
		
		$tv_store = TreeviewStorage::getTreeviewStorage();
		if (is_null($tv_store)) return "";

		// setup some settings
		$maxDepth = array_key_exists('maxDepth', $genTreeParameters) ? $genTreeParameters['maxDepth'] : NULL;
		$redirectPage = (($maxDepth > 0) &&  isset($genTreeParameters['redirectPage']))
		                ? Title::newFromText($genTreeParameters['redirectPage']) : NULL;
		$condition = array_key_exists('condition', $genTreeParameters) ? $genTreeParameters['condition'] : NULL;
		// check for dynamic expansion via Ajax
		$ajaxExpansion = (array_key_exists('dynamic', $genTreeParameters)) ? 1 : 0;

	    // start level of tree
		$hchar = array_key_exists('level', $genTreeParameters) && ($genTreeParameters['level'] > 0)
		         ? str_repeat("*", $genTreeParameters['level']) : "*";
		$tv_store->setup($ajaxExpansion, $maxDepth, $redirectPage, $displayProperty, $hchar, $this->json, $condition, $openTo);
		
		$tree = $tv_store->getHierarchyByRelation($relationName, $categoryName, $start);

		// check if we have to return certain parameter with the result set when the dynamic expansion
		// is set and the page is rendered for the first two level tree.
		if (!$this->json && ($ajaxExpansion || $tv_store->openToFound() || $urlparams)) {
		    $returnPrefix= "\x7f";
			if ($ajaxExpansion) {			
				$returnPrefix.= "dynamic=1&property=".$genTreeParameters['property']."&";
		    	if ($categoryName) $returnPrefix .= "category=".$genTreeParameters['category']."&";
		    	if ($displayProperty) $returnPrefix .= "display=".$displayProperty."&";
				if ($start) $returnPrefix .= "start=".$genTreeParameters['start']."&";
		    	if ($maxDepth) $returnPrefix .= "maxDepth=".$maxDepth."&";
		    	if ($condition) $returnPrefix .= "condition=".$condition."&";
		    	if (isset($genTreeParameters['refresh'])) $returnPrefix .= "refresh=1&";
			}
			if ($tv_store->openToFound() != null)
				$returnPrefix .= "opento=".$openTo->getDbKey()."&";
			if ($urlparams)
				$returnPrefix .= "urlparams=".urlencode($urlparams)."&";
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

	private $ajaxExpansion;
    private $maxDepth;
    private $redirectPage;
    private $displayProperty;
    private $hchar;
    private $json;
    private $condition;
    private $openTo;

    // for the conditions, that can be used for generating the
    // tree, the smw_id will be fetched and stored here
    private $smw_relation_id;
    private $smw_category_ids;
    private $smw_start_id;
    private $smw_condition_ids;
    
    // information about the tree is stored here
   	private $elementProperties;
   	private $sIds;
    private $treeList;
    private $rootNodes;
    private $leafNodes;

    public function setup($ajaxExpansion, $maxDepth, $redirectPage, $displayProperty, $hchar, $jsonOutput, $condition, $openTo) {
		$this->ajaxExpansion = $ajaxExpansion;
        $this->maxDepth = ($maxDepth) ? $maxDepth + 1 : NULL; // use absolute depth 
        $this->redirectPage = $redirectPage;
        $this->displayProperty = $displayProperty;
        $this->hchar = $hchar;
        $this->json = $jsonOutput;
        $this->condition = $condition;
        $this->openTo = $openTo;
        
        $this->smw_relation_id = NULL;
     	$this->smw_category_ids = NULL;
		$this->smw_start_id = NULL;
		$this->smw_condition_ids = NULL;
		
		// empty class variables, that will store information about this generated tree
		$this->elementProperties= array();
		$this->sIds = array();
		$this->treeList = new ChainedList(); // store each element array(0=>id, 1=>depth) in a chained list
		$this->rootNodes = array();
		$this->leafNodes = array();
    }
    
    public function openToFound() {
    	return ($this->openTo != null);
    }

	public function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL) {

	  	$db = NULL;
		$categoryConstraintTable = '';
		$categoryConstraintWhere = '';
		$categoryConstraintGroupBy = '';
		
	    // relation must be set -> we fetch here the smw_id of the requested relation
		if (! ($this->smw_relation_id = $this->getSmwIdByTitle($relation))) return ($this->json) ? array() : "";
		// if category is set, we will fetch the id of the category
		if ($category) {
			$this->getCategoryList($category);
		    if (is_null($this->smw_category_ids)) return ($this->json) ? array() : "";
		    if (!$db) $db =& wfGetDB( DB_SLAVE );
		    $smw_inst2 = $db->tableName('smw_inst2');
		    $categoryConstraintTable = ",$smw_inst2 i ";
		    $categoryConstraintWhere = " AND i.o_id in (".implode(',', $this->smw_category_ids).")".
									    " AND (r.o_id = i.s_id OR r.s_id = i.s_id)";
			$categoryConstraintGroupBy = " GROUP BY s_id, o_id";
		}
		
		if (!$db) $db =& wfGetDB( DB_SLAVE );		
		$smw_rels2 = $db->tableName('smw_rels2');

		// match all triples that are of the requested relation
		$query = "SELECT r.s_id as s_id, r.o_id as o_id FROM $smw_rels2 r $categoryConstraintTable"
		         ."WHERE r.p_id = ".$this->smw_relation_id.$categoryConstraintWhere;

		if ($start && (! ($this->smw_start_id = $this->getSmwIdByTitle($start))))
			return ($this->json)
		            ? array ("name" => $start->getDBKey(), "link" => $start->getDBKey())
		            : $this->hchar."[[".$start->getDBKey()."]]\n";
		
		if ($this->ajaxExpansion || $this->maxDepth && $this->maxDepth < 3) { // only root and one level below
			if ($start)
				$query.= " AND r.o_id = ".$this->smw_start_id;
			else
		    	$query.= " AND r.o_id NOT in (SELECT r.s_id FROM $smw_rels2 $categoryConstraintTable ".
		    			 " WHERE r.p_id = ".$this->smw_relation_id.$categoryConstraintWhere.")";
		}
		$query.= $categoryConstraintGroupBy;

		// check, if there were condition for the tree.
		if ($this->condition) $this->getCondition($this->condition);

		// now run the query to get all relations of the desired property
		$res = $db->query($query);
		while ($row = $db->fetchObject($res)) {
			$this->addTupleToResult($row->s_id, $row->o_id);
		}
		$db->freeResult($res);

		if (count($this->sIds) == 0) return;

		// check if the tree is supposed to be opened down to a certain node
		if ($this->openTo) $this->getPathToOpenTo();

		// fetch properties of that smw_ids found (both s_id and o_id) if there are no condtions set
		$this->getElementProperties();
		// sorting the nodes (so that the member variable sIds is in correct order) before building the tree 
		$this->sortElements();

		// if:
		// - start is set
		// - but not found in element properties
		// - rootNodes is empty -> no replacements for the start node
		// then the start node doesn't belong to the desired subset and no tree is drawn but:
		// make an exception for the main page as this normaly doesn't belong to any category nor as any properties set
		if ($start && !isset($this->elementProperties[$this->smw_start_id]) && count($this->rootNodes) == 0) { 
			if ($start != wfMsg("mainpage")) return;
			$this->elementProperties[$this->smw_start_id] = array(wfMsg("mainpage"), NULL, wfMsg("mainpage"));
		}

		$this->generateTreeDeepFirstSearch();

        if ($this->json)
            return $this->formatTreeToJson();
		return $this->formatTreeToText();
	}

	/**
	 * If parameter condition is set, then execute the ask query and retrieve
	 * all pages (respective their smw_id) that are wanted for this tree.
	 * Because the title and namespace of the pages are retrieved as well,
	 * fill up the elementProperties array at once with all neccessary
	 * information for the nodes. When fetching the relations for the tree
	 * (i.e. fill the array $sIds) in function getHierarchyByRelation()
	 * only the nodes that are contained in the list at $smw_condition_ids
	 * are used.
	 * 
	 * @access private
	 * @param  string querystring ask query 
	 */
	private function getCondition($querystring) {
		$fixparams = array(
			"format" => "ul",
		);
		$result = SMWQueryProcessor::getResultFromQueryString($querystring, $fixparams, array(), SMW_OUTPUT_WIKI);

		// the list contains some html and wiki text, we need to extract the page values
		$result = strip_tags($result);

		preg_match_all('/\[\[[^\|]+/', $result, $matches); 
		$pages = $matches[0];
		$smwIds = array();
		foreach ($pages as $page) {
			$page = substr($page, 2); // remove the "[["
			$title = Title::newFromDBkey($page);
			$smw_id = $this->getSmwIdByTitle($title);
			$smwIds[] = $smw_id;
			
			// fill the elementProperties variable for this element. Almost all neccessary data
			// are availabe at this point
			$this->elementProperties[$smw_id] = array(substr($page, strpos($page, ':') + 1), $title->getNamespace());
			
			// if we want to display the value of some property instead of the page name then this
			// post processing must be done.
			$row->smw_id = $smw_id;
			$row->title = $this->elementProperties[$smw_id][0];
			$row->ns = $title->getNamespace();
			$this->postProcessingForElement($row);
		}
		$this->smw_condition_ids = $smwIds;
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
	    $limit = 50;   // fetch only that many items at once from the db
	    $i = 0;        // counter when building query to add only limit elements to one query where clause
	    $pos = 0;      // counter when building query to check when all elements have been fetched
	    $sizeOfData = count($this->elementProperties);  // size of $dataArr
	    // query for title for each smw_id
	    $query= "SELECT s.smw_id as smw_id, s.smw_sortkey as title, s.smw_namespace as ns ".
	             "FROM $smw_ids s ".
	             "WHERE s.smw_id in (%s)";
	    // if the tree is limited by categories, add these to the query
	    if (! is_null($this->smw_category_ids)) {
	    	$smw_inst2 = $db->tableName('smw_inst2');
	    	$query = str_replace("WHERE s.smw_id", ", $smw_inst2 a WHERE s.smw_id", $query);
	    	$query.= " AND s.smw_id = a.s_id AND a.o_id in (".implode(',', $this->smw_category_ids).")";
	    }
	    $query_add = ""; // list of ids
	    foreach (array_keys($this->elementProperties) as $id) {
	    	$pos++;
	    	
	    	// if there are special conditions, the elementProperties of some ids
	    	// have been fetched already
	    	if (count($this->elementProperties[$id]) > 0) continue;
	        $i++;
	        $query_add.= $id.","; 
	        if ($i == $limit || $pos == $sizeOfData) {
	            $query_add = substr($query_add, 0, -1);
	            $fids = array_flip(explode(",", $query_add));
	            $res = $db->query(sprintf($query, $query_add));
	            while ($row = $db->fetchObject($res)) {
               		$this->elementProperties[$row->smw_id]= array($row->title, $row->ns);
               		$this->postProcessingForElement($row);
               		unset($fids[$row->smw_id]);
                }
	            $db->freeResult($res);
	            
	            // if there are remaining fids then these didn't belong to the selected set of nodes
	            // remove them from the member variables sIds and elementProperties
	            if (count($fids) > 0) {
	            	foreach (array_keys($fids) as $id) {
	            		unset($this->elementProperties[$id]);
	            		if (isset($this->sIds[$id])) unset($this->sIds[$id]);
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
	 * Get all sub categories for a given title which is supposed to be a category
	 * 
	 * @access private
	 * @param  Object title $category
	 */
	private function getCategoryList($category) {
		$catIds = array();
		$catIds[] = $this->getSmwIdByTitle($category);
		if (count($catIds) == 0) return;
		$db =& wfGetDB( DB_SLAVE );
		$smw_inst = $db->tableName('smw_inst2');
		$smw_ids = $db->tableName('smw_ids');
		$query = "SELECT s.smw_id AS cat FROM $smw_ids s, $smw_inst i " .
				 "WHERE s.smw_id = i.s_id AND i.o_id = %d AND s.smw_namespace = ".NS_CATEGORY;
		$children = $catIds;
		while (count($children) > 0) {
			$currentCat = array_shift($children);
			$res = $db->query(sprintf($query, $currentCat));
			if ($res) {
				while ($row = $db->fetchObject($res)) {
					$children[] = $row->cat;
					$catIds[] = $row->cat;
				}
			}
		}
		$this->smw_category_ids = array_unique($catIds);
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
	private function getSmwIdByTitle(Title &$title) {
		static $db;
		if (!$db) $db =& wfGetDB( DB_SLAVE );
		$ns = $title->getNamespace();
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
	
	private function getPathToOpenTo() {
		// the node to be openend must be a ordinary page, no property nor category
		if (in_array($this->openTo->getNamespace(), array(NS_CATEGORY, SMW_NS_PROPERTY))) {
			$this->openTo = null;
			return;
		}
		// get smw_id of that page
		$currentId = $this->getSmwIdByTitle($this->openTo);

		// the page down to where the tree should be opened could not identified
		if (is_null($currentId)) {
			$this->openTo = null;
			return;
		}

		// if the id is already in the node set, we are done
		if (isset($this->elementProperties[$currentId]))
			return;
			
		// if a current depth is set and no ajax expansion is used, then we are done as
		// well because if the node is not yet in the results, any further lookup will
		// exceed the desired maxDepth
		if ($this->maxDepth != null && $this->ajaxExpansion == 0) {
			$this->openTo = null;
			return; 
		}

		$db =& wfGetDB( DB_SLAVE );
		$smw_inst2 = $db->tableName('smw_inst2');		
		$smw_rels2 = $db->tableName('smw_rels2');
		$query = $this->getQuery4Relation($smw_inst2, $smw_rels2);

		// remember all ids, that we will add now to find the node to open.		
		$newIds = array();
		
		$maxDepth = ($this->maxDepth != null) ? $this->maxDepth : 9999;
		$currentDepth = 2;
		
		while (!isset($this->sIds[$currentId]) && $currentDepth < $maxDepth) {
			$cquery= str_replace('___CONDITION___', "AND r.s_id = ".$currentId, $query);
			$res = $db->query($cquery);
			if ($res && $db->affectedRows() > 0) {
				$row = $db->fetchObject($res);
				$this->addTupleToResult($row->s_id, $row->o_id);
				$cParent = $row->o_id;
				$newIds[] = $row->s_id;
			}
			else break;
			$cquery = str_replace('___CONDITION___', "AND r.o_id = ".$cParent, $query);
			$res = $db->query($cquery);
			if ($res && $db->affectedRows() > 0) {
				while ($row = $db->fetchObject($res)) {
					// this is the relation how we found the parent, so skip it.
					if ($row->s_id == $currentId) continue;
					$this->addTupleToResult($row->s_id, $row->o_id);
					// remember all ids that we add to sIds and elementProperties
					$newIds[] = $row->s_id;
				}
			}
			else break;
			$currentId = $cParent;
			$currentDepth++;
		}
		// current parent after some iterations of the node to open is still not
		// in the result  found, then remove all added
		if (!isset($this->sIds[$currentId]) && !isset($this->sIds[$cParent])) {
			$newIds = array_uniqe($newIds);
			foreach ($newIds as $id) {
				unset($this->sIds[$id]);
				unset($this->elementProperties[$id]);
			}
			$this->openTo = null;
		} 
	}

	private function getQuery4Relation($smw_inst2, $smw_rels2) {
		$categoryConstraintTable = '';
		$categoryConstraintWhere = '';
		$categoryConstraintGroupBy = '';
		if (!is_null($this->smw_category_ids)) {

		    $categoryConstraintTable = ",$smw_inst2 i ";
		    $categoryConstraintWhere = " AND i.o_id in (".implode(',', $this->smw_category_ids).")".
									    " AND (r.o_id = i.s_id OR r.s_id = i.s_id)";
			$categoryConstraintGroupBy = " GROUP BY s_id, o_id";
		}
		// match triples that are of the requested relation with the current id as the object
		return "SELECT r.s_id as s_id, r.o_id as o_id FROM $smw_rels2 r $categoryConstraintTable"
		       ."WHERE r.p_id = ".$this->smw_relation_id.$categoryConstraintWhere.
			   " ___CONDITION___".$categoryConstraintGroupBy;
	}

	private function addTupleToResult($s_id, $o_id) {
		// parameter condition was set, check if current subject
		// (s_id) of the triple is in the allowed page list
		if (($this->condition) && !in_array($s_id, $this->smw_condition_ids))
			return;

		// if we had set parameter start, set all children of start node as root nodes.
		// the start node itself is excluded from the tree 
		if ($this->smw_start_id &&
			$this->smw_start_id == $o_id &&
			!in_array($s_id, $this->rootNodes)) {
			$this->rootNodes[] = $s_id;	
		}

		// if parameter condition is set, but object is not in the
		// allowed page list and s_id is not yet set, create a node without parent
		if (($this->condition) &&
			!in_array($o_id, $this->smw_condition_ids)) {
			// if node doesn't exist yet, create it without parents
			if (!isset($this->sIds[$s_id])) $this->sIds[$s_id]= array();
			$o_id = null;
		}
		// normal handling, create subject node and add parent (object) or add
		// additional parent
		else {			
			if (!isset($this->sIds[$s_id]))
				$this->sIds[$s_id]= array($o_id);
			else
				$this->sIds[$s_id][]= $o_id;
		}
		// merge all id's into one list to fetch title and a
		// certain property of these pages later only once
		if (!isset($this->elementProperties[$s_id]))
		    $this->elementProperties[$s_id]= array();
		if ($o_id && !isset($this->elementProperties[$o_id]))
		    $this->elementProperties[$o_id]= array();	
	}
	
	/**
	 * Get all root nodes. These do not have any parents, hence
	 * are not defined as keys in the sIds array. The result is an
	 * array of the smw_id of root element(s).
	 * If search was narrowed by a category, maybe the parent in
	 * elementProperties has been deleted already because the parent
	 * didn't belong to the desired category. Therefore we also have
	 * to check the sIds array, to look for elements that have a parents
	 * which are not in the elementProperties anymore. Then this element
	 * is a root node as well.
	 *
	 * @return array $rootCats list of element ids of the root category
	 */
	private function getRootNodes() {
		// start is set, root nodes must have been filled alreads in
		// function addTuple2Result()
		if ($this->smw_start_id) {
			$this->rootNodes = $this->sortElements($this->rootNodes);
			return;
		}
		
		// add all nodes, that are are no keys of sIds array -> then these
		// exist as object in the triple and therefore have no parents
   		foreach (array_keys($this->elementProperties) as $id) {
    		if (!isset($this->sIds[$id]))
	       		$this->rootNodes[]= $id;
	    }

		// if the node set was narrowed by a category or condition, then some
		// parents might not exist anymore and it's children are the new roots
	    if (!is_null($this->smw_category_ids) || !is_null($this->condition)) {
	    	foreach (array_keys($this->sIds) as $id) {
	    		$parentNotFound = true;
	    		foreach ($this->sIds[$id] as $item) {
	    			// if parent exists, current node can't be a root
	    			if (isset($this->sIds[$item])) {
	    				$parentNotFound = false;
	    				break;
	    			}
	    		}
	    		if ($parentNotFound &&						// no parent found
	    			isset($this->elementProperties[$id]) &&	// node exists in props -> correct cat
	    			count(array_intersect($this->rootNodes, $this->sIds[$id])) == 0 && // no parent is a root yet
	    			!in_array($id, $this->rootNodes)) {		// node itself is not yet a root
	    			$this->rootNodes[]= $id;
	    		}
	    	}
	    }
	    $this->rootNodes = $this->sortElements($this->rootNodes);
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
	 * @access private
	 */
	private function generateTreeDeepFirstSearch() {
	    // stack for elements that have several parents. key is the smw_id of the node
	    // value is the number of remaining parents, after the node has been added below
	    // his first parent. Everytime the node is traversed again, the subtree of the
	    // node is copied at the new position and the number of children is decreased by 1. 
	    $findSubTree = array();

		// build or complete array with root nodes.
        $this->getRootNodes();	

	    // now search for all elements below a root category and create
	    // one list of entries in hierarchic order
	    foreach ($this->rootNodes as $id) {
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
	                        // - maxDepth is set and already reached 
             		    	if ($this->maxDepth && $this->maxDepth == $depth)
             		    	    continue 2;
             		    	
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
               				    if ($findSubTree[$s_id] == 1)
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
	private function addSubTree() {
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
	private function formatTreeToText() {
		global $wgContLang;
		
		$tree = '';
	    if ($this->redirectPage)
	        $this->elementProperties[-1] = 
	            array($this->redirectPage->getDBkey()."|...", NULL);
	    $fillchar = $this->hchar{0};
	    $prefix = substr($this->hchar, 1);
	    $this->treeList->rewind();
	    while ($item = $this->treeList->getCurrent()) {
	    	$this->treeList->next();

	        $link = $this->elementProperties[$item[0]][0];
	        // prefix link with namespace text
	        if ($this->elementProperties[$item[0]][1] != NS_MAIN)
	        	$link = $wgContLang->getNsText($this->elementProperties[$item[0]][1]).":".$link;

		    $tree.= $prefix.str_repeat($fillchar, $item[1])."[[";
		    // parameter display was set to use some property value for node name and link it with the page
		    if (isset($this->elementProperties[$item[0]][2]))
		        $tree.= $link."|".$this->elementProperties[$item[0]][2];
		    // just the page name is used for the node
		    else {
		    	 // if the page is in the main namespace it's sufficient to display [[page_name]] and the
		    	 // wiki rendering will do the rest. Otherwise display [[Prefix:page_name|page_name]]
		         if ($this->elementProperties[$item[0]][1] != NS_MAIN)
		             $tree.= str_replace(' ', '_', $link)."|".$this->elementProperties[$item[0]][0];
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
	private function formatTreeToJson() {
		global $wgContLang;
		
	    $tree = array();
	    if ($this->redirectPage)
	        $this->elementProperties[-1] = 
	            array("...", NULL, $this->redirectPage->getDBkey());
	    $this->treeList->rewind();
	    while ($item = $this->treeList->getCurrent()) {
	        $this->treeList->next();
	        
	        $link = $this->elementProperties[$item[0]][0];
	        // prefix link with namespace text
	        if ($this->elementProperties[$item[0]][1] != NS_MAIN)
	        	$link = $wgContLang->getNsText($this->elementProperties[$item[0]][1]).":".$link;

		    $tree[]= array(
		      'name' => isset($this->elementProperties[$item[0]][2]) 
		                ? $this->elementProperties[$item[0]][2]
		                : $this->elementProperties[$item[0]][0],
		      'link' => str_replace(' ', '_', $link),
		      'depth' => $item[1],
		    );
		    unset($item);
	    }
	    $this->treeList = NULL;
	    return $tree;
	}
	
	/**
	 * Sorts an array by some search criteria. The array to sort can be an array
	 * with smw_ids as done for root nodes -> see: getRootNodes() as
	 * well for the member variable $sIds in general.
	 * The values to sort for are used by reading the appropriate fields of the
	 * member variable $elementProperties.
	 * Sorting is possible by the following criteria:
	 * - alphabetically by the node name
	 * 
	 * @access private
	 * @param  array $values (optional) array with smw_ids
	 * @return mixed if $values is given, it's ids are returned in sort order
	 * 				 otherwise true is returned and $sIds are sorted afterwards
	 */
	private function sortElements($values = NULL) {
		$orderBy = 1; // at the moment sorting only by node name 
		
		// build the array for sorting, keys must be strings so that the do not get new assigned 
		$sortArr = array();
		$sortIds = is_array($values) ? $values : array_keys($this->elementProperties);
		foreach ($sortIds as $id) {
			// fetch sort values for new item
			if ($orderBy == 1)
				$sortArr[$id] = isset($this->elementProperties[$id][2])
				               ? strtoupper($this->elementProperties[$id][2])
			    	           : strtoupper($this->elementProperties[$id][0]);
		}
		// sort the array now
		asort($sortArr, SORT_STRING);
		
		// if we had an array to sort in parameter, return the keys of the sorted array
		if (is_array($values))
			return array_keys($sortArr);
		
		// otherwise rebuild the sIds array with the sorted keys
		$oldSids = $this->sIds;
		$this->sIds = array();	
		foreach (array_keys($sortArr) as $id) {
			if (isset($oldSids[$id])) $this->sIds[$id]= $oldSids[$id];
		}
		return true;					   
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