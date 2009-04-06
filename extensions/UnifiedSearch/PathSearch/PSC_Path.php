<?php

/*
 * Holds functionality to exmine a path trough the existing Ontology of a wiki. 
 */

class PSC_Path {
	private $startId;
	private $startName;
	private $data;
	private $completeLeft;
	private $completeRight;
	private $addPos;
	
	/**
	 * creates the object to start searching a path. All parameters are optional and
	 * can be set in create() as well.
	 * 
	 * @access public
	 * @param  int    start smw_id of the start element
	 * @param  string startName name of the element to start with (this is not
	 *                          really needed for any functionality but there for cosmetic reasons)
	 * @param  int    setAddPos direction in which to look for the next neighbours 0 is right, 1 is left.  
	 */
	public function __construct($start = NULL, $startName = "", $setAddPos = 0) {
		$this->create($start, $startName, $setAddPos);
	}

	/**
	 * creates the start node of the path.
	 * 
	 * @access public
	 * @param  int    start smw_id of the start element
	 * @param  string startName default empty, name of the element to start with (this is not
	 *                          really needed for any functionality but there for cosmetic reasons)
	 * @param  int    setAddPos default 0, direction in which to look for the next neighbours 0 is right, 1 is left.  
	 */	
	public function create($start, $startName = "", $setAddPos = 0) {
		$this->data = array($start);
		$this->startId = $start;
		$this->startName = $startName;
		$this->completeLeft = false;
		$this->completeRight = false;
		$this->addPos = $setAddPos; // default right
	}

	/**
	 * add a node to the existing path
	 * 
	 * @access public
	 * @param  int id smw_id of the node to add
	 * @return bool true if successful or false on error
	 */	
    public function add($id) {
    	if ($this->addPos == 0) {
    		$res = $this->addRight($id);
    		if ($res == 0 ) return true;  // i was able to add at the right side
    	}
   		return ($this->addLeft($id) == 0) ? true : false;
    }
    
	/**
	 * add a node to the existing path on the right side
	 * 
	 * @access public
	 * @param  int id smw_id of the node to add
	 * @return bool true if successful or false on error
	 */		
	public function addRight($id) {
		if ($this->completeRight) return 1;
		$this->data[] = $id;
		return 0;
	}

	/**
	 * add a node to the existing path on the left side
	 * 
	 * @access public
	 * @param  int id smw_id of the node to add
	 * @return bool true if successful or false on error
	 */	
	public function addLeft($id) {
		if ($this->completeLeft) return 1;
		array_unshift($this->data, $id);
		return 0;
	}

	/**
	 * checks for a node if this one exists in the path
	 * 
	 * @access public
	 * @param  int id of the node to check
	 * @return bool true if exists and false if the node doesn't exist in this path
	 */
	public function isInPath($id) {
		return (array_search($id, $this->data) !== false);
	}

	/**
	 * tells if the path is complete (i.e. no more neighbours for the ending elements
	 * on the left and right.
	 * 
	 * @access public
	 * @return bool if no node can be added anymore or false if there are still neighbours found
	 */
	public function isComplete() {
		return ($this->completeLeft && $this->completeRight);
	}

	/**
	 * returns the length of the path (i.e. the number of elements including the start node)
	 * 
	 * @access public
	 * @return int length of path
	 */
	public function length() {
		return count($this->data);
	}

	/**
	 * returns all nodes of the path in an array. the start node canot be identified as the furthermost
	 * left or right node of this array.
	 * 
	 * @access public
	 * @return array(int) id of nodes
	 */	
	public function getPath() {
		return ($this->addPos == 0) ? $this->data : array_reverse($this->data);
	}
	
	/**
	 * returns the name of the start node. This might be an empty string if the name hasn't been set.
	 * 
	 * @access public
	 * @return string node name of start node
	 */
	public function getStartName() {
		return $this->startName;
	}
	
	/**
	 * chops the last added element. This function may not chop the correct node,
	 * if the direction where to add the nodes has been altered and at both sides
	 * there are nodes added already, so use it with care.
	 * 
	 * @access public
	 * @return bool true on success or false on error
	 */
	public function chop() {
		if ($this->addPos == 0 && end($this->data) != $this->startId) {
			array_pop($this->data);
			return true;
		}
		if ($this->addPos == 1 && $this->data[0] != $this->startId) {
			array_shift($this->data);
			return true;
		}
		return false;
	}
	
	/**
	 * returns the path from the start node up to a specified node.
	 * 
	 * @access public
	 * @return array (int) id of nodes
	 */
    public function getPartialPath($id) {
    	$result = array();
    	$pos1 = array_search($this->startId, $this->data);
    	$pos2 = array_search($id, $this->data); 
    	if ($pos2 > $pos1) {
    		return array_reverse(array_slice($this->data, $pos1, $pos2 - $pos1 + 1));
    	}
    	return array_slice($this->data, $pos2, $pos1 - $pos2 + 1);
    }

	/**
	 * returns the last element of the path. Depending on the direction this is the
	 * furthermost left or furthermost right element.
	 * 
	 * @access public
	 * @return int node id
	 */
    public function getLast() {
		// get current last element id in path (from left or right)
		if ($this->addPos == 0)	return end($this->data);
		return $this->data[0];
    }

