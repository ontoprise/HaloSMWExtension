<?php


/**
 * This datavalue implements special processing for glossary terms. Words that are
 * annotated with a property whose type is "GlossaryTerm" are highlighted and get
 * a tooltip that contains the description of the word.
 *
 * @author Thomas Schweitzer
 * @note AUTOLOADED
 */
class SMWGlossaryTypeHandler extends SMWWikiPageValue {
	public function getShortWikiText($linked = NULL) {
		smwfRequireHeadItem(SMW_HEADER_TOOLTIP);
		
		$txt = '<span class="smwglossaryhighlight"> '
			   .'<span class="smwttinline"> '
		       .parent::getShortWikiText($linked)
		       .'<span class="smwttcontent">'
		       .$this->getTermDescription($this->getWikiValue())
		       .'</span></span></span> ';
		return $txt;
	}


	public function getLongWikiText($linked = NULL) {
		smwfRequireHeadItem(SMW_HEADER_TOOLTIP);
		$txt = '<span class="smwttinline"> '
		       .parent::getLongWikiText($linked)
		       .'<span class="smwttcontent">'
		       .$this->getTermDescription($this->getWikiValue())
		       .'</span></span> ';
       return $txt;
	}
	
	/**
	 * Retrieve the description of a term (i.e. an article). The content of
	 * the property "description" is returned.
	 *
	 * @param string $term
	 * 		The term whose description is requested.
	 * @return string
	 * 		Description of the term.
	 */
	public function getTermDescription($term) {
		
		$query_string = "[[$term]][[description::*]][[description::+]]";
		$params = array();
		$printlabel = "";
		$printouts[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, $printlabel);
		$query  = SMWQueryProcessor::createQuery($query_string, $params, true, 'auto', $printouts);
		$results = smwfGetStore()->getQueryResult($query);
		$row = $results->getNext();
		if (is_array($row) && count($row) > 0) {
			$desc = $row[0]->getContent();
			$desc = $desc[0]->getShortWikiText();
			return $desc;
		}
		return wfMsg('smw_gloss_no_description', $term);
	}
	
}

?>
