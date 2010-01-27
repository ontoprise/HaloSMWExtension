<?php

// $Id$

global $wgHooks;
$wgHooks['LanguageGetMagic'][] = 'wfTreeGeneratorLanguageGetMagic';

// name of tree generator parser function
define ('GENERATE_TREE_PF', 'generateTree');

class TreeGenerator {
    private $json;
    private $loadNextLevel;
    private $useTsc;

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
		// check if initOnload is set. Then we quit right here because the tree will be initialized
		// by an Ajax call once the page is loaded and rendered.
		if (array_key_exists('initOnload', $genTreeParameters)) {
			$params = "";
			foreach ($genTreeParameters as $k => $v) {
				// send this parameter only if dynamic is set
				if ($k == "checkNode") continue;
				// these parameter values are ignored therefore set them here to 1 if set
				if ($k == "dynamic" || $k == "refresh") $v = 1;
				$params .= $k."=".urlencode($v)."&";
			}
			if (array_key_exists('dynamic', $genTreeParameters) && array_key_exists('checkNode', $genTreeParameters))
				$params .= 'checkNode=1&';
			return "\x7finitOnload('$params')\x7f*";
		}
		// most important, check this first
        if (is_null($this->useTsc)) $this->useTsc = array_key_exists('useTsc', $genTreeParameters);

		// check property, this is the only mandatory parameter, without it we stop right away
		if (!array_key_exists('property', $genTreeParameters)) return "";
		$relationName = $this->getValidTitle($genTreeParameters['property'], SMW_NS_PROPERTY);
        if (!$relationName) return "";
		// parameter category, if pages from a certain category (and it's subcategories) are wanted
		if (array_key_exists('category', $genTreeParameters)) {
			$genTreeParameters['category'] = str_replace("{{{USER-NAME}}}", $wgUser != NULL ? $wgUser->getName() : "", $genTreeParameters['category']);
			$categoryName = $this->getValidTitle($genTreeParameters['category'], NS_CATEGORY);
		} else {
			$categoryName = NULL;
		}
		// parameter start, shall we start from some page?
		$start = array_key_exists('start', $genTreeParameters) ? $this->getValidTitle($genTreeParameters['start']) : NULL;
		// parameter display, that must be the name of a property, which value is displayed instead of the default pagename
		$displayProperty = array_key_exists('display', $genTreeParameters) && $this->getValidTitle($genTreeParameters['display'], SMW_NS_PROPERTY)
            ? $genTreeParameters['display'] : NULL;
		// parameter opento, must contain a pagename down to where the tree is opened
		$openTo = array_key_exists('opento', $genTreeParameters) ? $this->getValidTitle($genTreeParameters['opento']) : NULL;
		// parameter urlparams
		$urlparams = array_key_exists('urlparams', $genTreeParameters) ? $genTreeParameters['urlparams'] : NULL;
		// parameter check
		$tv_store = TreeviewStorage::getTreeviewStorage($this->useTsc);
		if (is_null($tv_store)) return "";

		// setup some settings
		$maxDepth = array_key_exists('maxDepth', $genTreeParameters) ? $genTreeParameters['maxDepth'] : NULL;
		$redirectPage = (($maxDepth > 0) &&  isset($genTreeParameters['redirectPage']))
		                ? $this->getValidTitle($genTreeParameters['redirectPage']) : NULL;
		$condition = array_key_exists('condition', $genTreeParameters) ? $genTreeParameters['condition'] : NULL;
		// check for dynamic expansion via Ajax
		// 0 = static, no ajax in use, 1 = ajax in use but (complete or partial) tree is fetched, 2 = only the next level is fetched
		if ($this->loadNextLevel)
			$ajaxExpansion = 2;
		else
			$ajaxExpansion = (!array_key_exists('iolStatic', $genTreeParameters) && !$this->useTsc &&
							 (array_key_exists('dynamic', $genTreeParameters) || $this->json)) ? 1 : 0;

		$checkNode = ($ajaxExpansion > 0 && array_key_exists('checkNode', $genTreeParameters)) ? true : false;

	    // start level of tree
		$hchar = array_key_exists('level', $genTreeParameters) && ($genTreeParameters['level'] > 0)
		         ? str_repeat("*", $genTreeParameters['level']) : "*";
		$tv_store->setup($ajaxExpansion, $maxDepth, $redirectPage, $displayProperty, $hchar, $this->json, $condition, $openTo, $checkNode);
		
		// order by property
		if (array_key_exists('orderbyProperty', $genTreeParameters) &&
            $this->getValidTitle($genTreeParameters['orderbyProperty'], SMW_NS_PROPERTY))
			$tv_store->setOrderByProperty($genTreeParameters['orderbyProperty']);
		
		$tree = $tv_store->getHierarchyByRelation($relationName, $categoryName, $start);

