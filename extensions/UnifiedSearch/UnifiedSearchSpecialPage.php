<?php
/*
 * Created on 28.01.2009
 *
 * @author: Kai Kühn
 */
if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( "$IP/includes/SpecialPage.php" );

function array_clone(& $src) {
    $dst = array();
    foreach($src as $e) {
        $dst[] = $e;
    }
    return $dst;
}

/*
 * Replaces the MW Search special page
 */

class USSpecialPage extends SpecialPage {


    public function __construct() {
        parent::__construct('Search');
    }

    public function execute() {
        global $wgRequest, $wgOut, $wgPermissionACL, $wgContLang, $wgLang, $wgWhitelistRead, $wgPermissionACL_Superuser, $wgExtensionCredits;
        $search = str_replace( "\n", " ", $wgRequest->getText( 'search', '' ) );
        $t = Title::newFromText( $search );

        $fulltext = $wgRequest->getVal( 'fulltext', '' );
        if ($fulltext == NULL) {
            # If the string cannot be used to create a title
            if(!is_null( $t ) ){


                # If there's an exact or very near match, jump right there.
                $t = SearchEngine::getNearMatch( $search );
                if( !is_null( $t ) ) {
                    $wgOut->redirect( $t->getFullURL() );
                    return;
                }

                # If just the case is wrong, jump right there.
                $t = USStore::getStore()->getSingleTitle($search);
                if (!is_null( $t ) ) {
                    $wgOut->redirect( $t->getFullURL() );
                    return;
                }
            }
        }

        $limit =  $wgRequest->getVal('limit') !== NULL ? $wgRequest->getVal('limit') : 20;

        $offset = $wgRequest->getVal('offset') !== NULL ? $wgRequest->getVal('offset') : 0;


        $newpage = Title::newFromText($search);


        $searchPage = SpecialPage::getTitleFor("Search");

        // do search
        if (trim($search) != '') {
            list($searchResults,$searchSet) = $this->doSearch($limit, $offset);
            // save results for statistics
            if ($searchSet !== NULL) USStore::getStore()->addSearchTry($search, $searchSet->numRows() );
        } else {
            // initialize when searchstring is empty
            $searchResults = array();
            $searchSet = NULL;

        }


        $numOfResults = count($searchResults);

        // -- suggestion (Did you mean?) --
        $suggestion = $searchSet != NULL ? $searchSet->getSuggestionQuery() : NULL;
        if ($suggestion != NULL) {

            $suggestion = str_replace('_', ' ', $suggestion);

        }

        // -- search form --
        $html = '<form id="us_searchform"><table><tr><td>'.wfMsg('us_searchfield').'</td><td>'.wfMsg('us_tolerance').'</td><td></td></tr><tr><td><input id="us_searchfield" type="text" size="30" name="search"></td>'.
            '<td><select id="toleranceSelector" name="tolerance" onchange="smwhg_toleranceselector.onChange()"><option id="tolerantOption"  value="0">'.wfMsg('us_tolerantsearch').'</option>'.
            '<option id="semitolerantOption"  value="1">'.wfMsg('us_semitolerantsearch').'</option>'.
            '<option id="exactOption"  value="2">'.wfMsg('us_exactsearch').'</option></select></td>'.
            '<td><input type="submit" name="searchbutton" value="'.wfMsg('us_searchbutton').'"><input type="hidden" name="fulltext" value="true"></td></tr></table>'.

        '</form>';


        // -- new page link --
        if ($newpage !== NULL && !$newpage->exists()) {
            $newLink = '<a class="new" href="'.$newpage->getFullURL('action=edit').'">'.wfMsg('us_clicktocreate').'</a>';
            $html .= '<div id="us_newpage">'.wfMsg('us_page_does_not_exist', $newLink).'</div>';
        }

        // -- refine links --
        $tolerance = $wgRequest->getVal('tolerance');
        $tolerance = $tolerance == NULL ? 0 : $tolerance;
        $noRefineURL = $searchPage->getFullURL("search=$search&fulltext=true&tolerance=$tolerance");
        
        // create refine links
        global $usgAllNamespaces;
        $namespaceFilterURLs = array();
        foreach($usgAllNamespaces as $ns => $img) {
            $namespaceFilterURLs[] = $searchPage->getFullURL("search=$search&fulltext=true&restrict=$ns&tolerance=$tolerance");
        }

        // create refine links table 
        global $wgContLang;
        $restrictNS = $wgRequest->getVal('restrict');
        $restrictNS = $restrictNS === NULL ? NULL : intval($restrictNS);
        $html .= wfMsg('us_refinesearch');
        $html .='<div id="us_refineresults"><table cellspacing="0">';
        $highlight = $this->highlight(NULL, $restrictNS) ? "us_refinelinks_highlighted" : "us_refinelinks";
        $row =  '<td rowspan="2" width="100"><a class="'.$highlight.'" href="'.$noRefineURL.'">'.wfMsg('us_all').'</a>';

        $nsURL = reset($namespaceFilterURLs);
        $c = 0;

        foreach($usgAllNamespaces as $ns => $img) {
            if ($c > 0 && $c % 5 == 0) {
                $html .= '<tr>'.$row.'</tr>';
                $row = "";
            }
            if ($c >= 5) $style="style=\"border-top: 1px solid;\""; else $style="";
            $nsName = $ns == NS_MAIN ? wfMsg('us_article') : $wgContLang->getNsText($ns);
            $highlight = $this->highlight($ns, $restrictNS) ? "us_refinelinks_highlighted" : "us_refinelinks";
            $row .= '<td class="filtercolumn" '.$style.'><div style="margin: 6px;"><img alt="'.wfMsg('us_search_tooltip_refine', $nsName).'" title="'.wfMsg('us_search_tooltip_refine', $nsName).
                     '" style="vertical-align: baseline;margin-top: 1px;" src="'.UnifiedSearchResultPrinter::getImageURI($img ).'"/><a style="margin-left: 6px;vertical-align: top;" class="'.$highlight.'" href="'.$nsURL.'">'.$nsName.
                     '</a></div></td><td '.$style.'>|</td>';
            $nsURL = next($namespaceFilterURLs);
            $c++;
        }
        
        // fill complete line of refinement links
        while ($c % 5 > 0) { $row .= '<td '.$style.'></td><td '.$style.'></td>'; $c++; }
        $html .= '<tr>'.$row.'</tr>';
        $html .= '</table></div>';

        $totalHits = $searchSet != NULL ?  $searchSet->getTotalHits() : 0;
        
        // -- browsing --
        $next = $this->createBrowsingLink($search, $offset + $limit, $limit, wfMsg('us_browse_next'));
        $previous = $this->createBrowsingLink($search,$offset - $limit, $limit, wfMsg('us_browse_prev'));
        $limit20 = $this->createLimitLink($search,$offset,  20, $limit );
        $limit50 = $this->createLimitLink($search,$offset,  50, $limit);
        $limit100 = $this->createLimitLink($search,$offset,  100, $limit);
        $limit250 = $this->createLimitLink($search,$offset, 250, $limit);
        $limit500 = $this->createLimitLink($search,$offset, 500, $limit);

        $nextButton =  (count($searchResults) < $limit) ? wfMsg('us_browse_next') : $next;
        $prevButton = ($offset == 0) ? wfMsg('us_browse_prev') : $previous;
        
        // browsing bar top
        if (count($searchResults) > 0) {
            $html .= "<table id=\"us_browsing\"><tr><td>".wfMsg('us_page')." ".(intval($offset/$limit)+1)." - ".(intval($totalHits/$limit)+1)."</td>";
            $html .= "<td style=\"text-align: center;color: gray;\">($prevButton) ($nextButton)</td>";
            $html .= "<td style=\"width: 33%; text-align: right;\">".wfMsg('us_entries_per_page')." ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</td></tr></table>";
        }
        
        // -- show Did you mean --
        $didyoumeanURL = $searchPage->getFullURL("search=$suggestion");
        $wgOut->setPageTitle(wfMsg('us_search'));
        $html .= '<div id="us_didyoumean">'.($suggestion !== NULL ? "<i style=\"color:red;\">".wfMsg('us_didyoumean').":</i> <a style=\"text-decoration:underline;\" href=\"$didyoumeanURL\">".$suggestion."</a>" : "").'</div>';
    
        if (count($searchResults) == 0) {
            $html .= wfMsg('us_noresults_text', $search);
        }

        // show search results
        // heading
        if (count($searchResults) > 0) {
            $html .= '<div id="us_searchresults">';
            $resultInfo =  wfMsg('us_resultinfo',$offset+1,$offset+$limit > $totalHits ? $totalHits : $offset+$limit, $totalHits, $search);
            $html .= "<div id=\"us_resultinfo\">".wfMsg('us_results').": $resultInfo</div>";
            $html .= UnifiedSearchResultPrinter::serialize($searchResults, $search);
            $html .= '</div>';
        }
        
        // browsing bar bottom
        if (count($searchResults) > 0) {
            $html .= "<table id=\"us_browsing\"><tr><td>".wfMsg('us_page')." ".(intval($offset/$limit)+1)." - ".(intval($totalHits/$limit)+1)."</td>";
            $html .= "<td style=\"text-align: center;color: gray;\">($prevButton) ($nextButton)</td>";
            $html .= "<td style=\"width: 33%; text-align: right;\">".wfMsg('us_entries_per_page')." ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</td></tr></table>";
        }
        $wgOut->addHTML($html);
    }

