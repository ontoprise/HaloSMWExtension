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
 * Typehandler class for chemical equations.
 *  
 * @defgroup SMWHaloDataValues SMWHaloDataValues
 * @ingroup SMWHalo
 * @author Thomas Schweitzer
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

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
		return ''; // empty unit
	}

	public function getInfolinks() {
		return $this->m_infolinks;
	}

	public function getHash() {
		return $this->getShortWikiText(false);
	}
	
	/**
	 * Creates the export line for the RDF export
	 *
	 * @param string $QName The element name of this datavalue
	 * @param ExportRDF $exporter the exporter calling this function
	 * @return the line to be exported
	 */
	public function exportToRDF($QName, ExportRDF $exporter) {
		$content = $this->getXSDValue();
		$content = mb_ereg_replace ( "", "", $content);
		return "\t\t<$QName rdf:datatype=\"http://www.w3.org/2001/XMLSchema#string\">$content</$QName>\n";
	}
}

