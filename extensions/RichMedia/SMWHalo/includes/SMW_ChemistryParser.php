<?php
/*  Copyright 2007, ontoprise GmbH
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
 * @ingroup SMWHaloDataValues
 * 
 * Parser for chemical equations and formulas.
 * 
 * @author Thomas Schweitzer
 */


// Some regular expressions
//define('FORMULA_RE', '/^(\d*?n?\s*)?((\d+)\^)?((n?\(*[A-Z][a-z]*\d*(\)\d*)*)+)(\^(\d*[+\-]))?(\(aq\)|\(g\)|\(l\)|\(s\))?$/');
define('FORMULA_RE', '/^(\d*?n?\s*)?((\d+)\^)?((n?\(*\.?[A-Z][a-z]*\d*(\.?|=|=-)?(\)\d*)*)+)(\^(\d*[+\-]))?(\(([^\(\)]*?[^\(\)\w]+)?aq(\W+.*?)?\)|\(([^\(\)]*?[^\(\)\w]+)?g(\W+.*?)?\)|\(([^\(\)]*?[^\(\)\w]+)?l(\W+.*?)?\)|\(([^\(\)]*?[^\(\)\w]+)?s(\W+.*?)?\))?$/');
define('ELEMENT_RE', '/^((?-i)A[cglmrstu]|B[aehikr]?|C[adeflmorsu]?|D[bsy]|E[rsu]|F[emf]?|G[ade]|H[efgos]?|I[nk]?|Kr?|L[airu]|M[dgnot]|N[abdeiop]?|Os?|P[abdmortu]?|R[abefghnu]|S[bcegimnr]?|T[abcehil]|U(u[bhopqst])?|V|W|Xe|Yb?|Z[nr])$/');

/**
 * This class has two public methods that can parse chemical equations and formulas.
 * The results can be retrieved as wiki text and HTML. Potential errors are 
 * available as well.
 */
class ChemEqParser{

//--- Fields ---

	/** Contains error messages if the parsed expression is not correct. */
	private $mError = "";
	
	/** 
	 * If the chemical expression is correct, this will contain the expression
	 * in wiki text format.
	 */
	private $mWikiFormat = "";

	/** 
	 * If the chemical expression is correct, this will contain the expression
	 * in HTML format.
	 */
	private $mHtmlFormat = "";

//--- Public methods ---
	
	/**
	 * Returns the error message. Can be called after 
	 * <checkEquation> or <checkFormula> have been invoked.
	 * @return String Localized error message
	 */
	public function getError() {
		return $this->mError;
	}
		
	/**
	 * Returns the chemical expression as wiki text. Can be called after 
	 * <checkEquation> or <checkFormula> have been invoked.
	 * @return String Wiki text for the chemical expression
	 */
	public function getWikiFormat() {
		return $this->mWikiFormat;
	}
		
	/**
	 * Returns the chemical expression as HTML. Can be called after 
	 * <checkEquation> or <checkFormula> have been invoked.
	 * @return String HTML for the chemical expression
	 */
	public function getHtmlFormat() {
		return $this->mHtmlFormat;
	}
		
	/**
	 * Checks a chemical equation for syntactical correctness.
	 * 
	 * Grammar for chemical equations:
	 * CE := CF [{'+'|'*'|'x'} CF]* [{'<-' | '<->' | '->' } CF [{'+'|'*'|'x'} CF]*]+
	 * CF is a chemical formula (see checkFormula())
	 *
	 * The fields <mError>, <mWikiFormat> and <mHtmlFormat> will be set. 
	 *  
	 * @param string $eq Wiki text of a chemical equation
	 * @return boolean <true> if the expression is a valid chemical equation,
	 *                 <false> otherwise
	 */
	public function checkEquation($eq) {
		$eq = trim(htmlspecialchars_decode($eq));
		$formulaParser = new ChemEqParser();
		
		try {
			$result = $this->splitEquation($eq);
	
			$wikiFormat = "";
			$htmlFormat = "";
			$delimCount = 0;
			$delimiters = array('<-', '<->', '->', '+', '*', 'x');
			
			foreach ($result as $term) {
				$formulas = $this->splitChemTerm($term);
				foreach ($formulas as $formula) {
					if (in_array($formula, $delimiters)) {
						$delimCount--;
					}		
					switch ($formula) {
						case "<-":
							$htmlFormat .= ' ← ';
							$wikiFormat .= $formula;
							break;
						case "->":
							$htmlFormat .= ' → ';
							$wikiFormat .= $formula;
							break;
						case "<->":
							$htmlFormat .= ' ⇌ ';
							$wikiFormat .= $formula;
							break;
						case "+":
							$htmlFormat .= ' + ';
							$wikiFormat .= $formula;
							break;
						case "*":
						case "x":
							$htmlFormat .= '●';
							$wikiFormat .= "*";
							break;
						default:
							if ($formulaParser->checkFormula($formula) === true) {
								$delimCount++;	
								$wikiFormat .= $formulaParser->getWikiFormat();
								$htmlFormat .= $formulaParser->getHtmlFormat();
							} else {
								throw new Exception($formulaParser->getError());
							}
					}
					if ($delimCount < 0 || $delimCount > 1) {
						throw new Exception(wfMsgForContent('smw_no_alternating_formula', $eq));
					}
				}
			}
			if ($delimCount != 1) {
				// In the end, there must be one more formula than operators
				throw new Exception(wfMsgForContent('smw_no_alternating_formula', $eq));
			}
			$this->mWikiFormat = $wikiFormat;
			$this->mHtmlFormat = $htmlFormat;
			$this->mError = "";
			return true;
			
		} catch (Exception $e) {
			$this->mWikiFormat = "";
			$this->mHtmlFormat = "";
			$this->mError = $e->getMessage();
			return false;
		}		
	}

