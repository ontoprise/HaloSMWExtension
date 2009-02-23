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
		$args_prev = "";
		for ($i = 0; $i < count($termsarray); $i++) {
			$args .= "&rsargs[]=" . $termsarray[$i] . "";
			$args_prev .= $termsarray[$i] . " ";
		}
		// GreyBox
		$html = '<table id="us_queryresults">';
		foreach($entries as $e) {
			$html .= '<tr class="us_resultrow"><td>';
			$html .= '<div class="us_search_result">';
			// Categories
			$categories = USStore::getStore()->getCategories($e->getTitle());
	
			$html .= '<li><span class="searchprev"><a rel="gb_pageset_halo[search_set, '.$args_prev.', '.$e->getTitle()->getFullURL().']" href="'.$wgServer.$wgScript.'?action=ajax&rs=smwf_ca_GetHTMLBody&rsargs[]='.$e->getTitle() . $args .'" title="'. $e->getTitle() .'"></a></span>';			
			$html .= '<a class="us_search_result_link" href="'.$e->getTitle()->getFullURL().'">'.$e->getTitle()->getText().'</a>';
			$html .= '<img src="'.self::getImageURI(self::getImageFromNamespace($e)).'"/>';
			
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
			case NS_MAIN: { $image = "smw_plus_instances_icon_16x16.png"; break; }
			case NS_CATEGORY: { $image = "smw_plus_category_icon_16x16.png"; break; }
			case NS_TEMPLATE: { $image = "smw_plus_template_icon_16x16.png"; break; }
			case SMW_NS_PROPERTY: { $image = "smw_plus_property_icon_16x16.png"; break; }
			case SMW_NS_TYPE: { $image = "smw_plus_template_icon_16x16.png"; break; }
			case NS_HELP: { $image = "smw_plus_help_icon_16x16.png"; break; }
		    case NS_IMAGE: { $image = "smw_plus_image_icon_16x16.png"; break; }
			case NS_DOCUMENT: { $image = "smw_plus_document_icon_16x16.png"; break; }
			case NS_PDF: { $image = "smw_plus_pdf_icon_16x16.png"; break; }
		    case NS_AUDIO: { $image = "smw_plus_music_icon_16x16.png"; break; }
            case NS_VIDEO: { $image = "smw_plus_video_icon_16x16.png"; break; }
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