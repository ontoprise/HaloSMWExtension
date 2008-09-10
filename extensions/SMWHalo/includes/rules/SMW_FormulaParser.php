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
 * A parser for simple formulas.
 *
 *
 * @author Thomas Schweitzer
 */


if (!defined('MEDIAWIKI')) die();

define('SR_FP_NO_ERROR', 0);
define('SR_FP_MISSING_OPERATOR', 1);
define('SR_FP_EXPECTED_FACTOR', 2);
define('SR_FP_EXPECTED_OB', 3);
define('SR_FP_EXPECTED_CB', 4);
define('SR_FP_EXPECTED_PARAMETER', 5);
define('SR_FP_EXPECTED_COMMA', 6);

/**
 * Class for parsing a simple formula.
 *
 */
class SMWFormulaParser {

	private $mTokens = array(	
		"(",
		")",
		"+",
		"-",
		"*",
		"/",
		"%",
		"^",
		",",
		"sin",
		"cos",
		"tan",
		"asin",
		"acos",
		"ceil",
		"floor",
		"exp",
		"rint",
		"sqrt",
		"round",
		"max",
		"min",
		"pow"
	);
	
	// Supported functions together with their arity
	private $mFunctions = array('sin', 1, 'cos', 1, 'tan', 1, 'asin', 1, 
	                            'acos', 1, 'ceil', 1, 'floor', 1, 'rint', 1,
	                            'sqrt', 1, 'round', 1, 
	                            'exp', 2, 'max', 2, 'min', 2, 'pow', 2);
	
	private $mErrorMessages = array(
		SR_FP_MISSING_OPERATOR => 'smw_srf_missing_operator',
		SR_FP_EXPECTED_FACTOR => 'smw_srf_expected_factor',
		SR_FP_EXPECTED_OB => 'smw_srf_expected_(',
		SR_FP_EXPECTED_CB => 'smw_srf_expected_)',
		SR_FP_EXPECTED_PARAMETER => 'smw_srf_expected_parameter',
		SR_FP_EXPECTED_COMMA => 'smw_srf_expected_comma'
		);
		
	private $mFormula;	// string: The formula that is parsed
	
	private $mFormulaValid = false;
	
	private $mVariables = array();
	
	private $mErrorMsg = "";
	private $mErrorCode = SR_FP_NO_ERROR;
	
	// The UPN stack consists of a list of typenames and corresponding elements
	// for a parsed formula
	// var, <name of variable>
	// const, <a constant>
	// op, one of + - * /
	// func<arity>, <one of the functions defined above>
	// e.g. a + 3 * b will be represented as: 
	// var,a,const,3,var,b,op,*,op,+
	private $mUPNStack = array();
	
	/*** Getters / Setters ***/ 
	
	public function isFormulaValid() {
		return $this->mFormulaValid;
	}
	
	public function getVariables() {
		return array_keys($this->mVariables);
	}
	
	public function getErrorMsg() {
		return $this->mErrorMsg;
	}
	
	public function getUPNStack() {
		return $this->mUPNStack;
	}
	
	/**
	 * Constructor
	 *
	 * Parses the given formula.
	 *
	 * @param string $formula
	 * 		The formula that will be parsed.
	 */
	function __construct($formula) {
		$this->mFormula = $formula;
		$this->parse($formula);
	}

