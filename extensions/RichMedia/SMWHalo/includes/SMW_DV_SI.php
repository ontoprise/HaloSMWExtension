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
 * Created on 19.04.2007
 *
 * This class handles parsing of SI units of measurement.
 * 
 */
 
// Constants for the access to the result array of method <parseSIValueAndUnit>
define("SMW_DT_SI_M",     'm');
define("SMW_DT_SI_KG",    'kg');
define("SMW_DT_SI_S",     's');
define("SMW_DT_SI_A",     'A');
define("SMW_DT_SI_K",     'K');
define("SMW_DT_SI_MOL",   'mol');
define("SMW_DT_SI_CD",    'cd');
define("SMW_DT_SI_ERR",   'error');
define("SMW_DT_SI_VALUE", 'value');

class SMWSIUnitTypeHandler extends SMWDataValue {

//--- Fields ---
	private $m_xsdValue = ''; // representation of the value in the database
	private $m_wikitext = ''; // representation of the value as wiki text
	private $m_html     = ''; // representation of the value as HTML


//--- public methods ---
	/**
	 * Constructor
	 */
	public function SMWSIUnitTypeHandler($typeid) {
		SMWDataValue::__construct($typeid);
		wfLoadExtensionMessages('SemanticMediaWiki');
	}

    /**
	 * This method transforms the user-provided value of an
	 * attribute into several output strings (one for XML,
	 * one for printout, etc.) and reports parsing errors if
	 * the value is not valid for the given data type.
	 *
	 * @access public
	 */
	public function parseUserValue($value) {
		if ($value=='') { //do not accept empty strings
			$this->addError(wfMsgForContent('smw_emptystring'));
			return true;
		}
		
		$this->m_xsdValue = smwfXMLContentEncode($value);
		// 255 below matches smw_attributes.value_xsd definition in smwfMakeSemanticTables()
		// Note that depending on database encoding and UTF-8 settings, longer or
		// shorter strings than this with int'l characters may exceed database field.
		if (strlen($this->m_xsdValue) > 255) {
			$this->addError(wfMsgForContent('smw_maxstring', $this->m_xsdValue));
		} else {
			$res = $this->parseSIValueAndUnit($value);
			if ($res[SMW_DT_SI_ERR] != '') {
				// an error was detected
				$this->addError($value.' ('.$res[SMW_DT_SI_ERR].')');
			} else {
				$this->m_html = $this->createSIUnit($res,true);
				if ($this->m_caption === false) {
					$this->m_caption = $this->m_html;
				}
				$this->m_wikitext = $value;
			}
		}

		return true;
	}

    protected function parseDBkeys($args) {
        $this->parseUserValue($args[0]);
        
    }
    
    public function getDBkeys() {
        $this->unstub();
        return array($this->m_xsdValue);
    }
    
	protected function parseXSDValue($value, $unit) {
		$this->setUserValue($value);
	}

	public function setOutputFormat($formatstring){
		//TODO
	}

	public function getShortWikiText($linked = NULL) {
		if ($this->m_caption !== false) {
			return $this->m_caption;
		}
		return $this->m_html;
	}

	public function getShortHTMLText($linker = NULL) {
		return $this->m_html;
	}

	public function getLongWikiText($linked = NULL) {
		if (!$this->isValid()){
			return $this->getErrorText();
		} else {
			return $this->m_html;
		}
	}

	public function getLongHTMLText($linker = NULL) {
		if (!$this->isValid()){
			return $this->getErrorText();
		} else {
			return '<span class="external free">' .$this->m_html . '</span>'; /// TODO support linking
		}
	}

	public function getXSDValue() {
		return $this->m_xsdValue;
	}

	public function getWikiValue(){
		return $this->m_wikitext;
	}
	
	public function getNumericValue() {
		return NULL;
	}

	public function getUnit() {
		return $this->m_html;
	}

	public function getInfolinks() {
		return $this->m_infolinks;
	}

	public function getHash() {
		return $this->getShortWikiText(false);
	}

	function isNumeric() {
		return true;
	}

