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
 * Parser for advanced annotation mode.
 * This parser augments the generated HTML in order to create mapping
 * from HTML to the original wiki text on the client side.
 * 
 * @author Thomas Schweitzer
 */
class SMWH_AAMParser {

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
			array('*','*','*','wc'),
		),
		
		// state 1
		array(
			array('*','{','*',2),
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
			array('*','[[','*','at'),
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
	

//--- Public methods ---

	/**
	 * Adds offset information for some wiki text elements to the generated HTML.
	 * With these offsets it is possible to map from the rendered HTML to the
	 * corresponding location in the wiki text.
	 * These elements are indexed:
	 * 1. Headings of levels 1,2,3,4 and 5 e.g. ==Heading 2==
	 * 2. Templates e.g. {{MyTemplate}}
	 * 3. Template parameters e.g. {{{tparam}}} 
	 * 4. Line breaks
	 * 5. Links and annotations starting with double brackets
	 * 
	 * Offsets have the following format: {wikiTextOffset=offset}
	 * The correct HTML is created in a later parsing stage. See wikiTextOffset2HTML.
	 *
	 * @param string $wikiText The original wiki text.
	 * @return string Marked wiki text.
	 */
	public function addWikiTextOffsets(&$wikiText)	{
		// Treat HTML comments and the like. They may contain dangerous material.
		$text = $this->maskHTML($wikiText);
		
		// Search for templates, template parameters and headings
		$parts = preg_split('/(\{)|'.
		                    '(\})|'.
							'(\n)|'.
		                    '(^======.*?======\s*)|'.
							'(^=====.*?=====\s*)|'.
		                    '(^====.*?====\s*)|'.
		                    '(^===.*?===\s*)|'.
		                    '(^==.*?==\s*)|'.
		                    '(^=.*?=\s*)|'.
							'(\[+)|'.
							'(\]\])|'.
							'(\|)|'.
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
		$numParts = count($parts);
		for ($i = 0; $i < $numParts; ++$i) {
			$part = $parts[$i];
			$len = mb_strlen($part, "UTF-8");
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
				// a template has been found. 
				// $tmplDescr[1]: name of the template
				// $tmplDescr[2]: start index of the template
				// $tmplDescr[3]: end index of the template
				// $tmplDescr[4]: content
				$markedText .= "\n".'{wikiTextOffset='.$pos
				               .' template="'.$tmplDescr[1].'"'
			                   .' id="tmplt'.$id.'"}'."\n".$tmplDescr[4]
				               ."\n".'{templateend:tmplt'.$id.'}'."\n";
				$id++;
				$templateStart = -1;
				// The parse can continue after the template
				$i = $tmplDescr[3];
				$pos += mb_strlen($tmplDescr[4], "UTF-8");
				
			} else {
				// parser is not collecting tokens for a template
				if ($part0 == '[[') {
					if ($braceCount == 0) {
						$markedText .= "{wikiTextOffset=".$pos."}".$part0;
					} else {
						$markedText .= $part0;
					}
					$braceCount++;
				} else if ($part0 == ']]') {
					if ($braceCount > 0) {
						$braceCount--;
					}
					$markedText .= $part0;
				} else {
					if ($braceCount > 0) {
						$markedText .= $part0;
					} else {
						if ($part0{0} == '=' 
						    || $part0{0} == "\n"
						    || $part0{0} == "*"
						    || $part0{0} == "#") {
							// title, empty line or enumeration found
							$markedText .= "{wikiTextOffset=".$pos."}\n".$part0;
						} else {
							$wto = "";
							$ignoreToken = ($part0{0} == '{') 
							               || ($part0{0} == '}')
							               || ($part0{0} == '|');
							if (!$ignoreToken && !$ignoredLastToken) {
								// write the wiki text offset only, if there are
								// no braces at the beginning or end
								$wto = "{wikiTextOffset=".$pos."}";	
							}
							$markedText .= $wto.$part0;
							$ignoredLastToken = $ignoreToken;
						}
					}
				}
				$pos += $len;
			}
		}
		$part0 = mb_substr($wikiText, $pos, 1);
		$markedText .= $part0."\n{wikiTextOffset=".$pos."}\n";
				
		return $markedText;
	}
	
	/**
	 * Replaces the intermediate format of the wiki text offset by the correct
	 * HTML representation: <a name="offset" type="wikiTextOffset"></a> 
	 *
	 * @param unknown_type $wikiText
	 * @return unknown
	 */
	public function wikiTextOffset2HTML(&$wikiText)
	{
		global $wgContLang;
		
		$templateNS = $wgContLang->getNsText(10);
		// replace intermediate format for templates
		$text = preg_replace(
			'/(<p><br \/>\s*(<\/p>)?)?(<p>)?\s*\{wikiTextOffset=(\d*) template=\"(.*?)\" id=\"(.*?)\"\}\s*(<\/p>)?/',
			'<a name="$4" type="wikiTextOffset"></a>'.
			'<a type="template" tmplname="'.$templateNS.':$5" id="$6"></a>',
			$wikiText);
		$text = preg_replace('/(<p><br \/>\s*(<\/p>)?)?(<p>)?\s*\{templateend:(.*?)\}\s*(<\/p>)?/',
		                     '<a type="templateend" id="$4_end"></a>', 
		                     $text);
			
		// replace standalone occurrences of intermediate format
		$text = preg_replace('/\{wikiTextOffset=(\d*)}/',
		                     '<a name="$1" type="wikiTextOffset"></a>',
		                     $text);
        return $text;
	}
	
	/**
	 * Creates a highlighted background for annotations. 
	 * 
	 * In this first stage annotations are surrounded by intermediate tags.
	 *
	 * @param string $wikiText
	 */
	public function highlightAnnotations(&$wikiText)
	{
		// add intermediate tags to annotations
		$parts = preg_split('/(\[\[)|(::)|(:=)|(\]\])/sm', $wikiText, -1, 
		                    PREG_SPLIT_DELIM_CAPTURE |
		                    PREG_SPLIT_NO_EMPTY);
		
		$braceCount = 0;
		$isLink = true;
		$braceContent = "";
		$text = "";
		$count = 0;                    
        foreach ($parts as $part) {
        	switch ($part) {
        		case '[[':
        			$braceCount++;
        			$braceContent .= $part;
        			if ($braceCount == 1) {
        				$isLink = true;
        			}
        			break;
        		case ':=':
        		case '::':
        			if ($braceCount == 0) {
        				$text .= $part;
        			} else {
        				if ($braceCount == 1) {
        					$isLink = false;
	        			}
        				$braceContent .= $part;
        			}
        			break;
        		case ']]':
        			--$braceCount;
       				$braceContent .= $part;
       				$short = strlen($braceContent) < 41;
        			if ($braceCount == 0) {
        				if ($isLink) {
        					$text .= ($short) 
        								? '{shortlinkstart'.++$count.'}'.$braceContent.'{shortlinkend}'
        								: '{linkstart'.++$count.'}'.$braceContent.'{linkend}';
        				} else {
        					$text .= ($short) 
        								? '{shortannostart'.++$count.'}'.$braceContent.'{shortannoend}'
        								: '{annostart'.++$count.'}'.$braceContent.'{annoend}';
        				}
	        			$braceContent = "";
        			}
        			if ($braceCount < 0) {
        				// this should never occur (malformed wiki text)
        				$braceCount = 0;
        				$text .= $braceContent;
	        			$braceContent = "";
        			}
        			break;
        		default:
        			if ($braceCount == 0) {
        				$text .= $part;
        			} else {
        				$braceContent .= $part;
        			}
        	}
        }
		return $text;
	}
		
	/**
	 * Creates a highlighted background for annotations. 
	 * 
	 * In this second stage the intermediate tags are replaced by HTML spans.
	 *
	 * @param string $wikiText
	 */
	public function highlightAnnotations2HTML(&$wikiText)
	{
		global $smwgHaloScriptPath;
		$annoDeco =
			'<a href="javascript:AdvancedAnnotation.smwhfEditAnno($1)">'.
			'<img src="'. $smwgHaloScriptPath . '/skins/edit.gif"/></a>'.
			'<span id="anno$1" class="aam_prop_highlight">$2</span>'.
			'<a href="javascript:AdvancedAnnotation.smwhfDeleteAnno($1)">'.
   			'<img src="'. $smwgHaloScriptPath . '/skins/Annotation/images/delete.png"/></a>';
		$shortAnnoDeco = // wrapper span with no line breaks
			'<span id="anno$1w" style="white-space:nowrap">'.
			$annoDeco.
			 '</span>';
		$annoDeco =  // wrapper span
			'<span id="anno$1w">'.
			$annoDeco.
			 '</span>';
		$linkDeco =
			 '<a href="javascript:AdvancedAnnotation.smwhfEditLink($1)">'.
   			 '<img src="'. $smwgHaloScriptPath . '/skins/Annotation/images/add.png"/></a>'.
             '<span id="anno$1" class="aam_page_link_highlight">$2</span>';
		$shortLinkDeco = // wrapper span with no line breaks
			'<span id="anno$1w" style="white-space:nowrap">'.
			$linkDeco.
			'</span>';
		$linkDeco =  // wrapper span
			'<span id="anno$1w">'.
			$linkDeco.
			'</span>';
			
		// decorate annotations
		$text = preg_replace('/{annostart(\d*)}(.*?){annoend}/sm', $annoDeco,
							 $wikiText);
		$text = preg_replace('/{shortannostart(\d*)}(.*?){shortannoend}/sm',
							 $shortAnnoDeco, $text);
		
		// ignore empty links
		$text = preg_replace('/{shortlinkstart(\d*)}(\s*){shortlinkend}/sm','', $text);
		$text = preg_replace('/{linkstart(\d*)}(\s*){linkend}/sm','', $text);
		
		// decorate links
		$text = preg_replace('/{shortlinkstart(\d*)}(.*?){shortlinkend}/sm',
		                     $shortLinkDeco, $text);
		$text = preg_replace('/{linkstart(\d*)}(.*?){linkend}/sm', $linkDeco, $text);
		return $text;
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
	 * - <ask>-sections
	 * - <pre>-sections
	 *
	 * @param string $wikiText
	 * @return string masked wiki text
	 */
	private function maskHTML(&$wikiText)
	{
		$parts = preg_split('/(<!--)|(-->)|(<nowiki>)|(<\/nowiki>)|(<ask.*?>)|(<\/ask>)|(<pre>)|(<\/pre>)/sm', 
		                    $wikiText, -1, 
		                    PREG_SPLIT_DELIM_CAPTURE |
		                    PREG_SPLIT_NO_EMPTY);

		$text = "";
		$openingTag = null;
		$maskLen = 0;
		foreach ($parts as $part) {
			$isTag = ($part == "<!--") ||
			         ($part == "-->") ||
			         ($part == "<nowiki>") ||
			         ($part == "</nowiki>") ||
			         ($part == "<pre>") ||
			         ($part == "</pre>") ||
			         (strpos($part, '<ask') === 0) ||
			         ($part == "</ask>");
			$maskLen += mb_strlen($part);
			if ($isTag) {
				if (!$openingTag) {
					$openingTag = $part;
					$maskLen = mb_strlen($part);
				} else {
					$comment = ($openingTag == '<!--') && ($part == '-->');
					$nowiki = ($openingTag == '<nowiki>') && ($part == '</nowiki>');
					$pre = ($openingTag == '<pre>') && ($part == '</pre>');
					$ask = (strpos($openingTag, '<ask') === 0) && ($part == '</ask>');
					if ($comment || $nowiki || $ask || $pre) {
						//The opening tag matches the closing tag
					    $text .= str_repeat("*", $maskLen);
					    $openingTag = null;
					}
				}
			} else if (!$openingTag) {
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
		global $wgTitle;
		
		$pageType = $wgTitle->getNamespace() == NS_TEMPLATE
						? 't' : 'n';
		
		foreach ($nextStates as $state) {
			if (($state[0] === '*' || $state[0] == $currentlyParsing) &&
				($state[1] === '*' || $state[1] == $token) &&
				($state[2] === '*' || $state[2] == $pageType)) {
				return $state;
				}
		}
		return null;
		
	}
	
}

?>
