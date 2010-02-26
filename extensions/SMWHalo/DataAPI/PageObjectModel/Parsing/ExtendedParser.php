<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

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
 * Parser for advanced annotation mode.
 * This parser augments the generated HTML in order to create mapping
 * from HTML to the original wiki text on the client side.
 *
 * @author Dian
 * @author Thomas Schweitzer
 */
class POMExtendedParser extends POMParser{
	//--- Fields ---
	// State of the template parser
	private $mTpState = 0;

	// Name of the top level template
	private $mTemplateName = null;

	// This flag stores if opening braces were dropped
	private $mBracesDropped = false;

	// States of the template parsers state machine
	// Each state consists of these elements:
	// 0 - Top of the template stack (t = template, p = template parameter, e = empty)
	// 1 - Input token
	// 2 - Type of page (n=normal, t=template)
	// 3 - Action:
	//        number => index of next state
	//        'wc'   => return content buffer and go to state 0
	//        'pt'   => push 't', go to state 0
	//        'pp'   => push 'p', go to state 0
	//        'pop'  => pop element from template stack, go to state 0
	//		  'db'	 => drop one opening brace at the beginning, go to state in field 4
	// 4 - number => index of next state
	private $mStateMachine = array(
	// state 0
	array(
	array('*','{','*',1),
	array('*','}','*',4),
	array('*','|','t',4),
	array('*','*','*','wc'),
	),

	// state 1
	array(
	array('*','{','*',2),
	array('*','|','t',2),
	array('*','*','*',8),
	),

	// state 2
	array(
	array('*','{','n','db',6),
	array('*','{','t',3),
	array('*','[[','*','wc'),
	array('*',']]','*','wc'),
	array('*','|','*','wc'),
	array('*',"\n",'*',11),
	array('*','*','*','pt',8),
	),

	// state 3
	array(
	array('*','{','t','db',7),
	array('*','*','*','pp'),
	),

	// state 4
	array(
	array('t','}','*','pop'),
	array('p','}','*',5),
	array('e','*','*','wc'),
	),

	// state 5
	array(
	array('p','}','*','pop'),
	array('p','*','*','wc'),
	),

	// state 6
	array(
	array('*','{','*','db',6),
	array('*','[[','*','wc'),
	array('*',']]','*','wc'),
	array('*','|','*','wc'),
	array('*',"\n",'*',11),
	array('*','*','*','pt',8),
	),

	// state 7
	array(
	array('*','{','*','db',7),
	array('*','*','*','pp'),
	),

	// state 8
	array(
	array('*','|','*','wc'),
	array('*','{','*',1),
	array('*',"\n",'*',9),
	array('*','}','*',10),
	//			array('*','[[','*','at'),
	array('*','}}','*','at'),
	array('*','*','*','wc'),
	),

	// state 9
	array(
	array('*','|','*','wc'),
	array('*','}','*',10),
	array('*',"\n",'*',9),
	array('*','*','*','at'),
	),

	// state 10
	array(
	array('*','}','*','pop'),
	array('*','*','*','at'),
	),

	// state 11
	array(
	array('*',"\n",'*',11),
	array('*','[[','*','wc'),
	array('*',']]','*','wc'),
	array('*','|','*','wc'),
	array('*','*','*','pt', 8)
	)
	);

	// new variables
	private $_page = NULL;
	// ~new variables


	/**
	 * No direct initialization of the parser available.
	 *
	 * @return POMExtendedParser
	 */
	protected function POMExtendedParser(){

	}

	//--- Public methods ---
	/**
	 * Get an instance of the parser.
	 *
	 * @return POMExtendedParser
	 */
	public static function getParser(){
		return new POMExtendedParser();
	}


