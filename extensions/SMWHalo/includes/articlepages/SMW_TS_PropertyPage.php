<?php
/**
 * @file
 * @ingroup SMWHaloDeviations
 * @ingroup SMWHaloArticlepages
 * 
 * Special handling for property description pages when connected to a triplestore.
 * Some code based on CategoryPage.php
 * 
 * When connected to a triplestore, the code requesting the database 
 * in the original implementation was too slow. 
 *
 * @author: Markus Krötzsch / Kai Kühn
 */

/**
 * Implementation of MediaWiki's Article that shows additional information on
 * property pages. Very simliar to CategoryPage, but with different printout 
 * that also displays values for each subject with the given property.
 * @ingroup SMW
 */
class SMWTSPropertyPage extends SMWOrderedListPage {

    private $subproperties;  // list of sub-properties of this property
    private $mProperty; // property object

    /**
     * Use small $limit (property pages might become large)
     */
    protected function initParameters() {
        global $smwgContLang, $smwgPropertyPagingLimit, $wgRequest;
        $this->limit = $smwgPropertyPagingLimit;
        $offset = $wgRequest->getVal( 'offset' );
        $this->offset = !is_null($offset) ? $offset : 0;
        $this->mProperty = SMWPropertyValue::makeProperty($this->mTitle->getDBKey());
        return true;
    }

    protected function clearPageState() {
        parent::clearPageState();
        $this->subproperties = array();
    }
    
protected function getNavigationLinks($query = array()) {
        global $wgUser, $wgLang, $smwgPropertyPagingLimit;
        $sk = $this->getSkin();
        $limitText = $wgLang->formatNum( $this->limit );
        
        $ac = count($this->articles);
        if ($this->until != '') {
            if ($ac > $this->limit) { // (we assume that limit is at least 1)
                $first = $this->articles[1]->getSortkey();
            } else {
                $first = '';
            }
            $last = $this->until;
        } elseif ( ($ac > $this->limit) || ($this->from != '') ) {
            $first = $this->from;
            if ( $ac > $this->limit) {
                $last = $this->articles[$ac-1]->getSortkey();
            } else {
                $last = '';
            }
        } else {
            return '';
        }

        $prevLink = htmlspecialchars( wfMsg( 'prevn', $limitText ) );
        $this->mTitle->setFragment('#SMWResults'); // make navigation point to the result list
        if( $first != '' ) {
            $prevLink = $sk->makeLinkObj( $this->mTitle, $prevLink,
                wfArrayToCGI( $query + array( 'until' => $first ) ) );
        }
        $nextLink = htmlspecialchars( wfMsg( 'nextn', $limitText ) );
        if( $last != '' ) {
            $nextLink = $sk->makeLinkObj( $this->mTitle, $nextLink,
                wfArrayToCGI( $query + array( 'from' => $last, 'offset' => $this->offset+$smwgPropertyPagingLimit ) ) );
        }
        return "($prevLink) ($nextLink)";
    }
    /**
     * Fill the internal arrays with the set of articles to be displayed (possibly plus one additional
     * article that indicates further results).
     */
    protected function doQuery() {
        $store = smwfGetStore();
        $options = new SMWRequestOptions();
        $options->limit = $this->limit + 1;
        $options->offset = $this->offset;
        $options->sort = true;
        $reverse = false;
        if ($this->from != '') {
            $options->boundary = $this->from;
            $options->ascending = true;
            $options->include_boundary = true;
        } elseif ($this->until != '') {
            $options->boundary = $this->until;
            $options->ascending = false;
            $options->include_boundary = false;
            $reverse = true;
        }
        $this->annotations = $store->getAllPropertyAnnotations($this->mProperty, $options);
        $this->articles = array();
        foreach($this->annotations as $a) {
        	list($article, $values) = $a;
        	$this->articles[] = $article;
        }
       
        if ($reverse) {
            $this->annotations = array_reverse($this->annotations);
             $this->articles = array_reverse($this->articles);
        }
        // retrieve all subproperties of this property
        $s_options = new SMWRequestOptions();
        $s_options->sort = true;
        $s_options->ascending = true;
        $this->subproperties = $store->getPropertySubjects(SMWPropertyValue::makeProperty('_SUBP'), $this->getDataValue(), $s_options);
       
    }

