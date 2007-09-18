<?php

/**
 * Typehandler class for mathematical equations.
 *
 * @author Thomas Schweitzer
 */


/**
 * Class for managing mathematical equations.
 */
class SMWMathematicalEquationTypeHandler implements SMWTypeHandler {

	function getID() {
		return 'mathematicalequation';
	}

	function getXSDType() {
		return 'http://www.w3.org/2001/XMLSchema#string';
	}

	function getUnits() { //no units for equations
		return array (
			'STDUNIT' => false,
			'ALLUNITS' => array ()
		);
	}

	/**
	 * Parses the mathematical equation in $value and sets its properties in $datavalue.
	 * At the moment, math. equations are not evaluated at all. This might happen
	 * in future versions.
	 * 
	 * @param string $value The mathematical equation that will be parsed.
	 * @param SMWDataValue $datavalue
	 *         The HTML-, XSD- and printout representation of the value as well
	 *         as potential errors will be stored in this object.
	 *
	 */
	function processValue($value, & $datavalue) {
		$xsdvalue = -1; // initialize to failure
		// TODO: To save code, trim values before they get to processValue().

		$res = null;
		$value = trim($value);
		if ($value != '') { //do not accept empty strings

			$xsdvalue = smwfXMLContentEncode($value);
			$datavalue->setProcessedValues($value, $xsdvalue);
			$datavalue->setPrintoutString($value);
			$datavalue->addQuicksearchLink();
		} else {
			$datavalue->setError(wfMsgForContent('smw_emptystring'));
		}
		return true;
	}

	function processXSDValue($value, $unit, & $datavalue) {
		return $this->processValue($value, $datavalue);
	}

	// Mathematical formulas can be sorted
	function isNumeric() {
		return true;
	}


}
?>
