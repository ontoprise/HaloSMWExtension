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


/**
 * 
 */
class SMWH_AAMParser {

//--- Fields ---

	/** Contains error messages if the parsed expression is not correct. */
	private $mError = "";
	

//--- Public methods ---

	/**
	 * Adds offset information for some wiki text elements to the generated HTML.
	 * With these offsets it is possible to map from the rendered HTML to the
	 * corresponding location in the wiki text.
	 * These elements are indexed:
	 * 1. Headings of levels 1,2 and 3 e.g. ==Heading 2==
	 * 2. Templates e.g. {{MyTemplate}}
	 * 3. Template parameters e.g. {{{tparam}}} 
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
		$parts = preg_split('/(\{\{\{.*?\}\}\})|(\{\{.*?\}\})|^(====.*?====)|^(===.*?===)|^(==.*?==)|^(=.*?=)/sm', $text, -1, 
		                    PREG_SPLIT_DELIM_CAPTURE |
		                    PREG_SPLIT_OFFSET_CAPTURE |
		                    PREG_SPLIT_NO_EMPTY);
		$markedText = "";

		$id = 1;
		foreach ($parts as $part) {
			$part0 = substr($wikiText, $part[1], strlen($part[0]));
			// Is the part a template?
			if (preg_match("/^\s*\{\{[^{].*?[^}]\}\}$/s",$part0)) {
				preg_match("/\{\{\s*(.*?)\s*[\|\}]/", $part0, $name);
				$markedText .=  "\n".'{wikiTextOffset='.$part[1]
				               .' template="'.$name[1].'"'
			                   .' id="tmplt'.$id.'"}'."\n".$part0
				               ."\n".'{templateend}'."\n";
				$id++;
			} else {
				$markedText .= "\n{wikiTextOffset=".$part[1]."}\n".$part0;
			}
		}

		return $markedText;
	}
	
	/**
	 * Replaces the intermediate format of the wiki text offset by the correct
	 * HTML representation: <a name="offset"></a> 
	 *
	 * @param unknown_type $wikiText
	 * @return unknown
	 */
	public function wikiTextOffset2HTML(&$wikiText)
	{

		// replace intermediate format for templates
		$text = preg_replace(
			'/(<p><br \/>\s*(<\/p>)?)?(<p>)?\s*\{wikiTextOffset=(\d*) template=\"(.*?)\" id=\"(.*?)\"\}\s*(<\/p>)?/',
			'<span type="template" tmplname="$5" id="$6" class="aam_template_highlight"><a name="$4"></a>',
			$wikiText);
		$text = preg_replace('/(<p><br \/>\s*(<\/p>)?)?(<p>)?\s*\{templateend\}\s*(<\/p>)?/', '</span>', $text);
			
		// replace intermediate format within paragraphs
		$text = preg_replace('/<p>(<br \/>)?\s*\{wikiTextOffset=(\d*)}\s*<\/p>/m',
		                     '$1<a name="$2"></a>',
							 $text);
		// replace standalone occurrences of intermediate format
		$text = preg_replace('/\{wikiTextOffset=(\d*)}/',
		                     '<a name="$1"></a>',
		                     $text);
        return $text;
	}
	
	/**
	 * Creates a highlighted background for annotations. 
	 * 
	 * In this first stage annotations are surrounded by intermediate tags.
	 *
	 * @param unknown_type $wikiText
	 */
	public function highlightAnnotations(&$wikiText)
	{
		// add intermediate tags to annotations
		$text = preg_replace('/(\[\[.*?\]\])/','{annostart}$1{annoend}', $wikiText);
		return $text;
	}
		
	/**
	 * Creates a highlighted background for annotations. 
	 * 
	 * In this second stage the intermediate tags are replaces by HTML spans.
	 *
	 * @param unknown_type $wikiText
	 */
	public function highlightAnnotations2HTML(&$wikiText)
	{
		// add intermediate tags to annotations
		$text = preg_replace('/{annostart}(.*?){annoend}/',
		                     '<span class="aam_prop_highlight">$1</span>',
		                     $wikiText);
		return $text;
	}
		
	//--- Private methods ---

	/**
	 * The wiki text <$wikiText> may contain dangerous HTML code, that will
	 * confuse the parser that generates wiki text offsets 
	 * (see addWikiTextOffsets). All dangerous text is replaced by * to
	 * keep the number of characters the same.
	 *
	 * @param string $wikiText
	 * @return string masked wiki text
	 */
	private function maskHTML(&$wikiText)
	{
		if (!preg_match_all("/<!--|-->|<nowiki>|<\/nowiki>/",
		                    $wikiText,$matches,PREG_OFFSET_CAPTURE)) {
			return $wikiText;
		}
		
		$matches = $matches[0];
		$text = "";
		$openingTag = null;
		$startPos = 0;
		$endPos = 0;
		foreach ($matches as $match) {
			if (!$openingTag) {
				$openingTag = $match[0];
				$startPos = $match[1];
				$text .= substr($wikiText, $endPos, $startPos-$endPos);
				$endPos = $startPos + strlen($match[0]);
			} else {
				if (($openingTag == '<!--' && $match[0] == '-->') ||
				    ($openingTag == '<nowiki>' && $match[0] == '</nowiki>')) {
					//The opening tag matches the closing tag
				    $endPos = $match[1] + strlen($match[0]);
				    $text .= str_repeat("*", $endPos-$startPos);
				    $openingTag = null;
				}
			}
		}
		$text .= substr($wikiText, $endPos, strlen($wikiText)-$endPos);
		return $text;
	}
	
}

?>
