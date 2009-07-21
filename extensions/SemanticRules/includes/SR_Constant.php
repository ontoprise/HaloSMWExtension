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

 class SMWConstant extends SMWTerm {

	private $_value;

	// creates a Constant object consisting of
	// * a value of the constant
	// * inherited SMWTerm

	// constants must have arity = 0 and ground set to true
	function __construct($value) {
		// check if it is numeric value - if not, add quotes
		if (!is_numeric($value)) {
			// check if value is already quoted - if so, don't add quotes
			if ($value{0} != "\"") {
				$value = "\"" . $value . "\"";
			}
		}
		parent::__construct($value, 0, false);
		$this->_value = $value;
    }

	public function getValue() {
		return $this->_value;
	}

	public function setValue($value) {
		$this_value = $value;
	}

}

?>
