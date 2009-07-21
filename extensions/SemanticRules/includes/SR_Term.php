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
				if ($this->strStartsWith($arguments[1], '#')) {
				} else {
					$arguments[1] = "#" . $arguments[1];
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
			if ($this->strStartsWith($this->_arguments, '#')) {
				return substr($this->_arguments, 1);				
			}			
			return $this->_arguments;
		} else if (sizeof($this->_arguments)>1) {
			if ($this->strStartsWith($this->_arguments[1], '#')) {
				return substr($this->_arguments[1], 1);				
			}
			return $this->_arguments[1];
		} else {
			if ($this->strStartsWith($this->_arguments[1], '#')) {
				return substr($this->_arguments[0], 1);
			}
			return $this->_arguments[0];
		}
	}

	public function getNamespace() {
		global $smwgTripleStoreGraph;
		if (sizeof($this->_arguments)>1) {
			return $this->_arguments[0];
		} else {
			return $smwgTripleStoreGraph;
		}
	}

	public function getFullQualifiedName() {
		global $smwgTripleStoreGraph;
		if ($this->_arity == 0) {
			return $this->_arguments;
		} else {
			if (sizeof($this->_arguments)>1) {
				if ($this->strStartsWith($this->_arguments[0], '"')) {
					return $this->_arguments[0] . $this->_arguments[1];
				} else {
					return '"' . $this->_arguments[0] . '#"' . $this->_arguments[1];
				}
			} else {
				return $smwgTripleStoreGraph . $this->_arguments[0];
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

}

?>