    private function highlight($exp_ns, $act_ns) {
        return $exp_ns === $act_ns;
    }

    private function createBrowsingLink($search, $offset, $limit, $text="") {
        $searchPage = SpecialPage::getTitleFor("Search");
        return '<a href="'.$searchPage->getFullURL("search=$search&fulltext=true&limit=$limit&offset=$offset").'">'.$text." ".$limit.'</a>';
    }

    private function createLimitLink($search, $offset, $limit, $currentLimit) {
        $searchPage = SpecialPage::getTitleFor("Search");
        $limit = ($limit == $currentLimit) ? "<b>$limit</b>" : $limit;
        return '<a href="'.$searchPage->getFullURL("search=$search&fulltext=true&limit=$limit&offset=$offset").'">'.$limit.'</a>';
    }

    private function doSearch($limit, $offset) {
        global $wgRequest, $usgAllNamespaces;

        // initialize vars
        $search = $wgRequest->getVal('search');
        $restrictNS = $wgRequest->getVal('restrict');
        $tolerance = $wgRequest->getVal('tolerance');
        $tolerance = $tolerance == NULL ? 0 : $tolerance;



        // parse terms
        $terms = self::parseTerms($search);
        $cleanTerms = self::cleanTerms($terms);
        
        // query lucene server

        // if query contains boolean operators, consider as as user-defined
        // and do not use title search and pass search string unchanged to Lucene

        $namespacesToSearch = $restrictNS !== NULL ? array($restrictNS) : array_keys($usgAllNamespaces);
        if (!self::userDefinedSearch($terms, $search)) {
            // non user-defined
            $contentTitleSearchPattern = 'contents:($1$4) OR title:($2$3)';
            $expandedFTSearch = QueryExpander::expandForFulltext($terms, $tolerance);
            $expandedTitles = QueryExpander::expandForTitles($terms, $namespacesToSearch , $tolerance);
             //echo print_r(QueryExpander::findAggregatedTerms($terms), true);die();
            $aggregatedTerms = QueryExpander::opTerms(array_keys(QueryExpander::findAggregatedTerms($terms)), "OR");
          
            $contentTitleSearchPattern = str_replace('$1', $expandedFTSearch, $contentTitleSearchPattern);
            $contentTitleSearchPattern = str_replace('$2', $expandedTitles, $contentTitleSearchPattern);
            $contentTitleSearchPattern = str_replace('$3', $aggregatedTerms == '' ? '' : ' OR '.$aggregatedTerms, $contentTitleSearchPattern);
            $contentTitleSearchPattern = str_replace('$4', $aggregatedTerms == '' ? '' : ' OR '.$aggregatedTerms, $contentTitleSearchPattern);
            $searchSet = LuceneSearchSet::newFromQuery( 'raw',  $contentTitleSearchPattern, $namespacesToSearch, $limit, $offset);

            if ($searchSet == NULL || $searchSet->getTotalHits() == 0) {
                // use enhanced lucene search method with SKOS expansion for fulltext
                $searchSet = LuceneSearchSet::newFromQuery( 'search',  $expandedFTSearch, $namespacesToSearch, $limit, $offset);
            }

            global $wgLuceneSearchVersion;
            if (($searchSet == NULL || $searchSet->getTotalHits() == 0) && $wgLuceneSearchVersion >= 2.1) {
                // try at least a suggestion
                $searchSet = LuceneSearchSet::newFromQuery( 'suggest',  $search, $namespacesToSearch, $limit, $offset);
            }
        } else {
            // user defined
            // remove syntax elements in term list
            $removedOperators = array();
            foreach($terms as $t) {
                if (strtolower($t) != 'and' && strtolower($t) != 'or' && strtolower($t) != 'not') {
                    $removedOperators[] = $t;
                }
            }
            $terms = $removedOperators;

            $searchSet = LuceneSearchSet::newFromQuery( 'raw', $search , $namespacesToSearch, $limit, $offset);
            if ($searchSet == NULL || $searchSet->getTotalHits() == 0) {
                // use enhanced lucene search method with SKOS expansion for fulltext
                $searchSet = LuceneSearchSet::newFromQuery( 'search',  $search , $namespacesToSearch, $limit, $offset);
            }
        }

        // add matches
        $resultSet = array();

        if ($searchSet == NULL) {
            return array($resultSet, NULL);
        }

        
        //check for 'Did you mean?' proposal
        $suggestion = NULL;
        if ($searchSet!=NULL) {
            $suggestion = $searchSet->getSuggestionQuery();
        }

        // build results

        $nextFulltext = $searchSet->next();

        while ($nextFulltext !== false) {

            if ($nextFulltext != false ) {
                $lr = UnifiedSearchResult::newFromLuceneResult($nextFulltext, $cleanTerms);
                $resultSet[] = $lr;
            
            }
            $nextFulltext = $searchSet->next();


        }

        // result tuple consisting of result set, lucene searchset, total number of title matches
        // and offsets of fulltext and title search
        return array($resultSet, $searchSet);
    }

