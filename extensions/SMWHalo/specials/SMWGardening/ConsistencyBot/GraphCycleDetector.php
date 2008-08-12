<?php
/*
 * Created on 14.05.2007
 *
 * Author: kai
 * 
 * GraphCycleDetector is used to detect cycles in the 
 * inheritance graphs of the semantic model.
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

require_once("GraphEdge.php"); 
global $smwgHaloIP;
require_once("$smwgHaloIP/includes/SMW_GraphHelper.php"); 

class GraphCycleDetector {
 	
 	private $bot;
 	private $cc_store;
 	
 	
 	public function GraphCycleDetector(& $bot) {
 		
 		$this->bot = $bot;
 		$this->cc_store = ConsitencyBotStorage::getConsistencyStorage();
 	}
 	
 	/**
 	 * Returns the category cycles as hash array. Every entry refers
 	 * to an array which describes one cycle as array of GraphEdge Objects.
 	 * 
 	 * @return hash array containing arrays of GraphEdge objects.
 	 */
 	public function getAllCategoryCycles(& $categoryGraph) {
 		print "\nCategory cycle\n";
 		$cycles = $this->returnCycles($categoryGraph);
 		return $this->storeCycles($cycles);
 	}
 	
 	public function getAllPropertyCycles(& $propertyGraph) {
 		print "\nProperty cycle\n";
  		$cycles = $this->returnCycles($propertyGraph);
 		return $this->storeCycles($cycles);
 	}
 	
 		
 		
 	
 	/**
 	 * Returns all cycles as array of Cycle objects 
 	 */
 	private function returnCycles(& $graph) {
 		
 		$results = array(); // receives cycles as array of IDs
 		$totalWork = count($graph);
 		$this->bot->addSubTask($totalWork);
 		for($i = 0, $n = count($graph); $i < $n; $i++) {
 			$this->bot->worked(1);
 			if ($i % 10 == 0 || $i == $totalWork-1)  {
 				if ($this->bot->isAborted()) break;
 				GardeningBot::printProgress(($i+1)/$totalWork);
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
		$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
 		foreach($cycles as $c) {
			
 			$titles = $this->cc_store->translateToTitle($c);
 			$cycle = "";
 			foreach($titles as $t) {
 				$cycle .= $t->getNsText().':'.$t->getText().';';
 			}
 			if (count($titles) > 0) {
 				// attach Gardening issue for this cycle to first title
 				$gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARD_ISSUE_CYCLE, $titles[0], $cycle);
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
	
	
}
 
?>
