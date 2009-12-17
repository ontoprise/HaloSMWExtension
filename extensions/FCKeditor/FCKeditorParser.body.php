<?php

class FCKeditorParser extends Parser
{
	public static $fkc_mw_makeImage_options;
	protected $fck_mw_strtr_span;
	protected $fck_mw_strtr_span_counter=1;
	protected $fck_mw_taghook;
	protected $fck_internal_parse_text;
	protected $fck_matches = array();
	protected $fck_mw_propertyAtPage= array();
	protected $fck_mw_richmediaLinkAtPage = array();
	private $fck_allTags_currentTagReplaced = '';

    // list here all standard wiki tags that are supported by Mediawiki
    private $FCKeditorWikiTags = array(
       "nowiki",
       "includeonly",
       "onlyinclude",
       "noinclude",
       "ask" // old tag that is still supported in smw
    );
	private $FCKeditorMagicWords = array(
       "NOTOC",
       "FORCETOC",
       "TOC",
       "NOEDITSECTION",
       "NEWSECTIONLINK",
       "NONEWSECTIONLINK", // MW 1.15+
       "NOCONTENTCONVERT",
       "NOCC",
       "NOTITLECONVERT",
       "NOTC",
       "INDEX", // MW 1.14+
       "NOINDEX", // MW 1.14+
       "STATICREDIRECT", // MW 1.14+
       "NOGALLERY",
       "HIDDENCAT"
	);
    private $FCKeditorDateTimeVariables= array(
       'CURRENTYEAR', 
       'CURRENTMONTH',
       'CURRENTMONTHNAME',
       'CURRENTMONTHNAMEGEN',
       'CURRENTMONTHABBREV',
       'CURRENTDAY',
       'CURRENTDAY2',
       'CURRENTDOW',
       'CURRENTDAYNAME',
       'CURRENTTIME',
       'CURRENTHOUR',
       'CURRENTWEEK',
       'CURRENTTIMESTAMP'
    );
    private $FCKeditorWikiVariables = array(
       'SITENAME',
       'SERVER',
       'SERVERNAME',
       'DIRMARK',
       'SCRIPTPATH',
       'CURRENTVERSION',
       'CONTENTLANG',
       'REVISIONID',
       'REVISIONDAY',
       'REVISIONDAY2',
       'REVISIONMONTH',
       'REVISIONYEAR',
       'REVISIONTIMESTAMP',
       'REVISIONUSER', // MW 1.15+
       'FULLPAGENAME',
       'PAGENAME',
       'BASEPAGENAME',
       'SUBPAGENAME',
       'SUBJECTPAGENAME',
       'TALKPAGENAME',
       'NAMESPACE',
       'ARTICLESPACE',
       'TALKSPACE'
    );
    private $FCKeditorFunctionHooks = array(
        'lc',
        'lcfirst',
        'uc',
        'ucfirst',
        'formatnum',
        '#dateformat', // MW 1.15+
        'padleft',
        'padright',
        'plural',
        'grammar',
        '#language',
        'int',
        '#tag',
    );

    public function __construct() {
        global $wgParser;
        parent::__construct();

        // add custom tags from extensions to list
        foreach ($wgParser->getTags() as $h) {
            if (! in_array($h, $this->FCKeditorWikiTags))
                $this->FCKeditorWikiTags[] = $h;
    	}
        // add custom parser funtions from extensions to list
        foreach ($wgParser->getFunctionHooks() as $h) {
            // ask and sparql + ws are no special tags and have there own <span> elements in FCK
            if (!in_array($h, array("ask", "sparql", "ws")) &&
                !in_array($h, $this->FCKeditorFunctionHooks))
                $this->FCKeditorFunctionHooks[] = '#'.$h;
        }
    }

    public function getSpecialTags() {
        return $this->FCKeditorWikiTags;
    }
    public function getMagicWords() {
        return $this->FCKeditorMagicWords;
    }
    public function getDateTimeVariables() {
        return $this->FCKeditorDateTimeVariables;
    }
    public function getWikiVariables() {
        return $this->FCKeditorWikiVariables;
    }
    public function getFunctionHooks() {
        return $this->FCKeditorFunctionHooks;
    }
	
    /**
     * Add special string (that would be changed by Parser) to array and return
     * a simple unique string that will remain unchanged during whole parsing
     * operation. At the end we'll replace all this unique strings with
     * the original content.
     * $text contains the new FCK format to display in WYSIWYG mode
     * $inner contains the unchanged wiki text, necessary when expressions
     * are used in annotations.
     * The member variable fck_mw_strtr_span is filled with:
     *  'key' = wysiwsg text
     *  'href="key"' = 'href="wikitext"'
     * When later the replacement is done, the wysiwyg text is used, except
     * when the key appears in an annotated content, then we need the orignal
     * wiki text, because the argument is not a seperate element that can
     * be edited.
     *
     * @param string $text for wysiwyg mode
     * @param string $inner original wiki text
     * @param bool $replaceLineBreaks optional default is true
     * @return string key of the replaced text i.e. Fckmw12fckmw
     */
        private function fck_addToStrtr($text, $inner, $replaceLineBreaks = true) {
		$key = 'Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw';
		$this->fck_mw_strtr_span_counter++;
		if ($replaceLineBreaks) {
			$this->fck_mw_strtr_span[$key] = str_replace(array("\r\n", "\n", "\r"),"fckLR",$text);
		}
		else {
			$this->fck_mw_strtr_span[$key] = $text;
		}
                $this->fck_mw_strtr_span['href="'.$key.'"'] = 'href="'.$inner.'"';
		return $key;
	}

