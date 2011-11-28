<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup ConsistencyBot
 * 
 * @author Kai K�hn
 * 
 * Created on 23.05.2007
 *
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

