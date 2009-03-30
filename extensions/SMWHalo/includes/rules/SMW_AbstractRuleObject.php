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

require_once("SMW_Literal.php");
require_once("SMW_PredicateSymbol.php");
require_once("SMW_Variable.php");
require_once("SMW_Constant.php");

if (!defined('MEDIAWIKI')) die();

abstract class SMWAbstractRuleObject {

	private $_sessionId;
	private $_body;
	private $_head;
	private $_freeVars;
	private $_boundVars;
	private $_axiomId;
	private $_ontologyId;

	// constructor
	function __construct($axiomId = "") {
		$this->_axiomId = $axiomId;
    }

	// setter functions to fill rule object

	public function parseRuleObject($ruleObject) {
		// set flogic string
		$this->_flogicID = $ruleObject;

		// fetch sessionId
		$_ruleobject = $ruleObject->rule;
		// fetch rule
		if ($_ruleobject !== NULL) {
			if (isset($_ruleobject->rule->_error)) {
				echo $_ruleobject->rule->_error;
				return;
			} else {
				$this->setRule($_ruleobject);
				return $this;
			}
		}
	}

	public function setRule($rule) {

		// set axiomId
		if (isset($rule->_axiomId)) {
			$this->_axiomId = $rule->_axiomId;
		}

		// set ontologyId
		if (isset($rule->_ontologyId)) {
			$this->_ontologyId = $rule->_ontologyId;
		}

		// fetch head of rule
		if (isset($rule->head)) {
			$this->_head = $this->setLiteral($rule->head);
		}

		// fetch body of rule
		if (isset($rule->body)) {
			$bodyargs = array();
			if (!is_array($rule->body)) {
				$rule->body = array($rule->body);
			}
			foreach ($rule->body as $belement) {
				array_push($bodyargs, $this->setLiteral($belement));
			}
			$this->_body = $bodyargs;
		}

		// fetch bound variables
		if (isset($rule->boundVariables)) {
			$boundvars = array();
			if (is_array($rule->boundVariables)) {
				foreach ($rule->boundVariables as $boundval) {
					array_push($boundvars, $this->setVariable($boundval));
				}
			} else {
				array_push($boundvars, $this->setVariable($rule->boundVariables));
			}
			$this->_boundVars = $boundvars;
		}

		// fetch free variables
		if (isset($rule->freevariables)) {
			$this->_freeVars = $this->setVariable($rule->freevariables);
		}
	}

	public function setVariable($var) {
		return new SMWVariable($var->_variableName);
	}

	public function setLiteral($lit) {
		$templit = new SMWLiteral($this->setPredicatesymbol($lit->_preditcatesymbolws), $this->setArguments($lit->_arguments));
		$templit->setArity($lit->_arity);
		return $templit;
	}

	public function setPredicatesymbol($ps) {
		return new SMWPredicateSymbol($ps->_name, $ps->arity);
	}

	public function setArguments($arg) {
		$termargs = array();
		foreach ($arg as $termval) {
			if ($termval->_arity == 0) {
				if ($termval->_isGround == "true") {
					// 1st char. '"' denotes property/category... FIXME: provide method to distinguish Variables/Constants/Categories/Properties
					if (is_numeric($termval->_argument) || $termval->_argument[0] == "\"") {			
						$tempterm = new SMWConstant($termval->_argument);
					} else {
						$tempterm = new SMWTerm($termval->_argument, $termval->_arity, true);						 									
					}
				} else {
					$tempterm = new SMWVariable($termval->_argument);
				}
			} else {
				$tempterm = new SMWTerm($termval->_argument, $termval->_arity, $termval->_isGround);
			}
			array_push($termargs, $tempterm);
		}

		return $termargs;
	}

	public function setBody($body) {
		$this->_body = $body;
	}

	// exactly one SMW_Literal object
	public function setHead($head) {
		$this->_head = $head;
	}

	public function setSessionId($sid) {
		$this->_sessionId = $sid;
	}

	public function setAxiomId($id) {
		$this->_axiomId = $id;
	}
	
	public function setBoundVariables($boundvars) {
		$this->_boundVars = $boundvars;
	}

	// getter functions to access parsed rule object

	// sessionId when parsing a flogic rule via webservice
	public function getSessionId() {
		return $this->_sessionId;
	}

	// body consisting of array of SMW_Literal objects (implicitly concatenated by "AND")
	public function getBody() {
		return $this->_body;
	}

	// exactly one SMW_Literal object
	public function getHead() {
		return $this->_head;
	}

	// #of free variables in rule
	public function getFreeVariables() {
		return $this->_freeVars;
	}

	// #of bound variables in rule
	public function getBoundVariables() {
		return $this->_boundVars;
	}

	public function getAxiomId() {
		return $this->_axiomId;
	}

}

?>