	/**
	 * Handle link to subpage if necessary
	 * @param string $target the source of the link
	 * @param string &$text the link text, modified as necessary
	 * @return string the full name of the link
	 * @private
	 */
	function maybeDoSubpageLink($target, &$text) {
		return $target;
	}
	
	/**
	* Callback function for wiki tags: nowiki, includeonly, noinclude
	*
	* @param string $tagName tag name, eg. nowiki, math
	* @param string $str Input
	* @param array $argv Arguments
	* @return string
	*/
	function fck_wikiTag( $tagName, $str, $argv = array()) {
	    $class = in_array($tagName, array("nowiki", "includeonly", "onlyinclude", "noinclude", "gallery"))
               ? $tagName
               : "special";
		if (empty($argv))
			$ret = "<span class=\"fck_mw_".$class."\" _fck_mw_customtag=\"true\" _fck_mw_tagname=\"".$tagName."\" _fck_mw_tagtype=\"t\">";
		else {
			$ret = "<span class=\"fck_mw_".$class."\" _fck_mw_customtag=\"true\" _fck_mw_tagname=\"".$tagName."\" _fck_mw_tagtype=\"t\"";
			foreach ($argv as $key=>$value) {
				$ret .= " ".$key."=\"".$value."\"";
			}
			$ret .=">";
		}
		if (is_null($str))
			$ret = substr($ret, 0, -1) . " />";
		else
			$ret .= htmlspecialchars($str)."</span>";

		$replacement = $this->fck_addToStrtr($ret, '<'.$tagName.'>');

		return $replacement;
	}

	/** Replace HTML comments with unique text using fck_addToStrtr function
	 *
	 * @private
	 * @param string $text
	 * @return string
	 */
	private function fck_replaceHTMLcomments( $text ) {
		wfProfileIn( __METHOD__ );
		while (($start = strpos($text, '<!--')) !== false) {
			$end = strpos($text, '-->', $start + 4);
			if ($end === false) {
				# Unterminated comment; bail out
				break;
			}

			$end += 3;

			# Trim space and newline if the comment is both
			# preceded and followed by a newline
			$spaceStart = max($start - 1, 0);
			$spaceLen = $end - $spaceStart;
			while (substr($text, $spaceStart, 1) === ' ' && $spaceStart > 0) {
				$spaceStart--;
				$spaceLen++;
			}
			while (substr($text, $spaceStart + $spaceLen, 1) === ' ')
			$spaceLen++;
			if (substr($text, $spaceStart, 1) === "\n" and substr($text, $spaceStart + $spaceLen, 1) === "\n") {
				# Remove the comment, leading and trailing
				# spaces, and leave only one newline.
				$replacement = $this->fck_addToStrtr(substr($text, $spaceStart, $spaceLen+1), '', false);
				$text = substr_replace($text, $replacement."\n", $spaceStart, $spaceLen + 1);
			}
			else {
				# Remove just the comment.
				$replacement = $this->fck_addToStrtr(substr($text, $start, $end - $start), '', false);
				$text = substr_replace($text, $replacement, $start, $end - $start);
			}
		}
		wfProfileOut( __METHOD__ );

		return $text;
	}

	function replaceInternalLinks( $text ) {
		return parent::replaceInternalLinks($text);
	}

	function makeImage( $nt, $options ) {
		FCKeditorParser::$fkc_mw_makeImage_options = $options;
		return parent::makeImage( $nt, $options );
	}