	/**
	 * Find neighbours depending on the ontology for the last node of the path. The node is
	 * examinded by it's type (property, category, page). Also it's checked whether the current
	 * relation needs a domain or range (if the path contains that many nodes already).
	 * 
	 * Optional parameters can be set, so that pages are included as well. Normally for a property
	 * it's check only which categories are related to that property. Single pages are not searched,
	 * except if there is no relation with an annotated category found. If then page should not be
	 * found at any circumstances, set this option to false to prevent finding pages at all. If the
	 * start node is a page, properties on that page are looked up in order to start finding a path.
	 *  
	 * Normally it's also checked that an existing node in the path is not repeated. This can occur
	 * if several categories have the same properties. To prevent endles loops in the path, this
	 * option is set tu true and existing nodes are ignored as new neighbours.
	 * 
	 * If there are no neighbours for the current node, an empty array is returned but also internal
	 * the node is set o be complete of that side. If there were no neighbour at one side, still
	 * there could be neighbours looking in the other direction from the start node. This will be set,
	 * so that the direction (adding a node) alters and that from this side the path is set to be complete.
	 * 
	 * @access public
	 * @param  bool includePages default true
	 * @param  bool checkDouble default true
	 * @return array (int) of nodes that can be neighbours of the last node
	 */
	public function getNext($includePages = true, $checkDouble = true) {
		$last = $this->getLast();
		
		$res = array();
		
		// if last element is a property, check for what we have to look for (domain or range)		
		if (PSC_WikiData::isProperty($last)) {
			// if property is a value property, then this is a range property, check for a domain
			
			if (PSC_WikiData::isPropertyXsdType($last))
				$res = PSC_WikiData::getPropertyDomainByXsdType($last);

			// we have several elements, check type of previous neighbour and return the opposite
			else if (count($this->data) > 1) {
				$prev = ($this->addPos == 0) ? $this->data[count($this->data) - 2] : $this->data[1];
				if (in_array($prev, PSC_WikiData::getPropertyRange($last)))
					$res = PSC_WikiData::getPropertyDomain($last);
				if (in_array($prev, PSC_WikiData::getPropertyDomain($last))) {
					$res = array_merge($res, PSC_WikiData::getPropertyRange($last));
					$res = array_unique($res);
				}
			}
			
			// no neigbours yet, return both domain and range (i.e, several paths will be build afterwards)
			else { 
				$res = array_merge(PSC_WikiData::getPropertyRange($last),
				                   PSC_WikiData::getPropertyDomain($last),
				                   PSC_WikiData::getPropertyDomainByXsdType($last));
				// still no results, check for pages, if desired
				if (count($res) == 0 && $includePages) {
					if (isset($prev) && in_array($prev, PSC_WikiData::getPropertyRange($last)))
						$res = PSC_WikiData::searchNextDomain($last);
					else if (isset($prev) && in_array($prev, PSC_WikiData::getPropertyDomain($last)))
						$res = PSC_WikiData::searchNextRange($last);
					else
						$res = array_merge(PSC_WikiData::searchNextDomain($last), PSC_WikiData::searchNextRange($last));
				}
				// have each result once only (important if domain and range are mixed)
				$res = array_unique($res);
			}
		}
		
		// last element is no property, is it a category?
		else if (PSC_WikiData::isCategory($last)) {
			// we have several elements, check the type of this element (domain, range)
			// depending on the property next to it
			if (count($this->data) > 1) {
				$prev = ($this->addPos == 0) ? $this->data[count($this->data) - 2] : $this->data[1];
				if (in_array($last, PSC_WikiData::getPropertyDomain($prev))) { // last one is a domain
					foreach (PSC_WikiData::getProperties() as $pId) {
						if (in_array($last, PSC_WikiData::getPropertyRange($pId)) && $pId != $prev)
							$res[] = $pId;
					}
				}
				else { // last one is a range
					foreach (PSC_WikiData::getProperties() as $pId) {
						if (in_array($last, PSC_WikiData::getPropertyDomain($pId)) && $pId != $prev)
							$res[] = $pId;
					}
				}
			}
			// no neighbours yet, return all properties where this category is a range or domain
			else {
				foreach (PSC_WikiData::getProperties() as $pId) {
					if (in_array($last, PSC_WikiData::getPropertyDomain($pId)))
						$res[] = $pId;
					if (in_array($last, PSC_WikiData::getPropertyRange($pId)))
						$res[] = $pId;
				}
			}
		}
		// normal page, fetch category, if no category is assigned get properties used on that page
		else {
			// check if page is in any category
			$res = PSC_WikiData::searchCategory4Page($last);
			 // if no categories found, check for properties
			if (count($res) == 0) 
				$res = PSC_WikiData::searchProperty4Page($last);				
		}

		// do not return nodes that are already in the path
		if ($checkDouble)
			$res = array_diff($res, $this->data);

		// check if results are found and the path can be continued or must be terminated at one end
		if (count($res) == 0) // no result found
			$this->toggleComplete();
		return $res;
	}
	
	/**
	 * alters direction of where to add nodes to the path. Also set the complete variables to tell
	 * that a path is terminated at one side.
	 * 
	 * @access private
	 */
	private function toggleComplete() {
		if ($this->addPos == 0) {
			$this->completeRight = true;
			$this->addPos = 1;
		}
		else {
			$this->completeLeft = true;
			$this->addPoss = 0;
		}
	}
} 


?>
