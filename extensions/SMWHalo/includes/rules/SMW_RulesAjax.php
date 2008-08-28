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
 * Ajax functions for simple rules.
 *
 *
 * @author Thomas Schweitzer
 */


if (!defined('MEDIAWIKI')) die();

global $wgAjaxExportList;

$wgAjaxExportList[] = 'smwf_sr_AddRule';
$wgAjaxExportList[] = 'smwf_sr_ParseRule';
$wgAjaxExportList[] = 'smwf_sr_ParseFormula';



/**
 * Adds or updates a rule.
 *
 * @param string $ruleName
 * 		Name of the rule.
 * @param string $ruleXML
 * 		The XML definition of the rule.
 *
 */
function smwf_sr_AddRule($ruleName, $ruleXML) {
		
	try {
		$xml = new SimpleXMLElement($ruleXML);
	} catch (Exception $e) {
		return $e->getMessage();
	}
	
	global $smwgHaloIP, $smwgNamespace;
	require_once "$smwgHaloIP/includes/rules/SMW_RuleObject.php";
	
	if ($xml->formula) {
		// create a calculation rule
		$property = (string) $xml->formula->property;
		$expr = (string) $xml->formula->expr;
		$variables = array();
		foreach ($xml->formula->variable as $var) {
			$vn = (string) $var->name;
			$vp = isset($var->property) ? (string) $var->property : null;
			$vc = isset($var->constant) ? (string) $var->constant : null;
			$variables[] = array($vn, 
			                     is_null($vc) ? 'prop' : 'const', 
			                     is_null($vc) ? $vp : $vc); 
		}
		$rule = SMWRuleObject::newFromFormula($property, $expr, $variables);
		if (is_a($rule, 'SMWRuleObject')) {
			return $rule->getWikiFlogicString();
		} else {
			// return the error message
			return $rule;
		}
	}
	
	$rule = new SMWRuleObject($ruleName);
	$boundVars = array();
	//Namespaces for
	// Instances: baseURI/a#
	// Categories: baseURI/category# 
	// Properties: baseURI/property#
	
	$head = $xml->head;
	if ($head->category) {
		// create a category literal
		$subject = new SMWVariable($head->category->subject);
		$boundVars[(string)$head->category->subject] = $subject;
		$headLit = new SMWLiteral(new SMWPredicateSymbol(P_ISA, 2), 
		                          array($subject,
		                                new SMWTerm(array($smwgNamespace.'/category', 
		                                                  $head->category->name), 2, false)));
		$rule->setHead($headLit);
	} else if ($head->property) {
		// create a property literal
		$object = null;
		if ($head->property->variable) {
			$object = new SMWVariable($head->property->variable);
			$boundVars[(string)$head->property->variable] = $object;
		} else if ($head->property->value) {
			$object = new SMWConstant(urldecode($head->property->value));
		}
		
		$subject = new SMWVariable($head->property->subject);
		$boundVars[(string)$head->property->subject] = $subject;
		$headLit = new SMWLiteral(new SMWPredicateSymbol(P_ATTRIBUTE, 2), 
		                          array($subject,
		                                new SMWTerm(array($smwgNamespace.'/property',
		                   		                          $head->property->name), 2, false),
		                         		$object));
		$rule->setHead($headLit);
	}

	$bodyLits = array();
	
	$body = $xml->body;
	// Process all categories
	foreach ($body->category as $cat) {
		$subject = new SMWVariable($cat->subject);
		$boundVars[(string)$cat->subject] = $subject;
		$bodyLit = new SMWLiteral(new SMWPredicateSymbol(P_ISA, 2), 
		                          array($subject,
		                                new SMWTerm(array($smwgNamespace.'/category', 
		                                                  $cat->name), 2, false)));
		$bodyLits[] = $bodyLit;
	}
	
	// Process all properties
	foreach ($body->property as $prop) {
		$subject = new SMWVariable($prop->subject);
		$boundVars[(string)$prop->subject] = $subject;
		
		$rel = new SMWTerm(array($smwgNamespace.'/property', 
		                         $prop->name), 2, false);
		                         		                         
		$object = null;
		if ($prop->variable) {
			$object = new SMWVariable($prop->variable);
			$boundVars[(string)$prop->variable] = $object;
		} else if ($prop->value) {
			$object = new SMWConstant(urldecode($prop->value));
		}
		$bodyLit = new SMWLiteral(new SMWPredicateSymbol(P_ATTRIBUTE, 2), 
		                          array($subject, $rel, $object));
		$bodyLits[] = $bodyLit;
	}
	$rule->setBody($bodyLits);
	
	
	$rule->setBoundVariables(array_values($boundVars));
	$flogic = $rule->getWikiFlogicString();
	
	return $flogic;
}