	/**
	 * Replace templates with unique text to preserve them from parsing
	 *
	 * @todo if {{template}} is inside string that also must be returned unparsed, 
	 * e.g. <noinclude>{{template}}</noinclude>
	 * {{template}} replaced with Fckmw[n]fckmw which is wrong...
	 * 
	 * @param string $text
	 * @return string
	 */
	private function fck_replaceTemplates( $text ) {

		$callback = array('{' =>
		array(
		'end'=>'}',
		'cb' => array(
		2=>array('FCKeditorParser', 'fck_leaveTemplatesAlone'),
		3=>array('FCKeditorParser', 'fck_leaveTemplatesAlone'),
		),
		'min' =>2,
		'max' =>3,
		)
		);

		$text = $this->replace_callback($text, $callback);

		$tags = array();
		$offset=0;
		$textTmp = $text;
		while (false !== ($pos = strpos($textTmp, "<!--FCK_SKIP_START-->"))) {
			$tags[abs($pos + $offset)] = 1;
			$textTmp = substr($textTmp, $pos+21);
			$offset += $pos + 21;
		}

		$offset=0;
		$textTmp = $text;
		while (false !== ($pos = strpos($textTmp, "<!--FCK_SKIP_END-->"))) {
			$tags[abs($pos + $offset)] = -1;
			$textTmp = substr($textTmp, $pos+19);
			$offset += $pos + 19;
		}

		if (!empty($tags)) {
			ksort($tags);

			$strtr = array("<!--FCK_SKIP_START-->" => "", "<!--FCK_SKIP_END-->" => "");

			$sum=0;
			$lastSum=0;
			$finalString = "";
			$stringToParse = "";
			$startingPos = 0;
			$inner = "";
			$strtr_span = array();
			foreach ($tags as $pos=>$type) {
				$sum += $type;
				if ($sum == 1 && $lastSum == 0) {
					$stringToParse .= strtr(substr($text, $startingPos, $pos - $startingPos), $strtr);
					$startingPos = $pos;
				}
				else if ($sum == 0) {
					$stringToParse .= 'Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw';
					$inner = htmlspecialchars(strtr(substr($text, $startingPos, $pos - $startingPos + 19), $strtr));
                    $inner = $this->revertEncapsulatedString($inner);
                    if (substr($inner, 0, 7) == '{{#ask:' || substr($inner, 0, 10) == '{{#sparql:') 
                        $fck_mw_template =  'fck_mw_askquery';
                    else if (substr($inner, 0, 6) == '{{#ws:' )
					    $fck_mw_template =  'fck_mw_webservice';
                    else {
                        $funcName = (($fp = strpos($inner, ':', 2)) !== false) ? substr($inner, 2, $fp - 2) : substr($inner, 2, strlen($inner) - 4);
                        if (in_array($funcName, $this->FCKeditorDateTimeVariables))
                            $fck_mw_template = 'v';
                        else if (in_array($funcName, $this->FCKeditorWikiVariables))
                            $fck_mw_template = 'w';
                        else if (in_array($funcName, $this->FCKeditorFunctionHooks))
                            $fck_mw_template = 'p';
                        else
                            $fck_mw_template = 'fck_mw_template';
                    }
                    if (strlen($fck_mw_template) > 1) {
                        $this->fck_mw_strtr_span['Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw'] = '<span class="'.$fck_mw_template.'">'.str_replace(array("\r\n", "\n", "\r"),"fckLR",$inner).'</span>';
                    } else {
                        $this->fck_mw_strtr_span['Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw'] = '<span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="'.$funcName.'" _fck_mw_tagtype="'.$fck_mw_template.'"';
                        if (strlen($inner) > strlen($funcName) + 5) {
                            $content = substr($inner, strlen($funcName) + 3, -2);
                            $this->fck_mw_strtr_span['Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw'].= '>'.$content.'</span>';
                        }
                        else $this->fck_mw_strtr_span['Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw'].= '/>';
                    }
                    $this->fck_mw_strtr_span['href="Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw"'] = 'href="'.$inner.'"';

                    $startingPos = $pos + 19;
                    $this->fck_mw_strtr_span_counter++;
                }
                $lastSum = $sum;
            }
            $stringToParse .= substr($text, $startingPos);
            $text = &$stringToParse;
        }
        return $text;
	}

	/**
	 * replace property links like [[someProperty::value]] with FCK_PROPERTY_X_FOUND where X is
	 * the number of the replaced property. The actual property string is stored in
	 * $this->fck_mw_propertyAtPage[X] with X being the same number as in the replaced text.
	 * This affects also links that are created with the RichMedia extension like:
	 * [[Document:My_Document.doc|Document:My Document.doc]]. These are replaced with
	 * FCK_RICHMEDIA_X_FOUND where X is the number of the replaced link. The actual link
	 * value is stored in $this->fck_mw_richmediaLinkAtPage[X].
	 * 
	 * @access private
	 * @param string $wikitext
	 * @return string $wikitext
	 */
	private function fck_replaceSpecialLinks( $text ) {
		// use the call back function to let the parser do the work to find each link
		// that looks like [[something whatever is inside these brakets]]
		$callback = array('[' =>
		array(
		'end'=>']',
		'cb' => array(
		2=>array('FCKeditorParser', 'fck_leaveTemplatesAlone'),
		3=>array('', ''),
		),
		'min' =>2,
		'max' =>2,
		)
		);
		$text = $this->replace_callback($text, $callback);

		// now each property string is prefixed with <!--FCK_SKIP_START--> and
		// tailed with <!--FCK_SKIP_END-->
		// use this knowledge to find properties within these comments
		// and replace them with FCK_PROPERTY_X_FOUND that will be used later to be replaced
		// by the current property string
		while (preg_match('/\<\!--FCK_SKIP_START--\>\[\[(.*?)\]\]\<\!--FCK_SKIP_END--\>/', $text, $matches)) {
            $replacedVal = $this->revertEncapsulatedString($matches[1]);
			$replacement = $this->replaceSpecialLinkValue($replacedVal, $matches[1]);
			$pos = strpos($text, $matches[0]);
			$before = substr($text, 0, $pos);
			$after = substr($text, $pos + strlen($matches[0]));
			$text = $before . $replacement . $after;
		}
		return $text;		
	}

