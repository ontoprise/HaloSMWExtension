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

class SMWRuleObject extends SMWAbstractRuleObject {

	// internally used to parse UPN stack
	private $numarray = array();
	// holds bound variables for UPN stack parser.
	private $bound = array();
	private $tokentypes = array('const', 'var', 'op', 'func1', 'func2');

	// variable index counter for creating unique variables
	private static $varIndex = 0;

	function __construct($value = "") {
		parent::__construct($value);
	}

	// create f-logic rule from formula
	public static function newFromFormula($resultprop, $function, $bndvars) {
			
		$formula = array();
		// parse formula to get UPN from infix notation
		$parsedformula = new SMWFormulaParser($function);
		if ($parsedformula->isFormulaValid()) {
			$formula = $parsedformula->getUPNStack();

		} else {
			return $parsedformula->getErrorMsg();
		}
		$variables = $parsedformula->getVariables();

		$donotaddtoheader = array();
		// replace constants in parsed formula
		for ($i = 0; $i < sizeof($formula); $i++) {
			if ($formula[$i] == "var") {
				$i++;
				for ($j = 0; $j < sizeof($bndvars); $j++) {
					if (sizeof($bndvars[$j] == 3)) {
						if ($bndvars[$j][1] == "const" && $bndvars[$j][0] == $formula[$i]) {
							$formula[$i-1] = "const";
							$formula[$i] = $bndvars[$j][2];
							$element = array_search($bndvars[$j][0], $variables);
							if ($element !== FALSE) {
								unset($variables[$element]);
							}
						}
					}
				}
			}
		}
			
			
		$ruleobject = new SMWRuleObject();

		// serialize formula to be valid ObjectLogic
		// ie. make the variables to OL variables
		$ser_formula = $ruleobject->serializeFormula($formula);
			
		// create rule head and always include result variable.
		global $smwgHaloTripleStoreGraph;
		$oblstring = "";
		$resultvar = "?_RESULT";

		// build rule body assignments of bound variables
		$variableassignments = "";
		$count = 0;
		for ($j = 0; $j < sizeof($bndvars); $j++) {
			if (sizeof($bndvars[$j] == 3)) {
				if ($bndvars[$j][1] == "prop") {
					if ($count > 0) {
						$variableassignments .= " AND ";
					}
					$count++;
					// do not add further instance variable
					//					$internalvar = "_X" . $count;
					//					$oblstring .= ", " . $internalvar;
					$variableassignments .= $ruleobject->argtostring(new SMWPredicateSymbol(P_ATTRIBUTE,3), $ruleobject->createPropertyAssignment("_XRES", $bndvars[$j][2], $bndvars[$j][0]), "BODY");
				}

			}
		}

		// add result

		$resultMapping = " AND $resultvar = $ser_formula";
			

		// fetch rule head
		$head = $ruleobject->argtostring(new SMWPredicateSymbol(P_ATTRIBUTE,3), $ruleobject->createPropertyAssignment("_XRES", $resultprop, "_RESULT"), "HEAD");
		$oblstring .= " ". $head . " :- ";

		// don't foget the "." :)
		return $oblstring . $variableassignments . $resultMapping . ".";
	}

	/**
	 * Returns the ObjectLogic rule as it is put into the wiki text.
	 *
	 */
	public function getWikiOblString() {
		global $smwgHaloTripleStoreGraph;
		$oblstring = "";


		// fetch rule head
		$head = $this->argtostring($this->getHead()->getPreditcatesymbol(), $this->getHead()->getArguments(), "HEAD");
		$oblstring .= " " . $head . " :- ";

		// fetch array of rule body and concatenate arguments
		$body = "";
		$bodyarray = $this->getBody();
		for ($i = 0; $i < sizeof($bodyarray); $i++) {
			if ($i > 0) {
				$body .= " AND ";
			}
			$body .= $this->argtostring($bodyarray[$i]->getPreditcatesymbol(), $bodyarray[$i]->getArguments(), "BODY");
		}

		// don't forget the "." @ end of obl string ;-)

		$oblstring .= $body . ".";

		return $oblstring;
	}

	/**
	 * Converts formula from postfix to infix notation and adds OBL variables.
	 *
	 * @param Formula as postfix notation (on a stack) $formula
	 * @return Infix formula
	 */
	private function serializeFormula($formula) {
			
		$stack = array();
		$stackpointer = 0;
		for($i = 0; $i < count($formula); $i=$i+2) {
			if ($formula[$i] == 'var') {
				$stack[$stackpointer] = "?".ucfirst($formula[$i+1]);
				$stackpointer++;
			} else if ($formula[$i] == 'const') {
				$stack[$stackpointer] = $formula[$i+1];
				$stackpointer++;
			} else if ($formula[$i] == 'op') {
				$op =  $formula[$i+1];

				// operands
				$op1 = $stack[$stackpointer-2];
				$op2 = $stack[$stackpointer-1];
				$f = "($op1 $op $op2)";
				$stackpointer -= 2;
				$stack[$stackpointer] = $f;
				$stackpointer++;

			} else if (strpos($formula[$i],'func') !== false) {

				// functions
				$op =  $formula[$i+1];
				$op1 = $stack[$stackpointer-1];
				$f = "$op($op1)";
				$stackpointer -= 1;
				$stack[$stackpointer] = $f;
				$stackpointer++;
			}
		}
		return $stack[0];
	}





