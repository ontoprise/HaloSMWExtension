<?php
/*
 * Created on 23.05.2007
 *
 * Author: kai
 */
 
 class GraphHelper {
 	
 		
	private function GraphHelper() {
		// Utility class
	}
	

	// various helper functions
	
 	/**
 	 * Checks if there is a path from $c_id1 to $c_id2
 	 * 
 	 * @param $graph
 	 * @param $c_id1 ID of page
 	 * @param $c_id2 ID of page
 	 * 
 	 * @return true, if $c_id1 is subcategory of $c_id2
 	 */
 	public static function checkForPath(& $graph, $c_id1, $c_id2) {
 		if ($c_id1 == $c_id2) {
 			return true;
 		}
 		$nextEdges = GraphHelper::searchInSortedGraph($graph, $c_id1);
 		if ($nextEdges == null) {
 			return false;
 		}
 		foreach($nextEdges as $e) {
 			if ($e->to == $c_id2) {
 				return true;
 			}
 			$finished = GraphHelper::checkForPath($graph, $e->to, $c_id2);
 			if ($finished) {
 				return true;
 			}
 		}
 		return false;
 	}
 	
 	
 	
 	/**
 	* Searches a value in a sorted array of GraphEdges (sorted for from property). 
 	* If the array is unsorted the result is undefined.
 	* Complexity: O(log(n))
	 *
 	* @return Indices: (upper, lower) with graph[i] = $from for all lower <= i <= upper
 	*/
	public static function searchBoundInSortedGraph( & $sortedGraph, $from) {
 	 $lowerBound = 0;
 	 $upperBound = count($sortedGraph)-1;
 	 do {
 		$diff = $upperBound - $lowerBound;
 		$diff = $diff % 2 == 0 ? $diff/2 : intval($diff/2);
 		$cs = $lowerBound + $diff;
 		if ($sortedGraph[$cs]->from == $from) {
 			return GraphHelper::getAllEdgeBounds($sortedGraph, $cs);
	 	} else {
 			if ($sortedGraph[$cs]->from < $from) {
 				$lowerBound = $cs;
 			} else {
 				$upperBound = $cs;
 			}
 		}
 	 } while($lowerBound < $upperBound && $diff > 0);
 	 return $sortedGraph[$upperBound]->from == $from ? GraphHelper::getAllEdgeBounds($sortedGraph, $upperBound) : null;
	}
	
	
 	/**
 	* Searches a value in a sorted array of GraphEdges (sorted for from property). 
 	* If the array is unsorted the result is undefined.
 	* Complexity: O(log(n))
	 *
 	* @return array of all edges: forall x,y <- (x,y) and x = $from, otherwise null.
 	*/
	public static function searchInSortedGraph( & $sortedGraph, $from) {
 	 $lowerBound = 0;
 	 $upperBound = count($sortedGraph)-1;
 	 do {
 		$diff = $upperBound - $lowerBound;
 		$diff = $diff % 2 == 0 ? $diff/2 : intval($diff/2);
 		$cs = $lowerBound + $diff;
 		if ($sortedGraph[$cs]->from == $from) {
 			return GraphHelper::getAllEdges($sortedGraph, $cs);
	 	} else {
 			if ($sortedGraph[$cs]->from < $from) {
 				$lowerBound = $cs;
 			} else {
 				$upperBound = $cs;
 			}
 		}
 	 } while($lowerBound < $upperBound && $diff > 0);
 	 return $sortedGraph[$upperBound]->from == $from ? GraphHelper::getAllEdges($sortedGraph, $upperBound) : null;
	}
	
	private static function getAllEdges( & $sortedGraph, $index) {
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
	
	
	private static function getAllEdgeBounds( & $sortedGraph, $index) {
		
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