	/**
	 * Checks a chemical formula for syntactical correctness.
	 * 
	 * Grammar for chemical formulas:
	 * (Elements in brackets [] are optional)
	 * Chemical formula: CF := N [' '] M[SOM]
	 * Number of molecules/atoms/mols: N := number ['n']
	 * Molecule: M := ED | '('M')'number | MM | M MC M | M ION | ["."] M ["."]]
	 * Molecule connection: MC := '=' | '=-'
	 * Element descriptor: ED := [I]E[number] | [I]E[number][ION]
	 * Isotope: I := number '^'
	 * Ion: ION := '^'[number]{'+'|'-'}
	 * Element: E := Symbol of all chemical elements
	 * State of matter: SOM := '(' [SOMMOD NOTALPHANUM] 'g' [NOTALPHANUM SOMMOD] ')' | 
	 *                         '(' [SOMMOD NOTALPHANUM] 'l' [NOTALPHANUM SOMMOD] ')' | 
	 *                         '(' [SOMMOD NOTALPHANUM] 's' [NOTALPHANUM SOMMOD] ')' | 
	 *                         '(' [SOMMOD NOTALPHANUM] 'aq' [NOTALPHANUM SOMMOD] ')'
	 * State of matter modifier:
	 * 					SOMMOD := any string but ( and )
	 * 					NOTALPHANUM := anything but letters nor digits
	 * 
	 * The fields <mError>, <mWikiFormat> and <mHtmlFormat> will be set. 
	 *  
	 * @param string $eq Wiki text of a chemical formula
	 * @return boolean <true> if the expression is a valid chemical formula,
	 *                 <false> otherwise
	 */
	public function checkFormula($formula) {
		$wikiFormat = "";
		$htmlFormat = "";
		$moleculeSF = "";
		$moleculeHtml = "";
		$numElements = 0;
		
		$formula = trim($formula);
		
		try {
			$molecules = array ();
			if (preg_match(FORMULA_RE, $formula, $molecules) != 1) {
				throw new Exception(wfMsgForContent('smw_chem_syntax_error', $formula));
			}
	
			if (isset($molecules[4]) && $molecules[4] != '') {
				list($moleculeSF,$moleculeHtml, $numElements) = $this->checkMolecule($molecules[4]);
			} else {
				throw new Exception(wfMsgForContent('smw_no_molecule', $formula));
			}
			if (isset($molecules[1]) && $molecules[1] != '') {
				// Number of molecules
				$wikiFormat .= $molecules[1]." ";
				$htmlFormat .= $molecules[1]." ";
			}
			if (isset($molecules[3]) && $molecules[3] != '') {
				// Isotope
				if ($numElements > 1) {
					// Only one element allowed for Isotopes
					throw new Exception(wfMsgForContent('smw_too_many_elems_for_isotope', $molecules[4]));
				}
				$wikiFormat .= $molecules[3]."^";
				$htmlFormat .= "<sup>".$molecules[3]."</sup>";
			}
			if (isset($molecules[4]) && $molecules[4] != '') {
				// Molecule
				$wikiFormat .= $moleculeSF;
				$htmlFormat .= $moleculeHtml;
			}
			if (isset($molecules[9]) && $molecules[9] != '') {
				// Ion
				$wikiFormat .= "^".$molecules[9];
				$htmlFormat .= "<sup>".$molecules[9]."</sup>";
			}
			if (isset($molecules[10]) && $molecules[10] != '') {
				// State
				$wikiFormat .= $molecules[10];
				$htmlFormat .= "<sub>".$molecules[10]."</sub>";
			}
		
			$this->mWikiFormat = $wikiFormat;
			$this->mHtmlFormat = $htmlFormat;
			$this->mError = "";
			return true;

		} catch (Exception $e) {
			$this->mWikiFormat = "";
			$this->mHtmlFormat = "";
			$this->mError = $e->getMessage();
			return false;
		}		
	}
	
//--- Private methods ---

