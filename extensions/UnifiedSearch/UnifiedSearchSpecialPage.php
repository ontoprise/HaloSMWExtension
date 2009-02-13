<?php
/*
 * Created on 28.01.2009
 *
 * @author: Kai K�hn
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
			if( is_null( $t ) ){
				return; //TODO: return something
			}

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

		// suggestion (Did you mean?)
		$suggestion = $searchSet != NULL ? $searchSet->getSuggestionQuery() : NULL;
		if ($suggestion != NULL) {

			$suggestion = str_replace('_', ' ', $suggestion);

		}
			
		// serialize HTML
		$html = '<form id="us_searchform"><table><tr><td>'.wfMsg('us_searchfield').'</td><td>'.wfMsg('us_tolerance').'</td><td></td></tr><tr><td><input id="us_searchfield" type="text" size="30" name="search"></td>'.
    	    '<td><select id="toleranceSelector" name="tolerance" onchange="smwhg_toleranceselector.onChange()"><option id="tolerantOption"  value="0">'.wfMsg('us_tolerantsearch').'</option>'.
            '<option id="semitolerantOption"  value="1">'.wfMsg('us_semitolerantsearch').'</option>'.
            '<option id="exactOption"  value="2">'.wfMsg('us_exactsearch').'</option></select></td>'.
    	    '<td><input type="submit" name="searchbutton" value="'.wfMsg('us_searchbutton').'"><input type="hidden" name="fulltext" value="true"></td></tr></table>'.

		'</form>';
			
			
		// new link
		if ($newpage !== NULL && !$newpage->exists()) {
			$newLink = '<a class="new" href="'.$newpage->getFullURL('action=edit').'">'.wfMsg('us_page').'</a>';
			$html .= '<div id="us_newpage">'.wfMsg('us_page_does_not_exist', $newLink).'</div>';
		}

		// refine links
		$tolerance = $wgRequest->getVal('tolerance');
		$tolerance = $tolerance == NULL ? 0 : $tolerance;
		$noRefineURL = $searchPage->getFullURL("search=$search&fulltext=true&tolerance=$tolerance");

		global $usgAllNamespaces;
		$namespaceFilterURLs = array();
		foreach($usgAllNamespaces as $ns => $img) {
			$namespaceFilterURLs[] = $searchPage->getFullURL("search=$search&fulltext=true&restrict=$ns&tolerance=$tolerance");
		}


		global $wgContLang;
		$restrictNS = $wgRequest->getVal('restrict');
		$restrictNS = $restrictNS === NULL ? NULL : intval($restrictNS);
		$html .= wfMsg('us_refinesearch');
		$html .='<div id="us_refineresults"><table>';
		$highlight = $this->highlight(NULL, $restrictNS) ? "us_refinelinks_highlighted" : "us_refinelinks";
		$row =  '<td><a class="'.$highlight.'" href="'.$noRefineURL.'">'.wfMsg('us_all').'</a>';

		$nsURL = reset($namespaceFilterURLs);
		$c = 1;

		foreach($usgAllNamespaces as $ns => $img) {
			if ($c > 0 && $c % 5 == 0) {
				$html .= '<tr>'.$row.'</tr>';
				$row = "";
			}

			$nsName = $ns == NS_MAIN ? wfMsg('us_article') : $wgContLang->getNsText($ns);
			$highlight = $this->highlight($ns, $restrictNS) ? "us_refinelinks_highlighted" : "us_refinelinks";
			$row .= '<td><img style="margin-bottom: 6px;" src="'.UnifiedSearchResultPrinter::getImageURI($img ).'"/><a class="'.$highlight.'" href="'.$nsURL.'">'.$nsName.'</a></td>';
			$nsURL = next($namespaceFilterURLs);
			$c++;
		}
		$html .= '<tr>'.$row.'</tr>';
		$html .= '</table></div>';


		$totalHits = $searchSet != NULL ?  $searchSet->getTotalHits() : 0;
		//$html .= '<div style="float:right;padding-top:7px;margin-right:30px;">'.wfMsg('us_totalresults').': <b>'.($totalHits).'</b></div>';

		// browsing
		$next = $this->createBrowsingLink($search, $offset + $limit, $limit, wfMsg('us_browse_next'));
		$previous = $this->createBrowsingLink($search,$offset - $limit, $limit, wfMsg('us_browse_prev'));
		$limit20 = $this->createBrowsingLink($search,$offset,  20 );
		$limit50 = $this->createBrowsingLink($search,$offset,  50);
		$limit100 = $this->createBrowsingLink($search,$offset,  100);
		$limit250 = $this->createBrowsingLink($search,$offset, 250);
		$limit500 = $this->createBrowsingLink($search,$offset, 500);

		$nextButton =  (count($searchResults) < $limit) ? wfMsg('us_browse_next') : $next;
		$prevButton = ($offset == 0) ? wfMsg('us_browse_prev') : $previous;

		if (count($searchResults) > 0) {
			$html .= "<table id=\"us_browsing\"><tr><td>".(intval($offset/$limit)+1)." von ".(intval($totalHits/$limit)+1)."</td>";
			$html .= "<td style=\"text-align: center;\">($prevButton) ($nextButton)</td>";
			$html .= "<td style=\"width: 33%; text-align: right;\">".wfMsg('us_entries_per_page')." ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</td></tr></table>";
		}
		// Did you mean
		$didyoumeanURL = $searchPage->getFullURL("search=$suggestion");
		$wgOut->setPageTitle(wfMsg('us_search'));
		$html .= '<div id="us_didyoumean">'.($suggestion !== NULL ? "<i style=\"color:red;\">".wfMsg('us_didyoumean').":</i> <a style=\"text-decoration:underline;\" href=\"$didyoumeanURL\">".$suggestion."</a>" : "").'</div>';

		if (count($searchResults) == 0) {
			$html .= wfMsg('us_noresults_text', $search);
		}

		// search results
			
		// heading
		if (count($searchResults) > 0) {
			$html .= '<div id="us_searchresults">';
			$resultInfo =  wfMsg('us_resultinfo',$offset+1,$offset+$limit > $totalHits ? $totalHits : $offset+$limit, $totalHits, $search);
			$html .= "<div id=\"us_resultinfo\">".wfMsg('us_results').": $resultInfo</div>";
			$html .= UnifiedSearchResultPrinter::serialize($searchResults, $search);
			$html .= '</div>';
		}

		if (count($searchResults) > 0) {
			$html .= "<table id=\"us_browsing\"><tr><td>".(intval($offset/$limit)+1)." von ".(intval($totalHits/$limit)+1)."</td>";
			$html .= "<td style=\"text-align: center;\">($prevButton) ($nextButton)</td>";
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

	private function doSearch($limit, $offset) {
		global $wgRequest, $usgAllNamespaces;
			
		// initialize vars
		$search = $wgRequest->getVal('search');
		$restrictNS = $wgRequest->getVal('restrict');
		$tolerance = $wgRequest->getVal('tolerance');
		$tolerance = $tolerance == NULL ? 0 : $tolerance;



		// expand query
		$terms = self::parseTerms($search);
			

		// query lucene server

		// if query contains boolean operators, consider as as user-defined
		// and do not use title search and pass search string unchanged to Lucene

		$namespacesToSearch = $restrictNS !== NULL ? array($restrictNS) : array_keys($usgAllNamespaces);
		if (!self::userDefinedSearch($terms, $search)) {
			// non user-defined
			$contentTitleSearchPattern = 'contents:($1) OR title:($2)';
			$expandedFTSearch = QueryExpander::expandForFulltext($terms, $tolerance);
			$expandedTitles = QueryExpander::expandForTitles($terms, $namespacesToSearch , $tolerance);
			$contentTitleSearchPattern = str_replace('$1', $expandedFTSearch, $contentTitleSearchPattern);
			$contentTitleSearchPattern = str_replace('$2', $expandedTitles, $contentTitleSearchPattern);
			$searchSet = LuceneSearchSet::newFromQuery( 'raw', 	$contentTitleSearchPattern, $namespacesToSearch, $limit, $offset);

			if ($searchSet->getTotalHits() == 0) {
				// use enhanced lucene search method with SKOS expansion for fulltext
				$searchSet = LuceneSearchSet::newFromQuery( 'search',  $expandedFTSearch, $namespacesToSearch, $limit, $offset);
			}

			global $wgLuceneSearchVersion;
			if ($searchSet->getTotalHits() == 0 && $wgLuceneSearchVersion >= 2.1) {
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

			$searchSet = LuceneSearchSet::newFromQuery( 'search', $search , $namespacesToSearch, $limit, $offset);

		}

		// add matches
		$resultSet = array();

		if ($searchSet == NULL) {
			return array($resultSet, NULL);
		}




			
		// remove remaining syntax elements from term array for highlightinh
		for($i = 0; $i < count($terms); $i++) {
			$terms[$i] = str_replace('~', '', $terms[$i]); // remove unsharp search hint
			$terms[$i] = preg_replace('/\[\d+\]:/', '', $terms[$i]); // remove namespace hint
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
				$lr = UnifiedSearchResult::newFromLuceneResult($nextFulltext, $terms);
				$lr->setWordCount($nextFulltext->getWordCount());
				$lr->setTimeStamp($nextFulltext->getTimestamp());
				$resultSet[] = $lr;
				$i++;
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
		}

		 // check for special lucene syntax
		return preg_match('/\[\d+\]:/', $search) !== 0  // namespace prefix, e.g.  [12]:
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

		foreach($matches[0] as $term) {
			// unquote if necessary
			if (substr($term, 0, 1) == '"' && substr($term, strlen($term)-1, 1) == '"') {
				$term = substr($term, 1, strlen($term)-2);
				$term = str_replace(' ','_',$term);
			}

			$terms[] = $term;//strtolower($term);
		}
		return $terms;
	}
}




?>
