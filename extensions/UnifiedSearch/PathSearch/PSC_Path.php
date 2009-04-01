<?php


class PSC_Path {
	private $startId;
	private $startName;
	private $data;
	private $completeLeft;
	private $completeRight;
	private $addPos;
	
	public function __construct($start = NULL, $startName = "", $setAddPos = 0) {
		$this->create($start, $startName, $setAddPos);
	}
	
	public function create($start, $startName = "", $setAddPos = 0) {
		$this->data = array($start);
		$this->startId = $start;
		$this->startName = $startName;
		$this->completeLeft = false;
		$this->completeRight = false;
		$this->addPos = $setAddPos; // default right
	}
	
    public function add($id) {
    	if ($this->addPos == 0) {
    		$res = $this->addRight($id);
    		if ($res == 0 ) return true;  // i was able to add at the right side
    	}
   		return ($this->addLeft($id) == 0) ? true : false;
    }
	
	public function addRight($id) {
		if ($this->completeRight) return 1;
		$this->data[] = $id;
		return 0;
	}
	
	public function addLeft($id) {
		if ($this->completeLeft) return 1;
		array_unshift($this->data, $id);
		return 0;
	}
	
	public function isInPath($id) {
		return (array_search($id, $this->data) !== false);
	}

	public function isComplete() {
		return ($this->completeLeft && $this->completeRight);
	}

	public function length() {
		return count($this->data);
	}
	
	public function getPath() {
		return ($this->addPos == 0) ? $this->data : array_reverse($this->data);
	}
	
	public function getStartName() {
		return $this->startName;
	}
	
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
	
    public function getPartialPath($id) {
    	$result = array();
    	$pos1 = array_search($this->startId, $this->data);
    	$pos2 = array_search($id, $this->data); 
    	if ($pos2 > $pos1) {
    		return array_reverse(array_slice($this->data, $pos1, $pos2 - $pos1 + 1));
    	}
    	return array_slice($this->data, $pos2, $pos1 - $pos2 + 1);
    }

    public function getLast() {
		// get current last element id in path (from left or right)
		if ($this->addPos == 0)	return end($this->data);
		return $this->data[0];
    }

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
