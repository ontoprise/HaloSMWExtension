<?php
/*
 * Created on 14.05.2007
 *
 * Author: kai
 * 
 * GraphCycleDetector is used to detect cycles in the 
 * inheritance graphs of the semantic model.
 */
require_once("GraphEdge.php"); 
global $smwgHaloIP;
require_once("$smwgHaloIP/includes/SMW_GraphHelper.php"); 

class GraphCycleDetector {
 	
 	private $bot;
 	
 	
 	
 	public function GraphCycleDetector(& $bot) {
 		
 		$this->bot = $bot;
 	}
 	
 	/**
 	 * Returns the category cycles as hash array. Every entry refers
 	 * to an array which describes one cycle as array of GraphEdge Objects.
 	 * 
 	 * @return hash array containing arrays of GraphEdge objects.
 	 */
 	public function getAllCategoryCycles($header) {
 		global $wgLang;
 		print "\nCategory cycle\n";
 		$categoryGraph = smwfGetSemanticStore()->getCategoryInheritanceGraph();
 		$cycles = $this->returnCycles($categoryGraph);
 		return $this->storeCycles($cycles);
 	}
 	
 	public function getAllPropertyCycles($header) {
 		global $smwgContLang;
 		print "\nProperty cycle\n";
  		$namespaces = $smwgContLang->getNamespaces();
 		$attributeGraph = smwfGetSemanticStore()->getPropertyInheritanceGraph();
 		$cycles = $this->returnCycles($attributeGraph);
 		return $this->storeCycles($cycles);
 	}
 	
 		
 		
 	
 	/**
 	 * Returns all cycles as array of Cycle objects 
 	 */
 	private function returnCycles(& $graph) {
 		
 		$results = array(); // receives cycles as array of IDs
 		$work = count($graph);
 		$this->bot->addSubTask(count($graph));
 		for($i = 0, $n = count($graph); $i < $n; $i++) {
 			$this->bot->worked(1);
 			if ($i % 10 == 0 || $i == $work-1) { 
 				print "\x08\x08\x08\x08\x08".number_format(($i+1)/$work*100, 0)."% ";
 			}
 			$e = $graph[$i];
 			$visitedNodes = array();
 			$this->_returnCycles($graph, array($i,$i), $visitedNodes, $results); 			
 		}
 		
 		// eliminate duplicates and build cycle objects
 		$cycles = array();
 		foreach($results as $c) {
 			$cycle = new Cycle($c);
 			$cycle->sortCycle();
 			if (!$this->containsCycle($cycles, $cycle)) {
 				$cycles[] = $cycle;
 			}
 		}
 		return $cycles;
 	}
 	
 	/**
 	 * Depth-first search in inheritance graph
 	 */
 	private function _returnCycles(& $graph, $currentEdges, & $visitedNodes, & $results) {
 			list($upper, $lower) = $currentEdges;
 			for($i = $lower; $i <= $upper; $i++) {
 				$ce = $graph[$i];
 				if (in_array($ce->from, $visitedNodes)) {
 					return array($ce->from);
 				} else {
 					$visitedNodes[] = $ce->from;
 				}
 				$nextEdges = GraphHelper::searchBoundInSortedGraph($graph, $ce->to);
 				if ($nextEdges != null) {
 					$cycle = $this->_returnCycles($graph, $nextEdges, $visitedNodes, $results);
 					if ($cycle != null && $ce->from != $cycle[0]) {
 						$cycle[] = $ce->from;
 						array_pop($visitedNodes); // remove last visited node
 						return $cycle;
 					} else if ($ce->from == $cycle[0]){
 						//$cycle[] = $ce->from; // add first node also as last node to show the cycle
 						$results[] = $cycle;
 						array_pop($visitedNodes); // remove last visited node
 						//return null;
 					}
 				}
 			}
 		return null;
 	}

	// Helper for eliminating cycle duplicates
	private function containsCycle(array & $cycleArray, Cycle & $cycle) {
		foreach($cycleArray as $c) {
			if ($c->equals($cycle)) {
				return true;
			}
		}
		return false;
	}
	
	private function storeCycles($cycles) {
		$gi_store = SMWGardening::getGardeningIssuesAccess();
 		foreach($cycles as $c) {
			$cycleID = uniqid (rand()); // generate cycle group ID
 			$titles = $c->translateToTitle();
 			foreach($titles as $t) {
 				// $value parameter is used to store a group ID to which the entity belongs.
 				$gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARD_ISSUE_PART_OF_CYCLE, $t, $cycleID);
 			}
 		}
 	}
}

/**
 * Represents a cycle
 */
class Cycle {
	
	public $cycle; // IDs of cycle
	
	public function Cycle(array & $cycle) {
		$this->cycle = $cycle;
	}
	
	
	public function equals(Cycle & $cycle) {
		if (count($this->cycle) != count($cycle->cycle)) {
			return false;
		}
		for ($i = 0, $n = count($this->cycle); $i < $n; $i++) {
			if ($this->cycle[$i] != $cycle->cycle[$i]) {
				return false;
			}
		}
		return true;
	}
	
	public function sortCycle() {
		sort($this->cycle);
	}
	
	/**
	 * Returns a hash array from ID -> Title
	 */
	public function translateToTitle() {
		
		$db =& wfGetDB( DB_MASTER );
		$sql = "";
		for ($i = 0, $n = count($this->cycle); $i < $n; $i++) {
			if ($i < $n-1) { 
				$sql .= 'page_id ='.$this->cycle[$i].' OR ';
			} else {
				$sql .= 'page_id ='.$this->cycle[$i];
			}
		}
		
		$res = $db->select(  array($db->tableName('page')), 
		                    array('page_title','page_namespace', 'page_id'),
		                    $sql, 'SMW::translate', NULL);
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[$row->page_id] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$db->freeResult($res);
		
		$titles = array();
		foreach($this->cycle as $id) {
			$titles[] = $result[$id];
		}
		return $titles;
	}
}
 
?>
