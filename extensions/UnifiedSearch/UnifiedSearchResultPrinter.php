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
	
	public static function newFromLuceneResult(LuceneResult $lc, array & $terms) {
		return new UnifiedSearchResult($lc->getTitle(), $lc->getScore(), $lc->getTextSnippet($terms));
	}
	
    public static function newFromWikiTitleResult(Title $title, $score) {
        return new UnifiedSearchResult($title, $score);
    }
    
    public static function sortByScore(array & $searchResults) {
    	for($i = 0, $n=count($searchResults); $i < $n; $i++) {
	    	for($j = 0, $m=count($searchResults)-1; $j < $m; $j++) {
	            if ($searchResults[$j]->getScore() < $searchResults[$j+1]->getScore()) {
	            	$temp = $searchResults[$j+1];
	            	$searchResults[$j+1] = $searchResults[$j];
	            	$searchResults[$j] = $temp;
	            }
	        }
    	}
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
        	$html .= '<div class="us_search_result">';
        	$html .= '<li><img src="'.self::getImageURI($e->getTitle()).'"/>';
        	$html .= '<a class="us_search_result_link" href="'.$e->getTitle()->getFullURL().'">'.$e->getTitle()->getText().'</a>';
        	$html .= '[Score: '.$e->getScore()."]";
        	if ($e->getSnippet() !== NULL) $html .= '<div class="snippet">'.$e->getSnippet().'</div>';
        	$html .= '</div>';
        	
        	$html .= '</td></tr>';
        }
		$html .= '</table>';
		return $html;
	}
	
    private static function getImageURI(Title $page) {
        global $wgServer, $wgScriptPath;
        $imagePath = "$wgServer$wgScriptPath/extensions/UnifiedSearch/skin/images/";
        switch($page->getNamespace()) {
            case NS_MAIN: { $imagePath .= "instance.gif"; break; }
            case NS_CATEGORY: { $imagePath .= "concept.gif"; break; }
            case NS_TEMPLATE: { $imagePath .= "template.gif"; break; }
            case SMW_NS_PROPERTY: { $imagePath .= "property.gif"; break; }
            case SMW_NS_TYPE: { $imagePath .= "template.gif"; break; }
        }
        return $imagePath;
    }
}
?>