	/**
	 * Parses the text of a given page and creates an element structure.
	 * Supported types of elements include:<br/>
	 * - templates,<br/>
	 * - categories,<br/>
	 * - properties and<br/>
	 * - simple text<br/>
	 * which represent the rest.
	 *
	 * @param  POMPage &$page The page for which the POM will be built.
	 */
	public function Parse(POMPage &$page)	{
		$wikiText = &$page->text;
		$this->_page = $page;

		// was is ein <ref>-tag?
		if (!$this->doAddWTO($wikiText)) {
			return $wikiText;
		}

		// Treat HTML comments and the like. They may contain dangerous material.
		$text = $this->maskHTML($wikiText);

		// Search for templates, template parameters and headings
		$parts = preg_split('/(\{)|'. // curly bracket start
		                    '(\})|'. // curly bracket end
							'(\n+)|'. // new line(s)
		                    '(^======)|'. // heading 6
							'(^=====)|'. // heading 5
		                    '(^====)|'. // heading 4
		                    '(^===)|'. // heading 3
		                    '(^==)|'. // heading 2
		                    '(^=)|'. // heading 1
							'(\[{2,})|'. // two or more squared brackets start
							'(\]\])|'. // two squared brackets end
							'(\|)|'. // one |
							'(<nowiki>)|'. // the nowiki start tag
							'(<\/nowiki>)|'. // the nowiki end tag
							'(<noinclude>)|'. // the noinclude start tag
							'(<\/noinclude>)|'. // the noinclude end tag
							'(<ask)|'. // the ask start tag
							'(<\/ask>)|'. // the ask end tag
							'(<pre>)|'. // the pre start tag
							'(<\/pre>)|'. // the pre end tag
							'^$/sm', $text, -1, 
							PREG_SPLIT_DELIM_CAPTURE |
							PREG_SPLIT_NO_EMPTY);
		$markedText = "";

		$id = 1;
		$pos = 0;
		$braceCount = 0;
		$templateStart = -1;
		$ignoreTemplates = -1;
		$tmplDescr = null;
		$ignoredLastToken = false;
		$lastTokenWOT = false; // true, if the last token was an opening tag
		$numParts = count($parts);
		$prevPart = null; // the part of the wiki text before the current part
		$part0 = null;
		$prefix = '';
		$titleOpen = false; // true if a title (e.g. ==Title==) is parsed

		// annotation vars
		$aText = ''; // the node text
		$aStartPos = -1; // the starting position
		// ~annotation vars

		// simple text vars
		$stText = ''; // the node text
		$stStartPos = -1; // the starting position
		// ~simple text vars

		for ($i = 0; $i < $numParts; ++$i) {
			$part = $parts[$i];
			$len = mb_strlen($part, "UTF-8");
			if ($part0) {
				$prevPart = $part0;
			}
			$part0 = mb_substr($wikiText, $pos, $len, "UTF-8");

			// Is the part a template?
			if ($templateStart == -1 && $i > $ignoreTemplates) {
				// no template detected yet.
				$tmplDescr = $this->parseTemplate($parts, $i);
				if ($tmplDescr[0] == 't') {
					// a template has been found => store only its start
					// It may be ahead of the current parser position.
					// It will be processed when its start is reached during
					// normal processing.
					$templateStart = $tmplDescr[2];
				} else if ($tmplDescr[0] == 'a') {
					// some tokens have been skipped
					// => advance parsing without looking for templates
					$ignoreTemplates = $tmplDescr[2];
				}
			}

			if ($i == $templateStart) {
				// check if a textnode has to be added
				if( $stStartPos != -1){
					// add the text node before the template
					$page->addElement(new POMSimpleText($stText, $stStartPos));
					// reset the simple text variables
					$stText = ''; // the node text
					$stStartPos = -1; // the starting position
					// ~add the text node
				}

				// a template has been found.
				// $tmplDescr[1]: name of the template
				// $tmplDescr[2]: start index of the template
				// $tmplDescr[3]: end index of the template
				// $tmplDescr[4]: content
				if (ereg('^#',$tmplDescr[1])) {// we have an extension parser function
					if ($tmplDescr[1] == '#ask:') {
						$page->addElement(new POMAskFunction($tmplDescr[4]));
					}else if($tmplDescr[1] == '#language:'
					||$tmplDescr[1] == '#special:'
					||$tmplDescr[1] == '#tag:'){
						// we have a built parser function as
						// described in http://www.mediawiki.org/wiki/Extension:Parser_function_extensions
						$page->addElement(new POMBuiltInParserFunction($tmplDescr[4]));
					}
					else{// we have an extension parser function other than "ask"
						$page->addElement(new POMExtensionParserFunction($tmplDescr[4]));
					}
				}else if (preg_match('#\w\:.*#',$tmplDescr[1]) !== 0){
					// we have a built-in parser function
					$page->addElement(new POMBuiltInParserFunction($tmplDescr[4]));
				}else{
					$page->addElement(new POMTemplate($tmplDescr[4]));
				}
				$templateStart = -1;
				// The parse can continue after the template
				$i = $tmplDescr[3];
				$pos += mb_strlen($tmplDescr[4], "UTF-8");

			} else {
				// parser is not collecting tokens for a template
				if ($part0 == '[[') {
					if ($braceCount == 0) {
						$aStartPos = $pos;
						if( $stStartPos != -1){
							// add the text node
							$page->addElement(new POMSimpleText($stText));
							// reset the simple text variables
							$stText = ''; // the node text
							$stStartPos = -1; // the starting position
							// ~add the text node
						}
					} else {
						$markedText .= $part0;
						// TODO: does this mean [[[[ ??
					}
					$braceCount++;
				} else if ($part0 == ']]') {
					if ($braceCount > 0) {
						$braceCount--;
					}
					$markedText .= $part0;
					$aText = '[['.$aText.']]';
					if (strpos($aText, '::')){
						$page->addElement(new POMProperty($aText));
						$aText = '';
					}else if (strpos($aText, ':')){
						$page->addElement(new POMCategory($aText));
						$aText = '';
					}else{
						// empty
					}

				} else {
					if ($braceCount > 0) {
						$markedText .= $part0;
						$aText .= $part0;
					} else {
						// we are not converting into objects anything else than
						// templates, annotations and simple text
						if($stStartPos == -1){
							$stStartPos = $pos;
						}
						$stText .= $part0;
					}
				}
				$pos += $len;
			}
		}
		if( $stStartPos != -1){
			// add the text node
			$page->addElement(new POMSimpleText($stText));
			// reset the simple text variables
			$stText = ''; // the node text
			$stStartPos = -1; // the starting position
			// ~add the text node
		}
		$part0 = mb_substr($wikiText, $pos, 1);
	}

