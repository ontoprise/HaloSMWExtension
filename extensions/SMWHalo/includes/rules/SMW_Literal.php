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

class SMWLiteral {

	private $_preditcatesymbol;
	private $_arguments;
	private $_arity;

	// creates a Literal object consisting of
	// * a predicatesymbol (att, rel, isa)
	// * arguments for the predicate (SMWTerm)

	function __construct($predicatesymbol, $arguments) {
    	$this->_preditcatesymbol = $predicatesymbol;
    	$this->_arguments = $arguments;
    }

	public function getArguments() {
		return $this->_arguments;
	}

	public function setArguments($_arguments) {
		$this->_arguments = $_arguments;
	}

	public function getArity() {
		return $this->_arity;
	}

	public function setArity($_arity) {
		$this->_arity = $_arity;
	}

	public function setPreditcatesymbol($_preditcatesymbolws) {
		$this->_preditcatesymbol = $_preditcatesymbolws;
	}

	public function getPreditcatesymbol() {
		return $this->_preditcatesymbol;
	}
}

?>