		// check if we have to return certain parameter with the result set when the dynamic expansion
		// is set and the page is rendered for the first tree
		// prefixed parameter are send like GET params in an URL encapsulated with "\x7f". In the tree parser
		// function, these parameters are evaluated and some Javascript for the dTree is added.
		if (!$this->json && (strlen(trim($tree)) > 0) && ($ajaxExpansion > 0 || $tv_store->openToFound() || $urlparams)) {
		    $returnPrefix= "\x7f";
			if ($ajaxExpansion > 0) {			
				$returnPrefix.= "dynamic=1&property=".$genTreeParameters['property']."&";
		    	if ($categoryName) $returnPrefix .= "category=".$genTreeParameters['category']."&";
		    	if ($displayProperty) $returnPrefix .= "display=".$displayProperty."&";
				if ($start) $returnPrefix .= "start=".$genTreeParameters['start']."&";
		    	if ($maxDepth) $returnPrefix .= "maxDepth=".$maxDepth."&";
		    	if ($condition) $returnPrefix .= "condition=".$condition."&";
		    	if (isset($genTreeParameters['refresh'])) $returnPrefix .= "refresh=1&";
		    	if (isset($genTreeParameters['orderbyProperty'])) $returnPrefix .= "orderbyProperty=".$genTreeParameters['orderbyProperty']."&";
		    	if (isset($genTreeParameters['checkNode'])) $returnPrefix .= "checkNode=1&";
			}
			if ($tv_store->openToFound() != null) {
				$arg = ($openTo instanceof Title) ? $openTo->getPrefixedDBkey() : $openTo;
				$returnPrefix .= "opento=".urlencode($arg)."&";
			}
			if ($urlparams)
				$returnPrefix .= "urlparams=".urlencode($urlparams)."&";
            if ($this->useTsc)
                $returnPrefix .= "useTsc=1&";
		    return $returnPrefix."\x7f".$tree;
		}
		return $tree;
	}
	
    public function setJson() {
        $this->json = true;
    }
    public function setLoadNextLevel() {
        $this->loadNextLevel = true;
    }
    public function setUseTsc() {
        $this->useTsc = true;
    }
    private function getValidTitle($text, $ns = 0) {
        $t = Title::newFromText($text, $ns);
        if ($t == null) return;
        if (!$t->exists() && $this->useTsc) return $t;
        if ($ns == SMW_NS_PROPERTY &&
            in_array('propertyread', User::getAllRights()) &&
            !$t->userCan('propertyread'))
            return;
        return $t->userCanRead() ? $t : null;
    }
}

abstract class TreeviewStorage {
    
	private static $store;

    // parameter setup from parser function call in wikitext
	public $ajaxExpansion;
    public $maxDepth;
    public $redirectPage;
    public $displayProperty;
    public $hchar;
    public $json;
    public $condition;
    public $openTo;
    public $checkNode;

	// sorting options
	public $orderByProperty;
	public $orderSequence;

    // information about the tree is stored here
   	public $elementProperties;
   	public $sIds;
    public $treeList;
    public $rootNodes;
    public $leafNodes;
    public $openToPath;

    // internal ids
    public $smw_start_id;
    public $smw_category_ids;