	// obl-logic helper functions

	private function argtostring($pred, $args, $rulePart) {
		switch ($pred->getPredicateName()) {
			case P_ATTRIBUTE:
				// attribute statement
				return $this->getOblPropertyPart($args, $rulePart);
				break;
			case P_RELATION:
				// relation statement
				return $this->getOblPropertyPart($args, $rulePart);
				break;
			case P_ISA:
				// isa statement
				return $this->getOblIsaPart($args);
				break;
			default:
				// custom statement
				return $this->getOblOperatorPart($pred->getPredicateName(), $args);
				break;
		}
	}

	private function getOblPropertyPart($args, $rulePart) {
		// statement with 3 terms (att/rel)
		// attribute/relation
		$tmp = "";
		$tmp2 = "";
		for ($i = 0; $i < sizeof($args); $i++) {


			if ($i == 0) {
				$tmp .= $args[$i] instanceof SMWVariable ? $args[$i]->getName() : ucfirst($args[$i]->getName());
				$tmp .= "[prop#";
			} else if ($i == 1) {
				$tmp .= $args[$i] instanceof SMWVariable ? $args[$i]->getName() : ucfirst($args[$i]->getName());
				$tmp .="->";
				if (!($args[$i] instanceof SMWVariable)) {
					$property = SMWDIProperty::newFromUserLabel(ucfirst($args[$i]->getName()));
					$wikiType = $property->findPropertyTypeID();
					
				}
			} else if ($i == 2) {
				if ($args[$i] instanceof SMWConstant && $rulePart == "BODY") {

					// in this case a second literal containing the comparison
					// must be created
					$xsdType = WikiTypeToXSD::getXSDType($wikiType);
					$xsdType = str_replace(":", "#", $xsdType);
					$typeHint = "";
					if ($wikiType == '_boo') {
						$value = '"'.strtolower($args[$i]->getName()).'"^^xsd:boolean';
						$tmp .= $value;
					} else if (!WikiTypeToXSD::isPageType($wikiType) && $wikiType != '_num') {
						$typeHint = "^^$xsdType";
						$value = '"'.$args[$i]->getValue().'"'.$typeHint;
						$tmp .= $value;
					} else if ($wikiType == '_num') {
						$value = $args[$i]->getValue();
						if (is_null($args[$i]->getOperand())) {
							$operand = "==";
						} else $operand = $args[$i]->getOperand();
						$tmp .= "?__VALUE".self::$varIndex;
						$tmp2 = " AND ?__VALUE".self::$varIndex." ".$operand." ".$value;
						self::$varIndex++;
					} else if (WikiTypeToXSD::isPageType($wikiType)) {
						$uri_value = $args[$i]->getFullQualifiedName($resultType);
						$value = $resultType == "fullURI" ? "<$uri_value>" : $uri_value;
						$tmp .= $value;
					}

				} else if ($args[$i] instanceof SMWConstant && $rulePart == "HEAD") {

					$xsdType = WikiTypeToXSD::getXSDType($wikiType);
					$xsdType = str_replace(":", "#", $xsdType);
					$typeHint = "";
					if ($wikiType == '_boo') {
						$value = '"'.strtolower($args[$i]->getName()).'"^^xsd:boolean';
					} else if (!WikiTypeToXSD::isPageType($wikiType) && $wikiType != '_num') {
						$typeHint = "^^$xsdType";
						$value = '"'.$args[$i]->getValue().'"'.$typeHint;
					} else if ($wikiType == '_num') {
						$value = $args[$i]->getValue();
					} else if (WikiTypeToXSD::isPageType($wikiType)) {
						$uri_value = $args[$i]->getFullQualifiedName($resultType);
                        $value = $resultType == "fullURI" ? "<$uri_value>" : $uri_value;
					}
					$tmp .= $value;

				} else if ($args[$i] instanceof SMWVariable) {
					$tmp .= $args[$i]->getName();
				} else if ($args[$i] instanceof SMWTerm) {
					$uri_value = $args[$i]->getFullQualifiedName($resultType);
                    $tmp .= $resultType == "fullURI" ? "<$uri_value>" : $uri_value;
				
				}
			}
		}
		return $tmp .= "] ".$tmp2;
	}

	private function getOblOperatorPart($op, $args) {
		$tmp = $op . "(";
		for ($i = 0; $i < sizeof($args); $i++) {
			if ($i > 0) {
				$tmp .= ",";
			}
			$tmp .= $args[$i]->getName();
		}
		return $tmp .= ")";
	}

	private function getOblIsaPart($args) {
		$tmp = '';
		for ($i = 0; $i < sizeof($args); $i++) {
			if ($i > 0) {
				$tmp .= ":cat#";
			}
			$tmp .= ucfirst($args[$i]->getName());
		}
		return $tmp;
	}

	private function createPropertyAssignment($intvariable, $prop, $variable) {
		global $smwgHaloTripleStoreGraph;
		$f = array();
		array_push($f, new SMWVariable($intvariable));
		array_push($f, new SMWTerm(array($smwgHaloTripleStoreGraph.'/property', $prop), 2, false));
		array_push($f, new SMWVariable($variable));
		return $f;
	}



}

