<?php
/**
 * Prints a set of LuceneResults and a WikiTitleResults
 *
 * @author: Kai Kühn
 */
class UnifiedSearchResult {
	private $title;
	private $snippet;
	private $score;
	private $wordCount;
	private $timeStamp;

	private $fullTextResult;

	private $description;
	private $examples;

	public function __construct($title, $score, $fullTextResult, $snippet = NULL) {
		$this->title = $title;
		$this->score = $score;
		$this->fullTextResult = $fullTextResult;
		$this->snippet = $snippet;
	}

	public function isFulltextResult() {
		return $this->fullTextResult;
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

	public function setWordCount($wordCount) {
		$this->wordCount = $wordCount;
	}

	public function getWordCount() {
		return $this->wordCount;
	}

	public function setTimeStamp($timeStamp) {
		$this->timeStamp = $timeStamp;
	}

	public function getTimeStamp() {
		return $this->timeStamp;
	}


	public function getDescription() {
		if ($this->description == NULL) {

			$this->description = reset(smwfGetStore()->getPropertyValues($this->title, SKOSVocabulary::$DESCRIPTION));
		}
		return $this->description;
	}

	public function getExamples() {
		if ($this->examples == NULL) {

			$this->examples = smwfGetStore()->getPropertyValues($this->title, SKOSVocabulary::$EXAMPLE);
		}
		return $this->examples;
	}

	public static function newFromLuceneResult(LuceneResult $lc, array & $terms) {
		return new UnifiedSearchResult($lc->getTitle(), $lc->getScore(), true, !$lc->isMissingRevision() ? $lc->getTextSnippet($terms) : NULL);
	}

	public static function newFromWikiTitleResult(Title $title, $score) {
		return new UnifiedSearchResult($title, $score, false);
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
	public static function serialize(array & $entries, & $terms) {
		global $wgServer, $wgScript;
		
		$termsarray = split(' ', $terms);
		// GreyBox
		$args = "";
		for ($i = 0; $i < count($termsarray); $i++) {
			$args .= "&rsargs[]=" . $termsarray[$i] . "";
		}
		// GreyBox
		$html = '<table id="us_queryresults">';
		foreach($entries as $e) {
			$html .= '<tr class="us_resultrow"><td>';
			$html .= '<div class="us_search_result">';
			// Categories
			$categories = USStore::getStore()->getCategories($e->getTitle());


			$html .= '<li><img src="'.self::getImageURI(self::getImageFromNamespace($e)).'"/>';
			//if ($e->isFulltextResult()) $html.= '<img style="margin-left: 6px;" src="'.self::getImageURI("fulltext.gif" ).'"/>';
			$html .= '<a class="us_search_result_link" href="'.$e->getTitle()->getFullURL().'">'.$e->getTitle()->getText().'</a>';
			$html .= '<span class="searchprev"><a rel="gb_page_center[]" href="'.$wgServer.$wgScript.'?action=ajax&rs=smwf_ca_GetHTMLBody&rsargs[]='.$e->getTitle() . $args .'" title="'. $e->getTitle() .'"></a></span>';			
			
			if (count($categories) > 0) {
				$html .= '<div class="category">'.wfMsg('us_isincat').': ';
				for($i = 0, $n = count($categories); $i < $n; $i++) {
					$sep = $i < $n-1 ? " | " : "";
					$html .= '<a href="'.$categories[$i]->getFullURL().'">'.$categories[$i]->getText().'</a>'.$sep;
				}
				$html .= '</div>';
			}
			if ($e->getSnippet() !== NULL) $html .= '<div class="snippet">'.$e->getSnippet().'</div>';
			$html .= '<div class="metadata">'.wfMsg('us_lastchanged').': '.self::formatdate($e->getTimeStamp()).'</div>';
				
			// Description
			/*$desc = $e->getDescription();
			 if ($desc !== false) {
			 $html .= '<a title="'.wfMsg('us_showdescription').'"><img onclick="smwhg_unifiedsearch.showDescription(\''.$e->getTitle()->getDBkey().'\')" style="margin-left:10px;" src="'.self::getImageURI("info.gif").'"/></a>';
			 $html .= '<div class="us_description" id="'.$e->getTitle()->getDBkey().'" style="margin-left:10px;display: none;">'.$desc->getXSDValue().'</div>';
			 }
			 $html .= '</div>';*/

			$html .= '</td></tr>';
		}
		$html .= '</table>';
		return $html;
	}

	public static function getImageURI($imageName) {
		global $wgServer, $wgScriptPath;
		$imagePath = "$wgServer$wgScriptPath/extensions/UnifiedSearch/skin/images/";

		$imagePath .= $imageName;

		return $imagePath;
	}



	private static function getImageFromNamespace($result) {
			
		switch($result->getTitle()->getNamespace()) {
			case NS_MAIN: { $image = "instance.gif"; break; }
			case NS_CATEGORY: { $image = "concept.gif"; break; }
			case NS_TEMPLATE: { $image = "template.gif"; break; }
			case SMW_NS_PROPERTY: { $image = "property.gif"; break; }
			case SMW_NS_TYPE: { $image = "template.gif"; break; }
			case NS_DOCUMENT: { $image = "doc.gif"; break; }
			case NS_PDF: { $image = "pdf.gif"; break; }
		}
		return $image;
	}

	private static function formatdate($timestamp) {
		$year = substr($timestamp,0,4);
		$month = substr($timestamp,4,2);
		$day = substr($timestamp,6,2);
		$hour = substr($timestamp,8,2);
		$min = substr($timestamp,10,2);
		$sec = substr($timestamp,12,2);

		global $wgLang;
		switch($wgLang->getCode()) {
			case "de": return "$day-$month-$year $hour:$min:$sec";
			case "en": return "$year-$month-$day $hour:$min:$sec";
			default: return "$year-$month-$day $hour:$min:$sec";
		}
	}
}
?>