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

require_once("SMW_AbstractRuleObject.php");
require_once("SMW_FormulaParser.php");

if (!defined('MEDIAWIKI')) die();

class SMWRuleObject extends SMWAbstractRuleObject {
	
	// internally used to parse UPN stack
	private $numarray = array();
	// holds bound variables for UPN stack parser.
	private $bound = array();
	private $tokentypes = array('const', 'var', 'op', 'func1', 'func2');
	
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
							$formula[$i] = $bndvars[$j][2];
							$element = array_search($bndvars[$j][0], $variables);
							if ($element) {
								unset($variables[$element]);						
							}
						}
					}
				}			
			}
 		}
 		
 		
    	$ruleobject = new SMWRuleObject();
       	// parse formula to get all bound variables
       	$evalflogic = $ruleobject->parseMathRuleArray($formula);
    		    	
    	// create rule head and always include result variable.
		global $smwgNamespace;
		$flogicstring = "FORALL _XRES, _RESULT";
 	
		// fetch bound variables
		$boundvariables = $ruleobject->bound;
		$boundvars = "";
		for ($i = 0; $i < sizeof($boundvariables); $i++) {
			$boundvars .= ", " . $boundvariables[$i];
		}		
		$flogicstring .= $boundvars;
    	foreach ($variables as $var) {
			$flogicstring .= ", " . $var;
		}
		$resultvar = end($boundvariables);
		
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
//					$flogicstring .= ", " . $internalvar;
					$variableassignments .= $ruleobject->argtostring(new SMWPredicateSymbol(P_ATTRIBUTE,3), $ruleobject->createPropertyAssignment("_XRES", $bndvars[$j][2], $bndvars[$j][0]));
				}
				
			}
		}
		
		// add result
		if ($variableassignments !== "") {
			$variableassignments .= " AND evaluable_(_RESULT, " . $resultvar . ")@\"" . $smwgNamespace . "\" AND ";
		} else {
			$variableassignments .= " evaluable_(_RESULT, " . $resultvar . ")@\"" . $smwgNamespace . "\" AND ";
		}
		
		// fetch rule head
		$head = $ruleobject->argtostring(new SMWPredicateSymbol(P_ATTRIBUTE,3), $ruleobject->createPropertyAssignment("_XRES", $resultprop, "_RESULT"));
		$flogicstring .= " | " . $head . " <- ";
		
		// don't foget the "." :)
    	return $flogicstring . $variableassignments . $evalflogic . ".";
    }

	/*
	 * Method to get Flogic string from a Rule Object
	 */
    
	public function getFlogicString() {
		global $smwgNamespace;
		$flogicstring = 'RULE "' . $smwgNamespace . '#"#' . $this->getAxiomId() . ": ";
		return $flogicstring . $this->getPureFlogic(); 		
	}
	
	public function getWikiFlogicString() {
		return $this->getPureFlogic();
	}
        
	private function getPureFlogic() {
		global $smwgNamespace;
		$flogicstring = "FORALL ";

		// fetch bound variables
		$boundvars = "";
		$boundvariables = $this->getBoundVariables();
		for ($i = 0; $i < sizeof($boundvariables); $i++) {
			if ($i > 0) {
				$boundvars .= ", ";
			}
			$boundvars .= $boundvariables[$i]->getVariableName();
		}
		$flogicstring .= $boundvars;

		// fetch rule head
		$head = $this->argtostring($this->getHead()->getPreditcatesymbol(), $this->getHead()->getArguments());
		$flogicstring .= " | " . $head . " <- ";

		// fetch array of rule body and concatenate arguments
		$body = "";
		$bodyarray = $this->getBody();
		for ($i = 0; $i < sizeof($bodyarray); $i++) {
			if ($i > 0) {
				$body .= " AND ";
			}
			$body .= $this->argtostring($bodyarray[$i]->getPreditcatesymbol(), $bodyarray[$i]->getArguments());
		}

		// don't forget the "." @ end of flogic string ;-)

		$flogicstring .= $body . ".";

		return $flogicstring;
	}

	/*
	 *  Method to generate explanation rule from a Rule Object.
	 */

	public function getExplanationRule() {
		global $smwgNamespace;
        // we will insert the header later
    	$queryId = "0"; //$NON-NLS-1$
        $header = "FORALL I, "; //$NON-NLS-1$
		$result = "";

		$result .= "explain_(" . $queryId . ", I, S) <- I:Instantiation[ruleid->>" . $smwgNamespace . '#"#' . $this->getAxiomId() . "; variables ->> {";

		// build variable declarations for bound variables
		$_usedvars = $this->getBoundVariables();
		for ($i=0; $i<sizeof($_usedvars); $i++) {
			if ($this->strStartsWith($_usedvars[$i]->getVariableName(), "?")) {
				$_usedvars[$i]->setVariableName(substr($_usedvars[$i]->getVariableName(), 1));
			}

			$_varValue = "var_" . $_usedvars[$i]->getVariableName();
			$header .= $_varValue . ", ";
			$result .= "i(" . $_usedvars[$i]->getVariableName() . ", " . $_varValue . "),";
		}

		if (!$this->strEndsWith($result, "{")) {
			$result = substr_replace($result ,"",-1);
		}

		$result .= "}]@prooftreefacts_(" . $this->getAxiomId() . ") AND (S is ";
		$header .= "S ";
		// generate rule by concatenating header with vars and the generated template.
		$explanationrule = $header . $result . $this->buildExplanationTemplate();
		return $explanationrule;
	}

	// f-logic helper functions
	
	private function argtostring($pred, $args) {
		switch ($pred->getPredicateName()) {
		case P_ATTRIBUTE:
			// attribute statement
			return $this->getFloPropertyPart($args);
		    break;
		case P_RELATION:
			// relation statement
			return $this->getFloPropertyPart($args);
		    break;
		case P_ISA:
			// isa statement
			return $this->getFloIsaPart($args);
			break;
		default:
			// custom statement
			return $this->getFloOperatorPart($pred->getPredicateName(), $args);
			break;
		}
	}

	private function getFloPropertyPart($args) {
		// statement with 3 terms (att/rel)
		// attribute/relation
		$tmp = "";		 	
		for ($i = 0; $i < sizeof($args); $i++) {
			$tmp .= $args[$i]->getName();		
			if ($i == 0) {				
				$tmp .= "[";
			} else if ($i == 1) {
				$tmp .="->";
			}
		}
		return $tmp .= "]";
	}

	private function getFloOperatorPart($op, $args) {
		$tmp = $op . "(";
		for ($i = 0; $i < sizeof($args); $i++) {
			if ($i > 0) {
				$tmp .= ",";
			}
			$tmp .= $args[$i]->getName();
		}
		return $tmp .= ")";
	}

	private function getFloIsaPart($args) {
		$tmp = '';
		for ($i = 0; $i < sizeof($args); $i++) {
			if ($i > 0) {
				$tmp .= ":";
			}
			$tmp .= $args[$i]->getName();
		}
		return $tmp;
	}

	private function buildExplanationTemplate() {
		$head = $this->getHead();
		$body = $this->getBody();

		$template = "";
		$bodytemplate = "";
		// do we have a property defining head?
		if ($head->getPreditcatesymbol()->getPredicateName() == P_ATTRIBUTE || $head->getPreditcatesymbol()->getPredicateName() == P_RELATION) {
			$template .= $this->getPropertyExplanationPart($head->getArguments());
		}

		$template .= ' + " BECAUSE " + ';

		// for each body statement
		for ($i = 0; $i < sizeof($body); $i++) {
			if ($i > 0) {
				$bodytemplate .= ' AND " + ';
			}
			if ($body[$i]->getPreditcatesymbol()->getPredicateName() == P_ATTRIBUTE || $body[$i]->getPreditcatesymbol()->getPredicateName() == P_RELATION) {
				$bodytemplate .= $this->getPropertyExplanationPart($body[$i]->getArguments());
				if ($i < sizeof($body)) {
					$bodytemplate .= ' + " ';
				}
			} else if ($body[$i]->getPreditcatesymbol()->getPredicateName() == P_ISA) {
				$bodytemplate .= $this->getIsaExplanationPart($body[$i]->getArguments());
			} else {
				$bodytemplate .= $this->getOperatorExplanationPart($body[$i]->getArguments());
			}
		}

		$allinone = $template . $bodytemplate . '. ").';

		return $allinone;
	}

	private function getPropertyExplanationPart($triplearray) {
		$template = "";
		if ($triplearray[2] instanceof SMWConstant) {
			$template .= '"' . $triplearray[1]->getName() . ' of " + ';
			$template .= "var_" . $triplearray[0]->getArgument() . " + ";
			$template .= '" equal ' . $triplearray[2]->getValue() . '"';
		} else {
			$template .= "var_" . $triplearray[0]->getArgument() . " + ";
			$template .= '"' . $triplearray[1]->getName() . '" + ';
			$template .= "var_" . $triplearray[2]->getArgument();
		}
		return $template;
	}

	private function getIsaExplanationPart($tuplearray) {
		$template .= "var_" . $tuplearray[0]->getArgument() . " + ";
		$template .= '"is a ' . $tuplearray[1]->getName();
		return $template;
	}

	private function getOperatorExplanationPart($tuplearray) {
		$template .= "var_" . $tuplearray[0]->getArgument() . " + ";
		$template .= '"op is a ' . $tuplearray[1]->getName();
		return $template;
	}
	
	// flogic-mathematic functions helpers	
	private function parseMathRuleArray($stack) {
		global $smwgNamespace; 
		$flogic = "";
		$count;
		for ($x = 0; $x <= sizeof($stack); $x++)
		{
			// fetch type token
		    $typetoken = $stack[$x];
		    $x++;
		    // fetch value token
		    $valuetoken = $stack[$x];
		    
			switch ($typetoken) {
			case $this->tokentypes[0]:
		    	array_push($this->numarray, $valuetoken);
			    break;
			case $this->tokentypes[1]:
		    	array_push($this->numarray, $valuetoken);
				break;
			case $this->tokentypes[2]:
			    if ($count > 0) {
			    	$flogic .= " AND ";
			    }
			    $count++;
		    	$var1 = array_pop($this->numarray);
		    	$var2 = array_pop($this->numarray);
		    	array_push($this->bound,$boundvar = "t".$x);				    	
		    	  		
		    	$flogic .= $this->evalBinary($var1, $var2, $valuetoken, $boundvar) . "@\"" . $smwgNamespace . "\"";
			    break;
			case $this->tokentypes[3]:
			    if ($count > 0) {
			    	$flogic .= " AND ";
		    	}
				$count++;
				$var1 = array_pop($this->numarray);
		    	array_push($this->bound,$boundvar = "t".$x);		
				
		    	$flogic .= $this->evalUnary($var1, $valuetoken, $boundvar) . "@\"" . $smwgNamespace . "\"";		
			    break;
			case $this->tokentypes[4]:
			    if ($count > 0) {
			    	$flogic .= " AND ";
		    	}
		    	$count++;				
		    	$var1 = array_pop($this->numarray);
		    	$var2 = array_pop($this->numarray);
		    	array_push($this->bound,$boundvar = "t".$x);		
		    	$flogic .= $this->evalBinary($var1, $var2, $valuetoken, $boundvar) . "@\"" . $smwgNamespace . "\"";	
			    break;
			}
		}
		return $flogic;
	}
	
	// creates binary eval expression in f-logic
	
	private function evalBinary($var, $var2, $op, $x) {
		// put result onto stack
		array_push($this->numarray, $x);
		return "evaluable_(".$x.", ".$op."(".$var2.",".$var."))";
	}

	// creates unary eval expression in f-logic
	private function evalUnary($var, $op, $x) {		
		// put result onto stack
	   	array_push($this->numarray, $x);	
		return "evaluable_(".$x.", ".$op."(".$var."))";
	}
	
	private function createPropertyAssignment($intvariable, $prop, $variable) {
		global $smwgNamespace;
		$f = array();
		array_push($f, new SMWVariable($intvariable));
		array_push($f, new SMWTerm(array($smwgNamespace.'/property', $prop), 2, false));
		array_push($f, new SMWVariable($variable));
		return $f;				
	}

	// String helper functions
	private function strStartsWith($source, $prefix)
	{  
   		return strncmp($source, $prefix, strlen($prefix)) == 0;
	}

	private function strEndsWith($haystack, $needle) {
  		$needle = preg_quote( $needle);
  		return preg_match( '/(?:$needle)\$/i', $haystack);
	}

}
?>
