<?php
/**
 * @file
 * @ingroup EnhancedRetrieval
 *
 * Prints a set of LuceneResults and a WikiTitleResults
 *
 * @author: Kai K�hn
 */
class EnhancedRetrievalResult {

	// wrapped luceneResult
	private $luceneResult;

	// clean terms to highlight (i.e. no syntax elements)
	private $terms;

	private $description;
	private $examples;

	public function __construct($luceneResult, $terms) {
		$this->luceneResult = $luceneResult;
		$this->terms = array();
		foreach($terms as $t) {
			array_push($this->terms, preg_quote($t,"/"));
		}
	}

	public function getTitle() {
		return $this->luceneResult->getTitle();
	}

	public function getScore() {
		return $this->luceneResult->getScore();
	}

	public function getSnippet() {
		return !$this->luceneResult->isMissingRevision() ?
		$this->luceneResult->getTextSnippet($this->terms) : NULL;
	}

	public function getWordCount() {
		return !$this->luceneResult->isMissingRevision() ? $this->luceneResult->getWordCount() : 0;
	}

	public function getTimeStamp() {
		return !$this->luceneResult->isMissingRevision() ? $this->luceneResult->getTimeStamp() : 0;
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
		return new EnhancedRetrievalResult($lc, $terms);
	}

}

class EnhancedRetrievalResultPrinter {

	/**
	 * Creates a result table
	 *
	 * @param array $entries
	 */
	public static function serialize(array & $entries, & $terms) {
		
		global $wgContLang;
		$termsarray = explode(' ', $terms);
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
			if (!self::userCan($e->getTitle(), 'read')) {
				continue;
			}
			
			$html .= '<tr class="us_resultrow"><td>';
			$html .= '<div class="us_search_result">';
			// Categories
			$categories = USStore::getStore()->getCategories($e->getTitle());

			$html .= self::addPreview($e, $args, $args_prev);
			$html .= '<a class="us_search_result_link" href="'.$e->getTitle()->getFullURL().'">'.$e->getTitle()->getText().'</a>';
			$nsName = $e->getTitle()->getNamespace() == NS_MAIN ? wfMsg('us_article') : $wgContLang->getNsText($e->getTitle()->getNamespace());
			$html .= '<img alt="'.$nsName.'" height="16" title="'.$nsName.'" src="'.self::getImageFromNamespace($e).'"/>';

			if (count($categories) > 0) {
				$html .= '<div class="category">'.wfMsg('us_isincat').': ';
				for($i = 0, $n = count($categories); $i < $n; $i++) {
					$sep = $i < $n-1 ? " | " : "";
					$html .= '<a href="'.$categories[$i]->getFullURL().'">'.$categories[$i]->getText().'</a>'.$sep;
				}
				$html .= '</div>';
			}
			if ($e->getSnippet() !== NULL) $html .= '<div class="snippet">'.$e->getSnippet().'</div>';
			if ($e->getTimeStamp() > 0) $html .= '<div class="metadata">'.wfMsg('us_lastchanged').': '.self::formatdate($e->getTimeStamp()).'</div>';

			$html .= '</td></tr>';
		}
		$html .= '</table>';
		return $html;
	}

	public static function getImageURI($imageName) {
		global $wgServer, $wgScriptPath;
		$imagePath = "$wgServer$wgScriptPath/extensions/EnhancedRetrieval/skin/images/";

		$imagePath .= $imageName;

		return $imagePath;
	}
	
    public static function getImageURIFromPath($path) {
        global $wgServer, $wgScriptPath;
        $imagePath = "$wgServer$path";
        return $imagePath;
    }

	// adds preview to result depending on namespace
	private static function addPreview($e, $args, $args_prev) {
		global $wgServer, $wgScript;

		return $html = '<li><span class="searchprev"><a rel="gb_pageset_halo[search_set, '.$args_prev.
                             ', '.$e->getTitle()->getFullURL().']" href="'.$wgServer.$wgScript.'?action=ajax&rs=smwf_ca_GetHTMLBody&rsargs[]='.urlencode($e->getTitle()->getPrefixedText()) .
		$args .'" title="'. $e->getTitle() .'">&nbsp;</a></span>';
	}

	private static function getImageFromNamespace($result) {
		if ($result->getTitle()->getNamespace() == NS_MAIN) {
			$categories=$result->getTitle()->getParentCategories();
			foreach($categories as $prefixedText => $text) {
				$path = USStore::getStore()->getImageURL(Title::newFromText($prefixedText));
				if (!is_null($path)) {
					return self::getImageURIFromPath($path);
				}
			}
		}
		$image = "";
		switch($result->getTitle()->getNamespace()) {
			case NS_MAIN: { $image = "smw_plus_instances_icon_16x16.png"; break; }
			case NS_CATEGORY: { $image = "smw_plus_category_icon_16x16.png"; break; }
			case NS_TEMPLATE: { $image = "smw_plus_template_icon_16x16.png"; break; }
			case SMW_NS_PROPERTY: { $image = "smw_plus_property_icon_16x16.png"; break; }
			case SMW_NS_TYPE: { $image = "smw_plus_template_icon_16x16.png"; break; }
			case NS_HELP: { $image = "smw_plus_help_icon_16x16.png"; break; }
			case NS_IMAGE: { $image = "smw_plus_image_icon_16x16.png"; break; }
		}
		// if MIME type extension is installed
		if (defined("SMW_RM_VERSION")) {
			switch($result->getTitle()->getNamespace()) {
				case NS_DOCUMENT: { $image = "smw_plus_document_icon_16x16.png"; break; }
				case NS_PDF: { $image = "smw_plus_pdf_icon_16x16.png"; break; }
				case NS_AUDIO: { $image = "smw_plus_music_icon_16x16.png"; break; }
				case NS_VIDEO: { $image = "smw_plus_video_icon_16x16.png"; break; }
			}
		}
		// if collaboration extension is installed
		if (defined("CE_VERSION")) {
			switch($result->getTitle()->getNamespace()) {
				case CE_COMMENT_NS: { $image = "smw_plus_comment_icon_16x16.png"; break; }
			}
		}
		// if SemanticForms is installed
		if (defined("SF_NS_FORM")) {
			if ($result->getTitle()->getNamespace() == SF_NS_FORM) {
				$image = "smw_plus_form_icon_16x16.png";
			}
		}
		// SMWUserManual is installed
		if (defined("SMW_NS_USER_MANUAL")) {
			switch($result->getTitle()->getNamespace()) {
				case SMW_NS_USER_MANUAL: { $image = "smw_plus_help_icon_16x16.png"; break; }
			}
		}
		return self::getImageURI($image);
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
	
	/**
	 * Checks if the current user can perform the given $action on the article with
	 * the given $title.
	 *
	 * @param Title $title
	 * 		The title object of the article
	 * @param string $action
	 * 		Name of the action
	 *
	 * @return bool
	 * 		<true> if the action is permitted
	 * 		<false> otherwise
	 */
	private static function userCan(Title $title, $action) {
		global $wgUser;
		$result = true;
		wfRunHooks('userCan', array($title, $wgUser, $action, &$result));
		return $result;
	}
	
}