	function internalParse ( $text ) {

		$this->fck_internal_parse_text =& $text;

		// also replace all custom tags
		foreach ($this->FCKeditorWikiTags as $tag) {
		    $tag = $this->guessCaseSensitiveTag($tag, $text);
		    $this->fck_allTagsCurrentTagReplaced = $tag;
		    $text = StringUtils::delimiterReplaceCallback( "<$tag>", "</$tag>", array($this, 'fck_allTags'), $text );
		}
		// Rule tags on Property pages
		$text = $this->replaceRules($text);
		
        // __TOC__ etc. must be replaced
        $text = $this->stripToc( $text );
		//html comments shouldn't be stripped
		$text = $this->fck_replaceHTMLcomments( $text );
		//as well as templates
		$text = $this->fck_replaceTemplates( $text );
		// as well as properties
		$text = $this->fck_replaceSpecialLinks( $text );

		$finalString = parent::internalParse($text);

		return $finalString;
	}
	function fck_allTags( $matches ) {
        return $this->fck_wikiTag($this->fck_allTagsCurrentTagReplaced, $matches[1]);
    }
	function fck_leaveTemplatesAlone( $matches ) {
		return "<!--FCK_SKIP_START-->".$matches['text']."<!--FCK_SKIP_END-->";
	}
	function formatHeadings( $text, $isMain=true ) { return $text; }
	function replaceFreeExternalLinks( $text ) { return $text; }
	function stripNoGallery(&$text) {}
	function stripToc( $text ) {
        $prefix = '<span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="%s" _fck_mw_tagtype="c">';
        $suffix = '</span>';
	  
		$strtr = array();
		foreach ($this->FCKeditorMagicWords as $word) {
			$strtr['__'.$word.'__'] = sprintf($prefix, $word) . $suffix;
		}
		foreach ($strtr as $key => $val) {
		    $strtr[$key] = $this->fck_addToStrtr($val, $key);
		}
		return strtr( $text, $strtr );
	}
    /**
     * Replace a former encoded FckmwXXfckmw with it's actual value. Things
     * are replaces sequencially and if the same part has a replacement
     * already and the outer part will replaced by itself, then the inner
     * content must be the original string, without any replacements.
     *
     * This can happen, if a property contains a param/template call like:
     * [[Property::{{{1}}}]] or if a parser function/template contains a
     * comment. Things are replaces sequencially and if the same part has
     * a replacement already but is replaced as well then we are in trouble.
     *
     * @access private
     * @param string text
     * @return string text
     */
    private function revertEncapsulatedString($text) {
        if (preg_match_all('/Fckmw\d+fckmw/', $text, $matches)) {
	        for ($i = 0, $is = count($matches[0]); $i < $is; $i++ ) {
	           if (isset($this->fck_mw_strtr_span['href="'.$matches[0][$i].'"'])) {
	               $text = str_replace(
                           $matches[0][$i],
                           substr($this->fck_mw_strtr_span['href="'.$matches[0][$i].'"'], 6, -1),
	                       $text);
	           }
	        }
        }
        return $text;
    }

    /**
     * Checks for replacments by replacePropertyValue() and replaceRichmediaLinkValue()
     * If a property was replaces, don't try to find and replace a richmedia link
     *
     * @access private
     * @param  string match
     * @param  string orig (maybe FckmwXfckmw)
     * @return string replaced placeholder or [[match]]
     */
    private function replaceSpecialLinkValue($match, $orig) {
        $res = $this->replacePropertyValue($match);
        if (preg_match('/FCK_PROPERTY_\d+_FOUND/', $res)) // property was replaced, we can quit here.
            return $res;
        $res = $this->replaceRichmediaLinkValue($match);
        if (preg_match('/FCK_RICHMEDIA_\d+_FOUND/', $res)) // richmedia link was replaced, we can quit here.
            return $res;
        // an ordinary link. If this is something like [[{{{1}}}]] then this would be an
        // empty link, because during parsing, the parameter will not exist. Therefore
        // do not use the original value but the template replacement
        if ('[['.$orig.']]' == $res) return $res;
        $key = 'Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw';
        $this->fck_mw_strtr_span_counter++;
        $this->fck_mw_strtr_span[$key] = $res;
        return $key;
    }

	/**
	 * check the parser match from inside the [[ ]] and see if it's a property.
	 * If thats the case, safe the property string in the array
	 * $this->fck_mw_propertyAtPage and return a placeholder FCK_PROPERTY_X_FOUND
	 * for the Xth occurence. Otherwise return the link content unmodified.
	 * The missing [[ ]] have to be added again, so that the original remains untouched
	 * 
	 * @access private
	 * @param  string $match
	 * @return string replacement or "[[$match]]"
	 */
	private function replacePropertyValue($match) {
		$prop = explode('::', $match);
  		if ((count($prop) == 2) && (strlen($prop[0]) > 0) && (strlen($prop[1]) > 0)) {
  			if (($p = strpos($prop[1], '|')) !== false) {
  				$prop[0] .= '::'.substr($prop[1], 0, $p);
  				$prop[1] = substr($prop[1], $p + 1);
  			}
  			// replace an empty value with &nbsp; for IE8
  			if (preg_match('/^\s+$/', $prop[1])) $prop[1] = '&nbsp;';
    		$p = count($this->fck_mw_propertyAtPage);
    		$this->fck_mw_propertyAtPage[$p]= '<span class="fck_mw_property" property="'.$prop[0].'">'.$prop[1].'</span>';
    		return 'FCK_PROPERTY_'.$p.'_FOUND';
  		}
  		return "[[".$match."]]";
	}

