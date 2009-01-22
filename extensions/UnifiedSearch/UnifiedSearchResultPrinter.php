<?php
/**
 * Prints a set of LuceneResults and a WikiTitleResults
 *
 */
class UnifiedSearchResult {
	private $title;
	private $snippet;
	private $score;

	private $description;
	private $examples;

	public function __construct($title, $score, $snippet = NULL) {
		$this->title = $title;
		$this->score = $score;
		$this->snippet = $snippet;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getScore() {
		return $this->score;
	}

	public function getSnippet() {
		return $this->snippet;
	}

	public function getDescription() {
		if ($this->description == NULL) {
			$descProperty = SMWPropertyValue::makeUserProperty(wfMsg(QueryExpander::$DESCRIPTION));
			$this->description = reset(smwfGetStore()->getPropertyValues($this->title, $descProperty));
		}
		return $description;
	}

	public function getExamples() {
		if ($this->examples == NULL) {
			$examplesProperty = SMWPropertyValue::makeUserProperty(wfMsg(QueryExpander::$EXAMPLE));
			$this->examples = smwfGetStore()->getPropertyValues($this->title, $descProperty);
		}
		return $this->examples;
	}
	
	public static function newFromLuceneResult(LuceneResult $lc) {
		return new UnifiedSearchResult($lc->getTitle(), $lc->getScore(), $lc->getTextSnippet());
	}
}

class UnifiedSearchResultPrinter {

	/**
	 * Creates a result table
	 *
	 * @param array $entries
	 */
	public static function serialize(array & $entries) {
		$html = '<table id="us_queryresults">';
        foreach($entries as $e) {
        	$html .= '<tr class="us_resultrow"><td>';
        	$html .= $e->getTitle()->getText();
        	$html .= '</td></tr>';
        }
		$html .= '</table>';
		return $html;
	}
}
?>