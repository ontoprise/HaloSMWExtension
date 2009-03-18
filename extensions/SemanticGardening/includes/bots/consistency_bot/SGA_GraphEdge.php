<?php
/*
 * Created on 23.05.2007
 *
 * Author: kai
 */
 
 /**
 * Inheritance graph edge representation 
 */
class GraphEdge {
	public $from;
	public $to;
	
	
		
	public function GraphEdge($from, $to) {
		$this->from = $from;
		$this->to = $to; 
		
	}	
	
	public function equals( & $e) {
		if ($e == null) {
			return false;
		}
		return ($e->from == $this->from) && ($e->to == $this->to);
	}	
	
	public function printEdge() {
		echo 'from: '.$this->from. ' to: '.$this->to."\n";
	}
} 
?>
