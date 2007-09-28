<?php
/**
 * Typehandler class for chemical equations.
 *
 * @author Thomas Schweitzer
 */

global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/SMW_ChemistryParser.php");

/**
 * Class for managing chemical equations.
 */
class SMWChemicalEquationTypeHandler extends SMWDataValue {
	
	private $m_xsdValue = ''; // representation of the value in the database
	private $m_wikitext = ''; // representation of the value as wiki text
	private $m_html     = ''; // representation of the value as HTML

	public function SMWChemicalEquationTypeHandler($typeid) {
		SMWDataValue::__construct($typeid);
	}

	function isNumeric() {
		return false;
	}
	
	protected function parseUserValue($value) {
		
		$value = trim($value);
		if ($value == '') { //do not accept empty strings
			$this->addError(wfMsgForContent('smw_emptystring'));
		} else {
	
			$parser = new ChemEqParser();
			if ($parser->checkEquation($value) === true) {
				$this->m_xsdValue = smwfXMLContentEncode($parser->getWikiFormat());
				$this->m_html = $parser->getHtmlFormat();
				if ($this->m_caption === false) {
					$this->m_caption = $this->m_html;
				}
				$this->m_wikitext = $value;
			} else {
				$this->addError($value.":".$parser->getError()."<br>");
			}
		}
		if ($this->m_caption === false) {
			$this->m_caption = $value;
		}
		return true;

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
		return ''; // empty unit
	}

	public function getInfolinks() {
		return $this->m_infolinks;
	}

	public function getHash() {
		return $this->getShortWikiText(false);
	}
	
}

?>