    /**
     * Returns true if the $terms contain boolean operators or $search contains namespace prefixes
     *
     * @param string $queryString
     * @return boolean
     */
    private static function userDefinedSearch($terms, $search) {

        // check for boolean operators
        foreach($terms as $term) {
            $term = strtolower($term);
            if ($term == 'and' || $term == 'or' || $term == 'not') {
                return true;
            }
            if (substr($term,0,1) == '-' || substr($term,0,1) == '+') {
                return true;
            }
        }

        // check for special lucene syntax
        $fieldSyntax = preg_match('/\w+\s*:\s*{[^}]+}|\w+\s*:\s*\[[^]]+\]/', $search) !== 0;
        $namespaceSyntax = preg_match('/\[\d+\]:/', $search) !== 0;
        return $namespaceSyntax || $fieldSyntax  // namespace prefix, e.g.  [12]:
        || strpos($search, '~') !== false       // unsharp
        || strpos($search, '*') !== false       // wildcard * (any number of chars)
        || strpos($search, '?') !== false;      // wildcard ? (one char)

    }


    /**
     * Splits a search string on whitespaces considering that
     * quoted terms may contain significant whitespaces.
     *
     * @param string $termString
     * @return array of string
     */
    public static function parseTerms($termString) {
        $terms = array();
        // split terms at whitespaces unless they are quoted
        preg_match_all('/([^\s"\(\)]+|"[^"\(\)]+")+/', $termString, $matches);

       foreach($matches[0] as $term) $terms[] = $term;
       return $terms;
    }
    
    private static function cleanTerms(array $terms) {
        $results = array();
        foreach($terms as $r) {
            $r = str_replace('~', '', $r); // remove unsharp search hint
            $r = str_replace('*', '', $r); 
            $r = str_replace('+', '', $r); 
            $r = str_replace('-', '', $r);
            $r = str_replace('?', '', $r);  
            $r = preg_replace('/\[\d+\]:/', '', $r); // remove namespace hint
            if (substr($r, 0, 1) == '"' && substr($r, strlen($r)-1, 1) == '"') {
                $r = substr($r, 1, strlen($r)-2);
                
            }
            $results[] = $r;
        }
        return $results;
    }
}

?>
