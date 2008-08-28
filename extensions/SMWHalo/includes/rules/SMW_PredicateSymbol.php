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

require_once('SMW_Predicate.php');

if (!defined('MEDIAWIKI')) die();

// creates a Predicatesymbol object consisting of
// * a predicatesymbol (P_ATTRIBUTE, P_RELATION, P_ISA, ...)
// * the arity of the predicate

// constants defining predicate names

define('P_ATTRIBUTE', 'attr_');
define('P_RELATION', 'attr_');
define('P_ISA', 'isa_');
define('P_DISA', 'directisa_');
define('P_EQUAL', 'equal');
define('P_GREATERTHAN', 'greater');
define('P_GREATEROREQUALTHAN', 'greaterorequal');
define('P_LESSTHAN', 'less');
define('P_LESSOREQUALTHAN', 'lessorequal');

class SMWPredicateSymbol extends SMWPredicate {

	private $_name;

	function __construct($predicatename, $arity) {
		switch ($predicatename) {
			case "att_":
				$this->_name = P_ATTRIBUTE;
				break;
			case "isa_":
				$this->_name = P_ISA;
				break;
			default:
				$this->_name = $predicatename;
		}
		$this->setArity($arity);
    }

	public function getPredicateName() {
		return $this->_name;
	}

	public function setPredicateName($_name) {
		$this->_name = $_name;
	}
}

?>