	/**
	 * check the parser match from inside the [[ ]] and see if it's a link from
	 * the RichMedia extension.
	 * If thats the case, safe the richmedia string in the array
	 * $this->fck_mw_richmediaLinkAtPage and return a placeholder FCK_RICHMEDIA_X_FOUND
	 * for the Xth occurence. Otherwise return the link content unmodified.
	 * The missing [[ ]] have to be added again, so that the original remains untouched
	 * 
	 * @access private
	 * @param  string $match
	 * @return string replacement or "[[$match]]"
	 */
	private function replaceRichmediaLinkValue($match) {
		if ($match && $match{0} == ":") $match = substr($match, 1);
		if (strpos($match, ":") === false)
			return "[[".$match."]]";
		$ns = substr($match, 0, strpos($match, ':'));
		if (in_array(strtolower($ns), array('pdf', 'document', 'audio', 'video'))) { //$wgExtraNamespaces doesn't help really 
  			$link = explode('|', $match);
  			$basename = substr($link[0], strlen($ns) + 1);
    		$p = count($this->fck_mw_richmediaLinkAtPage);
    		$this->fck_mw_richmediaLinkAtPage[$p]= '<a title="'.str_replace('_', ' ', $basename).'" _fck_mw_type="'.$ns.'" '.
    			'_fck_mw_filename="'.$basename.'" _fcksavedurl="'.$link[0].'" href="'.$basename.'">'.
    			((count($link) > 1) ? $link[1] : str_replace('_', ' ', $link[0])).'</a>';
    		return 'FCK_RICHMEDIA_'.$p.'_FOUND';
  		}
  		return "[[".$match."]]";
	}

	function parse( $text, &$title, $options, $linestart = true, $clearState = true, $revid = null ) {
		$text = preg_replace("/^#REDIRECT/", "<!--FCK_REDIRECT-->", $text);
		$parserOutput = parent::parse($text, $title, $options, $linestart , $clearState , $revid );

		$categories = $parserOutput->getCategories();
		if ($categories) {
			$appendString = "<p>";
			foreach ($categories as $cat=>$val) {
				if ($val != $title->mPrefixedText)
					$appendString .= '<span class="fck_mw_category" sort="'.$val.'">'.str_replace('_', ' ', $cat).'</span> ';
				else
					$appendString .= '<span class="fck_mw_category">'.str_replace('_', ' ', $cat).'</span> ';
			}
			$appendString .= "</p>";
			$parserOutput->setText($parserOutput->getText() . $appendString);
		}

		if (!empty($this->fck_mw_strtr_span)) {
			global $leaveRawTemplates;
			if (!empty($leaveRawTemplates)) {
				foreach ($leaveRawTemplates as $l) {
					$this->fck_mw_strtr_span[$l] = substr($this->fck_mw_strtr_span[$l], 30, -7);
				}
			}
			$parserOutput->setText(strtr($parserOutput->getText(), $this->fck_mw_strtr_span));
		}

                // there were properties, look for the placeholder FCK_PROPERTY_X_FOUND and replace
		// it with <span class="fck_mw_property">property string without brakets</span>
		if (count($this->fck_mw_propertyAtPage) > 0) {
			$tmpText = $parserOutput->getText();
			foreach ($this->fck_mw_propertyAtPage as $p => $val)
				$tmpText = str_replace('FCK_PROPERTY_'.$p.'_FOUND', $val, $tmpText);
			$parserOutput->setText($tmpText);
		}
		// there were Richmedia links, look for the placeholder FCK_RICHMEDIA_X_FOUND and replace
		// it with <a title="My Document.doc" _fck_mw_type="Document" _fck_mw_filename="My Document.doc"
		// _fcksavedurl="Document:MyDocument.doc" href="My_Document.doc">Document:My Document.doc</a>
		if (count($this->fck_mw_richmediaLinkAtPage) > 0) {
			$tmpText = $parserOutput->getText();
			foreach ($this->fck_mw_richmediaLinkAtPage as $p => $val)
				$tmpText = str_replace('FCK_RICHMEDIA_'.$p.'_FOUND', $val, $tmpText);
			$parserOutput->setText($tmpText);
		}

		if (!empty($this->fck_matches)) {
			$text = $parserOutput->getText() ;
			foreach ($this->fck_matches as $key => $m) {
				$text = str_replace( $key, $m[3], $text);
			}
			$parserOutput->setText($text);
		}
		
		if (!empty($parserOutput->mLanguageLinks)) {
			foreach ($parserOutput->mLanguageLinks as $l) {
				$parserOutput->setText($parserOutput->getText() . "\n" . "<a href=\"".$l."\">".$l."</a>") ;
			}
		}

		$parserOutput->setText(str_replace("<!--FCK_REDIRECT-->", "#REDIRECT", $parserOutput->getText()));

		// the old parser returned relative links only. This is also essential for the FCK editor
		$parserOutput->setText($this->makeLinksRelative($parserOutput->getText()));

		return $parserOutput;
	}

