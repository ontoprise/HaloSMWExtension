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

require_once('SMW_Term.php');

if (!defined('MEDIAWIKI')) die();

// defining a variable consisting of:
// * a variablename
// * inherited Term (need to set arity to 0 and ground to false)

class SMWVariable extends SMWTerm {
	private $_variableName;

	function __construct($variableName) {
    	$this->_variableName = $variableName;
		parent::__construct($variableName, 0, false);
    }

	public function getVariableName() {
		return $this->_variableName;
	}

	public function setVariableName($name) {
		$this->_variableName = $name;
	}

}

?>