	/**
	 * Splits a chemical equation at the arrow (one of ->, <->, <-) into reactants
	 * and products.
	 * 
	 * @param string $eq Wiki text for a chemical equation
	 * 
	 * @return array(string) Reactants, arrow, products
	 * 
	 * Throws exception if number of equation parts is not 3.
	 */
	private function splitEquation($eq) {
		$result = array ();
		$result = preg_split('/(<->|<-|->)/', $eq, -1, PREG_SPLIT_DELIM_CAPTURE);
		$num = count($result);
		if ($num < 3) {
			throw new Exception(wfMsgForContent('smw_no_chemical_equation', $eq));
		}
	
		for ($i = 0; $i < $num; $i++) {
			$result[$i] = trim($result[$i]);
		}
		return $result;
	}

	/**
	 * Splits a chemical term into chemical formulas and operators.
	 * 
	 * A chemical term is a concatenation of chemical formulas with the operators
	 * '+', '*', 'x'.
	 * 
	 * @param string $term A chemical term
	 * @return array(string) Array of chemical formulas and operators.
	 */
	private function splitChemTerm($term) {
		//temporarily replace positive ionic charges, so that the term can be 
		// split at "+"-characters
		$term = preg_replace('/\^(\d*)\+/', 'ioncharge($1)', $term);
		$formulas = preg_split('/([\+\*x])/', $term, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		foreach ($formulas as $idx => $formula) {
			$f = trim($formula);
			// put back ionic charges
			$formulas[$idx] = preg_replace('/ioncharge\((\d*)\)/', '^$1+', $f);
		}
		return $formulas;
	}

	/**
	 * Checks a molecule for correctness. It may contain molecule groups like
	 * Mg(NO3)2.
	 * 
	 * @param string $molecule Wiki text for a molecule.
	 * 
	 * @return array(string, string, int) 
	 *          [0] => Optimized wiki text
	 * 			[1] => Html for the molecule
	 * 			[2] => Number of elements in the molecule
	 * 
	 * Throws exception if the number and position of brackets is not correct.
	 * 
	 */
	private function checkMolecule($molecule) {
		
		$wikiFormat = "";
		$html = "";
		$numElements = 0;
		$brackets = preg_split('/(\(.*\))/', $molecule, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($brackets as $bracket) {
			if (strpos($bracket, "(") !== false) {
				if (substr_count($bracket,'(') != substr_count($bracket,')')) {
					// number of opening brackets does not match that of closing ones
					throw new Exception(wfMsgForContent('smw_chem_unmatched_brackets', $bracket));
				}
				$bracket = substr($bracket,1,strlen($bracket)-2);
				list($s,$h, $ne) = $this->checkMolecule($bracket);
				$wikiFormat .= "(".$s.")";
				$html .= "(".$h.")";
				$numElements += $ne;
			} else {
				list($s,$h,$ne) = $this->checkSimpleMolecule($bracket);
				$wikiFormat .= $s;
				$html .= $h;
				$numElements += $ne;
			}
		}
		
		return array($wikiFormat, $html, $numElements);
		 
	}
	
	
	/**
	 * Checks a simple molecule for correctness. It must not contain molecule 
	 * groups like Mg(NO3)2. It can contain connections like "=" and "=-"
	 * 
	 * @param string $molecule Wiki text for a molecule.
	 * 
	 * @return array(string, string, int) 
	 *          [0] => Optimized wiki text
	 * 			[1] => Html for the molecule
	 * 			[2] => Number of elements in the molecule
	 * 
	 * Throws exception if a chemical element is not correct.
	 * 
	 */
	private function checkSimpleMolecule($molecule) {
		
		$wikiFormat = "";
		$html = "";
		$numElements = 0;
		
		$atoms = preg_split('/([A-Z][a-z]{0,2}|=-|=|.)/', $molecule, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		foreach ($atoms as $atom) {
			if (preg_match(ELEMENT_RE,$atom) == 1) {
				// Chemical elements are allowed
				$wikiFormat .= $atom;
				$html .= $atom;
				$numElements++;
			} else if (preg_match("/^\d+$/",$atom) == 1) {
				// numerical indices are allowed
				$wikiFormat .= $atom;
				$html .= "<sub>".$atom."</sub>";
			} else if ($atom == "=") {
				// connections are allowed
				$wikiFormat .= $atom;
				$html .= "=";
			} else if ($atom == "=-") {
				$wikiFormat .= $atom;
				$html .= "≡";
			} else if ($atom == ".") {
				$wikiFormat .= $atom;
				$html .= "•";
			} else {
				throw new Exception(wfMsgForContent('smw_not_a_chem_element', $atom));
			}
		}
		
		return array($wikiFormat, $html, $numElements);
		 
	}
}


