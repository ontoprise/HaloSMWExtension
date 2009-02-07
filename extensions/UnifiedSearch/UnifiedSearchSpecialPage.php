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

		$ft_offset = $wgRequest->getVal('ft_offset') !== NULL ? explode(",", $wgRequest->getVal('ft_offset')) : array(0);
		$ti_offset = $wgRequest->getVal('ti_offset') !== NULL ? explode(",", $wgRequest->getVal('ti_offset')) : array(0);

		$newpage = Title::newFromText($search);


		$searchPage = SpecialPage::getTitleFor("Search");
		
		// do search 
		if (trim($search) != '') {
		    list($searchResults,$searchSet, $totalNumTitle, $next_ft_offset, $next_ti_offset) = $this->doSearch($limit, $ti_offset, $ft_offset);
			// save results for statistics
			if ($searchSet !== NULL) USStore::getStore()->addSearchTry($search, $searchSet->numRows() + $totalNumTitle);
		} else {
			// initialize when searchstring is empty
			$searchResults = array();
			$searchSet = NULL;
			$totalNumTitle = 0;
			$next_ft_offset = 0;
			$next_ti_offset = 0;
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
		$ft_offset_next = array_clone($ft_offset);
		$ft_offset_next[] = end($ft_offset) + $next_ft_offset;
		$ft_offset_prev = array_clone($ft_offset);
		$ft_offset_prev = count($ft_offset_prev) > 1 ? array_slice($ft_offset_prev, 0, count($ft_offset_prev)-1) : $ft_offset_prev;

		$ti_offset_next = array_clone($ti_offset);
		$ti_offset_next[] = end($ti_offset) + $next_ti_offset;
		$ti_offset_prev = array_clone($ti_offset);
		$ti_offset_prev = count($ti_offset_prev) > 1 ? array_slice($ti_offset_prev, 0, count($ti_offset_prev)-1) : $ti_offset_prev;

		$next = $this->createBrowsingLink($search, $ti_offset_next, $ft_offset_next, $limit, wfMsg('us_browse_next'));
		$previous = $this->createBrowsingLink($search,$ti_offset_prev, $ft_offset_prev, $limit, wfMsg('us_browse_prev'));
		$limit20 = $this->createBrowsingLink($search,$ti_offset, $ft_offset, 20 );
		$limit50 = $this->createBrowsingLink($search,$ti_offset, $ft_offset, 50);
		$limit100 = $this->createBrowsingLink($search,$ti_offset, $ft_offset, 100);
		$limit250 = $this->createBrowsingLink($search,$ti_offset, $ft_offset, 250);
		$limit500 = $this->createBrowsingLink($search,$ti_offset, $ft_offset, 500);

		$nextButton =  (count($searchResults) == 0) ? wfMsg('us_browse_next') : $next;
		$prevButton = (end($ft_offset) == 0 && end($ti_offset) == 0) ? wfMsg('us_browse_prev') : $previous; 
		if (end($ft_offset) == 0 && end($ti_offset) == 0) {
			$html .= "<div id=\"us_browsing\">($prevButton) ($nextButton) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</div>";
		} else {
			
			$html .= "<div id=\"us_browsing\">($prevButton) ($nextButton) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</div>";
		}

		$totalFTHits = $searchSet != NULL ?  $searchSet->getTotalHits() : 0;
        $html .= '<div style="float:right;padding-top:7px;margin-right:30px;">'.wfMsg('us_totalresults').': <b>'.($totalFTHits+$totalNumTitle).'</b></div>';
        
        
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

	private function createBrowsingLink($search, $ti_offset, $ft_offset, $limit, $text="") {
		$searchPage = SpecialPage::getTitleFor("Search");
		return '<a href="'.$searchPage->getFullURL("search=$search&fulltext=true&limit=$limit&ti_offset=".implode(",", $ti_offset).
		                                           "&ft_offset=".implode(",", $ft_offset)).'">'.$text." ".$limit.'</a>';

	}

	private function doSearch($limit, $ti_offset, $ft_offset) {
		global $wgRequest, $usgAllNamespaces;
			
		// initialize vars
		$search = $wgRequest->getVal('search');
		$restrictNS = $wgRequest->getVal('restrict');
		$tolerance = $wgRequest->getVal('tolerance');
        $tolerance = $tolerance == NULL ? 0 : $tolerance;
		
		$titleSearchSet = array();
		$skosTitleSearchSet = array();

		$exactQuery = true; // assume exact query without expansion
        
		

		// expand query
		$terms = self::parseTerms($search);
			
		$lastTIOffset = end($ti_offset);
		$totalTitleNum = 0;
		
		// if query contains boolean operators, consider as as user-defined
		// and do not use title search and pass search string unchanged to Lucene
		
		if (!self::userDefinedSearch($terms, $search)) {
			// non user-defined
			$exactQuery = false;
			$expandedSearch = QueryExpander::expand($terms, $tolerance);
			list($titleSearchSet, $totalTitleNum) = USStore::getStore()->lookUpTitles($terms,
			$restrictNS !== NULL ? array($restrictNS) : array_keys($usgAllNamespaces), $limit , $lastTIOffset, $tolerance );
			
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
			
		}


		// query lucene server
		switch($restrictNS) {
			case SMW_NS_PROPERTY: $ns_ft = "[".SMW_NS_PROPERTY."]:"; break;
			case NS_CATEGORY: $ns_ft = "[".NS_CATEGORY."]:"; break;
			case NS_TEMPLATE: $ns_ft = "[".NS_TEMPLATE."]:"; break;
			case NS_MAIN:
			default: $ns_ft = "";
		}

		// add title matches
		$resultSet = array();

		$lastFTOffset = end($ft_offset);

		$searchSet = LuceneSearchSet::newFromQuery( 'search', $ns_ft .
		($exactQuery ? $search : $expandedSearch), $restrictNS !== NULL ? array($restrictNS) : array_keys($usgAllNamespaces), $limit, $lastFTOffset);
		
        
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
		$i = 1;
		$j = 1;
		$first = reset($titleSearchSet);
		if ($first !== false) $resultSet[] = $first;
		$c = $limit;
		while ($i+$j <= $limit && $c > 0) {

			$nextFulltext = $searchSet !== NULL ? $searchSet->next() : NULL;
			if ($nextFulltext != NULL && !$nextFulltext->isMissingRevision()) {
				$lr = UnifiedSearchResult::newFromLuceneResult($nextFulltext, $terms);
				$lr->setWordCount($nextFulltext->getWordCount());
				$lr->setTimeStamp($nextFulltext->getTimestamp());
				$resultSet[] = $lr;
				$i++;
			}
			if ($i+$j > $limit) break;
            $nextTitle = next($titleSearchSet);
			if ($nextTitle !== false) {
			     $resultSet[] = $nextTitle;
			     $j++;
			}
			
            $c--;

		}


		// sort results by score
		UnifiedSearchResult::sortByScore($resultSet);

		// result tuple consisting of result set, lucene searchset, total number of title matches 
		// and offsets of fulltext and title search
		return array($resultSet, $searchSet, $totalTitleNum, $i, $j);
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
		return preg_match('/\[\d+\]:/', $search) !== 0;
		
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
