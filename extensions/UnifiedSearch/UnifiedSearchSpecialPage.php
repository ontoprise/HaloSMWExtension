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
		$html = wfMsg('us_searchfield').': <form id="us_searchform"><input id="us_searchfield" type="text" size="30" name="search">'.
    	    '<input type="submit" name="searchbutton" value="'.wfMsg('us_searchbutton').'">'.
    	    '<input type="hidden" name="fulltext" value="true">'.
			'<input type="radio" name="tolerance" class="tolerantsearch" onclick="smwhg_toleranceselector.onClick(0)" value="0">'.wfMsg('us_tolerantsearch').'</input>'.
			'<input type="radio" name="tolerance" class="semitolerantsearch" onclick="smwhg_toleranceselector.onClick(1)" value="1">'.wfMsg('us_semitolerantsearch').'</input>'.
			'<input type="radio" name="tolerance" class="exactsearch" onclick="smwhg_toleranceselector.onClick(2)" value="2">'.wfMsg('us_exactsearch').'</input>'.
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
		$row =  '<td><a class="us_refinelinks" href="'.$noRefineURL.'">'.$this->highlight(wfMsg('us_all'), NULL, $restrictNS).'</a>';

		$nsURL = reset($namespaceFilterURLs);
		$c = 1;

		foreach($usgAllNamespaces as $ns => $img) {
			if ($c > 0 && $c % 5 == 0) {
				$html .= '<tr>'.$row.'</tr>';
				$row = "";
			}

			$nsName = $ns == NS_MAIN ? wfMsg('us_article') : $wgContLang->getNsText($ns);
			$row .= '<td><img style="margin-bottom: 6px;" src="'.UnifiedSearchResultPrinter::getImageURI($img ).'"/><a class="us_refinelinks" href="'.$nsURL.'">'.$this->highlight($nsName,$ns, $restrictNS).'</a></td>';
			$nsURL = next($namespaceFilterURLs);
			$c++;
		}
		$html .= '<tr>'.$row.'</tr>';
		$html .= '</table></div>';

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

		$html .= "<div id=\"us_browsing\">($prevButton) ($nextButton) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</div>";




		$totalHits = $searchSet != NULL ?  $searchSet->getTotalHits() : 0;
		$html .= '<div style="float:right;padding-top:7px;margin-right:30px;">'.wfMsg('us_totalresults').': <b>'.($totalHits).'</b></div>';


		// heading
		if (count($searchResults) > 0) {
			$html .= "<h2>".wfMsg('us_results')."</h2>";
		} else {
			$html .= "<h2>".wfMsg('us_noresults')."</h2>";
		}

		// Did you mean
		$didyoumeanURL = $searchPage->getFullURL("search=$suggestion");
		$wgOut->setPageTitle(wfMsg('us_search'));
		$html .= '<div id="us_didyoumean">'.($suggestion !== NULL ? "<i style=\"color:red;\">".wfMsg('us_didyoumean').":</i> <a style=\"text-decoration:underline;\" href=\"$didyoumeanURL\">".$suggestion."</a>" : "").'</div>';

		// search results
		$html .= '<div id="us_searchresults">';

		$html .= UnifiedSearchResultPrinter::serialize($searchResults, $search);
		$html .= '</div>';
		$wgOut->addHTML($html);
	}

	private function highlight($term, $exp_ns, $act_ns) {
		return $exp_ns === $act_ns ? '<b>'.$term.'</b>' : $term;
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

		if (!self::userDefinedSearch($terms, $search)) {
			// non user-defined
			$defaultPattern = 'contents:($1) OR title:($2)';	
			$expandedFTSearch = QueryExpander::expandForFulltext($terms, $tolerance);
			$expandedTitles = QueryExpander::expandForTitles($terms, $restrictNS !== NULL ? array($restrictNS) : array_keys($usgAllNamespaces), $limit, $offset, $tolerance);
			$defaultPattern = str_replace('$1', $expandedFTSearch, $defaultPattern);
			$defaultPattern = str_replace('$2', $expandedTitles, $defaultPattern);
			$searchSet = LuceneSearchSet::newFromQuery( 'raw', 	$defaultPattern, $restrictNS !== NULL ? array($restrictNS) : array_keys($usgAllNamespaces), $limit, $offset);
            
			if ($searchSet->getTotalHits() == 0) {
				$searchSet = LuceneSearchSet::newFromQuery( 'suggest',  $search, $restrictNS !== NULL ? array($restrictNS) : array_keys($usgAllNamespaces), $limit, $offset);
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

			$searchSet = LuceneSearchSet::newFromQuery( 'search',
			$search , $restrictNS !== NULL ? array($restrictNS) : array_keys($usgAllNamespaces), $limit, $offset);

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

			if ($nextFulltext != false && !$nextFulltext->isMissingRevision()) {
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
		foreach($terms as $term) {
			$term = strtolower($term);
			if ($term == 'and' || $term == 'or' || $term == 'not') {
				return true;
			}
		}
		
		return preg_match('/\[\d+\]:/', $search) !== 0 || strpos($search, '~') !== false;

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
