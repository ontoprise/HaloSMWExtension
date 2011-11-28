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
 * @ingroup SRRuleObject
 *
 * @author Kai K�hn
 */
if (!defined('MEDIAWIKI')) die();

class SMWConstant extends SMWTerm {

	private $_value;
	private $_operand;

	// creates a Constant object consisting of
	// * a value of the constant
	// * inherited SMWTerm

	// constants must have arity = 0 and ground set to true
	function __construct($value, $operand = NULL) {
		// check if it is numeric value - if not, add quotes
		$value_unquoted = $this->unquote($value);

		$value = $value_unquoted;

		parent::__construct($value, 0, false);
		$this->_value = $value;
		$this->_operand = $operand;
	}

	public function getValue() {
		return $this->_value;
	}
	
	public function getOperand() {
		switch($this->_operand) {
			case 'lt': return '<';
			case 'gt': return '>';
			case 'lte': return '<=';
            case 'gte': return '>=';
             case 'eql': return '==';
			default: return $this->_operand;
		}

	}

	public function setValue($value) {
		$this_value = $value;
	}

	

}