	/**
	 * Parses the given formula. It is first split into to token that are then
	 * processed according to the EBNF.
	 * 
	 * group    ::= '(' expr ')'
	 * variable ::= [A-z]([A-z0-9])*
	 * factor   ::= number | variable | group | function
	 * function ::= ((sin | cos | tan | asin | acos |ceil | floor | exp | 
	 *                rint | sqrt | round) '(' expr ')') 
	 *              | ((max | min | pow) '(' expr ',' expr ')') 
     * term     ::= factor (('*' factor) | ('/' factor) | ('^' factor) | ('%' factor))*
     * expr     ::= term (('+' term) | ('-' term))*
	 *
	 * @param string $formula
	 * 		The formula that will be parsed.
	 *
	 */
	private function parse($formula) {
		$split = "/";
		foreach ($this->mTokens as $t) {
			$t = ($t == '/') ? '\/' : preg_quote($t);
			$split .= '('.$t.')|';
		}
		$split .= '\s+/';
		$tokens = preg_split($split, $formula, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

		list($success, $nextToken) = $this->parseExpr($tokens, 0);
		if ($success == true && ($nextToken != count($tokens))) {
			$this->setError(SR_FP_MISSING_OPERATOR, $tokens, $nextToken-1);
		}
		$this->mFormulaValid = ($success && ($nextToken == count($tokens)));
	}
	
	/**
	 * Parses an expression according to the EBNF:
     * expr ::= term (('+' term) | ('-' term))*
	 *  
	 *
	 * @param array<string> $tokens
	 * 		List of tokens
	 * @param int $startToken
	 * 		Index of the first token to consider
	 * @return array(bool, int)
	 * 		(<true>, nextToken) if the expression was parsed successfully
	 * 		(<false>, failToken) , otherwise
	 */
	private function parseExpr($tokens, $startToken) {
		
		list($success, $nextToken) = $this->parseTerm($tokens, $startToken);
		if (!$success) {
			return array(false, $nextToken);
		}
		
		while ($nextToken < count($tokens)) {
			$currToken = $tokens[$nextToken++];
			if ($currToken == '+' || $currToken == '-') {
				list($success, $nextToken) = $this->parseTerm($tokens, $nextToken);
				if (!$success) {
					return array(false, $nextToken);
				}
				$this->mUPNStack[] = 'op';
				$this->mUPNStack[] = $currToken;
			} else {
				//no further terms
				return array(true, $nextToken-1);
			}
		}
		// all tokens processed
		return array(true, $nextToken);
	}

	/**
	 * Parses a term according to the EBNF:
     * term ::= factor (('*' factor) | ('/' factor) | ('^' factor))*
	 *  
	 * @param array<string> $tokens
	 * 		List of tokens
	 * @param int $startToken
	 * 		Index of the first token to consider
	 * @return array(bool, int)
	 * 		(<true>, nextToken) if the term was parsed successfully
	 * 		(<false>, failToken) , otherwise
	 */
	private function parseTerm($tokens, $startToken) {
		
		list($success, $nextToken) = $this->parseFactor($tokens, $startToken);
		if (!$success) {
			return array(false, $nextToken);
		}
		
		while ($nextToken < count($tokens)) {
			$currToken = $tokens[$nextToken++];
			if ($currToken == '*' || $currToken == '/' 
			    || $currToken == '^' || $currToken == '%') {
				list($success, $nextToken) = $this->parseFactor($tokens, $nextToken);
				if (!$success) {
					return array(false, $nextToken);
				}
				$this->mUPNStack[] = 'op';
				$this->mUPNStack[] = $currToken;
			} else {
				//no further factors
				return array(true, $nextToken-1);
			}
		}
		// all tokens processed
		return array(true, $nextToken);
	}
	
	/**
	 * Parses a factor according to the EBNF:
     * factor ::= number | variable | group | function
	 *  
	 * @param array<string> $tokens
	 * 		List of tokens
	 * @param int $startToken
	 * 		Index of the first token to consider
	 * @return array(bool, int)
	 * 		(<true>, nextToken) if the factor was parsed successfully
	 * 		(<false>, failToken) , otherwise
	 */
	private function parseFactor($tokens, $startToken) {
		
		list($success, $nextToken) = $this->parseFunction($tokens, $startToken);
		if (!$success) {
			list($success, $nextToken) = $this->parseVariable($tokens, $startToken);
		}
		if (!$success) {
			list($success, $nextToken) = $this->parseNumber($tokens, $startToken);
		}
		if (!$success) {
			list($success, $nextToken) = $this->parseGroup($tokens, $startToken);
		}
		if (!$success) {
			if ($this->mErrorCode != SR_FP_EXPECTED_CB) {
				$this->setError(SR_FP_EXPECTED_FACTOR, $tokens, $nextToken);
			}
			return array(false, $nextToken);
		}
		
		return array(true, $nextToken);
	}

	/**
	 * Parses a function according to the EBNF:
	 * function ::= ((sin | cos | tan | asin | acos |ceil | floor | exp | 
	 *                rint | sqrt | round) '(' expr ')') 
	 *              | ((max | min | pow) '(' expr ',' expr ')') 
     *   
	 * @param array<string> $tokens
	 * 		List of tokens
	 * @param int $startToken
	 * 		Index of the first token to consider
	 * @return array(bool, int)
	 * 		(<true>, nextToken) if the function was parsed successfully
	 * 		(<false>, failToken) , otherwise
	 */
	private function parseFunction($tokens, $startToken) {

		$currToken = $tokens[$startToken++];

		// find a function and its arity
		$arity = 0;
		for ($i = 0, $n = count($this->mFunctions); $i < $n; $i += 2) {
			$func = $this->mFunctions[$i];
			if ($currToken == $func) {
				$arity = $this->mFunctions[$i+1];
				break;
			}
		}

		if ($arity == 0) {
			// no function found
			return array(false, $startToken-1);
		}

		// opening brace expected
		$nextToken = $startToken;
		$currToken = $tokens[$nextToken++];
		if ($currToken != '(') {
			$this->setError(SR_FP_EXPECTED_OB, $tokens, $nextToken);
			return array(false, $nextToken-1);
		}
		for ($p = 0; $p < $arity; ++$p) {
			// parse first parameter
			list($success, $nextToken) = $this->parseExpr($tokens, $nextToken);
			if (!$success) {
				$this->setError(SR_FP_EXPECTED_PARAMETER, $tokens, $nextToken-1);
				return array(false, $nextToken);
			} 
			
			if ($p < $arity - 1) {
				// comma expected
				$currToken = $tokens[$nextToken++];
				if ($currToken != ',') {
					$this->setError(SR_FP_EXPECTED_COMMA, $tokens, $nextToken-1);
					return array(false, $nextToken-1);
				}
			}
		}
		$currToken = $tokens[$nextToken++];
		if ($currToken != ')') {
			$this->setError(SR_FP_EXPECTED_CB, $tokens, $nextToken-1);
			return array(false, $nextToken-1);
		}
		
		$this->mUPNStack[] = 'func'.$arity;
		$this->mUPNStack[] = $func;
		
		return array(true, $nextToken);
	}

	/**
	 * Parses a variable according to the regular expression:
     * variable = [a-zA-Z_](\w)*
	 *  
	 * @param array<string> $tokens
	 * 		List of tokens
	 * @param int $startToken
	 * 		Index of the first token to consider
	 * @return array(bool, int)
	 * 		(<true>, nextToken) if the variable was parsed successfully
	 * 		(<false>, failToken) , otherwise
	 */
	private function parseVariable($tokens, $startToken) {
		$currToken = $tokens[$startToken++];
		if (preg_match("/^[a-zA-Z_](\w)*$/", $currToken, $variable) == 1) {
			$this->mVariables[$variable[0]] = true;
			$this->mUPNStack[] = 'var';
			$this->mUPNStack[] = $variable[0];
			return array(true, $startToken);
		} else {
			return array(false, $startToken-1);
		}
	}

	/**
	 * Parses a number according to the regular expression:
     * number = ^[-+]?\d*(\.?\d+([eE][-+]?\d+)?)?$
	 *  
	 * @param array<string> $tokens
	 * 		List of tokens
	 * @param int $startToken
	 * 		Index of the first token to consider
	 * @return array(bool, int)
	 * 		(<true>, nextToken) if the number was parsed successfully
	 * 		(<false>, failToken) , otherwise
	 */
	private function parseNumber($tokens, $startToken) {
		$nextToken = $startToken;
		$currToken = $tokens[$nextToken++];
		$number = '';
		// optional sign
		if ($currToken == '-' || $currToken == '+') {
			$number = $currToken;
			$currToken = $tokens[$nextToken++];
		}
		if (preg_match("/^\d*\.?\d+([eE]\d*)?$/", $currToken, $const) == 1) {
			$number .= $const[0];
		} else {
			return array(false, $nextToken-1);
		}
		// does the number end with an exponent?
		$last = $number{strlen($number)-1};
		if ($last == 'e' || $last == 'E') {
			// optional sign
			$currToken = $tokens[$nextToken++];
			if ($currToken == '-' || $currToken == '+') {
				$number .= $currToken;
				$currToken = $tokens[$nextToken++];
			}
			// mandatory number
			if (preg_match("/^\d+/", $currToken, $const) == 1) {
				$number .= $const[0];
			} else {
				return array(false, $nextToken-1);
			}
			
		}
					
		$this->mUPNStack[] = 'const';
		$this->mUPNStack[] = $number;
		return array(true, $nextToken);
	}

	/**
	 * Parses a group according to the EBNF:
     * group ::= '(' expr ')'
	 *  
	 * @param array<string> $tokens
	 * 		List of tokens
	 * @param int $startToken
	 * 		Index of the first token to consider
	 * @return array(bool, int)
	 * 		(<true>, nextToken) if the group was parsed successfully
	 * 		(<false>, failToken) , otherwise
	 */
	private function parseGroup($tokens, $startToken) {

		$currToken = $tokens[$startToken++];
		if ($currToken != '(') {
			$this->setError(SR_FP_EXPECTED_OB, $tokens, $startToken-1);
			return array(false, $startToken-1);
		}
		
		list($success, $nextToken) = $this->parseExpr($tokens, $startToken);
		if (!$success) {
			return array(false, $nextToken);
		}
		
		if ($nextToken >= count($tokens)) {
			$this->setError(SR_FP_EXPECTED_CB, $tokens, $nextToken);
			return array(false, $nextToken+1);
		}
		
		$currToken = $tokens[$nextToken++];
		if ($currToken != ')') {
			$this->setError(SR_FP_EXPECTED_CB, $tokens, $nextToken-1);
			return array(false, $nextToken);
		}
		
		return array(true, $nextToken);
	}
	
	/**
	 * Assembles an error message with a given ID. The tokens that have been
	 * successfully parsed are concatenated and passed as parameter for wfMsg().
	 *
	 * @param int $errorID
	 * @param array<string> $tokens
	 * @param int $currToken
	 */
	private function setError($errorID, $tokens, $currToken) {
		
		$msgID = $this->mErrorMessages[$errorID];
		$this->mErrorCode = $errorID;
		$formula = '';
		$n = $currToken;
		if (count($tokens) < $n) {
			$n = count($tokens);
		}
		for ($i = 0; $i < $n; ++$i) {
			$formula .= $tokens[$i];
		}
		$this->mErrorMsg = wfMsg($msgID, $formula);
		
	}
	
}
?>