	/**
	 * Make lists from lines starting with ':', '*', '#', etc.
	 *
	 * @private
	 * @return string the lists rendered as HTML
	 */
	function doBlockLevels( $text, $linestart ) {
		$fname = 'Parser::doBlockLevels';
		wfProfileIn( $fname );

		# Parsing through the text line by line.  The main thing
		# happening here is handling of block-level elements p, pre,
		# and making lists from lines starting with * # : etc.
		#
		$textLines = explode( "\n", $text );

		$lastPrefix = $output = '';
		$this->mDTopen = $inBlockElem = false;
		$prefixLength = 0;
		$paragraphStack = false;

		if ( !$linestart ) {
			$output .= array_shift( $textLines );
		}
		foreach ( $textLines as $oLine ) {
			$lastPrefixLength = strlen( $lastPrefix );
			$preCloseMatch = preg_match('/<\\/pre/i', $oLine );
			$preOpenMatch = preg_match('/<pre/i', $oLine );
			if ( !$this->mInPre ) {
				# Multiple prefixes may abut each other for nested lists.
				$prefixLength = strspn( $oLine, '*#:;' );
				$pref = substr( $oLine, 0, $prefixLength );

				# eh?
				$pref2 = str_replace( ';', ':', $pref );
				$t = substr( $oLine, $prefixLength );
				$this->mInPre = !empty($preOpenMatch);
			} else {
				# Don't interpret any other prefixes in preformatted text
				$prefixLength = 0;
				$pref = $pref2 = '';
				$t = $oLine;
			}

			# List generation
			if( $prefixLength && 0 == strcmp( $lastPrefix, $pref2 ) ) {
				# Same as the last item, so no need to deal with nesting or opening stuff
				$output .= $this->nextItem( substr( $pref, -1 ) );
				$paragraphStack = false;

				if ( substr( $pref, -1 ) == ';') {
					# The one nasty exception: definition lists work like this:
					# ; title : definition text
					# So we check for : in the remainder text to split up the
					# title and definition, without b0rking links.
					$term = $t2 = '';
					if ($this->findColonNoLinks($t, $term, $t2) !== false) {
						$t = $t2;
						$output .= $term . $this->nextItem( ':' );
					}
				}
			} elseif( $prefixLength || $lastPrefixLength ) {
				# Either open or close a level...
				$commonPrefixLength = $this->getCommon( $pref, $lastPrefix );
				$paragraphStack = false;

				while( $commonPrefixLength < $lastPrefixLength ) {
					$output .= $this->closeList( $lastPrefix{$lastPrefixLength-1} );
					--$lastPrefixLength;
				}
				if ( $prefixLength <= $commonPrefixLength && $commonPrefixLength > 0 ) {
					$output .= $this->nextItem( $pref{$commonPrefixLength-1} );
				}
				while ( $prefixLength > $commonPrefixLength ) {
					$char = substr( $pref, $commonPrefixLength, 1 );
					$output .= $this->openList( $char );

					if ( ';' == $char ) {
						# FIXME: This is dupe of code above
						if ($this->findColonNoLinks($t, $term, $t2) !== false) {
							$t = $t2;
							$output .= $term . $this->nextItem( ':' );
						}
					}
					++$commonPrefixLength;
				}
				$lastPrefix = $pref2;
			}
			if( 0 == $prefixLength ) {
				wfProfileIn( "$fname-paragraph" );
				# No prefix (not in list)--go to paragraph mode
				// XXX: use a stack for nestable elements like span, table and div
				$openmatch = preg_match('/(?:<table|<blockquote|<h1|<h2|<h3|<h4|<h5|<h6|<pre|<tr|<p|<ul|<ol|<li|<\\/tr|<\\/td|<\\/th)/iS', $t );
				$closematch = preg_match(
				'/(?:<\\/table|<\\/blockquote|<\\/h1|<\\/h2|<\\/h3|<\\/h4|<\\/h5|<\\/h6|'.
				'<td|<th|<\\/?div|<hr|<\\/pre|<\\/p|'.$this->mUniqPrefix.'-pre|<\\/li|<\\/ul|<\\/ol|<\\/?center)/iS', $t );
				if ( $openmatch or $closematch ) {
					$paragraphStack = false;
					# TODO bug 5718: paragraph closed
					$output .= $this->closeParagraph();
					if ( $preOpenMatch and !$preCloseMatch ) {
						$this->mInPre = true;
					}
					if ( $closematch ) {
						$inBlockElem = false;
					} else {
						$inBlockElem = true;
					}
				} else if ( !$inBlockElem && !$this->mInPre ) {
					if ( ' ' == $t{0} and ( $this->mLastSection == 'pre' or trim($t) != '' ) ) {
						// pre
						if ($this->mLastSection != 'pre') {
							$paragraphStack = false;
							$output .= $this->closeParagraph().'<pre class="_fck_mw_lspace">';
							$this->mLastSection = 'pre';
						}
						$t = substr( $t, 1 );
					} else {
						// paragraph
						if ( '' == trim($t) ) {
							if ( $paragraphStack ) {
								$output .= $paragraphStack.'<br />';
								$paragraphStack = false;
								$this->mLastSection = 'p';
							} else {
								if ($this->mLastSection != 'p' ) {
									$output .= $this->closeParagraph();
									$this->mLastSection = '';
									$paragraphStack = '<p>';
								} else {
									$paragraphStack = '</p><p>';
								}
							}
						} else {
							if ( $paragraphStack ) {
								$output .= $paragraphStack;
								$paragraphStack = false;
								$this->mLastSection = 'p';
							} else if ($this->mLastSection != 'p') {
								$output .= $this->closeParagraph().'<p>';
								$this->mLastSection = 'p';
							}
						}
					}
				}
				wfProfileOut( "$fname-paragraph" );
			}
			// somewhere above we forget to get out of pre block (bug 785)
			if($preCloseMatch && $this->mInPre) {
				$this->mInPre = false;
			}
			if ($paragraphStack === false) {
				$output .= $t."\n";
			}
		}
		while ( $prefixLength ) {
			$output .= $this->closeList( $pref2{$prefixLength-1} );
			--$prefixLength;
		}
		if ( '' != $this->mLastSection ) {
			$output .= '</' . $this->mLastSection . '>';
			$this->mLastSection = '';
		}

		wfProfileOut( $fname );
		return $output;
	}
	// function added from the old Mediawiki parser object, to make this file work actually.

