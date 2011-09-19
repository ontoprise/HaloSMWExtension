<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file
 * @ingroup SRRuleObject
 * 
 * @author Kai K�hn
 */
if (!defined('MEDIAWIKI')) die();

 class SMWTerm {

	private $_arity;
	private $_arguments;
	private $_freeVariables;
    private $_isGround;

    // creates a Term object consisting of
    // * (array of) arguments (namespace, localname)
	// * an arity (constants, variables have arity 0, all others > 0)
	// * isground (constants are ground, variables not)

	function __construct($arguments, $arity, $isground) {
		$this->_arity = $arity;
		if ($arity > 0) {
			if (sizeof($arguments) > 1) {				
				if ($this->strStartsWith($arguments[1], '/')) {
				} else {
					$arguments[1] = "/" . $arguments[1];
				}
			}
		}
		$this->_arguments = $arguments;
		$this->_isGround = $isground;
    }

	public function getArity() {
		return $this->_arity;
	}

	public function setArity($_arity) {
		$this->_arity = $_arity;
	}

	// returns possible array of arguments (namespace, localname)
	public function getArgument() {
		return $this->_arguments;
	}

	public function getName() {
		if (!is_array($this->_arguments)) {
			if ($this->strStartsWith($this->_arguments, '/')) {
				return substr($this->_arguments, 1);				
			}			
			return $this->_arguments;
		} else if (sizeof($this->_arguments)>1) {
			if ($this->strStartsWith($this->_arguments[1], '/')) {
				return substr($this->_arguments[1], 1);				
			}
			return $this->_arguments[1];
		} else if (sizeof($this->_arguments)>1) { 
			if ($this->strStartsWith($this->_arguments[1], '/')) {
				return substr($this->_arguments[0], 1);
			}
			return $this->_arguments[0];
		} else return $this->_arguments[0];
	}
	
	public function getValue() {
		return $this->unquote($this->getName());
	}

	public function getNamespace() {
		global $smwgHaloTripleStoreGraph;
		if (sizeof($this->_arguments)>1) {
			return $this->_arguments[0];
		} else {
			return $smwgHaloTripleStoreGraph;
		}
	}

	public function getFullQualifiedName(& $resultType) {
		global $smwgHaloTripleStoreGraph;
		if ($this->_arity == 0) {
			// actually a constant here. Try to interprete it as term.
			$tsn = TSNamespaces::getInstance();
			$resultType = "";
			$fullURI = $tsn->toURI($this->_arguments, $resultType);
			
			return $fullURI;
			
		} else {
			$resultType = "fullURI";
			if (sizeof($this->_arguments)>1) {
				if (strpos($this->_arguments[0], $smwgHaloTripleStoreGraph) === 0) {
					// full qualified
					return $this->_arguments[0] . substr($this->_arguments[1],1);
				} else if (strpos($this->_arguments[0], "obl:default:") === 0) {
					// no namespace given, assume instance
					return $smwgHaloTripleStoreGraph."/a/". ucfirst(substr($this->_arguments[1],1));
				} else {
					// only suffix given
					return $smwgHaloTripleStoreGraph.$this->_arguments[0] . ucfirst(substr($this->_arguments[1],1));	
				}
				
			} else {
				
				return $this->_arguments[0];
			}
		}
	}

	public function setArgument($_arguments) {
		$this->_arguments = $_arguments;
	}

	public function getFreeVariables() {
		return $this->_freeVariables;
	}

	public function setFreeVariables($variables) {
		$this->_freeVariables = $variables;
	}

	public function isGround() {
		return $this->_isGround;
	}

	public function setGround($ground) {
		$this->_isGround = $ground;
	}

	private function strStartsWith($source, $prefix)
	{
   		return strncmp($source, $prefix, strlen($prefix)) == 0;
	}
	
	
	protected function unquote($literal) {
		$literal = self::unDoublequote(self::unSingleQuote($literal));
		$literal = self::unSingleQuote(self::unDoublequote($literal));
		return $literal;
	}
	
 	private static function unDoublequote($literal) {
		$trimed_lit = trim($literal);
		if (stripos($trimed_lit, "\"") === 0 && strrpos($trimed_lit, "\"") === strlen($trimed_lit)-1) {
			$substr = substr($trimed_lit, 1, strlen($trimed_lit)-2);
			return str_replace("\\\"", "\"", $substr);
		}
		return $trimed_lit;
	}
	
    private static function unSingleQuote($literal) {
        $trimed_lit = trim($literal);
        if (stripos($trimed_lit, "'") === 0 && strrpos($trimed_lit, "'") === strlen($trimed_lit)-1) {
            $substr = substr($trimed_lit, 1, strlen($trimed_lit)-2);
            return $substr;
        }
        return $trimed_lit;
    }
}


