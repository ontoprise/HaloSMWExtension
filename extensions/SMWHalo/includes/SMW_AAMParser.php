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


//--- Public methods ---

	/**
	 * Adds offset information for some wiki text elements to the generated HTML.
	 * With these offsets it is possible to map from the rendered HTML to the
	 * corresponding location in the wiki text.
	 * These elements are indexed:
	 * 1. Headings of levels 1,2,3 and 4 e.g. ==Heading 2==
	 * 2. Templates e.g. {{MyTemplate}}
	 * 3. Template parameters e.g. {{{tparam}}} 
	 * 4. Line breaks
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
		$parts = preg_split('/(\{\{\{.*?\}\}\})|(\{\{.*?\}\})|^(====.*?====)|^(===.*?===)|^(==.*?==)|^(=.*?=)|^$/sm', $text, -1, 
		                    PREG_SPLIT_DELIM_CAPTURE |
		                    PREG_SPLIT_OFFSET_CAPTURE |
		                    PREG_SPLIT_NO_EMPTY);
		$markedText = "";
		
		$id = 1;
		$pos = 0;
		foreach ($parts as $part) {
			$len = mb_strlen($part[0], "UTF-8");
			$part0 = mb_substr($wikiText, $pos, $len, "UTF-8");
			// Is the part a template?
 			if (preg_match("/^\s*\{\{[^{].*?[^}]\}\}$/s",$part0)) {
				preg_match("/\{\{\s*(.*?)\s*[\|\}]/", $part0, $name);
				$markedText .=  "\n".'{wikiTextOffset='.$pos
				               .' template="'.$name[1].'"'
			                   .' id="tmplt'.$id.'"}'."\n".$part0
				               ."\n".'{templateend:tmplt'.$id.'}'."\n";
				$id++;
			} else {
				$markedText .= "\n{wikiTextOffset=".$pos."}\n".$part0;
			}
			$pos += $len;
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
			
		// replace intermediate format within paragraphs
		$text = preg_replace('/<p>(<br \/>)?\s*\{wikiTextOffset=(\d*)}\s*<\/p>/m',
		                     '$1<a name="$2" type="wikiTextOffset"></a>',
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
		$text = preg_replace('/(\[\[.*?\]\])/','{linkstart}$1{linkend}', $wikiText);
		$text = preg_replace('/{linkstart}(\[\[.*?(::|:=).*?\]\]){linkend}/',
		                     '{annostart}$1{annoend}', $text);
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
		// add intermediate tags to annotations
		$text = preg_replace('/{annostart}(.*?){annoend}/',
							 '<a href="javascript:smwhfEditAnno()">'.
		           			 '<img src="'. $smwgHaloScriptPath . '/skins/edit.gif"/></a>'.
							 '<span class="aam_prop_highlight">$1</span>',
		                     $wikiText);
		$text = preg_replace('/{linkstart}(.*?){linkend}/',
							 '<a href="javascript:smwhfEditLink()">'.
		           			 '<img src="'. $smwgHaloScriptPath . '/skins/edit.gif"/></a>'.
		                     '<span class="aam_existing_prop_highlight">$1</span>',
		                     $text);
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
	
}

?>