    /**
     * Generates the headline for the page list and the HTML encoded list of pages which
     * shall be shown.
     */
    protected function getPages() {
        wfProfileIn( __METHOD__ . ' (SMW)');
        wfLoadExtensionMessages('SemanticMediaWiki');
        $r = '';
        $ti = htmlspecialchars( $this->mTitle->getText() );
        $nav = $this->getNavigationLinks();
        if (count($this->subproperties) > 0) {
            $r .= "<div id=\"mw-subcategories\">\n<h2>" . wfMsg('smw_subproperty_header',$ti) . "</h2>\n";
            $r .= '<p>';
            if (!$this->mProperty->isUserDefined()) {
                $r .= wfMsg('smw_isspecprop') . ' ';
            }
            $r .= wfMsgExt('smw_subpropertyarticlecount', array( 'parsemag' ), min($this->limit, count($this->subproperties))) . "</p>\n";
            if (count($this->subproperties) < 6) {
                $r .= $this->shortList(0,count($this->subproperties), $this->subproperties);
            } else {
                $r .= $this->columnList(0,count($this->subproperties), $this->subproperties);
            }
            $r .= "\n</div>";
        }
        $r .= '<a name="SMWResults"></a>' . $nav . "<div id=\"mw-pages\">\n";
        $r .= '<h2>' . wfMsg('smw_attribute_header',$ti) . "</h2>\n";
        $r .= '<p>';
        if (!$this->mProperty->isUserDefined()) {
            $r .= wfMsg('smw_isspecprop') . ' ';
        }
        $r .= wfMsgExt('smw_attributearticlecount', array( 'parsemag' ), min($this->limit, count($this->annotations))) . "</p>\n";
        $r .= $this->subjectObjectList() . "\n</div>" . $nav;
        wfProfileOut( __METHOD__ . ' (SMW)');
        return $r;
    }

    /**
     * Format a list of articles chunked by letter in a table that shows subject articles in
     * one column and object articles/values in the other one.
     */
    private function subjectObjectList() {
        global $wgContLang;
        $store = smwfGetStore();

        $ac = count($this->annotations);
        if ($ac > $this->limit) {
            if ($this->until != '') {
                $start = 1;
            } else {
                $start = 0;
                $ac = $ac - 1;
            }
        } else {
            $start = 0;
        }

        $r = '<table style="width: 100%; ">';
        $prev_start_char = 'None';
        for ($index = $start; $index < $ac; $index++ ) {
        	list($article, $values) = $this->annotations[$index];
            $start_char = $wgContLang->convert( $wgContLang->firstChar( $article->getSortkey() ) );
            // Header for index letters
            if ($start_char != $prev_start_char) {
                $r .= '<tr><th class="smwpropname"><h3>' . htmlspecialchars( $start_char ) . "</h3></th><th></th></tr>\n";
                $prev_start_char = $start_char;
            }
            // Property name
            $searchlink = SMWInfolink::newBrowsingLink('+',$article->getShortHTMLText());
            $r .= '<tr><td class="smwpropname">' . $article->getLongHTMLText($this->getSkin()) .
                  '&nbsp;' . $searchlink->getHTML($this->getSkin()) . '</td><td class="smwprops">';
            // Property values
            $ropts = new SMWRequestOptions();
            $ropts->limit = 4;
                       
            for ($i = 0; $i < 4; $i++) {
            	if (!array_key_exists($i, $values)) break;
            	$value = $values[$i];
                if ($i != 0) {
                    $r .= ', ';
                }
                
                if ($i < 3) {
                    $r .= $value->getLongHTMLText($this->getSkin()) . $value->getInfolinkText(SMW_OUTPUT_HTML, $this->getSkin());
                } else {
                    $searchlink = SMWInfolink::newInversePropertySearchLink('&hellip;', $article->getWikiValue(), $this->mTitle->getText());
                    $r .= $searchlink->getHTML($this->getSkin());
                }
            }
            $r .= "</td></tr>\n";
        }
        $r .= '</table>';
        return $r;
    }
}