	/**
	 * Parses a value and a unit.
	 * 
	 * The values are processed by 'parseValue' of the super class.
	 * The unit consists of the product/division of the seven standard SI units
	 * with an optional exponent. 
	 * 
	 * Grammar:
	 * SI-Unit: 'm', 'kg', 's', 'A', 'K', 'mol', 'cd'
	 * exp = SI-Unit {'^' integer}
	 * integer = {'+' | '-'} digit+
	 * product = exp | exp'*'exp | exp' 'exp 
	 * unit-description = product { '/' product} | 1 '/' product
	 * 
	 * @param string $valueAndUnit The string represents a float value with an 
	 * SI unit.
	 * @return array (SMW_DT_SI_VALUE => float value, 
	 *                SMW_DT_SI_ERR => error description or empty
	 *                SMW_DT_SI_M   => Exponent for meters
	 *                SMW_DT_SI_KG  => Exponent for kilograms
	 *				  SMW_DT_SI_S   => Exponent for seconds
	 *				  SMW_DT_SI_A   => Exponent for Ampères
	 *				  SMW_DT_SI_K   => Exponent for Kelvin
	 *				  SMW_DT_SI_MOL => Exponent for Mole
	 *				  SMW_DT_SI_CD  => Exponent for Candela)
	 */
	public function parseSIValueAndUnit($valueAndUnit) {
		$errStr = "";
		$result = array(
			SMW_DT_SI_M   => 0,
			SMW_DT_SI_KG  => 0,
			SMW_DT_SI_S   => 0,
			SMW_DT_SI_A   => 0,
			SMW_DT_SI_K   => 0,
			SMW_DT_SI_MOL => 0,
			SMW_DT_SI_CD  => 0,
			SMW_DT_SI_ERR => ""  
		);
		
		$res = $this->parseValue($valueAndUnit);
		
		if ($res[1] == null) {
			// No value given
			$result[SMW_DT_SI_ERR] = $res[3];
			return $result;
		}
		
		if (empty($res[2])) {
			// Value has no unit.
			$result[SMW_DT_SI_ERR] = wfMsgForContent('smw_no_si_unit');
			return $result;
		}

		$result[SMW_DT_SI_VALUE] = $res[1];
		$unit = $res[2];
		
		// Split the unit in enumerator and denominator		
		$unitParts = explode("/", $res[2]);
		
		if (count($unitParts) > 2) {
			$result[SMW_DT_SI_ERR] = wfMsgForContent('smw_too_many_slashes');
			return $result;
		}		
		
		//Extract all factors of the enumerator and denominator
		$i = 0;
		foreach ($unitParts as $part) {
			// unit may contain several '*'s
			if (strpos($part, '**') !== false) {
				$errStr = wfMsgForContent('smw_too_many_asterisks', $part);
				break;
			}
			
			//The enumerator may be "1"
			if ($part == "1") {
				if ($i == 1) {
					// The denominator must not be 1
					$errStr = wfMsgForContent('smw_denominator_is_1');
				}
			} else {
				$singleUnit = strtok($part, " *");
				
				while ($singleUnit !== false) {
					
					$singleUnit = trim($singleUnit);
					list($id, $exp, $err) = $this->parseSingleSIUnit($singleUnit);
					
					if ($err != "") {
						// An error occurred => assemble an error string
						$errStr .= $err;
					} else {
						// parsing of single unit was successful => collect exponents
						if ($id != "") {
							$result[$id] += $exp * (($i == 0) ? 1 : -1);
						}
					}
				    $singleUnit = strtok(" *");
				}
			}			
			$i = 1;
		}
		
		if ($errStr == '') {
			
			// Check, if at least one exponent of a unit is different from 0
			$unitPresent = false;
			foreach ($result as $unit => $exp) {
				if ($unit != SMW_DT_SI_ERR && $unit != SMW_DT_SI_VALUE) {
					if ($exp != 0) {
						$unitPresent = true;
						break;
					}
				}
			}
			
			if (!$unitPresent) {
				$errStr = wfMsgForContent('smw_no_si_unit_remains');
			}
		}
		
		$result[SMW_DT_SI_ERR] = $errStr;
		return $result;
	}
	
	/**
	 * Creates an optimized string representation of a value and an SI unit:
	 * - eliminates exponents that are 0
	 * - orders enumerator and denominator correctly e.g. m^-1/s^-1 => s/m
	 * - can create a HTML-representation of the exponents
	 * 
	 * @param array $siArray Array as returned by method parseSIValueAndUnit.
	 * @param boolean $asHTML 
	 *        If true, exponents are represented as <sup>number</sup>.
	 *        Otherwise ^number is generated.
	 * 
	 * @return A string that contains a value and a unit of measurement based on
	 *         SI units.                
	 *
	 */
	public function createSIUnit($siArray, $asHTML) {
		
		$enum = null;
		$denom = null;
		foreach ($siArray as $unit => $exp) {
			if ($unit == SMW_DT_SI_ERR || $unit == SMW_DT_SI_VALUE) {
				continue;
			}
			$exp = $exp * 1;
			if ($exp > 0) {
				if (!empty($enum)) {
					$enum .= '*';
				}
				$enum .= $unit;
				if ($exp > 1) {
					if ($asHTML) {
						$enum .= '<sup>'.$exp.'</sup>';	
					} else {
						$enum .= "^".$exp;
					}
				}
			} else if ($exp < 0) {
				if (!empty($denom)) {
					$denom .= '*';
				}
				$exp *= -1;
				$denom .= $unit;
				if ($exp > 1) {
					if ($asHTML) {
						$denom .= '<sup>'.$exp.'</sup>';	
					} else {
						$denom .= "^".$exp;
					}
				}
			}
		}
		
		$unit = null;
		if (!isset($enum) && !isset($denom)) {
			$unit = "";
		} else if (!isset($enum) && isset($denom)) {
			$unit = "1 / ".$denom;
		} else if (isset($enum) && !isset($denom)) {
			$unit = $enum;
		} else {
			$unit = $enum.' / '.$denom;
		}
		return $siArray[SMW_DT_SI_VALUE].' '.$unit;
	}
	
//--- private methods ---