/**
 * Parse a rule and returns the XML representation of its literals.
 *
 * @param string $ruleName
 * 		The name of the rule
 * @param string $ruleText
 * 		The text of the rule e.g. its FLogic
 */
function smwf_sr_ParseRule($ruleName, $ruleText) {
	
	require_once 'SMW_FlogicParser.php';
	
	$fp = SMWFlogicParser::getInstance();
	
	$ruleObject = $fp->parseFloRule($ruleName, $ruleText);
	if ($ruleObject == null) {
		return 'false';
	}
	
	$headXML = smwhCreateRuleXML($ruleObject->getHead());
	$bodyXML = smwhCreateRuleXML($ruleObject->getBody());

	if ($headXML == false || $bodyXML == false) {
		return false;
	}
	
	// Create the XML structure of the rule
	$ruleXML =
		'<?xml version="1.0" encoding="UTF-8"?>' .
		'<SimpleRule>' .
		'	<head>' .
				$headXML.
		'	</head>' .
		'	<body>' .
				$bodyXML.
		'	</body>' .
		'</SimpleRule>';
		
	return $ruleXML;
}

/**
 * Parses a formula and returns its variables.
 *
 * @param string $formula
 * 		The formula to be parsed.
 *
 */
function smwf_sr_ParseFormula($formula) {
	require_once 'SMW_FormulaParser.php';
	
	$fp = new SMWFormulaParser($formula);
	if ($fp->isFormulaValid()) {
		return 'variables,'.implode(',', $fp->getVariables());
	} else {
		return 'error,'.$fp->getErrorMsg();
	}
}

/**
 * Generates the XML representation of the given literals.
 *
 * @param array<SMWLiteral> $literals
 * 		Category or property literals
 * @return string/bool
 * 		XML representation of literals or 
 * 		false, if the literals are not valid
 */
function smwhCreateRuleXML($literals) {
	$xml = '';
	
	if (!is_array($literals)) {
		$literals = array($literals);
	}
	
	foreach ($literals as $lit) {
		$ps = $lit->getPreditcatesymbol()->getPredicateName();
		$args = $lit->getArguments();
		if ($ps == P_ISA || $ps == P_DISA) {
			// Category
			if (count($args) != 2) {
				return false;
			}
			$xml .= '<category>';
			
			// first argument must be a variable
			$subject = $args[0]->getVariableName();
			$xml .= '<subject>'. $subject . '</subject>';
			
			// The object is a term
			$cat = $args[1]->getName();
			$xml .= '<name>'. $cat . '</name>';
			
			$xml .= '</category>';
		} else if ($ps == P_ATTRIBUTE || $ps == "attl_") {
			// Property
			if (count($args) < 3) {
				return false;
			}
			$xml .= '<property>';
			
			// first argument must be a variable
			$subject = $args[0]->getVariableName();
			$xml .= '<subject>'. $subject . '</subject>';
			
			// The property's name is a term
			$propName = $args[1]->getName();
			$xml .= '<name>'. $propName . '</name>';
			
			// The object can be a constant or a variable
			if (is_a($args[2], 'SMWConstant')) {
				$xml .= '<value>'. $args[2]->getValue() . '</value>';
			} else if (is_a($args[2], 'SMWVariable')) {
				$xml .= '<variable>'. $args[2]->getVariableName() . '</variable>';
			}
			$xml .= '</property>';
		}
		
	}
	
	return $xml;
}
?>