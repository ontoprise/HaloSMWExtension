<?php
/*
 * Created on 06.03.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 require_once('PSC_WikiData.php');
 require_once('PSC_Path.php');

 define('PSC_SMWDATA_NAME', 0);
 define('PSC_SMWDATA_TYPE', 1);
 define('PSC_COLTYPE_ISID', 0);
 define('PSC_COLTYPE_ISVALUE', 1);
 define('PSC_OUTPUT_PAGE', 0);
 define('PSC_OUTPUT_BOX', 1);
 
 // maximum lengh of a path that is returned. If this number is odd, then the length might be
 // increased by one.
 define('PSC_MAX_PATH_LENGTH', 10);
 // to prevent any endless loops in evalPath() if data is in arbitary structure
 define('PSC_MAX_LOOP_EVAL_PATH', 20);
 // how many results are supposed to be shown below the path
 define('PSC_MAX_SHOW_RESULT_LINES', 2);
 
 // error codes that are saved in member variable resultCode
 define('PSC_ERROR_INIT', -1);
 define('PSC_ERROR_SUCCESS', 0);
 define('PSC_ERROR_NOPATH', 1);
 define('PSC_ERROR_NOINSTANCE', 2);
 define('PSC_ERROR_PATHINVALID', 3);
 define('PSC_ERROR_INVALID_TERMS', 4);


 class PathSearchCore {
 	private $query;
 	private $path;
 	private $result;
 	private $instance;
 	private $details;
 	private $resultCode;
 	private $outputMethod;
 	private $numberOfPathsFound;
 	
 	public function __construct() {
 		$this->path = array();
 		$this->result = array();
 		$this->details = array();
 		$this->instance = array();
 		$this->resultCode = PSC_ERROR_INIT;
 	}

	public function getResultCode() {
		return $this->resultCode;
	}

	public function setOutputMethod($type) {
		$this->outputMethod = $type;
	}
	
	public function numberPathsFound() {
		return $this->numberOfPathsFound;
	}

	public function getResultAsHtml() {
		global $wgServer, $wgScript;
		
		if ($this->resultCode != 0) return "";
		if ($this->outputMethod == PSC_OUTPUT_BOX) $html = $this->getBoxHeader(); 
		$html = '<div id="pathsearchresult"><table><tr><td>';
		foreach ($this->result as $path) {

			// check if results exist for current path
			// if not, just skip the current path and continue with the next one
			$key = implode(',', $path);
			if (! isset($this->instance[$key])) continue;

			list($concepts, $properties) = $this->splitConceptsProperties($path);
			// draw all domains/ranges i.e. category, page
			$html .= '<table class="tblmain"><tr>';
			for ($i = 0, $is = count($concepts); $i < $is; $i++) {
					
				$html .= '<td style="vertical-align: top;">';
				$ids = explode('|', $concepts[$i]);
				
				if ($this->smwDataGetType($ids[0]) == NS_CATEGORY) $class = "category";
				else if ($this->isXsdType($ids[0]))	$class = "valuetype";
				else $class = "instance";
				$html .= '<span class="'.$class.'">'.$this->smwDataGetLink($ids[0]).'</span>'."\n";
				if (count($ids) > 1) { // display subcategories and pages within a category
					for ($j = 1, $js = count($ids); $j < $js; $j++) {
						if ($this->smwDataGetType($ids[$j]) == NS_CATEGORY) $class = "subcategory";
						else if ($this->isXsdType($ids[$j])) $class = "valuetype"; 
						else $class = "instance";
						$html .= '<span class="'.$class.'">'.$this->smwDataGetLink($ids[$j]).'</span>'."\n";
					} 
				}
				$html .= '</td>';
				if ($is - 1 > $i)  // if not last column, spacer for property (which is in next row)
					$html .= '<td></td>';
				
			}
			$html .= '</tr>';
			
			// now draw the properties
			$is = count($properties);
			// only if we have properties, this might not the case, if getDetails4Term() was called
			if ($is > 0) {
				$col = -1;
				$propertyRowEven = '';
				$propertyRowOdd = '';
				for ($i = 0; $i < $is; $i++) {
					list($id, $direction) = explode("|", $properties[$i]);
					$propName = ($direction == 1)
					          ? $this->smwDataGetLink($id).' &#9654;'
					          : '&#9664; '.$this->smwDataGetLink($id);
					$center = '<td><table class="propSpacer"><tr><td class="propSpacerCenter"></td><td rowspan="2"><div class="property">'.$propName.'</div></td></tr><tr><td class="propSpacerCenterBottom"></td></tr></table></td>';
					$left = '<td><table class="propSpacer"><tr><td></td><td class="propSpacerLeft"></td></tr><tr><td></td><td></td></tr></table></td>';
					$right = '<td><table class="propSpacer"><tr><td class="propSpacerRight"></td><td></td></tr><tr><td></td><td></td></tr></table></td>';
					if ($i % 2 == 0) {
						$propertyRowOdd .= $left.$center.$right;
					}
					else {
						$propertyRowOdd .= '<td></td>';
						$propertyRowEven .= '<td></td>'.$left.$center.$right;
					}
				}
				$html .= '<tr>'.$propertyRowOdd;
				if ($is % 2 == 0) $html .= '<td></td><td></td>';
				$html .= '</tr>';
				if ($is > 1) {
					$html .= '<tr><td></td>'.$propertyRowEven;
				    if ($is % 2 == 1) $html .= '<td></td><td></td>';
				    $html .= '</tr>';
				}
			}
			
			// now draw result values
			$numRows = 0;
			$numRowsTotal = count($this->instance[$key]);
			$numRowsShowMax = ($this->outputMethod == PSC_OUTPUT_PAGE) ? PSC_MAX_SHOW_RESULT_LINES : -1;
			foreach ($this->instance[$key] as $row) {
				$numRows++;
				// print row with value
				$html .= '<tr>';
				// print link with more results and quit loop here...
				if ($numRows == $numRowsShowMax && $numRowsTotal > $numRows + 1) {
					$html.= '<td colspan="'.(count($row) * 2 - 1).'">' .
							'<a ' .
							'href="'.$wgServer.$wgScript.'?action=ajax&rs=us_getPathDetails&rsargs[]='.urlencode($key).'" '.
							'onClick="return GB_showPage(\''.wfMsg('us_pathsearch_result_popup_header').'\', this.href)" '.
                            '>'.sprintf(wfMsg('us_pathsearch_show_all_results'), $numRowsTotal).'</a></td></tr>';
					break;
				}
				// print result line
				for ($col = 0, $cols = count($row); $col < $cols; $col++) {
					$html .= '<td style="white-space: nowrap;">'.$this->smwDataGetLink($row[$col]).'</td>';
					if ($col + 1 < $cols) $html .= '<td></td>';
				}
				$html .= '</tr>';
			}

			$html .= '</table><br/>';
		}
		
		$html .= '</td><td width="100%"></td></tr></table></div>';
		if ($this->outputMethod == PSC_OUTPUT_BOX) $html .= $this->getBoxFooter();
		return $html;
	}


	public function doSearch($searchArr, $limit = NULL, $offset = NULL) {
		
		foreach ($searchArr as $term) {
			if (is_array($term)) {
				if ($term[1] != NULL && $term[1] > -1)
					$this->addPath4Term($term[0], $term[1]);
				else $this->addPath4Term($term[0]);
			}
			else
				$this->addPath4Term($term);
		}
		// we must have at least two nodes to find a path in between
		$nop = count($this->path); 
		if ($nop > 1) {
			$this->evalPath(); // find any paths
			// if paths found complete label names for SMW ids, these are needed for checking path consistency
			$this->fetchNodeDetails($this->result); 
			// post processing for results
			$pathExists = array();
			for ($i = 0, $is = count($this->result); $i < $is; $i++ ) {
				// if the result path is terminating with a property, add categories/or pages
				$this->completePathSpo($this->result[$i], 0); // first
				$this->completePathSpo($this->result[$i], 1); // last

				// check path consistency and if there are results
				if (! $this->checkPathConsistency($this->result[$i])) {
					unset($this->result[$i]);
					continue;
				}
					
				// because of the paths filled up as spo and merging (sub) categories there might be doubles now
				$key = implode(',', $this->result[$i]);
				if (in_array($key, $pathExists))
					unset($this->result[$i]);
				else
					$pathExists[] = $key;
			}
		}
		else if ($nop == 1) { // for one path, check details of property/category/page
			$this->getDetails4Term();
			$this->fetchNodeDetails($this->result); // if results are found complete label names for ids in path
			$this->getNodeInstances();				// get instances for single nodes
		}
		
		// get concrete results for each path (i.e. pages and values) and also fetch names for these nodes
		$this->getPathInstances();
		
		// here we limit the results. A result is a path that has at least one instance as well
		$this->checkResultLimits($limit, $offset);
		
		// get node names and values for $instance
		foreach (array_keys($this->instance) as $key)
			$this->fetchNodeDetails($this->instance[$key], $key);

		// result may have been modified and adjusted, set correct error code if we have no results or no instances
		if (count($this->result) == 0)
			$this->errorCode = PSC_ERROR_NOPATH;
		else if (count($this->instance) == 0)
			$this->resultCode = PSC_ERROR_NOINSTANCE;
		else
			$this->resultCode = PSC_ERROR_SUCCESS;
	}
	
	public function getPathDetails($path) {
		// check if first element is no property	
		$first = $path[0];
		if (strlen($first) > 0 && PSC_WikiData::isProperty($first)) {
			$this->resultCode = PSC_ERROR_PATHINVALID;
			return;
		}
		// check if last element is no property
		if (count($path) > 1) {
			$last = $path[count($path) -1];
			if (strlen($last) > 0 && PSC_WikiData::isProperty($last)) {
				$this->resultCode = PSC_ERROR_PATHINVALID;
				return;
			}
		}
		// add path to result list
		$this->result[] = $path;
		$this->fetchNodeDetails($this->result);
		
		// get concrete results for each path (i.e. pages and values) and also fetch names for these nodes
		$this->getPathInstances();
		foreach (array_keys($this->instance) as $key)
			$this->fetchNodeDetails($this->instance[$key], $key);
		
		if (count($this->instance) == 0)
			$this->resultCode = PSC_ERROR_NOINSTANCE;
		else
			$this->resultCode = PSC_ERROR_SUCCESS;
	}	

	private function getBoxHeader() {
		return "";
	}

	private function getBoxFooter() {
		return "";
	}

	/**
	 * Check limit and offset for results. Paths are removed from the result set
	 * if the limit and offset is set and $result exceeds these limits.
	 * 
	 * @access private
	 * @param  int limit of how many result sets are expected
	 * @param  int offset from where to start
	 */
	private function checkResultLimits($limit, $offset) {
		// first of all, clear all paths that do not have any
		// instances set, as these are not shown anyway
		foreach (array_keys($this->result) as $k) {
			$key = implode(',', $this->result[$k]);
			if (! isset($this->instance[$key]))
				unset($this->result[$k]);
		}
		$this->numberOfPathsFound = count($this->result);

		// now check offset and remove all elements before reaching element number $offset
		if (($offset != NULL) && count($this->result) > $offset) {
			$i = 1;
			foreach (array_keys($this->result) as $k) {
				unset($this->result[$k]);
				if ($i == $offset) break;
				$i++;
			}
		}
		// if result exceeds limit, leav all below limit untouched but remove the rest.
		if (($limit != NULL) && count($this->result) > $limit) {
			$i = $limit + 1;
			foreach (array_keys($this->result) as $k) {
				$i--;
				if ($i > 0) continue;
				unset($this->result[$k]);
			}
			
		}
	}	

	/**
	 * Main function that takes the path member variables as start points and
	 * adds neighbours to each node. During each loop for each path, a neighbour
	 * is looked up and added to the existing path. If a node has several neighbours,
	 * the path is copied and n -1 times. All new neighbours 1 to n are added to each
	 * newly created path while the first node is added to the original path.
	 * 
	 * If a new neighbour is found, this node is looked for in all other paths (those
	 * that do not have the same term as origin). If a node is found in any of the
	 * other paths, a result path is connected from the current path and the partial
	 * path where the neighbour was found in. This path is added to the result set.
	 * 
	 * To avoid endless loops, there is a limit for over all steps. Also the length
	 * of the paths may not exceed a certain number (very long paths do not reveal
	 * information clearly for the user and are not easy to display at the webpage).
	 * As long as new neighbours can be added to a path, the loop is repeated.
	 * 
	 * The member variable path looks at the begining like:
	 * $path[$node_id_of_start_term] = array(number_of_paths)
	 * $path[$node_id_of_start_term][$number_of_path_x] = array(node_ids)
	 * where the key $node_id_of_start_term is the id of the element from the search
	 * term, key $number_of_path_x is just for counting the paths started from a
	 * certain start term and the value is an array of node ids that describe the path
	 * itself. An example:
	 * $path[25][3] = array(25,45,67,22)
	 * This is 4th derived path for term x with the id 25. The term of course starts
	 * at the start point 25. This might be a page that belongs to category 45. This
	 * category has property 67 which has a object 22 for this category. All ids are
	 * smw_id from the table smw_ids.
	 * 
	 * @access private
	 * @return void  
	 */
 	private function evalPath() {

 		// maximum lengh of a partitial path (the corresponding path cannot be longer as maxPathLen * 2)
 		$maxPathLen = ceil(PSC_MAX_PATH_LENGTH / 2);

 		// this is true as long as we add nodes to a partial path (and look for these nodes in other paths)
 		$workToDo = true;

 		// to prevent any endless loops if data is in arbitary structure
 		$loopCnt = 0;

 		while ($workToDo && $loopCnt < PSC_MAX_LOOP_EVAL_PATH) {
 			$loopCnt++;
 			$workToDo = false;
	        // loop over all paths
	        foreach (array_keys($this->path) as $startId) {
 				for ($i = 0, $is = count($this->path[$startId]); $i < $is; $i++) {
	 				// check if path is already complete
 					if ($this->path[$startId][$i]->isComplete() || $this->path[$startId][$i]->length() > $maxPathLen)
	 					continue;
					
 					// search next element for path $i and check if element exists in other paths
 					$newIds = $this->path[$startId][$i]->getNext();
 					// we have neighbouring elements in path $i
		 			if (is_array($newIds)) {
	 					// take each element and look if this exists in paths $j
 						foreach ($newIds as $nId) {
 							// search new neighbour in all other paths
 							foreach (array_keys($this->path) as $targetId) {
 								if ($targetId == $startId) continue;
 							
 								for ($j = 0, $js = count($this->path[$targetId]); $j < $js; $j++) {
		 							if ($this->path[$targetId][$j]->isInPath($nId)) {
			 							// yes, the next element from path $i is in path $j
	 									$part1 = $this->path[$startId][$i]->getPath();
	 									$part2 = $this->path[$targetId][$j]->getPartialPath($nId);
	 									foreach ($part2 as $ni) $part1[] = $ni;
	 									$this->addPathToResult($part1);
		 							}
 								}
 							}
 						}
 						
	 					// build new paths for all neighbours
	 					// (those that have matched as well for those that didn't)
	 					// add the first neighbour to the original path

 						// marker if first neighbour can added to original path
 						$first = true;
 						foreach ($newIds as $nId) {
	 						// first new id that was found, then add it to the path
	 						if ($first) {
								if ($this->path[$startId][$i]->add($nId)) {
									$first = false;
									$workToDo = true;
								}
							}
		 					// element was not the first, or canot be added to original path 
							else {
								$m = count($this->path[$startId]);
								$this->path[$startId][$m] = clone $this->path[$startId][$i];
								if ($workToDo) { // we had already one element added to the current path
									$this->path[$startId][$m]->chop(); // therefore remove that element from clone
								}
								$this->path[$startId][$m]->add($nId);
							}
		 				}
 					}
	 			}
	        }
 		}
 	}
 	
 	/**
 	 * takes a complete path and returns the parts devided
 	 * into categories/pages (concepts) and properties;
 	 * 
 	 * @access private
 	 * @param  array $path with elements of the path
 	 * @return array ($concepts, $properties) where concepts and properties
 	 *               are arrays with elements. 
 	 */
 	private function splitConceptsProperties($path) {
 		$concepts = array();
 		$properties = array();
 		$step = 0;
 		foreach ($path as $element) {
 			if ($step % 2 == 1) // this is a property
 				$properties[] = $element;
 			else
 				$concepts[] = $element;
 			$step++;
 		}
 		return array($concepts, $properties);
 	}

	/**
	 * If there is only one search term, we have one start path only. Therefore connected
	 * paths with different start elements canot be found. In this case, the search term
	 * is analyzed and and information is looked up. This can be (sub)ategories, pages
	 * and relations. For some of these even a path can be found (this is a triple then)
	 * which is treated almost the same way as in the evalPath() function.
	 * Also instances are looked up and are displayed at the result page.
	 * 
	 * @access private
	 */ 	
 	private function getDetails4Term() {
 		$smw_id = array_shift(array_keys($this->path));
		// do we have category?
		if (PSC_WikiData::isCategory($smw_id)) {
			$cats = PSC_WikiData::getAllSubCategories($smw_id);
			if (count($cats) > 0) {
				$this->result[] = array(implode('|', $cats)."|".$smw_id);
				return;
			}
			$this->result[] = array($smw_id);
			return;
		}
		// do we have a property?
		if (PSC_WikiData::isProperty($smw_id)) {
			$domain = PSC_WikiData::getPropertyDomain($smw_id);
			if (PSC_WikiData::isPropertyXsdType($smw_id))
				$range = PSC_WikiData::getPropertyDomainByXsdType($smw_id);
			else
				$range = PSC_wikiData::getPropertyRange($smw_id);
			foreach ($domain as $dId) {
				foreach ($range as $rId) {
					$direction = $this->checkTriplet($dId, $smw_id, $rId);
					if ($direction != 0) {
						$this->result[]= array($dId, "$smw_id|$direction", $rId);
					}
				}
			}
			return;
		}
		// must be a page
		$cats = PSC_WikiData::searchCategory4Page($smw_id);
		if (count($cats) > 0)
			$this->result[]= array(implode('|', $cats)."|".$smw_id);
		else $this->result[] = array($smw_id);
 	}

	private function addPathToResult($path) {
		// trim trailing elements so that path terminates with
		// a corresponding search term stored in $query
		while (count($path) > 0 && !isset($this->query[$path[0]]))
			array_shift($path);
		while (count($path) > 0 && !isset($this->query[$path[count($path) - 1]]))
			array_pop($path);

		// check for double entries, this is best done with a string build from the array
		// also check that the reverse order is matched too  
		$pathNew= implode(',', $path);
		foreach ($this->result as $pathRes) {
			$pathOld = implode(',', $pathRes);
			$pathOldReverse = implode(',', array_reverse($pathRes));
			if ($pathNew == $pathOld || $pathNew == $pathOldReverse)
				return;
		}
		// finally add the new path to the result array
		$this->result[] = $path;
	}

	private function completePathSpo(&$path, $m) {
		if ($m == 1) $m = count($path) - 1;
		if (PSC_WikiData::isProperty($path[$m])) {
			if (PSC_WikiData::isPropertyXsdType($path[$m]))
				array_unshift($path, PSC_WikiData::getPropertyXsdType($path[$m]));
			else {
				$psc = new PSC_Path($path[$m]);
				$n = ($m == 0) ? 1 : $m - 1;
				if (isset($path[$n])) $psc->addLeft($path[$n]);
				$next = $psc->getNext();
				if (count($next) > 0) {
					// get names for categories
					foreach ($next as $id) {
						$this->details[$id][PSC_SMWDATA_NAME] = PSC_WikiData::getNameById($id);
						$this->details[$id][PSC_SMWDATA_TYPE] = NS_CATEGORY;
					}

					if ($m == 0) // add new neighbour at furthermost right side
						array_unshift($path, implode('|', $next));
					else		 // add new neighbour at furthermost left side
						$path[] = implode('|', $next);
				} else {
					if ($m == 0) // remove furthermost left element
						array_shift($path);
					else         // remove furthermost right element
						array_pop($path);
				}
			}
		}
	}
	
	private function checkPathConsistency(&$path) {
		$i = 0;
		$is = count($path);
		while ($i < $is) {
			// if there are several elements in one level, these are devided by a |
			$subjects = explode("|", $path[$i]);
			$subject = $subjects[0];

			// if subject is an xsd value, then the next element is a property
			// that has an xsd value (which data type is this subject), so skip the following checks
			if (! $this->isXsdType($subject)) {
				// check if current element is a property, then something is wrong
				if ($this->smwDataGetType($subject) == SMW_NS_PROPERTY)
					return false;

				// check if current element is not a category and also n
				// -> then it must be a page. Check if a category follows
				if ($this->smwDataGetType($subject) != NS_CATEGORY) {
					// if next element is a category, then continue with this one 
					if (($is > $i + 1) && 
					    ($this->smwDataGetType($path[$i + 1]) == NS_CATEGORY)) {
					    $path[$i + 1] .= "|".$path[$i];
					    unset($path[$i]);
						$i++;
						continue;
					}
				}
			}

			// if this is the last element, then we are done
			if ($i + 1 == $is) return true;

			// we should be able to construct a triplet now with the next two elements
			if ($i + 2 == $is) {
				// only onle element left, check if the current element is a category and the last
				// element is a page, the this page is in that category.
				if ($this->smwDataIsPage($path[$i + 1]) && ($this->smwDataGetType($subject) == NS_CATEGORY)) {
					$path[$i].= "|".$path[$i + 1];
					unset($path[$i + 1]);
					// As we have a concrete instance of a category, check if the new constructed
					// element has the releation that was determined for the category before.
					// Therefore we just adjust the pointer and check the last triplet again.
					$i -= 2;
					$is -= 1;
					// remove direction from property again
					$path[$i + 1] = substr($path[$i + 1], 0, strpos($path[$i + 1], "|"));
					continue;
				} 
				// no category followed by a page, something went wrong and the path is broken
				else return false;
			}
				
			$property = $path[$i + 1];
			$objects = explode("|", $path[$i + 2]);
			$object = $objects[0];
			$direction = 0;

			// check if we have a subject with XSD Type definition (see details for these steps below)
			if ($this->isXsdType($subject)) {
				$newSub = array($subject); // just for compatiblity
				if ($this->smwDataGetType($object) == NS_CATEGORY) {
					$newObj = array();
					$x0 = $this->getCategoryHierarchy($objects, $newObj);
				}
				else {
					$newObj = array($object);
					$x0 = 0;
				}
				for ($x = $x0, $xs = count($objects); $x < $xs; $x++) {
					if ($this->checkInstance($property, $objects[$x]) == 1) {
				       $direction = -1;
				       $newObj[] = $objects[$x];
					}
				}
				if ($direction == -1) $path[$i + 2] = implode('|', array_unique($newObj));
			}
			
			// check if we have a object with XSD Type definition (see details for these steps below)
			else if ($this->isXsdType($object)) {
				$newObj = array($object); // just for compatiblity
				if ($this->smwDataGetType($subject) == NS_CATEGORY) {
					// build a new array of all possible subcategories/pages and check each element in a triple
					$newSub = array();
					$x0 = $this->getCategoryHierarchy($subjects, $newSub);
				}
				else {
					$newSub = array($subject);
					$x0 = 0;
				}
				for ($x = $x0, $xs = count($subjects); $x < $xs; $x++) {
					if ($this->checkInstance($property, $subjects[$x]) == 1) {
				       $direction = 1;
				       $newSub[] = $subjects[$x];
					}
				}
				if ($direction == 1) $path[$i] = implode('|', array_unique($newSub));
			}
			
			// normal triplet with domain and range (no XSD types at any side)
			else {
				if ($this->smwDataGetType($subject) == NS_CATEGORY) {
					// build a new array of all possible subcategories/pages and check each element in a triple.
					// push the top category in the path, even though there are no direct relations for that
					// category.
					$newSub = array();
					$x0 = $this->getCategoryHierarchy($subjects, $newSub);
				}
				// subject is no category, then just add the element to the new array (this must be there as it
				// was found when searching for a path.) 
				else {
					$newSub = array($subject);
					$x0 = 0;
				}
				// do the same for the object
				if ($this->smwDataGetType($object) == NS_CATEGORY) {
					$newObj = array();
					$y0 = $this->getCategoryHierarchy($objects, $newObj);
				}
				else {
					$newObj = array($object);
					$y0 = 0;
				}

				// now step over all subjects and objects and find these categories / pages only, that really have
				// a relation only. This will eleminate sub categories that might fit into a scheme but that don't
				// have any pages (instances) actually annotated with that relation.
				// If the subject/object has several elements then the first one is the (top) category and sub categories
				// or pages are following. Then skip the (top) category and check the rest, as the first element will
				// be part of the relation if the other elements do. The start index for the check is defined in x0 and
				// y0 and set by the function getCategoryHierarchy();
				for ($x = $x0, $xs = count($subjects); $x < $xs; $x++) {
					for ($y = $y0, $ys = count($objects); $y < $ys; $y++) {
						$found = $this->checkTriplet($subjects[$x], $property, $objects[$y]);
						if ($found != 0) {
							$direction = $found;
							$newSub[] = $subjects[$x];
							$newObj[] = $objects[$y];
						}
					}
				}
			}
			
			// if there was a relation between the objects, then use the new
			// constructed subjects and objects as elements in the path, otherwise
			// the path will be deleted anyway
			if ($direction != 0) {
				// check for subjects and objects, if the relation inlcuded page names
				// before but now the pages doesn't match the relation, then the relation
				// is invalid, as we wanted the page names to be included
				// do this for both, subjects ...
				$diff = array_diff($subjects, $newSub);
				if (count($diff) > 0) {
					foreach ($diff as $e) {
						if ($this->smwDataIsPage($e)) return false; 
					}
				}
				// ... and objects
				$diff = array_diff($objects, $newObj);
				if (count($diff) > 0) {
					foreach ($diff as $e) {
						if ($this->smwDataIsPage($e)) return false; 
					}
				}
				
				// everything is correct, build the new element for the found relations
				// this might eliminate some subcategories, that don't have instances with
				// that relation 
				$path[$i] = implode("|", array_unique($newSub));
				$path[$i + 2] = implode("|", array_unique($newObj));
			}
			// no triplets found relation doesn't exist,
			// return false and the path will be thrown away
			else return false;

			// otherwise add the direction of the arrow to the property
			// -1 is <- and 1 is ->			
			$path[$i + 1]= "$property|$direction";
			
			// move current pointer forward so that the current object will become
			// the next subject for the next triple
			$i += 2;
		}
	}

	private function checkInstance($property, $object) {
		static $instances;
		
		// check if we already searched this instance before
		$key="$property,$object";
		if (isset($instances[$key]))
			return $instances[$key];
		
		$db =& wfGetDB(DB_SLAVE);
		$smw_ids = $db->tableName('smw_ids');
		$smw_atts = $db->tableName('smw_atts2');
		$categorylinks = $db->tableName('categorylinks');

	    if ($this->smwDataGetType($object) == NS_CATEGORY)
	    	$objectSubSelect = "in (select s.smw_id from $smw_ids s, $categorylinks cl " .
	    			            "where s.smw_sortkey = cl.cl_sortkey and cl.cl_to = ".$db->addQuotes(PSC_WikiData::getNameById($object)).")";
	    else $objectSubSelect = "= $object";
	    $query = "select count(*) as cnt from $smw_atts a where a.s_id $objectSubSelect and a.p_id = $property";

		// execute query  
	    $res = $db->query($query);
	    if ($res && ($row = $db->fetchObject($res)) && $row->cnt > 0 ) {
	    	$db->freeResult($res);
	    	$triples[$key] = 1;
	    	return 1;
	    }
	    $triples[$key] = 0;
		return 0;
	}
	
	private function checkTriplet($subject, $property, $object) {
		static $triples;
		
		// check if we already searched this triple before
		$key = "$subject,$property,$object";
		if (isset($triples[$key]))
			return $triples[$key];
		// maybe already there in reverse order
		if (isset($triples["$object,$property,$subject"]))
			return ($triples["$object,$property,$subject"] * -1);

		$db =& wfGetDB(DB_SLAVE);
		$smw_ids = $db->tableName('smw_ids');
		$smw_rels = $db->tableName('smw_rels2');
		$categorylinks = $db->tableName('categorylinks');
	
		// construct query
	    if ($this->smwDataGetType($subject) == NS_CATEGORY)
	    	$subjectSubSelect = "in (select s.smw_id from $smw_ids s, $categorylinks cl " .
	    			            "where s.smw_sortkey = cl.cl_sortkey and cl.cl_to = ".$db->addQuotes(str_replace(' ', '_', PSC_WikiData::getNameById($subject))).")";
	    else $subjectSubSelect = "= $subject";
	    if ($this->smwDataGetType($object) == NS_CATEGORY)
	    	$objectSubSelect = "in (select s.smw_id from $smw_ids s, $categorylinks cl " .
	    			            "where s.smw_sortkey = cl.cl_sortkey and cl.cl_to = ".$db->addQuotes(str_replace(' ', '_', PSC_WikiData::getNameById($object))).")";
	    else $objectSubSelect = "= $object";
	    
	    $query = "select count(*) as cnt from $smw_rels r where r.s_id %s and r.p_id = $property and r.o_id %s";

		// execute query in order subject property object 
	    $res = $db->query(sprintf($query, $subjectSubSelect, $objectSubSelect));
	    if ($res && ($row = $db->fetchObject($res)) && $row->cnt > 0 ) {
	    	$db->freeResult($res);
	    	$triples[$key] = 1;
	    	return 1;
	    }
	    
		// execute query in order object property subject 
	    $res = $db->query(sprintf($query, $objectSubSelect, $subjectSubSelect));
	    if ($res && ($row = $db->fetchObject($res)) && $row->cnt > 0 ) {
	    	$db->freeResult($res);
	    	$triples[$key] = -1;
	    	return -1;
	    }
	    $triples[$key] = 0;
	    return 0;
	}

	private function getInstanceResult($property, $direction, $subject) {
		
		$result = array();
		
		$db =& wfGetDB(DB_SLAVE);
		$smw_ids = $db->tableName('smw_ids');
		$smw_atts = $db->tableName('smw_atts2');
		$categorylinks = $db->tableName('categorylinks');

	    if ($this->smwDataGetType($subject) == NS_CATEGORY)
	    	$objectSubSelect = "in (select s.smw_id from $smw_ids s, $categorylinks cl " .
	    			            "where s.smw_sortkey = cl.cl_sortkey and cl.cl_to = ".$db->addQuotes(PSC_WikiData::getNameById($subject)).")";
	    else $objectSubSelect = "= $object";
	    $query = "select a.s_id as s_id, a.value_xsd as value from $smw_atts a where a.s_id $objectSubSelect and a.p_id = $property";

		// execute query  
	    $res = $db->query($query);
	    if ($res) {
	    	while ($row = $db->fetchObject($res)) {
	    		if ($direction == 1)
	    			$result[] = array($row->s_id, $row->value);
	    		else
	    			$result[] = array($row->value, $row->s_id);
	    	}
	    	$db->freeResult($res);
	    }
		return $result;
	}

	private function getTripletResults($rel1, $prop, $rel2) {
		
		// read "arrow" direction
		list ($property, $direction) = explode('|', $prop);

		// check if subject or object is an XSD type
		if ($this->isXsdType($rel1))
			return $this->getInstanceResult($property, -1, $rel2);
		if ($this->isXsdType($rel2))
			return $this->getInstanceResult($property, 1, $rel1);

		// adjust subject / property order depending on "arrow" direction
		if ($direction == 1) {
			$subject = $rel1;
			$object = $rel2;
		} 
		else {
			$subject = $rel2;
			$object = $rel1;
		}

		// check if the elements have pages in their elements. If both are pages, then we return the
		// pages directly because these are included in the path already, hence no need to search further
		if ($this->smwDataIsPage($subject) !== false && $this->smwDataIsPage($object) !== false)
			return array(array($subject, $object));
		
		$result = array();
		
		$db =& wfGetDB(DB_SLAVE);
		$smw_ids = $db->tableName('smw_ids');
		$smw_rels = $db->tableName('smw_rels2');
		$categorylinks = $db->tableName('categorylinks');

		// construct query
	    if ($this->smwDataGetType($subject) == NS_CATEGORY)
	    	$subjectSubSelect = "in (select s.smw_id from $smw_ids s, $categorylinks cl " .
	    			            "where s.smw_sortkey = cl.cl_sortkey and cl.cl_to = ".$db->addQuotes(str_replace(' ', '_', PSC_WikiData::getNameById($subject))).")";
	    else $subjectSubSelect = "= $subject";
	    if ($this->smwDataGetType($object) == NS_CATEGORY)
	    	$objectSubSelect = "in (select s.smw_id from $smw_ids s, $categorylinks cl " .
	    			            "where s.smw_sortkey = cl.cl_sortkey and cl.cl_to = ".$db->addQuotes(str_replace(' ', '_', PSC_WikiData::getNameById($object))).")";
	    else $objectSubSelect = "= $object";
	    
	    $query = "select r.s_id as s_id, r.o_id as o_id from $smw_rels r where r.s_id %s and r.p_id = $property and r.o_id %s";

		// execute query in order subject property object 
	    $res = $db->query(sprintf($query, $subjectSubSelect, $objectSubSelect));
	    if ($res) {
	    	while ($row = $db->fetchObject($res)) {
				if ($direction == 1)	    		
	    			$result[] = array($row->s_id, $row->o_id);
	    		else
	    			$result[] = array($row->o_id, $row->s_id);
	    	}
	    	$db->freeResult($res);
	    }
		return $result;	    
	}

	/**
	 * Fetch instances (pages, values) for each path and the described nodes there
	 * All results are stored in the member variable $instances. This is an associative
	 * array. Keys are the path as a string (nodes conected with ",") and inside another
	 * array of matching rows with all results for the current path. These results are
	 * displayed later in the table exact below the path. Each result is below it's
	 * appropiate node name. The columns below the relations are empty. 
	 * 
	 * @access private
	 */
	private function getPathInstances() {
		// walk through each path and fetch results for nodes
		// that consist of properties, categories and sometimes
		// single pages as well.
		foreach ($this->result as $path) {
			$key = implode(',', $path);

			// still a triplet left for fetching results
			while (count($path) > 2) {
				// remove the following two elements from the path. The 3rd (object) is
				// the subject in the next run
				$subject = array_shift($path);
				$property = array_shift($path);
				$object = $path[0];
				
				// check for pages in the node, pages have priority over categories because
				// the original searh term must have been a page where the category was found for
				// and then the path was created using category information
				$pageSubject = $this->elementHasPage($subject); 
				$subs =  ( $pageSubject !== false ) ? array($pageSubject) : explode('|', $subject);
				$pageObject = $this->elementHasPage($object); 
				$objs = ( $pageObject !== false ) ? array($pageObject) : explode('|', $object);
				
				// save all tuples for the current subject, object (that can be several [sub]categories)
				// if a category itself has a subcategory, then use the subcategory for the search
				// because the page is linked with the subcategory but not the top category
				$currentTuple = array();
				foreach ($subs as $s) {
					if (count(PSC_WikiData::getSubcategories($s)) > 0)
						continue;
					foreach ($objs as $o) {
						if (count(PSC_WikiData::getSubcategories($o)) > 0)
							continue;
						$currentRes = $this->getTripletResults($s, $property, $o);
						if (count($currentRes) > 0)
							foreach ($currentRes as $tuple) $currentTuple[] = $tuple;
					}
				}
				// there were tuples, now try to add these to the previous tuples
				if (count($currentTuple) > 0)
					$this->addInstanceResult($currentTuple, $key);
				// if the current path has no instances anymore, then this is because
				// the different tuples of the single relations in the path didn't match
				// each other. Even though the path may exist in theory, there are no
				// instances anotated, that follow exact this path. If we are not at the end
				// for searching tuples, we don't even have to look further and can continue
				// with the next path of the result set. 
				if (! isset($this->instance[$key]))
					continue 2;
			}
		}
		// set error code if there are no results for any path found
		if (count($this->instance) < 1) {
			$this->resultCode = 2;
			return;
		}
	}

	/**
	 * This function fetches results for single nodes (that are not connected within a path).
	 * This is the case if one search term was provided only and the term was a category or
	 * page. The main difference is, that here the path array must be modified. For all
	 * categories that are listed in the node, the top category remains there but for all
	 * possible subcategories, that don't have pages annotated, these must be removed.
	 * Therefore this functionality is in this function while the rest works with the
	 * function getPathInstances(). See details on the member variables there.
	 * 
	 * @access private
	 * @see    getPathInstances()
	 */
	private function getNodeInstances() {
		
		foreach ($this->result as &$path) {
			// if this is a "normal" path (at least three elements) then the results
			// are fetched with the function getPathInstances(), therefore skip these
			if (count($path) > 1) continue;

			// check if this element has a page somewhere, then we already have the (one and only) value
			$pageId = $this->elementHasPage($path[0]);
			if ($pageId !== false) {
				$this->instance[$path[0]] = array(array($pageId));
				continue;
			}
			$ids = explode('|', $path[0]);
			$foundCats = array();
			
			// if the category has subcategories, then the first element
			// must be the top category. Then we check pages for subcategories only
			if (count($ids) > 1) $foundCats[]= array_shift($ids);
			
			// walk over all categories (that don't have subcats anymore) if pages
			// are found, the current category is added to the list that will be
			// displayed later.
			foreach ($ids as $id) {
				$pages = PSC_WikiData::searchPage4Category($id);
				if (count($pages) > 0) {
					$this->instance[$path[0]] = array();
					foreach ($pages as $page) $this->instance[$path[0]][] = array($page);
					$foundCats[]= $id;
				}
			}
			// now adjust the list of the categories, where we really found pages too
			// for normal paths, this has not to be done because the paths are checked
			// for consistency before actually looking for instances.
			if (count($foundCats) > 0) $path[0] = implode("|", $foundCats);
		} 
	}

	/**
	 * Add a result of a triplet to allready found other results for other triplets
	 * within the same path. This a bit complicated as for each triplet in the path
	 * several rows may occur. However the subject of the current result must match
	 * the object of the corresponding result one column to the left. Furthermore
	 * rows must matched by it's corresponding elements. Also if a object on the
	 * left side has suddenly several subjects on the current triple, these rows
	 * must be all copied so that for each possible combination one result row
	 * exists. Results are stored in the member variable $instance. If a match for
	 * the current triple cannot be done with the previous triples, the current
	 * result is unset. This function is called from the function getPathInstances().
	 * There it's also checked if the $instance variable is NULL suddenly so looking
	 * up further triples for this path is stoped.
	 * $instance is an associative array. Key is the string of the current path for
	 * where the instances are looked up for, nodes divided by ",". The value is
	 * another array, one for each result row. See getPathInctances() for more details.
	 * 
	 * @access private
	 * @see    getPathInstances()
	 */
	private function addInstanceResult($res, $key) {
		// if there is no result for this path yet, add the current
		// result to the instance variable for this path.
		// Otherwise get the length of the saved results to access
		// easily the last element in each path of the result lines.
		
		if (isset($this->instance[$key]))
			$length = count($this->instance[$key][0]) - 1;
		else {
			$this->instance[$key] = $res;
			return;
		}

		// save here the new result lines for the current path
		$newInstance = array();
		
		// as long as there are old resuls left
		while (count($this->instance[$key]) > 0) {
			// store in currOld all result lines that end with the current
			// element and in currNew all results that match these lines
			// in currVal is the value of the left side for which matches
			// in the new results are looked for
			$currOld = array();
			$currNew = array();
			$currOld[] = array_shift($this->instance[$key]);
			$currVal = $currOld[0][$length];
			
			// get all existing lines that end with the current value
			foreach (array_keys($this->instance[$key]) as $i) {
				if ($currVal == $this->instance[$key][$i][$length]) {
					$currOld[] = $this->instance[$key][$i];
					unset($this->instance[$key][$i]);
				}
			}
			// get all result tuples of the new data, that have on the "left"
			// side the current value so that the right side element can be added
			// to the appropriate result lines that already exist
			foreach (array_keys($res) as $k) {
				if ($currVal == $res[$k][0]) {
					$currNew[]= $res[$k][1];
					unset($res[$k]);
				}
			}
			// now merge existing results with new results
			// existing results that have no new results to match
			// are simply skiped. New results that do not have existing
			// results are still stored in $res but not in $currNew
			// therefore will be simply ignored as well.
			foreach ($currOld as $data) {
				foreach ($currNew as $val) {
					$newInstance[] = $data;
					$newInstance[count($newInstance) -1][] = $val;
				}	
			}	
		}
		// overwrite existing variable with new data, this will purge
		// all unwanted result lines from the existing array
		// if the new array is empty, remove also the original row as
		// there are no more result -> i.e. no match of old and new values
		if (count($newInstance) > 0)
			$this->instance[$key] = $newInstance;
		else
			unset($this->instance[$key]);
	}

	/**
	 * Build a new array of all possible top|subcategories for the id. This is important for
	 * the path. If possible we want to show the highest category on top that matches the
	 * search terms and the path. On the other hand, instances can be checked with pages only.
	 * Therefore we also need possible subcategories. The result is a chain of category ids
	 * ordered from left to right as highest to lowest.
	 * Hold the top category always in the path, as well as the subject itself. Add all lowest
	 * categories, that need to be checked.
	 *
	 * @access private
	 * @param  array &subjects array of current subjects
	 * @param  array &newCats  array of new categoy ids that will be in the subject but not checked.
	 * @return int index to start checking subjects
	 */
	private function getCategoryHierarchy(&$subjects, &$newCats) {
		$idxStart = count($subjects) > 1 ? 1 : 0;
		$id = $subjects[0];
		array_unshift($newCats, PSC_WikiData::getTopCategory($id));

		// if the top category is different from the category of the search
		// then get name and set type for later when displaying the data
		if ($newCats[0] != $id) {
			$this->details[$newCats[0]][PSC_SMWDATA_NAME] = PSC_WikiData::getNameById($newCats[0]);
			$this->details[$newCats[0]][PSC_SMWDATA_TYPE] = NS_CATEGORY;
		}
		
		// check, if a page is included in subjects, then don't look for any subcategories
		foreach ($subjects as $sId) {
			if ($this->smwDataIsPage($sId)) return $idxStart;
		}
		
		// we got until here, no page stoped us, then get possible sub categories and add these
		// for the subjct list to be checked.
		$lower = PSC_WikiData::getLowestCategories($id);
		if ($lower[0] != $id) {
			$idxStart++;
			foreach ($lower as $l) {
				$this->details[$l][PSC_SMWDATA_NAME] = PSC_WikiData::getNameById($l);
				$this->details[$l][PSC_SMWDATA_TYPE] = NS_CATEGORY;
				$subjects[] = $l;
			}
		}
		return $idxStart;
	}
	
	/**
	 * Fetch title and namespace for ids. These are taken from the smw_ids table. For categories
	 * and properties, the names and ids are already known by the PSC_WikiData object. Also all
	 * pages that have been in the search terms are already known.
	 * All other ids must be fetched from the DB.
	 * The information is stored in the member variable $details. The key identified the id
	 * of the object (page, category,property) and inside there is an array with two elements
	 * for name and type.
	 * Parameter is the array where ids are in the key available, for which the information need
	 * to be fetched. This is usually eigther $result or $instance for result paths and their
	 * instances.
	 * If ids for $instance is retrieved, then the key of the path is submited as well, because values
	 * in the instances might not be smw_ids but a simple value of a property, which must remain
	 * untouched.
	 * 
	 * @access private
	 * @param &array  data
	 * @param boolean $check default false
	 */
	private function fetchNodeDetails(&$data, $key= NULL) {
		$ids2fetch = array();
		for ($i = 0, $is = count($data); $i < $is; $i++) {
			$cntNode = -1;
			foreach ($data[$i] as $node) {
				$cntNode++;
				if ($key != NULL && $this->getColumnTypeForInstance($cntNode, $key) == PSC_COLTYPE_ISVALUE)
					continue;
				$ids = explode("|", $node);
				foreach ($ids as $id) {
					$name = PSC_WikiData::getNameById($id);
					if ( $name != "" ) {
						$this->details[$id][PSC_SMWDATA_NAME] = $name;
						if (PSC_WikiData::isProperty($id))
							$this->details[$id][PSC_SMWDATA_TYPE] = SMW_NS_PROPERTY;
						else $this->details[$id][PSC_SMWDATA_TYPE] = NS_CATEGORY;
					}
					else 
						$ids2fetch[] = $id;
				}
			}
		}
		if (count($ids2fetch) == 0) return;
		
		$db =& wfGetDB(DB_SLAVE);
		$smw_ids = $db->tableName('smw_ids');
		$res = $db->query("select smw_id, smw_sortkey, smw_namespace from $smw_ids where smw_id in (".implode(", ", $ids2fetch).")");
		if ($res) {
			while ($row = $db->fetchObject($res)) {
				$this->details[$row->smw_id][PSC_SMWDATA_NAME] = $row->smw_sortkey;
				$this->details[$row->smw_id][PSC_SMWDATA_TYPE] = $row->smw_namespace;
			}
		}
		$db->freeResult($res);
	}
	
	/**
	 * Check column type for a row of instances. This is important when in a row
	 * of matching instances the page name is looked up. It can happen that the
	 * result is the value of some property. A look up in the database would fail
	 * or even deliver the wrong value, because the id is no smw_id but the concrete
	 * value of some property. Instance result rows are stored in the member variable
	 * $instance which has the path string as it's first key. With this information
	 * the corresponding path can be found in the member variable $result and the
	 * column type of the concepts (without the properties) can be looked up.
	 * 
	 * @access private
	 * @param  int    $col column number
	 * @param  string $key of the path for $this->instance
	 * @return int    $type of column 
	 */
	private function getColumnTypeForInstance($col, $key) {
		static $colTypes;
		
		if (isset($colTypes[$key])) return $colTypes[$key][$col];
		
		foreach ($this->result as $path) {
			if ($key == implode(',', $path)) { // path to look up is here
				if (count($path) == 1) { // path has one element only, then it must be a page/category
					$colTypes[$key][$col] = PSC_COLTYPE_ISID;
					return PSC_COLTYPE_ISID;
				}
				list($concept, $property) = $this->splitConceptsProperties($path);
				for ($i = 0; $i < count($property); $i++) {
					list($prop, $dir) = explode('|', $property[$i]);
					// xsd value must be a range, so check propery type, and set col the property is pointing to
					if ($dir == -1) {
						$colTypes[$key][$i] = PSC_WikiData::isPropertyXsdType($prop) ? PSC_COLTYPE_ISVALUE : PSC_COLTYPE_ISID;
						$colTypes[$key][$i + 1] = PSC_COLTYPE_ISID;
					}
					else {
						$colTypes[$key][$i] = PSC_COLTYPE_ISID;
						$colTypes[$key][$i + 1] = PSC_WikiData::isPropertyXsdType($prop) ? PSC_COLTYPE_ISVALUE : PSC_COLTYPE_ISID;
					}
					
				}
				return $colTypes[$key][$col];
			}
		}
	}
	
	/**
	 * Takes one search term and the namespace (if defined) and created a start point
	 * for finding a path. Therefore the member variable $path is filled with a new
	 * element (object PSC_Path) that contains information about the search term. If
	 * the type (i.e. namespace) is not defined, the term is looked up in all namespaces
	 * and if found several times, also different paths are created.
	 * If the term was not found in the database, no path is created.
	 * 
	 * @access private
	 * @param string $term
	 * @param int    $type default NULL 
	 */
 	private function addPath4Term($term, $type = NULL) {
 		if ($type == NULL)
 			$types = array(NS_MAIN, SMW_NS_PROPERTY, NS_CATEGORY);
 		else
 			$types = array($type);
 		foreach ($types as $t) {
 			$res = $this->getData4Term($term, $t);
 			if (is_array($res)) {
 				foreach ($res as $smwVal) {
 					if (! isset($this->path[$smwVal[0]]))
 						$this->path[$smwVal[0]] = array();
 					$this->path[$smwVal[0]][] = new PSC_Path($smwVal[0], $smwVal[1]);
 					$this->query[$smwVal[0]] = $smwVal[1];
 					$this->details[$smwVal[0]] = array(
 						PSC_SMWDATA_NAME => $smwVal[1],
 						PSC_SMWDATA_TYPE => $smwVal[2]
 					);
 				}
 			}
 		}
 	} 	

	/**
	 * takes a term (string) and look it up in the wiki. The term can be
	 * found as a property name, category name or page name.
	 * The type is specified when looking for the term in the database.
	 * If the term can not be found exactly a similarity search is done. This
	 * may lead to several results.
	 * The type is defined by the namespace. If the namespace is a category or
	 * property, then the result must be of this type. If the type is the main
	 * namespace, then all entries are looked up (incl. namespace) that are no
	 * category nor property. Like this all pages can be found that have a namespace
	 * which is not the main namespace (e.g. help pages). This function is called from
	 * addPath4Term().
	 * 
	 * @access private
	 * @param  string $term contains the search term
	 * @param  int $type value of namespace (can be NS_MAIN, SMW_NS_PROPERTY or NS_CATEGORY)
	 * @return array of array(smw_id, title) or NULL if nothing found
	 * @see    addPath4Term()
	 */
 	private function getData4Term($term, $type) {
 		$result = array();
 		
 		// if namespace is not for property or category, then leave it open
 		// as this can be any for a page (like Help:)
 		$whereNameSpace = (in_array($type, array(SMW_NS_PROPERTY, NS_CATEGORY)))
                          ? "smw_namespace = $type"
                          : "smw_namespace not in (".SMW_NS_PROPERTY.",".NS_CATEGORY.")"; 
 		
		// create new title object from search term
		$title = Title::newFromText($term, $type);
		if ($title === NULL) {
			$this->resultCode = PSC_ERROR_INVALID_TERMS;
			return;
		}
		$titleQuery = strtoupper($title->getDbkey());
		$db =& wfGetDB(DB_SLAVE);
		$smw_ids = $db->tableName('smw_ids');
		
		// search first for exact match and we would get one result
		$query = "SELECT smw_id, smw_sortkey, smw_namespace FROM $smw_ids ".
                 "WHERE $whereNameSpace AND UPPER(smw_title) = ".$db->addQuotes($titleQuery);
		$res = $db->query($query);
		if ($row = $db->fetchObject($res)) {
			$result[] = array($row->smw_id, $row->smw_sortkey, $row->smw_namespace);
			$db->freeResult($res);
			return $result;
		}
		
		// if there was no match, use the submited name as partial string and search for several entries matching the search term 
		$titleQuery = "%".$titleQuery."%";
		$query = "SELECT smw_id, smw_sortkey, smw_namespace FROM $smw_ids ".
                 "WHERE $whereNameSpace AND UPPER(smw_title) LIKE ".$db->addQuotes($titleQuery);
		$res = $db->query($query);
		
		// return possibly several results
		if ($db->numRows($res) > 0) {
			while ($row = $db->fetchObject($res)) {
				$result[] =  array($row->smw_id, $row->smw_sortkey, $row->smw_namespace);
			}
			$db->freeResult($res);
			return $result;
		}
 	}
 	
 	private function smwDataGetName($id) {
 		if (! $this->isXsdType($id)) {
 			return isset($this->details[$id]) ? $this->details[$id][PSC_SMWDATA_NAME] : $id;
 		}
		return SMWDataValueFactory::findTypeLabel($id);
 	}
 	
 	private function smwDataGetLink($id) {
 		global $wgContLang, $wgServer, $wgScriptPath;

 		if ($this->isXsdType($id)) return SMWDataValueFactory::findTypeLabel($id);
 		if (! isset($this->details[$id])) return $id;
 		$name = $this->details[$id][PSC_SMWDATA_NAME];
 		if ($this->details[$id][PSC_SMWDATA_TYPE] != NS_MAIN)
 			$link = $wgContLang->getNsText($this->details[$id][PSC_SMWDATA_TYPE]).":$name";
 		else $link = $name;
 		return '<a href="'.$wgServer.$wgScriptPath.'/index.php/'.$link.'">'.$name.'</a>';
 	}
 	
 	private function smwDataGetType($id) {
 		if (! $this->isXsdType($id)) {
 			return isset($this->details[$id]) ? $this->details[$id][PSC_SMWDATA_TYPE] : "";
 		}
 		// data type is xsd value type, then it must be a property
 		return SMW_NS_PROPERTY;
 	}
 	
 	private function smwDataIsPage($id) {
 		if ($this->isXsdType($id)) return false;
 		return (! in_array($this->details[$id][PSC_SMWDATA_TYPE], array(NS_CATEGORY, SMW_NS_PROPERTY)));
 	}

 	private function elementHasPage($id) {
 		$ids = explode('|', $id);
 		foreach ($ids as $i) {
 			if ($this->smwDataIsPage($i)) return $i;		
 		}
 		return false;
 	}
 	
 	/**
 	 * check if current id is not numeric, if so then the element
 	 * refered with this id is an XSD Value type such as Date. This
 	 * can be the case if a property exists and there is a value set
 	 * as a range. In this case the id has a string value of the
 	 * datatype. 
 	 * 
 	 * @access private
 	 * @param  string  id of element 
 	 * @return boolean true if id is not numeric or false if id is numeric
 	 */
 	private function isXsdType($id) {
 		return (preg_match('/^\d+$/', $id) == 0);
 	}


 }
 
 
 
?>
