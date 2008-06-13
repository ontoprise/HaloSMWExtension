<?php
/*  Copyright 2008, ontoprise GmbH
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
 *
 *  Author: Thomas Schweitzer
 */

global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningIssues.php");
require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");

// This property is inserted for terms that are part of the glossary.
define('SMW_GLO_PROPNAME', 'glossary');

/**
 * This bot searches all articles in the category "ShowGlossary" for glossary 
 * terms and annotates them.
 *
 */
class GlossaryBot extends GardeningBot {


	/**
	 * Constructor
	 *
	 */
	function __construct() {
		parent::GardeningBot("smw_glossarybot");
	}

	/**
	 * Returns the help text for the Gardening page.
	 *
	 */
	public function getHelpText() {
		return wfMsg('smw_gard_glossarybothelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function allowedForUserGroups() {
		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
	}

	/**
	 * Returns an array of parameter objects.
	 * No parameters are needed.
	 */
	public function createParameters() {
	
		$params = array();
		
		return $params;
	}

	/**
	 * This method is called by the bot framework. It processes all articles.
	 */
	public function run($paramArray, $isAsync, $delay) {

		$this->processArticles();
		return '';

	}
	
	/**
	 * This methods finds all articles in the category 'ShowGlossary' and 
	 * processes their content.
	 *
	 */
	private function processArticles() {
		global $smwgIP;
		include_once($smwgIP . "/includes/SMW_QueryProcessor.php");
		
		// Find all articles with the property 'description'.
		$gt = &$this->getGlossaryTerms();
		
		$query_string = "[[Category:ShowGlossary]]";
		$params = array();
		$printlabel = "";
		$printouts[] = new SMWPrintRequest(SMW_PRINT_THIS, $printlabel);
		$query  = SMWQueryProcessor::createQuery($query_string, $params, true, 'auto', $printouts);
		$results = smwfGetStore()->getQueryResult($query);
		
		$numArticles = $results->getCount();
		echo $numArticles." article to process\n";
		$this->setNumberOfTasks(1);
		$this->addSubTask($numArticles);
		while ($row = $results->getNext()) {
			if (is_array($row) && count($row) > 0) {
				$r = $row[0]->getContent();
				if (is_a($r[0], 'SMWWikiPageValue')) {
					$title = $r[0]->getTitle();
					$this->processArticle($title, $gt);
					$this->worked(1);
				}
			}
		}
	}

	/**
	 * Terms in the article <$title> are annotated if there is a glossary entry
	 * for them.
	 *
	 * @param Title $title
	 * 		Title object of the article whose terms are annotated.
	 * @param array $glossaryTerms
	 * 		Array of terms that have a description.
	 */
	private function processArticle(Title $title, $glossaryTerms) {
//		echo "Processing ". $title->getText(). "...";
		$article = new Article($title);
		$cont = $article->getContent();
		// Mask tags like <nowiki>, <pre>, etc	
		list($cont, $replacements) = $this->maskHTML($cont);
		// Get all words in the article			
		$words = &$this->getWords($cont);
		// Find all words that have entries in the glossary
		$words = &$this->findMatchingWords($words, $glossaryTerms);
		// Annotate the words
		$cont = $this->replaceWords($words, $cont);
		// Restore <nowiki> etc
		$cont = $this->unmaskHTML($cont, $replacements);
		// Store the article
		$article->doEdit($cont,'Changed by glossary bot');

		if (count($words) > 0) {
			$log = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
			$log->addGardeningIssueAboutArticle(
				$this->id, SMW_GARDISSUE_FOUND_TERMS_IN_ARTICLE, 
				Title::newFromText($title->getText()));
		}
//		echo "done.\n";
	}
	
	/**
	 * Return a sorted array of words in the article.
	 * 
	 * @param string $content
	 * 		The wiki text if an article.
	 * @return array<string>
	 * 		A sorted array of all words of the article converted to lower case.
	 */
	private function &getWords(&$content) {
		$words = preg_split('/\W+/',$content, -1, PREG_SPLIT_NO_EMPTY);
		$wc = count($words);
		for ($i = 0; $i < $wc; ++$i) {
			$words[$i] = strtolower($words[$i]);
		}
		sort($words, SORT_STRING);
		return $words;
	}

	/**
	 * Finds all articles in the wiki with a description.
	 *
	 * @return array<string>
	 * 		A sorted array of names of all article that have the property
	 * 		'description' (converted to lower case).
	 */
	private function &getGlossaryTerms() {
		$query_string = "[[description::*]][[description::+]]";
		$params = array();
		$printlabel = "";
		$printouts[] = new SMWPrintRequest(SMW_PRINT_THIS, $printlabel);
		$query  = SMWQueryProcessor::createQuery($query_string, $params, true, 'auto', $printouts);
		global $smwgQMaxLimit;
		$query->setLimit($smwgQMaxLimit, false);
		$results = smwfGetStore()->getQueryResult($query);
		
		$glossaryTerms = array();
		while ($row = $results->getNext()) {
			if (is_array($row) && count($row) > 0) {
				$r = $row[0]->getContent();
				if (is_a($r[0], 'SMWWikiPageValue')) {
					$glossaryTerms[] = strtolower($r[0]->getTitle()->getText());
				}
			}
		}
		sort($glossaryTerms, SORT_STRING);
		return $glossaryTerms;
	}
	
	/**
	 * Matches the words of the article with the words that have a description.
	 *
	 * @param array<string> $words
	 * 		Words of the article.
	 * @param array<string> $glossaryTerms
	 * 		Words in the glossary.
	 * @return array<string>
	 * 		Intersection of <$words> and <$glossaryTerms>
	 */
	private function &findMatchingWords($words, $glossaryTerms) {
		
		$wIdx = $gIdx = 0;
		$wc = count($words);
		$gc = count($glossaryTerms);
		$matches = array(); 
		
		$gtChanged = true;
		while ($wIdx < $wc && $gIdx < $gc) {
			$w = $words[$wIdx];
			
			if ($gtChanged) {
				// A glossary term may consist of several words
				$gwords = preg_split('/\s+/',$glossaryTerms[$gIdx], -1, PREG_SPLIT_NO_EMPTY);
				$g = $gwords[0];
				$gtChanged = false;
			}
			$r = strcasecmp($w, $g);
			if ($r == 0) {
				// glossary terms with several words whose first word is matched
				// are candidates for the glossary annotation.
				$matches[] = $glossaryTerms[$gIdx];
				$gtChanged = true;
				++$gIdx;
			} else if ($r > 0) {
				++$gIdx;
				$gtChanged = true;
			} else {
				++$wIdx;
			}
		}
		return $matches;
	}
	
	/**
	 * Annotates the words in the article.
	 *
	 * @param array<string> $words
	 * 		All words that should be annotated.
	 * @param string $text
	 * 		The text that contains the words that are annotated.
	 * @return string
	 * 		The text with the annotated words.
	 */
	private function replaceWords(&$words, &$text) {
		global $smwgHaloContLang;
		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$glossProp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$glossProp = $glossProp['SMW_SSP_GLOSSARY'];
		$search = array();
		foreach ($words as $word) {
			$search[] = '/(\W)('.preg_quote($word).')(\W|$)/im';
		}
		$text = preg_replace($search,'$1[['.$glossProp.'::$2]]$3', $text);
		return $text;
	}
	
	/**
	 * The wiki text <$wikiText> may contain dangerous HTML code, 
	 * <nowiki>-sections etc.that will
	 * confuse the parser that generates wiki text offsets 
	 * (see addWikiTextOffsets). All dangerous text is replaced by * to
	 * keep the number of characters the same.
	 * 
	 * The following sections are handled:
	 * - HTML-comments (<!--  -->)
	 * - <nowiki>-sections
	 * - <ask>-sections
	 * - <pre>-sections
	 *
	 * @param string $wikiText
	 * @return string masked wiki text
	 */
	private function maskHTML(&$wikiText)
	{
		$tags = array(
			// regex for opening tag, beginning of tag, regex for closing tag, 
			// closing tag
			array('\[\[','[[','\]\]',']]'),
			array('<ask.*?>','<ask','<\/ask>','</ask>'),
			array('<!--','<!--','-->','-->'),
			array('<nowiki>','<nowiki>','<\/nowiki>','</nowiki>'),
			array('<pre>','<pre>','<\/pre>','</pre>'),
			array('<sup id="_ref.*?>','<sup id="_ref','<\/sup>','</sup>'),
			array('<ref .*?>','<ref','<\/ref>','</ref>')
			);
		$numTags = count($tags);
		$replacements = array();
		
		$regEx = '/';
		for ($i = 0; $i < $numTags; $i++) {
			$regEx .= '('.$tags[$i][0].')|('.$tags[$i][2].')';
			$regEx .= ($i == $numTags-1) ? '/sm' : '|'; 
		}
		$parts = preg_split($regEx, $wikiText, -1, 
		                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		
		$text = "";
		$openingTag = -1;
		$replacementID = "";
		$replaced = "";
		$replacementCount = 0;
		
		foreach ($parts as $part) {
			$isOpeningTag = false;
			$isClosingTag = false;
			$tagIdx = -1;
			for ($i = 0; $i < $numTags; $i++) {
				$t = $tags[$i];
				if (strpos($part, $t[1]) === 0) {
					// opening tag found
					$isOpeningTag = true;
					$tagIdx = $i;
					break;
				} else if (strpos($part, $t[3]) === 0) {
					// closing tag found
					$isClosingTag = true;
					$tagIdx = $i;
					break;
				}
			}
			
			if ($openingTag == -1 && $isOpeningTag) {
				$replacementID = "{#+*unqrplid".$replacementCount++.'#+*}';
				$replaced = $part;
				$text .= $replacementID;
				// Special handling for <ref .../>
				if (preg_match('/^<ref .*?\/>$/', $part)) {
				    //nothing to do
				} else {
					$openingTag = $tagIdx;
				}
			} else if ($openingTag >= 0) {
				$replaced .= $part;
				if ($isClosingTag) {
					if ($openingTag == $tagIdx) {
						//The opening tag matches the closing tag
				    	$replacements[$replacementID] = $replaced;
					    $openingTag = -1;
					}
				}
			} else if ($openingTag == -1) {
				// concatenate normal text
				$text .= $part;
			}
		}
		return array($text, $replacements);
	}
	
	private function unmaskHTML(&$text, &$replacements) {
		$search = array();
		$repl = array();
		foreach ($replacements as $id => $r) {
			$search[] = $id;
			$repl[]   = $r;
		}
		$text = str_replace($search, $repl, $text);
		return $text;
	}
	
}

// Create one instance to register the bot.
new GlossaryBot();

define('SMW_GLOSSARY_BOT_BASE', 2300);
define('SMW_GARDISSUE_FOUND_TERMS_IN_ARTICLE', SMW_GLOSSARY_BOT_BASE * 100 + 1);
//define('SMW_GARDISSUE_UPDATED_ARTICLE', (SMW_TERMIMPORT_BOT_BASE+1) * 100 + 1);

class GlossaryBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case SMW_GARDISSUE_FOUND_TERMS_IN_ARTICLE:
				return wfMsg('smw_gloss_annotated_glossary', $text1);
				
			default: return NULL;
				
		}
	}
}

class GlossaryBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_GLOSSARY_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'), 
		                                wfMsg('smw_gl_highlighted_article'));
	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {
		return array('pageTitle' => $wgRequest->getVal('pageTitle'));
	}

	public function getData($options, $request) {
		$pageTitle = $request->getVal('pageTitle');
		if ($pageTitle != NULL) {
			// show only issue of *ONE* title
			return $this->getGardeningIssueContainerForTitle($options, $request, Title::newFromText(urldecode($pageTitle)));
		} else return parent::getData($options, $request);
	}

	private function getGardeningIssueContainerForTitle($options, $request, $title) {
		$gi_class = $request->getVal('class') == 0 ? NULL : $request->getVal('class') + $this->base - 1;


		$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();

		$gic = array();
		$gis = $gi_store->getGardeningIssues('smw_glossarybot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);


		return $gic;
	}
}

?>
