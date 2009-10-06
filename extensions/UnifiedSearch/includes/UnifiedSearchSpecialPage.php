<?php
/*
 * Created on 28.01.2009
 *
 * @author: Kai K�hn
 */
if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( "$IP/includes/SpecialPage.php" );



/*
 * Replaces the MW Search special page
 */

class USSpecialPage extends SpecialPage {


	public function __construct() {
		parent::__construct('Search');
	}

	public function execute() {
		global $wgRequest, $wgOut, $wgPermissionACL, $wgContLang, $wgLang, $wgWhitelistRead, $wgPermissionACL_Superuser, $wgExtensionCredits, $wgUSPathSearch;
		$search = str_replace( "\n", " ", $wgRequest->getText( 'search', '' ) );
		$restrict = $wgRequest->getText( 'restrict', '' );
		$t = Title::newFromText( $search );

		$fulltext = $wgRequest->getVal( 'fulltext', '' );
        $fulltext_x = $wgRequest->getVal( 'fulltext_x', '' );
        if ($fulltext == NULL && $fulltext_x == NULL) {
			
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

		// path search options, if the form is called directly from this page
		if (isset($wgUSPathSearch) && $wgUSPathSearch) {
			$doPathSearch = $wgRequest->getVal('paths');
			if (strlen($doPathSearch) == 0) $doPathSearch = 0;
		}
		else
		$doPathSearch = 0;

		// fade out filter and browsing bars when doing a path search
		// therefore div us_refinesearch, us_browsing_top, us_browsing_bottom exist twice,
		// as us_refinesearch_hide, us_browsing_top_hide, us_browsing_bottom_hide
		// if pathsearch is done and there are search result for the normal fulltext search -> only then
		// we acctually can do path search, the navigation elements for the full text search must be faded out.
		if ($doPathSearch && $numOfResults > 0) {
			$styleShow = 'style="display: none;"';
			$styleHide = 'style="-ms-filter:\'progid:DXImageTransform.Microsoft.Alpha(Opacity=25)\'; filter: alpha(opacity=25); opacity: .25; display: block;"';
		}
		else {
			$styleShow = 'style="display: block;"';
			$styleHide = 'style="-ms-filter:\'progid:DXImageTransform.Microsoft.Alpha(Opacity=25)\'; filter: alpha(opacity=25); opacity: .25; display: none;"';
		}

		// -- search form --
		$html = '<form id="us_searchform"><table><tr><td>'.wfMsg('us_searchfield').'</td><td>'.wfMsg('us_tolerance').'</td><td></td></tr><tr><td><input id="us_searchfield" type="text" size="30" name="search"></td>'.
            '<td><select id="toleranceSelector" name="tolerance" onchange="smwhg_toleranceselector.onChange()"><option id="tolerantOption"  value="0">'.wfMsg('us_tolerantsearch').'</option>'.
            '<option id="semitolerantOption"  value="1">'.wfMsg('us_semitolerantsearch').'</option>'.
            '<option id="exactOption"  value="2">'.wfMsg('us_exactsearch').'</option></select></td>'.
            '<td><input type="submit" name="searchbutton" value="'.wfMsg('us_searchbutton').'"><input type="hidden" name="fulltext" value="true"><input id="doPathSearch" type="hidden" name="paths" value="'.$doPathSearch.'"/></td></tr></table>'.

        '</form>';

		// -- new page link --
		$colonIndex = strpos($search, ":");
		$localname = $colonIndex !== false ? substr($search, $colonIndex + 1) : $search;
		$caseInsensitiveTitle = USStore::getStore()->getSingleTitle($localname);
		
		if ($newpage !== NULL && !$newpage->exists() && is_null($caseInsensitiveTitle)) {
			global $wgParser;
			$wikilink = '[[:'.$newpage->getPrefixedText().'|'.wfMsg('us_clicktocreate').']]';
			$newLink = $wgParser->parse($wikilink, Title::newFromText("__dummy__"), new ParserOptions(), true, true)->getText();
			$newLink = strip_tags($newLink, '<a>');
			$html .= '<div id="us_newpage">'.wfMsg('us_page_does_not_exist', $newLink).'</div>';
		}
		if (!is_null($caseInsensitiveTitle)) {
			global $wgParser;
            $wikilink = '[[:'.$caseInsensitiveTitle->getPrefixedText().']]';
			if (!is_null($newpage) && !$newpage->exists()) $wikilink .= ' | [['.$newpage->getPrefixedText().'|'.wfMsg('us_clicktocreate').']]';
            $newLink = $wgParser->parse($wikilink, Title::newFromText("__dummy__"), new ParserOptions(), true, true)->getText();
            $newLink = strip_tags($newLink, '<a>');
            $html .= '<div id="us_newpage">'.wfMsg('us_similar_page_does_exist', $newLink).'</div>';
		}

		// -- refine links --
		$tolerance = $wgRequest->getVal('tolerance');
		$tolerance = $tolerance == NULL ? 0 : $tolerance;
		$noRefineURL = $searchPage->getFullURL("search=".urlencode($search)."&fulltext=true&tolerance=$tolerance&paths=$doPathSearch");

		// create refine links
		global $usgAllNamespaces;
		$namespaceFilterURLs = array();
		foreach($usgAllNamespaces as $ns => $img) {
			$namespaceFilterURLs[] = $searchPage->getFullURL("search=".urlencode($search)."&fulltext=true&restrict=$ns&tolerance=$tolerance&paths=$doPathSearch");
		}

		// create refine links table
		global $wgContLang;
		$restrictNS = $wgRequest->getVal('restrict');
		$restrictNS = $restrictNS === NULL ? NULL : intval($restrictNS);
		$html .= '<div id="us_refineresults_label" '.$styleShow.'>'.wfMsg('us_refinesearch').'</div>' .
        		 '<div id="us_refineresults_label_hide" '.$styleHide.'>'.wfMsg('us_refinesearch').'</div>';

		$refineResultsHtml = '<table cellspacing="0">';
		$highlight = $this->highlight(NULL, $restrictNS) ? "us_refinelinks_highlighted" : "us_refinelinks";
		$row =  '<td rowspan="2" width="100"><a class="'.$highlight.'" href="'.$noRefineURL.'">'.wfMsg('us_all').'</a></td>';

		$nsURL = reset($namespaceFilterURLs);
		$c = 0;

		foreach($usgAllNamespaces as $ns => $img) {
			if ($c > 0 && $c % 5 == 0) {
				$refineResultsHtml .= '<tr>'.$row.'</tr>';
				$row = "";
			}
			if ($c >= 5) $style="style=\"border-top: 1px solid;\""; else $style="";
			$nsName = $ns == NS_MAIN ? wfMsg('us_article') : $wgContLang->getNsText($ns);
			$highlight = $this->highlight($ns, $restrictNS) ? "us_refinelinks_highlighted" : "us_refinelinks";
			$textcolor = $this->highlight($ns, $restrictNS) ? 'color: white;"' : ""; // overwrite text color, if it is set by the skin
			$row .= '<td class="filtercolumn" '.$style.'><div style="margin: 6px;"><img alt="'.wfMsg('us_search_tooltip_refine', $nsName).'" title="'.wfMsg('us_search_tooltip_refine', $nsName).
                     '" style="vertical-align: baseline;margin-top: 1px;" src="'.UnifiedSearchResultPrinter::getImageURI($img ).'"/><a style="margin-left: 6px;vertical-align: top;'.$textcolor.'" class="'.$highlight.'" href="'.$nsURL.'">'.$nsName.
                     '</a></div></td><td '.$style.'>|</td>';
			$nsURL = next($namespaceFilterURLs);
			$c++;
		}

		// fill complete line of refinement links
		while ($c % 5 > 0) { $row .= '<td '.$style.'></td><td '.$style.'></td>'; $c++; }
		$refineResultsHtml .= '<tr>'.$row.'</tr>';
		$refineResultsHtml .= '</table></div>';

		// complete both html blocks for filter options and add them to the html
		$refineResultsHideHtml = '<div id="us_refineresults_hide" '.$styleHide.'>'.preg_replace('/href="[^"].*?"/', '', $refineResultsHtml);
		$refineResultsHtml = '<div id="us_refineresults" '.$styleShow.'>'.$refineResultsHtml;
		$html.= $refineResultsHtml.$refineResultsHideHtml;

		$totalHits = $searchSet != NULL ?  $searchSet->getTotalHits() : 0;

		// -- browsing --
		$next = $this->createBrowsingLink($search, $restrict, $offset + $limit, $limit, wfMsg('us_browse_next'));
		$previous = $this->createBrowsingLink($search,$restrict,$offset - $limit, $limit, wfMsg('us_browse_prev'));
		$limit20 = $this->createLimitLink($search,$restrict,$offset,  20, $limit );
		$limit50 = $this->createLimitLink($search,$restrict,$offset,  50, $limit);
		$limit100 = $this->createLimitLink($search,$restrict,$offset,  100, $limit);
		$limit250 = $this->createLimitLink($search,$restrict,$offset, 250, $limit);
		$limit500 = $this->createLimitLink($search,$restrict,$offset, 500, $limit);

		$nextButton =  (count($searchResults) < $limit) ? wfMsg('us_browse_next') : $next;
		$prevButton = ($offset == 0) ? wfMsg('us_browse_prev') : $previous;

		// browsing bar top
		if (count($searchResults) > 0) {
			$browsingBarTopHtml = "";
			$browsingBarTopHtml .= "<tr><td>".wfMsg('us_page')." ".(intval($offset/$limit)+1)." - ".(intval($totalHits/$limit)+1)."</td>";
			$browsingBarTopHtml .= "<td style=\"text-align: center;color: gray;\">($prevButton) ($nextButton)</td>";
			$browsingBarTopHtml .= "<td style=\"width: 33%; text-align: right;\">".wfMsg('us_entries_per_page')." ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</td></tr></table>";
			$browsingBarTopHideHtml = "<div id=\"us_browsing_top_hide_div\" $styleHide><table id=\"us_browsing_top_hide\">".preg_replace('/href="[^"].*?"/', '', $browsingBarTopHtml)."</div>";
			$browsingBarTopHtml = "<div id=\"us_browsing_top_div\" $styleShow><table id=\"us_browsing_top\">".$browsingBarTopHtml."</div>";
			$html.= $browsingBarTopHtml.$browsingBarTopHideHtml;
		}

		// -- show Did you mean --
		$didyoumeanURL = $searchPage->getFullURL("search=$suggestion");
		$wgOut->setPageTitle(wfMsg('us_search'));
		$html .= '<div id="us_didyoumean">'.($suggestion !== NULL ? "<i style=\"color:red;\">".wfMsg('us_didyoumean').":</i> <a style=\"text-decoration:underline;\" href=\"$didyoumeanURL\">".$suggestion."</a>" : "").'</div>';





		// create tab with fulltext search and path search and display search results as well

		$fulltextResults = '<div id="%%__DIV_NAME__%%"%%__STYLE_DISPLAY__%%>';
		$resultInfo =  wfMsg('us_resultinfo',$offset+1,$offset+$limit > $totalHits ? $totalHits : $offset+$limit, $totalHits, $search);
		if (count($searchResults) == 0) {
			$fulltextResults .= '<div style="margin:15px;">'.wfMsg('us_noresults_text', $search).'</div>';
		} else {
			$fulltextResults .= "<div id=\"us_resultinfo\">".wfMsg('us_results').": $resultInfo</div>";
			$fulltextResults .= UnifiedSearchResultPrinter::serialize($searchResults, $search);
		}
		$fulltextResults .= '</div>';

		// path search is enabled
		if (isset($wgUSPathSearch) && $wgUSPathSearch) {
			if ($searchSet != NULL)
			$psTerms = $this->initPathSearch($search, $searchSet);
			else
			$psTerms = $search;

			// start with html which is the same for both cases, paths have been found already or must be still searched
			$tabBarSearchResults = '
				    <div id="us_searchresults_tab">
				      <table style="border-collapse: collapse; width: 100%%;">
			    	    <tr>
			        	  <td style="border-bottom: 2px solid #AAA;"> </td>
						  <td class="us_tab_label" style="%s" onClick="javascript:switchTabs(0);">
						    '.wfMsg('us_pathsearch_tab_fulltext').'
						  </td>
						  <td style="border-bottom: 2px solid #AAA; width: 20px;"> </td>
						  <td class="us_tab_label" style="%s" onClick="javascript:switchTabs(1);%s">
                    	     '.wfMsg('us_pathsearch_tab_path').'
	                      </td>
						  <td style="border-bottom: 2px solid #AAA; width: 100%%;"></td>
						</tr>
						<tr><td colspan="5" width="100%%" style="border-left: 2px solid #AAA; border-right: 2px solid #AAA; border-bottom: 2px solid #AAA;">%s</td></tr>
		        	  </table>
			        </div>
			    ';

			// full text results will be displayed within a table below the tabs
			$styleDisplay = ' style="display: '.(($doPathSearch) ? 'none' : 'inline').';"';
			$fulltextResults = str_replace("%%__DIV_NAME__%%", 'us_fulltext_results', $fulltextResults);
			$fulltextResults = str_replace("%%__STYLE_DISPLAY__%%", $styleDisplay, $fulltextResults);

			// if we want to do a path search, do it and prepare results as well.
			// Otherwise this is done via Javascript later when clicking the link
			if ($doPathSearch == 1) {
				$psResultHtml = us_doPathSearch(urldecode($psTerms), true);
				$pathResults = '<div id="us_pathsearch_results" style="display: inline;">'.$psResultHtml.'</div>';
				$html .= sprintf($tabBarSearchResults, 'font-weight: normal; border: 2px solid #AAA;',
        	                                          'font-weight: bold; color: black; border-left: 2px solid #AAA; border-right: 2px solid #AAA; border-top: #FF8C00 solid;',
													  '',
				$fulltextResults . $pathResults);
			}
			else {
				$pathResults = '<div id="us_pathsearch_results" style="display: none;"></div>';
				$html .= sprintf($tabBarSearchResults, 'font-weight: bold; color: black; border-left: 2px solid #AAA; border-right: 2px solid #AAA; border-top: #FF8C00 solid;',
    	                                              'font-weight: normal; border: 2px solid #AAA;',
													  ' javascript:doPathSearch(\''.$psTerms.'\');',
				$fulltextResults . $pathResults);
			}
		}
		// pathsearch is disabled, no tab is displayed
		else
		$html .= str_replace("%%__DIV_NAME__%%", 'us_searchresults', $fulltextResults);


		// browsing bar bottom
		if (count($searchResults) > 0) {
			$html.= str_replace("us_browsing_top", "us_browsing_bottom", $browsingBarTopHtml);
			$html.= str_replace("us_browsing_top", "us_browsing_bottom", $browsingBarTopHideHtml);
		}
		$wgOut->addHTML($html);
	}

	private function highlight($exp_ns, $act_ns) {
		return $exp_ns === $act_ns;
	}

	private function createBrowsingLink($search, $restrict, $offset, $limit, $text="") {
		$searchPage = SpecialPage::getTitleFor("Search");
		$search = urlencode($search);
		$restrict =  empty($restrict) && $restrict !== '0' ? "" : "&restrict=$restrict";
		return '<a href="'.$searchPage->getFullURL("search=$search$restrict&fulltext=true&limit=$limit&offset=$offset").'">'.$text." ".$limit.'</a>';
	}

	private function createLimitLink($search, $restrict, $offset, $limit, $currentLimit) {
		$searchPage = SpecialPage::getTitleFor("Search");
		$search = urlencode($search);
		$label = ($limit == $currentLimit) ? "<b>$limit</b>" : $limit;
		$restrict = empty($restrict) && $restrict !== '0' ? "" : "&restrict=$restrict";
		return '<a href="'.$searchPage->getFullURL("search=$search$restrict&fulltext=true&limit=$limit&offset=$offset").'">'.$label.'</a>';
	}

	private function doSearch($limit, $offset) {
		global $wgRequest, $usgAllNamespaces, $wgExtraNamespaces;

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
        
        $allExtraNamespaces = array_diff(array_keys($wgExtraNamespaces), array_keys($usgAllNamespaces));
		$namespacesToSearch = $restrictNS !== NULL ? array($restrictNS) : array_merge(array_keys($usgAllNamespaces), $allExtraNamespaces);
		
		if (!self::userDefinedSearch($terms, $search)) {
			// non user-defined
			$contentTitleSearchPattern = 'contents:($1$4$5) OR title:($2$3$6)';

			if (isset($usgSKOSExpansion) && $usgSKOSExpansion === true) {
				$expandedFTSearch = SKOSExpander::expandForFulltext($terms, $tolerance);
				$expandedTitles = SKOSExpander::expandForTitles($terms, $namespacesToSearch , $tolerance);
			} else {
				$expandedFTSearch = QueryExpander::opTerms($terms, "AND");
				$expandedTitles = QueryExpander::opTerms($terms, "AND");
			}

			// find aggregated term, ie. terms which may be actually one term.
			$aggregatedTerms = "";
			if ($tolerance == US_HIGH_TOLERANCE || $tolerance == US_LOWTOLERANCE) {
				$aggregatedTerms = QueryExpander::opTerms(QueryExpander::findAggregatedTerms($terms), "AND");
			}

			// find synonyms
			global $usgSynsetExpansion;
			if ($usgSynsetExpansion == true && ($tolerance == US_HIGH_TOLERANCE || $tolerance == US_LOWTOLERANCE)) {
				$synonymTerms = QueryExpander::opTerms(Synsets::expandQuery($terms), "AND");
			} else {
				$synonymTerms = '';
			}



			$contentTitleSearchPattern = str_replace('$1', $expandedFTSearch, $contentTitleSearchPattern);
			$contentTitleSearchPattern = str_replace('$2', $expandedTitles, $contentTitleSearchPattern);

			// add agregated search terms
			$contentTitleSearchPattern = str_replace('$3', $aggregatedTerms == '' ? '' : (' OR '.$aggregatedTerms), $contentTitleSearchPattern);
			$contentTitleSearchPattern = str_replace('$4', $aggregatedTerms == '' ? '' : (' OR '.$aggregatedTerms), $contentTitleSearchPattern);

			// add synonyms
			$contentTitleSearchPattern = str_replace('$5', $synonymTerms == '' ? '' : (' OR '.$synonymTerms), $contentTitleSearchPattern);
			$contentTitleSearchPattern = str_replace('$6', $synonymTerms == '' ? '' : (' OR '.$synonymTerms), $contentTitleSearchPattern);

			// start search in raw mode
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

	private function initPathSearch(&$search, &$searchSet) {
		$sterms = $this->parseTerms($search);
		$sterms = $this->cleanTerms($sterms);
		$scoringTerms = array();
		foreach ($searchSet->mResults as $res) {
			list($score, $type, $term) = explode(' ', $res);
			foreach ($sterms as $s) {
				if (preg_match('/^'.preg_quote($s).'$/i', urldecode($term)))
				$scoringTerms[$s] = "$term,$type";
				else if (preg_match('/'.preg_quote($s).'/i', urldecode($term)) && (!isset($scoringTerms[$s])))
				$scoringTerms[$s] = "$term,$type";
			}
		}
		$psTerms = implode(',', $scoringTerms);
		for ($i = 0, $is = count($sterms); $i < $is; $i++) {
			if (isset($scoringTerms[$sterms[$i]])) unset($sterms[$i]);
		}
		if (count($sterms) > 0)	{
			if (strlen($psTerms) > 0) $psTerms.= ',';
			$psTerms .= implode(',-1,', $sterms).',-1';
		}
		return $psTerms.'%26'.$search;
	}

}