	/**
	 * parse any parentheses in format ((title|part|part))
	 * and call callbacks to get a replacement text for any found piece
	 *
	 * @param string $text The text to parse
	 * @param array $callbacks rules in form:
	 *     '{' => array(				# opening parentheses
	 *					'end' => '}',   # closing parentheses
	 *					'cb' => array(2 => callback,	# replacement callback to call if {{..}} is found
	 *								  3 => callback 	# replacement callback to call if {{{..}}} is found
	 *								  )
	 *					)
	 * 					'min' => 2,     # Minimum parenthesis count in cb
	 * 					'max' => 3,     # Maximum parenthesis count in cb
	 * @private
	 */
	private function replace_callback ($text, $callbacks) {
		wfProfileIn( __METHOD__ );
		$openingBraceStack = array();	# this array will hold a stack of parentheses which are not closed yet
		$lastOpeningBrace = -1;			# last not closed parentheses

		$validOpeningBraces = implode( '', array_keys( $callbacks ) );

		$i = 0;
		while ( $i < strlen( $text ) ) {
			# Find next opening brace, closing brace or pipe
			if ( $lastOpeningBrace == -1 ) {
				$currentClosing = '';
				$search = $validOpeningBraces;
			} else {
				$currentClosing = $openingBraceStack[$lastOpeningBrace]['braceEnd'];
				$search = $validOpeningBraces . '|' . $currentClosing;
			}
			$rule = null;
			$i += strcspn( $text, $search, $i );
			if ( $i < strlen( $text ) ) {
				if ( $text[$i] == '|' ) {
					$found = 'pipe';
				} elseif ( $text[$i] == $currentClosing ) {
					$found = 'close';
				} elseif ( isset( $callbacks[$text[$i]] ) ) {
					$found = 'open';
					$rule = $callbacks[$text[$i]];
				} else {
					# Some versions of PHP have a strcspn which stops on null characters
					# Ignore and continue
					++$i;
					continue;
				}
			} else {
				# All done
				break;
			}

			if ( $found == 'open' ) {
				# found opening brace, let's add it to parentheses stack
				$piece = array('brace' => $text[$i],
							   'braceEnd' => $rule['end'],
							   'title' => '',
							   'parts' => null);

				# count opening brace characters
				$piece['count'] = strspn( $text, $piece['brace'], $i );
				$piece['startAt'] = $piece['partStart'] = $i + $piece['count'];
				$i += $piece['count'];

				# we need to add to stack only if opening brace count is enough for one of the rules
				if ( $piece['count'] >= $rule['min'] ) {
					$lastOpeningBrace ++;
					$openingBraceStack[$lastOpeningBrace] = $piece;
				}
			} elseif ( $found == 'close' ) {
				# lets check if it is enough characters for closing brace
				$maxCount = $openingBraceStack[$lastOpeningBrace]['count'];
				$count = strspn( $text, $text[$i], $i, $maxCount );

				# check for maximum matching characters (if there are 5 closing
				# characters, we will probably need only 3 - depending on the rules)
				$matchingCount = 0;
				$matchingCallback = null;
				$cbType = $callbacks[$openingBraceStack[$lastOpeningBrace]['brace']];
				if ( $count > $cbType['max'] ) {
					# The specified maximum exists in the callback array, unless the caller
					# has made an error
					$matchingCount = $cbType['max'];
				} else {
					# Count is less than the maximum
					# Skip any gaps in the callback array to find the true largest match
					# Need to use array_key_exists not isset because the callback can be null
					$matchingCount = $count;
					while ( $matchingCount > 0 && !array_key_exists( $matchingCount, $cbType['cb'] ) ) {
						--$matchingCount;
					}
				}

				if ($matchingCount <= 0) {
					$i += $count;
					continue;
				}
				$matchingCallback = $cbType['cb'][$matchingCount];

				# let's set a title or last part (if '|' was found)
				if (null === $openingBraceStack[$lastOpeningBrace]['parts']) {
					$openingBraceStack[$lastOpeningBrace]['title'] =
						substr($text, $openingBraceStack[$lastOpeningBrace]['partStart'],
						$i - $openingBraceStack[$lastOpeningBrace]['partStart']);
				} else {
					$openingBraceStack[$lastOpeningBrace]['parts'][] =
						substr($text, $openingBraceStack[$lastOpeningBrace]['partStart'],
						$i - $openingBraceStack[$lastOpeningBrace]['partStart']);
				}

				$pieceStart = $openingBraceStack[$lastOpeningBrace]['startAt'] - $matchingCount;
				$pieceEnd = $i + $matchingCount;

				if( is_callable( $matchingCallback ) ) {
					$cbArgs = array (
									 'text' => substr($text, $pieceStart, $pieceEnd - $pieceStart),
									 'title' => trim($openingBraceStack[$lastOpeningBrace]['title']),
									 'parts' => $openingBraceStack[$lastOpeningBrace]['parts'],
									 'lineStart' => (($pieceStart > 0) && ($text[$pieceStart-1] == "\n")),
									 );
					# finally we can call a user callback and replace piece of text
					$replaceWith = call_user_func( $matchingCallback, $cbArgs );
					$text = substr($text, 0, $pieceStart) . $replaceWith . substr($text, $pieceEnd);
					$i = $pieceStart + strlen($replaceWith);
				} else {
					# null value for callback means that parentheses should be parsed, but not replaced
					$i += $matchingCount;
				}

				# reset last opening parentheses, but keep it in case there are unused characters
				$piece = array('brace' => $openingBraceStack[$lastOpeningBrace]['brace'],
							   'braceEnd' => $openingBraceStack[$lastOpeningBrace]['braceEnd'],
							   'count' => $openingBraceStack[$lastOpeningBrace]['count'],
							   'title' => '',
							   'parts' => null,
							   'startAt' => $openingBraceStack[$lastOpeningBrace]['startAt']);
				$openingBraceStack[$lastOpeningBrace--] = null;

				if ($matchingCount < $piece['count']) {
					$piece['count'] -= $matchingCount;
					$piece['startAt'] -= $matchingCount;
					$piece['partStart'] = $piece['startAt'];
					# do we still qualify for any callback with remaining count?
					$currentCbList = $callbacks[$piece['brace']]['cb'];
					while ( $piece['count'] ) {
						if ( array_key_exists( $piece['count'], $currentCbList ) ) {
							$lastOpeningBrace++;
							$openingBraceStack[$lastOpeningBrace] = $piece;
							break;
						}
						--$piece['count'];
					}
				}
			} elseif ( $found == 'pipe' ) {
				# lets set a title if it is a first separator, or next part otherwise
				if (null === $openingBraceStack[$lastOpeningBrace]['parts']) {
					$openingBraceStack[$lastOpeningBrace]['title'] =
						substr($text, $openingBraceStack[$lastOpeningBrace]['partStart'],
						$i - $openingBraceStack[$lastOpeningBrace]['partStart']);
					$openingBraceStack[$lastOpeningBrace]['parts'] = array();
				} else {
					$openingBraceStack[$lastOpeningBrace]['parts'][] =
						substr($text, $openingBraceStack[$lastOpeningBrace]['partStart'],
						$i - $openingBraceStack[$lastOpeningBrace]['partStart']);
				}
				$openingBraceStack[$lastOpeningBrace]['partStart'] = ++$i;
			}
		}

		wfProfileOut( __METHOD__ );
		return $text;
	}
	private function replaceRules($text) {
	    global $wgTitle, $wgRequest;
	    // rules exist in poperty pages only.
	    // if it's an ajax call we don't know the page name, so do it always
	    if (($wgRequest->getVal('action') == 'ajax') || 
	        (defined('SMW_NS_PROPERTY') && $wgTitle->getNamespace() == SMW_NS_PROPERTY)) {
	        if (preg_match_all('/<rule[^>]*>.*?<\/rule>/s', $text, $matches)) {
	             for ($i= 0; $i<count($matches[0]); $i++) {
	                 $this->fck_mw_strtr_span['Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw']=
	                     '<span class="fck_mw_rule">'.$matches[0][$i].'</span>';
	                 $this->fck_mw_strtr_span['href="Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw"']=
	                     'href="'.$matches[0][$i].'"';
                     $key = 'Fckmw'.$this->fck_mw_strtr_span_counter.'fckmw';
                     $this->fck_mw_strtr_span_counter++;
                     $cnt=1;
	                 $text = str_replace($matches[0][$i], $key, $text, $cnt);
	             }
	        }
	    }
	    return $text;
	}
	/**
	 * The old parser generation creates a link [[SomePage|SomePage]] by just translating
	 * it to <a href="SomePage">SomePage</a> while the new parser always adds $wgScript before
	 * the page name. This must be removed again here in the resulting links.
	 * A link (generated by the parser) always starts with <a href= followed by any
	 * additional attributes in the a element. This makes it possible to use a string replace
	 * instead of having to use a preg_replace.
	 * 
	 * @access private
	 * @param  string $wikitext with absolute links
	 * @return string $wikitext with relative links
	 */	
	private function makeLinksRelative($text) {
		global $wgScript;
		$text = str_replace('<a href="'.$wgScript.'/', '<a href="', $text);
		return $text;
	}
	
	/**
	 * When getting the available tags from the wiki, these are returned in
	 * lower case although some might be in camel case. Also tags are case
	 * sensitive. Therefore guess here the correct tag.
	 *
	 * @param string $tag small case
	 * @param string $text wiki text
	 * @return string $tag case sensitive tag
	 */
	private function guessCaseSensitiveTag($tag, $text) {
	    preg_match('/<('.$tag.')>/i', $text, $matchOpen);
	    preg_match('/<\/('.$tag.')>/i', $text, $matchClose);
	    if (count($matchOpen) > 0 &&
	        count($matchClose) > 0 &&
	        $matchOpen[1] == $matchClose[1])
	        return $matchOpen[1];
	    return $tag;
	}
}