	/**
	 * Returns hierrachy of Titles connected by given relation.
	 *
	 * @param Title $relation Connector relation
	 * @param Title $category Category constraint (optional)
	 * @param Title $start Article to start (optional)
	 * @return Tree of TreeNode objects
	 */
	public abstract function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL);
	
    public static function getTreeviewStorage($tsc= false) {
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
                    if ($tsc /*&& $smwgDefaultStore == 'SMWTripleStore'*/)
                        self::$store = new TreeviewTriplestore();
                    else
                        self::$store = new TreeviewStorageSQL2();
                    break;
            }
        }
        return self::$store;
    }

    public function setup($ajaxExpansion, $maxDepth, $redirectPage, $displayProperty, $hchar, $jsonOutput, $condition, $openTo, $checkNode) {
        // here the options are stored, that can be set in the generateTree parser function
		$this->ajaxExpansion = $ajaxExpansion;
        $this->maxDepth = ($maxDepth) ? $maxDepth + 1 : NULL; // use absolute depth
        $this->redirectPage = $redirectPage;
        $this->displayProperty = $displayProperty;
        $this->hchar = $hchar;
        $this->json = $jsonOutput;
        $this->condition = $condition;
        $this->openTo = $openTo;
        $this->checkNode = $checkNode;

        // flush sorting options, these will be set by functions setOrderByProperty() and setOrderDescending()
        $this->orderByProperty = null;
        $this->orderSequence = null;

        // empty class variables, that will store information about this generated tree and also general information
		$this->elementProperties= array();
		$this->sIds = array();
		$this->treeList = new ChainedList(); // store each element array(0=>id, 1=>depth) in a chained list
		$this->rootNodes = array();
        $this->leafNodes = array();
        $this->openToPath = array();

    }

    /**
	 * Set a property name, if nodes are supposed to be sorted by values of this
	 * property.
	 *
	 * @access public
	 * @param  string $property name of property
	 */
	public function setOrderByProperty($property) {
		$this->orderByProperty = $property;
	}

    /**
     * Set order descending
     *
     * @access public
     */
    public function setOrderDescending() {
    	$this->orderSequence = -1;
    }

    /**
     * Returns true or false depending on the fact if the node down to where the tree is
     * supposed to be opened. If this is set in the parameter openTo in the parser function
     * the member variable will contain the name.
     * Later from that name the smw_id is evaluated and checked wether the node is in the
     * result nodes. If this is not the case, the member variable openTo is set to null.
     * Based on that fact here we know if the node was found.
     *
     * @access public
     * @return Boolean found true if node is found, false otherwise
     */
    public function openToFound() {
    	return ($this->openTo != null);
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
	public function getRootNodes() {
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
	    if (isset($this->smw_category_ids) || isset($this->condition)) {
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
	 * @access public
	 */
	public function generateTreeDeepFirstSearch() {
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
	                        // - ajax is used and depth = 2 is reached
	                        // - current node or parent are not in the opento path
	                        //   (while using ajax expansion, and exceding depth beyond 2)
             		    	if ($this->maxDepth && $this->maxDepth == $depth)
             		    		continue 2;
             		    	if ($this->ajaxExpansion > 0 && $depth == 2 && $this->openTo == null)
             		    	    continue 2;
             		    	if ($this->ajaxExpansion > 0 && $depth > 1 && !in_array($s_id, $this->openToPath) && !in_array($currParent, $this->openToPath))
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
	public function addSubTree() {
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
	public function formatTreeToText() {
		$tree = '';
	    if ($this->redirectPage)
	        $this->elementProperties[-1] =
	            new ElementProperty($this->redirectPage->getText(), $this->redirectPage->getNamespace(), "...");
	    $fillchar = $this->hchar{0};
	    $prefix = substr($this->hchar, 1);
	    $this->treeList->rewind();
	    while ($item = $this->treeList->getCurrent()) {
	    	$this->treeList->next();
		    $tree.= $prefix.str_repeat($fillchar, $item[1]).$this->elementProperties[$item[0]]->getWikitext();
		    if (in_array($item[0], $this->leafNodes)) $tree.= "\x7f";
		    $tree .= "\n";
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
	public function formatTreeToJson() {
	    $tree = array();
	    if ($this->redirectPage)
	        $this->elementProperties[-1] =
	        	new ElementProperty($this->redirectPage->getText(), $this->redirectPage->getNamespace(), "...");
	    $this->treeList->rewind();
	    while ($item = $this->treeList->getCurrent()) {
	        $this->treeList->next();

		    $node= array(
		      'name' => $this->elementProperties[$item[0]]->getDisplayName(),
		      'link' => urlencode($this->elementProperties[$item[0]]->getLink()),
		      'depth' => $item[1],
		    );
		    if (in_array($item[0], $this->leafNodes)) $node['leaf'] = 1;
		    $tree[] = $node;
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
	public function sortElements($values = NULL) {

		// build the array for sorting, keys must be strings so that the do not get new assigned
		$sortArr = array();
		$sortIds = is_array($values) ? $values : array_keys($this->elementProperties);
		foreach ($sortIds as $id) {
			// fetch sort values for new item
			$sortArr[$id] = strtoupper($this->elementProperties[$id]->getSortName());
		}
		// sort the array now
		if ($this->orderSequence == -1) // descending
			arsort($sortArr, SORT_STRING);
		else  // ascending (default)
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

    public function checkLeafHc() {
		$currentDepth = 1;			// current depth of node that's being investigated
		$nodesToCheck = array();	// filled with ids of children, that must be checked
	    $this->treeList->rewind();
	    while ($item = $this->treeList->getCurrent()) {
	    	$this->treeList->next();
	    	// depth of current node is higher than previous node -> previous node has children
	    	if ($item[1] > $currentDepth) {
	    		array_pop($nodesToCheck);
	    		$nodesToCheck[] = $item[0];
	    	}
    		$nodesToCheck[] = $item[0];
    		$currentDepth = $item[1];
	    }
	    // walk through the $nodesToCheck array and see if there already some nodes that know
	    // that they have children
	    for ($i = 0, $is = count($nodesToCheck); $i < $is; $i++) {
			foreach ($this->sIds as $child) {
				foreach ($child as $parent) {
					if ($parent == $nodesToCheck[$i]) {
						unset($nodesToCheck[$i]);
						continue 3;
					}
				}
			}
		}
        return $nodesToCheck;
    }

}

class TreeviewTriplestore extends TreeviewStorage {

	public function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL) {
        global $smwgDefaultStore, $smwgQMaxInlineLimit;

        $query = "[[".$relation->getText()."::+]]";
        if ($category) {
            $query = "[[Category:".$category->getText()."]]".$query;
            // not needed here but must be set so that rootNodes will be examined correctly
            $this->smw_category_ids = array(1);
        }

		$fixparams = array(
			"format" => "table",
			"limit" => $smwgQMaxInlineLimit,
            "link" => "none",
            "merge" => "false"
		);
        // printouts
        $printout= array(
            new SMWPrintRequest(SMWPrintRequest::PRINT_PROP, "", SMWPropertyValue::makeUserProperty($relation->getText())),
        );
        
        $result = SMWSPARQLQueryProcessor::getResultFromQueryString($query, $fixparams, $printout, SMW_OUTPUT_HTML);
		if (stripos($result, "Could not connect to host") !== false)
			$result = SMWQueryProcessor::getResultFromQueryString($query, $fixparams, $printout, SMW_OUTPUT_HTML);

		$doc = new DomDocument();
        $doc->loadHTML($result);
        $rows = $doc->getElementsByTagName('tr');
        $nodeNames= array();
        foreach ($rows as $row) {
            $tds = $row->getElementsByTagName('td');
            if ($tds->length == 0) continue;
            if ($tds->item(0)->nodeValue == '!!!invalid title!!!') continue;
            $s_id = array_search($tds->item(0)->nodeValue, $nodeNames);
            if ($s_id === false) {
                $s_id = count($nodeNames);
                $nodeNames[] = $tds->item(0)->nodeValue;
            }
            $o_id = array_search($tds->item(1)->nodeValue, $nodeNames);
            if ($o_id === false) {
                $o_id = count($nodeNames);
                $nodeNames[] = $tds->item(1)->nodeValue;
            }
            // hack to make firstDeepSearch working, increase indices by 1
            $s_id+=1;
            $o_id+=1;
            if (!isset($this->sIds[$s_id]))
                $this->sIds[$s_id] = array($o_id);
            else
                $this->sIds[$s_id][] = $o_id;
        }

        // if we have a start node given, check if this was in the result triples
        if ($start) {
            $this->smw_start_id = array_search($start->getText(), $nodeNames);
            if ($this->smw_start_id === false) return "";
            $this->smw_start_id++;
            // all nodes that have "start" node as parent, are root nodes
            foreach (array_keys($this->sIds) as $id) {
                if (in_array($this->smw_start_id, $this->sIds[$id]) &&
                    !in_array($id, $this->rootNodes))
                    $this->rootNodes[]= $id;
            }
        }

        // build elementsProperty array
        $id = 1;
        while ($name = array_shift($nodeNames)) {
            $name=iconv("UTF-8", "ISO-8859-1", $name);
            $this->elementProperties[$id] = new ElementProperty($name, 0);
            $id++;
        }

        // sorting the nodes (so that the member variable sIds is in correct order) before building the tree
		$this->sortElements();

		$this->generateTreeDeepFirstSearch();

        if ($this->json)
            return $this->formatTreeToJson();
		return $this->formatTreeToText();

    }
}

class TreeviewStorageSQL2 extends TreeviewStorage {

    // for the conditions and limitations that can be used for generating the
    // tree, the smw_ids will be fetched and stored here
    private $smw_relation_id;
    private $smw_condition_ids;
        
    private $db;

	/**
	 * Starts to fetch the triples based on a relation for creating the tree. All internal
	 * limitations and management which nodes are needed are done here. This is the main
	 * function that is being called from outside.
	 * 
	 * @access public
	 * @param  object Title $relation
	 * @param  object Title $category
	 * @param  object Title $start
	 * @return string $tree wikitext that will be parsed by the tree function.
	 */
	public function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL) {
		$this->db =& wfGetDB( DB_SLAVE );

		$smw_rels2 = $this->db->tableName('smw_rels2');
		$smw_inst2 = $this->db->tableName('smw_inst2');

		$query ="";

        // relation must be set -> we fetch here the smw_id of the requested relation
		if (! ($this->smw_relation_id = $this->getSmwIdByTitle($relation))) return ($this->json) ? array() : "";
		
		// if category is set, we will fetch the id of the category
		if ($category) {
			$this->getCategoryList($category);
		    if (is_null($this->smw_category_ids)) return ($this->json) ? array() : "";
		}

		// if start is set, fetch smw_id for start element		
		if ($start && (! ($this->smw_start_id = $this->getSmwIdByTitle($start))))
			return ($this->json)
		            ? array(array ("name" => $start->getPrefixedText(), "link" => $start->getDBKey(), "depth" => 1))
		            : $this->hchar."[[".$start->getDBKey()."]]\n";

		if ($this->ajaxExpansion > 0 && !$this->condition) {
			// only root and one level below
			// if start is set, retrieve all children from start
			if ($start) {
				$query = $this->getQuery4Relation($smw_inst2, $smw_rels2);
				$query = str_replace('___CONDITION___', " AND r.o_id = ".$this->smw_start_id, $query);
			}
			// initial call, then we need the first two levels (children and grand children of start)
			else
		    	$query = $this->getQuery4Relation($smw_inst2, $smw_rels2, true);
		}
		else $query = $this->getQuery4Relation($smw_inst2, $smw_rels2);
		
		// remove placeholder for special where part, if not done aleady
		$query = str_replace('___CONDITION___', "", $query);

		// check, if there were condition for the tree.
		if ($this->condition) $this->getCondition($this->condition);

		// now run the query to get all relations of the desired property
		$res = $this->db->query($query);
		while ($row = $this->db->fetchObject($res)) {
			$this->addTupleToResult($row->s_id, $row->o_id);
		}
		$this->db->freeResult($res);

		if (count($this->sIds) == 0) return;

		// if we use the Ajax expansion but building the tree for the first time 
		// then one level further is fetched to have two levels. This is neccessary because
		// if start is set or other limitations are set then in the first triple the object
		// might be rejected, meaning that we have the subject only but need it's children
		// to have at least two levels.
		if ($this->ajaxExpansion == 1)
			$this->fetchNextLevelOfNodes();
			
		// check if the tree is supposed to be opened down to a certain node
		// if the next level is retrieved, we do not need to check for an open node as this node
		// must have been fetched with the initial call already
		if ($this->openTo && $this->ajaxExpansion < 2) $this->getPathToOpenTo();

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
			$this->elementProperties[$this->smw_start_id] = new ElementProperty(wfMsg("mainpage"), NULL, wfMsg("mainpage"));
		}

		$this->generateTreeDeepFirstSearch();

		// check if leaf nodes have children
		if ($this->checkNode) $this->leafNodes = $this->checkLeafHc();

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
		global $smwgDefaultStore, $smwgQMaxInlineLimit;
		$fixparams = array(
			"format" => "ul",
			"limit" => $smwgQMaxInlineLimit,
		);
		
		// if there's a triplestore in use, use the SPARQL QueryProcessor to translate the ask into SPARQL and then
		// the tripplestore gets used
		if ($smwgDefaultStore == "SMWTripleStore") {
			$result = SMWSPARQLQueryProcessor::getResultFromQueryString($querystring, $fixparams, array(), SMW_OUTPUT_WIKI);
			if (stripos($result, "Could not connect to host") !== false)
				$result = SMWQueryProcessor::getResultFromQueryString($querystring, $fixparams, array(), SMW_OUTPUT_WIKI);
		}
		else
			$result = SMWQueryProcessor::getResultFromQueryString($querystring, $fixparams, array(), SMW_OUTPUT_WIKI);

		// the list contains some html and wiki text, we need to extract the page values
		$result = strip_tags($result);

		preg_match_all('/\[\[[^\|]+/', $result, $matches); 
		$pages = $matches[0];
		$smwIds = array();
		foreach ($pages as $page) {
			$page = substr($page, 3); // remove the "[[:"
			$title = Title::newFromDBkey($page);
			if (is_null($title)) continue;
			$smw_id = $this->getSmwIdByTitle($title);
			if (is_null($smw_id)) continue;
			$smwIds[] = $smw_id;

			// fill the elementProperties variable for this element. Almost all neccessary data
			// are availabe at this point
			if (strpos($page, ":") === false)
				$this->elementProperties[$smw_id] = new ElementProperty($page, $title->getNamespace());
			else
				$this->elementProperties[$smw_id] = new ElementProperty(substr($page, strpos($page, ':') +1 ), $title->getNamespace());
			
			// if we want to display the value of some property instead of the page name then this
			// post processing must be done.
			$row->smw_id = $smw_id;
			$row->title = $this->elementProperties[$smw_id]->getTitle();
			$row->ns = $this->elementProperties[$smw_id]->getNs();
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
		
	    $smw_ids = $this->db->tableName('smw_ids');
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
	    	$smw_inst2 = $this->db->tableName('smw_inst2');
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
	            $res = $this->db->query(sprintf($query, $query_add));
	            while ($row = $this->db->fetchObject($res)) {
                    $t = Title::newFromText($row->title, $row->ns);
                    if (!$t || ! $t->userCanRead()) continue;
                    $this->elementProperties[$row->smw_id]= new ElementProperty($row->title, $row->ns);
                    $this->postProcessingForElement($row);
               		unset($fids[$row->smw_id]);
                }
	            $this->db->freeResult($res);
	            
	            // if there are remaining fids then these didn't belong to the selected set of nodes
	            // remove them from the member variables sIds and elementProperties
	            if (count($fids) > 0) {
	            	foreach (array_keys($fids) as $id) {
	            		unset($this->elementProperties[$id]);
	            		if (isset($this->sIds[$id])) unset($this->sIds[$id]);
	            		$pk = array_search($id, $this->rootNodes);
	            		if ($pk !== false) unset($this->rootNodes[$pk]);
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
	 * If the start parameter is set, and the dynamic expansion is used we need
	 * the first two levels of the tree only. Therefore the first query in
	 * getHierarchyByRelation() returns the triples of the first level as
	 * tree nodes. That is because all children of start are fetched. Because
	 * start itself will not be displayed in the tree, these children are root
	 * nodes. Now we need the children of these root nodes.
	 * 
	 * @access private 
	 */	
	private function fetchNextLevelOfNodes() {
		// get all nodes within the current smwIds variable
		$nodes = array();
		foreach (array_keys($this->sIds) as $cnode) {
			foreach ($this->sIds as $parents) {
				foreach ($parents as $pnode) {
					if ($pnode == $cnode) continue 3;
				}
			}
			$nodes[] = $cnode;
		}
		if (count($nodes) == 0) return;

		$smw_inst2 = $this->db->tableName('smw_inst2');		
		$smw_rels2 = $this->db->tableName('smw_rels2');
		$query = $this->getQuery4Relation($smw_inst2, $smw_rels2);
		$query = str_replace('___CONDITION___', ' AND r.o_id IN ('.implode(',', $nodes).')', $query);
		$res = $this->db->query($query);
		if ($res) {
			while ($row = $this->db->fetchObject($res)) {
				$this->addTupleToResult($row->s_id, $row->o_id);
			}
		}
		$this->db->freeResult($res);
	}
	
	/**
	 * Get all sub categories for a given title which is supposed to be a category
	 * 
	 * @access private
	 * @param  Object title $category
	 */
	private function getCategoryList($category) {
		$catIds = array();
		$cid = $this->getSmwIdByTitle($category);
		if (is_null($cid)) return;
		$catIds[] = $cid;
		$smw_inst = $this->db->tableName('smw_inst2');
		$smw_ids = $this->db->tableName('smw_ids');
		$query = "SELECT s.smw_id AS cat FROM $smw_ids s, $smw_inst i " .
				 "WHERE s.smw_id = i.s_id AND i.o_id = %d AND s.smw_namespace = ".NS_CATEGORY;
		$children = $catIds;
		while (count($children) > 0) {
			$currentCat = array_shift($children);
			$res = $this->db->query(sprintf($query, $currentCat));
			if ($res) {
				while ($row = $this->db->fetchObject($res)) {
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
	private function postProcessingForElement(&$row) {
	    // add property value for display    
	    if ($this->displayProperty) {
	    	$this->elementProperties[$row->smw_id]->setDisplayProperty($this->fetchPropertyValue($row->title, $row->ns, $this->displayProperty));
	    } 	
	    // add property value for sorting	
	    if ($this->orderByProperty) {
	    	$this->elementProperties[$row->smw_id]->setSortProperty($this->fetchPropertyValue($row->title, $row->ns, $this->orderByProperty));
	    } 		
	}

	/**
	 * fetch a property value based on a property name and a page name and namespace
	 * 
	 * @access private
	 * @param  string $name page name
	 * @param  int $ns namespace
	 * @param  string $property name of property
	 * @return mixed property value or null
	 */
	private function fetchPropertyValue($name, $ns, $property) {
        $title = Title::newFromText($name, $ns);
        if (!$title || !$title->userCanRead()) return;
       	$pname = Title::newFromText($property, SMW_NS_PROPERTY);
       	$prop = SMWPropertyValue::makeUserProperty($pname->getDBkey());
		$smwValues = smwfGetStore()->getPropertyValues($title, $prop);
	    if (count($smwValues) > 0) {
        	$propValue = str_replace("_", " ", $smwValues[0]->getXSDValue());
   		    if (strlen(trim($propValue)) > 0) return $propValue;
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
        global $wgUser;
        if (! $title->userCanRead()) return;
		$ns = $title->getNamespace();
		$smw_ids = $this->db->tableName('smw_ids');
		$query = "SELECT smw_id FROM $smw_ids WHERE ".
		         "smw_title = ".$this->db->addQuotes($title->getDBKey()).
		         " AND smw_namespace = ".$ns;

		$res = $this->db->query($query);
		$s = $this->db->fetchObject($res);
		$this->db->freeResult($res);
		if (!$s)
			return;
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
		// add the current node to the path for opento, this is important to break
		// the maximum depth if the ajax expansion is used.
		$this->openToPath[]= $currentId;

		// Also add the parent of this node to the path, to get the siblings of this
		// node as well, this is important if the node has 
		if (isset($this->sIds[$currentId])) {
			foreach ($this->sIds[$currentId] as $p)
				$this->openToPath[] = $p;
		}

		// if the id is already in the node set we are done
		if (isset($this->elementProperties[$currentId]))
			return;

		// if a current depth is set and no ajax expansion is used, then we are done as
		// well because if the node is not yet in the results, any further lookup will
		// exceed the desired maxDepth
		if ($this->maxDepth != null && $this->ajaxExpansion == 0) {
			$this->openTo = null;
			return; 
		}

		$smw_inst2 = $this->db->tableName('smw_inst2');		
		$smw_rels2 = $this->db->tableName('smw_rels2');
		$query = $this->getQuery4Relation($smw_inst2, $smw_rels2);

		// remember all ids, that we will add now to find the node to open.		
		$newIds = array();

		// check that we do not exceed maxDepth, this is indefinite (or 9999) if not defined
		$maxDepth = ($this->maxDepth != null) ? $this->maxDepth : 9999;
		// current depth is 2, that is because if dynamic expansion is used, we fetch two levels
		// only. Otherwise, all nodes are fetched and the node to open must be in the result set
		// already. 
		$currentDepth = 2;
		
		while (!isset($this->sIds[$currentId]) && $currentDepth < $maxDepth) {
			// look for all parents (actually look for one only) of the current node to be opened 
			$cquery= str_replace('___CONDITION___', "AND r.s_id = ".$currentId, $query);
			$res = $this->db->query($cquery);
			if ($res && $this->db->affectedRows() > 0) {
				$row = $this->db->fetchObject($res);
				if ($this->addTupleToResult($row->s_id, $row->o_id)) {
					// remember all ids that we add to sIds and elementProperties
					if (!isset($this->elementProperties[$row->s_id])) $newIds[] = $row->s_id;
					if (!isset($this->elementProperties[$row->o_id])) $newIds[] = $row->o_id;
				}
				$cParent = $row->o_id;
			}
			else break;
			// if the parent was found, look for all children of that patent i.e. all
			// siblings of the current node to open.
			$cquery = str_replace('___CONDITION___', "AND r.o_id = ".$cParent, $query);
			$res = $this->db->query($cquery);
			if ($res && $this->db->affectedRows() > 0) {
				while ($row = $this->db->fetchObject($res)) {
					// this is the relation how we found the parent, so skip it.
					if ($row->s_id == $currentId) continue;
					if ($this->addTupleToResult($row->s_id, $row->o_id))
						// remember all ids that we add to sIds and elementProperties
						if (!isset($this->elementProperties[$row->s_id])) $newIds[] = $row->s_id;
				}
			}
			else break;
			// we now have the node to open, it's siblings and the parent. Step upwards,
			// make the parent to the new node to open and repeat the process. 
			$currentId = $cParent;
			$currentDepth++;
			$this->openToPath[] = $currentId;
		}
		
		// add parent of last currentId to openToPath
		if (isset($this->sIds[$currentId])) {
			foreach ($this->sIds[$currentId] as $p)
				$this->openToPath[] = $p;
		}
		
		// current parent after some iterations of the node to open is still not
		// in the result  found, then remove all added
		if (!isset($this->sIds[$currentId]) && isset($cParent) && !isset($this->sIds[$cParent])) {
			$newIds = array_unique($newIds);
			foreach ($newIds as $id) {
				unset($this->sIds[$id]);
				unset($this->elementProperties[$id]);
			}
			$this->openTo = null;
			$this->openToPath = array();
		} 
	}

	/**
	 * returns the query string that is needed to fetch triples based on a relation
	 * for creating a tree.
	 * If $addAjaxLimit is set, only two levels of the tree are fetched. Otherwise
	 * all triples are fetched and hierarchy is created later in
	 * generateTreeDeepFirstSearch().
	 * 
	 * @access private
	 * @param  string $smw_inst2 name for table smw_inst2
	 * @param  string $smw_rels2 name for table smw_rels2
	 * @param  boolean $addAjaxLimit otional default false
	 * @return string $query 
	 */
	private function getQuery4Relation($smw_inst2, $smw_rels2, $addAjaxLimit = false) {
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
		$query="SELECT r.s_id as s_id, r.o_id as o_id FROM $smw_rels2 r $categoryConstraintTable"
		       ."WHERE r.p_id = ".$this->smw_relation_id.$categoryConstraintWhere." ___CONDITION___";
		if ($addAjaxLimit)
		   	$query.= " AND r.o_id NOT in (SELECT r.s_id FROM $smw_rels2 r $categoryConstraintTable ".
		   			 " WHERE r.p_id = ".$this->smw_relation_id.$categoryConstraintWhere.")";
		$query .= $categoryConstraintGroupBy;
		return $query;	   
	}

	/**
	 * takes a triple (subject and object) and adds it to the list of results. This list
	 * is created in the array $sIds. Key is the current node (subject), values is an array
	 * of nodes that are parent (object) of the current node.
	 * If the subject is not in the list of condition nodes (if used) then it's skiped.
	 * Also here the rootNodes are defined. This can be done if we use start (then the children
	 * of start are root nodes).
	 * Here the list of elementProperties is created as well (list of array with key) is done.
	 * Later this array will contain the node name and namespace. This is done in
	 * getElementProperties().
	 * 
	 * @access private
	 * @param  int $s_id smw_id of the subject
	 * @param  int $o_id smw_id of the object
	 * @return boolean true if result was added or false if not
	 */
	private function addTupleToResult($s_id, $o_id) {
		// parameter condition was set, check if current subject
		// (s_id) of the triple is in the allowed page list
		if (($this->condition) && !in_array($s_id, $this->smw_condition_ids))
			return false;

		// if we had set parameter start, set all children of start node as root nodes.
		// the start node itself is excluded from the tree 
		if ($this->smw_start_id &&
			$this->smw_start_id == $o_id &&
			!in_array($s_id, $this->rootNodes)) {
			$this->rootNodes[] = $s_id;	
		}

		// if parameter condition is set, but object is not in the
		// allowed page list and s_id is not yet set, create a node without parent
		if (($this->condition && !in_array($o_id, $this->smw_condition_ids)) ||
		    ($this->smw_start_id && $this->smw_start_id == $o_id)) {
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
		return true;	
	}
	
	public function checkLeafHc() {
        $nodesToCheck = parent::checkLeafHc();
		if (count($nodesToCheck) == 0) return;
		// all remaining nodes need to be checked in the DB now		
		$smw_inst2 = $this->db->tableName('smw_inst2');		
		$smw_rels2 = $this->db->tableName('smw_rels2');
		$query = $this->getQuery4Relation($smw_inst2, $smw_rels2);
		$query = str_replace('___CONDITION___', ' AND r.o_id IN ('.implode(',', $nodesToCheck).')', $query);
		$nodesToCheck = array_flip($nodesToCheck);
		$res = $this->db->query($query);
		if ($res) {
			while ($row = $this->db->fetchObject($res)) {
				if (($this->condition) && !in_array($row->o_id, $this->smw_condition_ids))
					continue;
				unset($nodesToCheck[$row->o_id]);
			}
		}
		$this->db->freeResult($res);
		return array_flip($nodesToCheck);
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

class ElementProperty {
	private $title;
	private $ns;
	private $displayProperty;
	private $sortProperty;
	
	public function __construct($title = null, $ns = null, $displayProperty = null, $sortProperty = null) {
		$this->title = $title;
		$this->ns = $ns;
		$this->displayProperty = $displayProperty;
		$this->sortProperty = $sortProperty;
	}
	
	public function getTitle() { return $this->title; }
	public function getNs() { return $this->ns; }
	public function getDisplayProperty() { return $this->displayProperty; }
	public function getSortProperty() { return $this->sortProperty; }

	public function setTitle($v) { $this->title = $v; }
	public function setNs($v) { $this->ns = $v; }
	public function setDisplayProperty($v) { $this->displayProperty = $v; }
	public function setSortProperty($v) { $this->sortProperty = $v; }

	public function getLink() {
		global $wgContLang;
		
		$link = str_replace(' ', '_', $this->title);
		// prefix link with namespace text
		if ($this->ns != NS_MAIN)
			$link = $wgContLang->getNsText($this->ns).":".$link;
		return $link;
	}
	
	public function getDisplayName() {
		// parameter display was set to use some property value for node name and link it with the page
		return ($this->displayProperty != null) ? $this->displayProperty : $this->title;
	}

	public function getSortName() {
		// parameter sort was set to use some property value for sorting
		return ($this->sortProperty != null) ? $this->sortProperty : $this->getDisplayName();
	}

	public function getWikitext() {
		$link = $this->getLink();
		// parameter display was set to use some property value for node name and link it with the page
		if ($this->displayProperty != null) {
			switch($this->ns) {
				// add a colon before NS_CATEGORY and NS_IMAGE
				// otherwise they are rendered as category annotations or images.
				case NS_CATEGORY:
	            case NS_IMAGE:
	                return "[[:".$link."|".$this->displayProperty."]]";
	            default:
	                return "[[".$link."|".$this->displayProperty."]]";
	        }
    	}
	    // just the page name is used for the node
		// if the page is in the main namespace it's sufficient to display [[page name]] and the
		// wiki rendering will do the rest. Otherwise display [[Prefix:page_name|page name]]
		switch($this->ns) {
			// add a colon before NS_CATEGORY and NS_IMAGE
            // otherwise they are rendered as category annotations or images.
			case NS_CATEGORY:
			case NS_IMAGE:
				return "[[:".$link."|".$this->title."]]";
			case NS_MAIN: 
				return "[[".$this->title."]]";
			default:
				return "[[".$link."|".$this->title."]]"; 
		}
	}	
}

function wfTreeGeneratorLanguageGetMagic(&$magicWords,$langCode = 0) {
	$magicWords[GENERATE_TREE_PF] = array( 0, GENERATE_TREE_PF );
	return true;
}
?>