	/**
	 * Some wiki text should not be augmented with wiki text offsets. This method
	 * defines the exceptions.
	 *
	 * @param string $wikiText
	 * 		The wiki text that will be examined
	 * @return boolean
	 * 		<true>, if the wiki text should be augmented with offsets
	 * 		<false> otherwise
	 */
	private function doAddWTO(&$wikiText) {

		if (strpos($wikiText, '[[#_note')) {
			// no wto for wiki text generated by <ref>-tags
			return false;
		}
		return true;
	}

	//--- Private methods ---

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
	 * - <noinclude>-sections
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
		// closing tag, replace tag (true) or only its content(false)
		array('<ask.*?>','<ask','<\/ask>','</ask>', false),
		array('<!--','<!--','-->','-->', true),
		array('<nowiki>','<nowiki>','<\/nowiki>','</nowiki>', false),
		array('<noinclude>','<noinclude>','<\/noinclude>','</noinclude>', false),
		array('<pre>','<pre>','<\/pre>','</pre>', false),
		array('<sup id="_ref.*?>','<sup id="_ref','<\/sup>','</sup>', true),
		array('<ref .*?>','<ref','<\/ref>','</ref>', true)
		);
		$numTags = count($tags);

		$regEx = '/';
		for ($i = 0; $i < $numTags; $i++) {
			$regEx .= '('.$tags[$i][0].')|('.$tags[$i][2].')';
			$regEx .= ($i == $numTags-1) ? '/sm' : '|';
		}
		$parts = preg_split($regEx, $wikiText, -1,
		PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		$text = "";
		$openingTag = -1;
		$maskLen = 0;
		foreach ($parts as $part) {
			$isOpeningTag = false;
			$isClosingTag = false;
			$tagIdx = -1;
			for ($i = 0; $i < $numTags; $i++) {
				$t = $tags[$i];
				if (strpos($part, $t[1]) === 0) {
					$isOpeningTag = true;
					$tagIdx = $i;
					break;
				} else if (strpos($part, $t[3]) === 0) {
					$isClosingTag = true;
					$tagIdx = $i;
					break;
				}
			}
			$maskLen += mb_strlen($part);

			if ($openingTag == -1 && $isOpeningTag) {
				// Special handling for <ref .../>
				if (preg_match('/^<ref .*?\/>$/', $part)) {
					$text .= str_repeat("*", mb_strlen($part));
				} else {
					$openingTag = $tagIdx;
					$maskLen = mb_strlen($part);
				}
			} else if ($openingTag >= 0 && $isClosingTag) {
				if ($openingTag == $tagIdx) {
					//The opening tag matches the closing tag
					if ($tags[$tagIdx][4] === false) {
						// do not mask the tag; only its content
						$t = $tags[$tagIdx];
						$maskLen -= strlen($t[1])+strlen($t[3]);
						$text .= $t[1].str_repeat("*", $maskLen).$t[3];
					} else {
						$text .= str_repeat("*", $maskLen);
					}
					$openingTag = -1;
				}
			} else if ($openingTag == -1) {
				// concatenate normal text
				$text .= $part;
			}
		}
		return $text;
	}

	/**
	 * Tries to parse a template.
	 *
	 * @param array<string> $tokens
	 * 		Array of pieces from the wiki text.
	 * @param int $startIndex
	 * 		Index where the template parsing starts in <$tokens>
	 * @param string currentlyParsing
	 * 		The parser is currently parsing a
	 * 		't' - template
	 * 		'p' - template parameter
	 * 		'e' - nothing special
	 *
	 * @return array If successful: ('t', templateName, templateStart, templateEnd, templateContent).
	 */
	private function parseTemplate(&$tokens, $startIndex, $currentlyParsing = 'e') {
		$numTokens = count($tokens);
		$i = $startIndex;
		$templateStart = -1;
		$templateEnd = -1;
		$templateContent = "";
		$templateName = "";
		$bracesDropped = false;
		$result = null;

		while ($i < $numTokens) {
			$token = $tokens[$i];
			$tmplDescr = $this->processTemplateToken($token, $currentlyParsing);

			switch ($tmplDescr[0]) {
				case 'c':
					// The template parser collects tokens
					if ($templateStart == -1) {
						// perhaps a template starts
						$templateStart = $i;
					}
					if ($i == $numTokens-1) {
						// last token has been read without finding something of
						// interest
						return array('a', $startIndex, $i);
					}
					++$i;
					break;
				case 't':
					// a template has started.
					// $tmplDescr[1] contains its name
					$templateName = $tmplDescr[1];
					$result = $this->parseTemplate($tokens, $i+1, 't');
					if ($result[0] == 't') {
						$end = $result[3]+1;
						$lookahead = ($end<$numTokens) ? $tokens[$end] : null;
						if ($bracesDropped && $lookahead == '}') {
							// the template is not a template
							$result = array('a', $startIndex, $result[3]);
						}
					}
					break;
				case 'et':
					// a template has ended.
					$templateEnd = $i;
					if ($currentlyParsing != 'e') {
						return array('t', $templateName, $templateStart, $templateEnd, "");
					}
					break;
				case 'p':
					// a template parameter has started
					$result = $this->parseTemplate($tokens, $i+1, 'p');
					break;
				case 'ep':
					// a template parameter has ended
					return array('a', $startIndex, $i);
				case 'db':
					// Drop one opening brace at the beginning. It does not belong
					// to a template.
					++$templateStart;
					++$i;
					$bracesDropped = true;
					break;
				case 'a':
					// the processed tokens are not relevant for templates
					if ($currentlyParsing == 'e') {
						return array('a', $startIndex, $i);
					}
					++$i;
					break;
				case 'at':
					// abort the current template candidate
					return array('a', $startIndex, $i);
			}
			if ($result) {
				// recursing deeper yielded a result
				// => determine the next position to continue
				if ($result[0] == 't') {
					$templateEnd = $result[3];
					$i = $result[3]+1;
				} else if ($result[0] == 'a') {
					$i = $result[2]+1;
				}
				$result = null;
				if ($currentlyParsing == 'e') {
					// we are on the top level
					// => stop parsing
					break;
				}
			}
		}
		if ($templateStart > -1 && $templateEnd > -1) {

			for ($j = $templateStart; $j <= $templateEnd; ++$j) {
				$templateContent .= $tokens[$j];
			}
			return array('t', $templateName, $templateStart, $templateEnd, $templateContent);
		}
		return array('a', $startIndex, $i);
	}

	/**
	 * Templates are parsed with a state machine. The parser is only interested
	 * in top level templates, however, they need to be parsed thoroughly to find
	 * their end correctly.
	 *
	 * @param string $token
	 * 		The next piece of text to examine
	 * @return array(string,string)
	 * 		 <null>, if the template parser is still collecting tokens or
	 * 		 an array with three values:
	 * 			0 - Type of result:
	 * 				t - start of template found
	 * 				at - abort current template candidate
	 * 				p - start of parameter found
	 * 				c - collecting tokens
	 * 				db - drop one opening brace
	 * 				a - append returned token to wiki text
	 * 				et - end of template found
	 * 				ep - end of parameter found
	 * 			1 - Name of a template or <null> if no template was found
	 * 			2 - the content of the template or the wiki text that was parsed
	 * 				or <null> if the parser is collecting tokens
	 *
	 */
	private function processTemplateToken(&$token, $currentlyParsing) {
		$nextStates = $this->mStateMachine[$this->mTpState];
		$nextState = $this->findNextState($token, $nextStates, $currentlyParsing);
		$action = $nextState[3];

		if (gettype($action) === 'integer' ) {
			// go to the next state
			$this->mTpState = $action;
			return array('c');
		} else {
			// perform some action

			// go to state 0, default
			$this->mTpState = 0;
			if (isset($nextState[4])) {
				$this->mTpState = $nextState[4];
			}
			switch ($action) {
				case 'at':
					// abort parsing this template canditate
					return array('at');
				case 'wc':
					// return content, reset parser
					return array('a');
				case 'pt':
					// start of template found
					// determine the template name
					$tn = trim($token);
					if (!$tn) {
						$tn = 'Unknown template';
					}
					return array('t', $tn);
				case 'pp':
					// start of template parameter found
					return array('p');
				case 'pop':
					// end of template (parameter) found
					return array($currentlyParsing == 't' ? 'et' : 'ep');
				case 'db':
					// drop one opening brace at the beginning, go to state in field 4
					return array('db');
			}
		}
	}

	/**
	 * Determines the next state of the template parser by a given token and
	 * a set of possible next states.
	 *
	 * @param string $token
	 * 			The next piece of text to examine
	 * @param array $nextStates
	 * 			Array of possible next states of the state machine.
	 *
	 * @return The matching state or <null>.
	 */
	private function findNextState(&$token, &$nextStates, $currentlyParsing) {

		$__pageType = $this->_page->getNamespace() == 'Template'
		? 't' : 'n';

		foreach ($nextStates as $state) {
			if (($state[0] == '*' || $state[0] == $currentlyParsing) &&
			($state[1] == '*' || $state[1] == $token) &&
			($state[2] == '*' || $state[2] == $__pageType)) {
				return $state;
			}
		}
		return null;

	}

	/**
	 * Checks if the content of an annotation (e.g. [[annotation]]) should be
	 * ignored for highlighting.
	 * This is used to define exceptions to the normal highlighting of internal
	 * wiki links.
	 *
	 * @param string $annotation
	 * 		The annotation to examine.
	 * @return boolean
	 * 		<true>, if the annotation should be ignored
	 * 		<false>, otherwise
	 *
	 */
	private function checkIgnoreAnnotation(&$annotation) {

		// Do not highlight annotations generated by <ref>...</ref>
		if (strpos($annotation, '#_note-') === 2) {
			return true;
		}

		// TODO: remove global var
		global $wgContLang;
		// Do not highlight Image and Media links.
		if (strpos($annotation, $wgContLang->getNsText(NS_IMAGE).":") == 2
		|| strpos($annotation, $wgContLang->getNsText(NS_MEDIA).":") == 2) {
			return true;
		}

		return false;
	}

}
