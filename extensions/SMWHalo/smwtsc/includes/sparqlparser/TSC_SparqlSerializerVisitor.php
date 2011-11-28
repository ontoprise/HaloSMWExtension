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
 * @ingroup LinkedData
 */
/**
 * Implements the class TSCSparqlSerializerVisitor
 * 
 * @author Thomas Schweitzer
 * Date: 19.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}


/**
 * Implements the SPARQL query visitor that serializes a parsed SPARQL query 
 * structure.
 * 
 * @author Thomas Schweitzer
 * 
 */
class TSCSparqlSerializerVisitor extends TSCSparqlQueryVisitor {
	
	//--- Constants ---
		
	//--- Private fields ---
	private $mSerialization;    		// string: Serialization of the query
	private $mPrefixes;					// array<string => string>: all prefixes
	
	/**
	 * Constructor for  class_name
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
//		$this->mXY = $xy;
	}
	

	//--- getter/setter ---
	public function getSerialization()	{return $this->mSerialization;}

	
	//--- Public methods ---
	
	public function preVisitRoot(&$pattern) {
		$this->mPrefixes = array();
		
		$s = &$this->mSerialization;
		$s = "";
		
		// BASE
		$base = "";
		if (isset($pattern['base'])) {
			$base = $pattern['base'];
			$s .= "BASE <$base>\n";
		}
		
		// PREFIX
		if (isset($pattern['prefixes'])) {
			$prefixes = $pattern['prefixes'];
			foreach ($prefixes as $p => $ns) {
				$this->mPrefixes[$p] = $ns;
				// Remove the base prefix from the namespace
				if (!empty($base) && strpos($ns, $base) === 0) {
					$ns = substr($ns, strlen($base));
				}
				$s .= "PREFIX $p <$ns>\n";
			}
		}
	}
	
	public function preVisitQuery(&$pattern) {
		if ($pattern['type'] !== "select") {
			// Currently only "select" queries are supported
			return;
		}
		
		$s = &$this->mSerialization;
		
		$s .= "SELECT ";
		
		if (isset($pattern['distinct']) && $pattern['distinct'] == 1) {
			$s .= "DISTINCT ";
		}
		$vars = $pattern['result_vars'];
		foreach ($vars as $v) {
			if (!isset($v['value'])) {
				// All variables are selected
				$s .= "* ";
				break;
			}
			$s .= '?'.$v['value'].' ';
		}
		$s .= "\n";
		
		// FROM (NAMED) ...
		foreach ($pattern['dataset'] as $ds) {
			$graph = $ds['graph'];
			$named = $ds['named'] ? "NAMED " : "";
			$s .= "FROM $named<$graph>\n";
		}
		
		$s .= "WHERE ";
		
	}
	
	public function preVisitGroup(&$pattern) {
		$this->mSerialization .= "{\n";
	}
		
	public function preVisitOptional(&$pattern) {
		$this->mSerialization .=  "\nOPTIONAL {";
	}

	public function preVisitFilter(&$pattern) {
		$constraint = $pattern['constraint'];
		$constraintType = $constraint['type'];
		$operator = $constraint['operator'];
		
		if ($constraintType == 'built_in_call') {
			 $call = $constraint['call'];
			 $args = $this->serializeArgs($constraint['args']);
			 $expr = "$call($args)";
		} else if ($constraintType == 'expression') {
			$expr = $constraint['patterns'];
			$op1 = $this->serializeOperand($expr[0]);
			$op2 = $this->serializeOperand($expr[1]);
		}

		if ($operator === '!') {
			$expr = "$operator$expr";
		} else if (empty($operator)) {
			// do nothing
		} else {
			$expr = "$op1 $operator $op2";
		}
		
		$this->mSerialization .= "FILTER ($expr)";
		
	}
	
	public function preVisitGraph(&$pattern) {
		$uri = $pattern['uri'];
		if (empty($uri)) {
			// No URI for the graph => expect a variable
			$var = $pattern['var'];
			$this->mSerialization .= "\nGRAPH ?{$var['value']} ";
		} else {
			$this->mSerialization .= "\nGRAPH <$uri> ";
		}
	}

	public function preVisitTriple(&$pattern) {
		$subj = $pattern['s'];
		switch ($pattern['s_type']) {
		case 'var':
			$subj = "?$subj"; break;
		case 'uri':
			$subj = $this->makeURI($subj); break;
		}
		
		$pred = $pattern['p'];
		switch ($pattern['p_type']) {
		case 'var':
			$pred = "?$pred"; break;
		case 'uri':
			$pred = $this->makeURI($pred); break;
		}
		
		$obj = $pattern['o'];
		switch ($pattern['o_type']) {
		case 'var':
			$obj = "?$obj"; break;
		case 'uri':
			$obj = $this->makeURI($obj); break;
		case 'literal':
			$obj = addslashes($obj);
			$obj = '"'.$obj.'"^^'.$this->makeURI($pattern['o_datatype']);
		}
		
		$this->mSerialization .= "$subj $pred $obj .\n";
		
	}
	
	public function interVisitUnion(&$pattern) {
		$this->mSerialization .= "UNION\n";
	}
	
	public function postVisitGroup(&$pattern) {
		$this->mSerialization .= "}\n";
	}
	
	public function postVisitOptional(&$pattern) {
		$this->mSerialization .= "}\n";
	}	
	
	//--- Private methods ---
	
	/**
	 * 
	 * Serializes the arguments of a built-in call in a filter
	 * @param array $args
	 * 		The arguments to serialize
	 */
	private function serializeArgs($args) {
		$s = "";
		$num = count($args);
		$i = 0;
		foreach ($args as $a) {
			if ($a['type'] === 'var') {
				$s .= "?{$a['value']}";
			} else if ($a['type'] === 'literal') {
				$s .= "\"{$a['value']}\"";
			}
			if (++$i < $num) {
				$s .= ', ';
			}
		}
		
		return $s;
	}
	
	/**
	 * Serializes an operand pattern of a filter.
	 * @param array $op
	 * 		The operand pattern to serialize.
	 * @return
	 * 		The serialized operand.
	 */
	private function serializeOperand(array $op) {
		if ($op['type'] == 'var') {
			$op = '?' . $op['value'];
		} else if ($op['type'] == 'literal') {
			$dt = $op['datatype'];
			if (isset($dt)) {
				$op = '"'.$op['value'].'"^^'.$this->makeURI($op['datatype']);
			} else {
				$op = $op['value'];
			}
		}
		return $op;
	}
	
	/**
	 * Converts the representation of the URI $uriValue to an URI with a known 
	 * prefix or an absolute URI in <>.
	 * 
	 * @param string $uriValue
	 * 		An absolute URI without embracing <>
	 */
	private function makeURI($uriValue) {
		foreach ($this->mPrefixes as $pre => $ns) {
			if (strpos($uriValue, $ns) === 0) {
				$uriValue = $pre.substr($uriValue, strlen($ns));
				return $uriValue;
			}
		}
		return "<$uriValue>";
	}
}