	/**@
	 * Parse a floating point value, possibly including prefix and 
	 * unit.
	 * @param value string
	 * @return array of prefix, float, postfix ("unit"), error string
	 */
	function parseValue($v) {
		$preNum = '';
		$num = null;  // This indicates error.
		$unit = '';

		$decseparator = wfMsgForContent('smw_decseparator');
		$kiloseparator = wfMsgForContent('smw_kiloseparator');

		// First, split off number from the rest.
		// Number is, e.g. -12,347,421.55e6
		// Note the separators might be a magic regexp value like '.', so have to escape them with backslash.
		// This rejects .1 , it needs a leading 0.
		// This rejects - 3, there can't be spaces in the number.
		$arr = preg_split('/([-+]?\d+(?:\\' . $kiloseparator . '\d+)*\\' . $decseparator . '?[\d]*(?:\s*[eE][-+]?\d+)?)[ ]*/', trim($v), 2, PREG_SPLIT_DELIM_CAPTURE);

		$arrSiz = count($arr);
		if ($arrSiz >= 1) $preNum = $arr[0];
		if ($arrSiz >= 2) $num = $arr[1];
		if ($arrSiz >= 3) $unit = $arr[2];

		if ($num !== null) {
			// sscanf doesn't like commas or other than '.' for decimal point.
			$num = str_replace($kiloseparator, '', $num);
			if ($decseparator != '.') {
				$num = str_replace($decseparator, '.', $num);
			}
			// sscanf doesn't like space between number and exponent.
			// TODO: couldn't we just delete all ' '? -- mak
			$num = preg_replace('/\s*([eE][-+]?\d+)/', '$1', $num, 1);
			
			$extra = ''; // required, a failed sscanf leaves it untouched.
			// Run sscanf to convert the number string to an actual float.
			// This also strips any leading + (relevant for LIKE search).
			list($num, $extra) = sscanf($num, "%f%s");
			
			// junk after the number after parsing indicates syntax error
			// TODO: can this happen? Isn't all junk thrown into $unit anyway? -- mak
			if ($extra != '') {
				$num = null;	// back to error state
			}

			// Clean up leading space from unit, which should be common
			$unit = preg_replace('/^(?:&nbsp;|&thinsp;|\s)+/','', $unit);
			
			if (is_infinite($num)) {
				return array($preNum, $num, $unit, wfMsgForContent('smw_infinite', $v));
			}
			return array($preNum, $num, $unit, '');
		} else {
			return array('', null, '', wfMsgForContent('smw_nofloat', $v));
		}
	}
	
	/**
	 * Parses a single unit with an optional exponent.
	 *
	 * @param string $siUnit One unit with optional exponent. 
	 * 
	 * @return array(string Unit if valid otherwise empty,
	 *               int Exponent if present and valid otherwise 0,
	 *               string Error if format is not valid)
	 */
	private function parseSingleSIUnit($siUnit) {
		
		$siUnit = trim($siUnit);
		preg_match("/^(mol|kg|s|A|K|m|cd)(\^([\+\-]?\d+))?$/", 
		           $siUnit, $matches);
		
		$unit = "";
		$exp = 0;
		$errStr = "";
		
		if (!empty($matches)) {
			if (!empty($matches[1])) {
				$unit = $matches[1];
				$exp = 1;
			}
			if (!empty($matches[3]) && $matches[3] != "") {
				$exp = $matches[3]*1;
			}
		} else {
			//Invalid format
			$errStr = wfMsgForContent('smw_invalid_format_of_si_unit', $siUnit);
		}
		
		return array($unit, $exp, $errStr);
	}
	
} // End class SMWSIUnitTypeHandler
