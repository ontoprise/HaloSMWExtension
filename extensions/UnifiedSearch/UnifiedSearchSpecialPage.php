<?php
/*
 * Created on 12.03.2007
 *
 * Author: kai
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
 * Called when gardening request in sent in wiki
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
		}

		$limit =  $wgRequest->getVal('limit') !== NULL ? $wgRequest->getVal('limit') : 20;
		$offset =  $wgRequest->getVal('offset') !== NULL ? $wgRequest->getVal('offset') : 0;
        $ft_offset = $wgRequest->getVal('ft_offset') !== NULL ? explode(",", $wgRequest->getVal('ft_offset')) : array(0);
        
		$newpage = Title::newFromText($search);


		$searchPage = SpecialPage::getTitleFor("Search");
		# Otherwise show special search page
		list($searchResults,$searchSet, $totalNumTitle, $next_ft_offset) = $this->doSearch($limit, $offset, $ft_offset);
		$numOfResults = count($searchResults);
		$suggestion = $searchSet != NULL ? $searchSet->getSuggestionQuery() : NULL;

		$html = "";
		$didyoumeanURL = $searchPage->getFullURL("search=$suggestion");
		$wgOut->setPageTitle(wfMsg('us_search'));
		$html .= '<div id="us_didyoumean">'.($suggestion !== NULL ? "<i style=\"color:red;\">".wfMsg('us_didyoumean').":</i> <a style=\"text-decoration:underline;\" href=\"$didyoumeanURL\">".$suggestion."</a>" : "").'</div>';
		if ($newpage !== NULL) {
			$newLink = '<a class="new" href="'.$newpage->getFullURL('action=edit').'">'.wfMsg('us_page').'</a>';
			$html .= wfMsg('us_page_does_not_exist', $newLink);
		}
		// refine elements

		$noRefineURL = $searchPage->getFullURL("search=$search&fulltext=true");
		$refineInstancesURL = $searchPage->getFullURL("search=$search&fulltext=true&restrict=0");
		$refineCategories = $searchPage->getFullURL("search=$search&fulltext=true&restrict=14");
		$refineProperties = $searchPage->getFullURL("search=$search&fulltext=true&restrict=102");
		$refineTemplates = $searchPage->getFullURL("search=$search&fulltext=true&restrict=10");

		$totalFTHits = $searchSet != NULL ?  $searchSet->getTotalHits() : 0;
		$html .= '<div style="float:right;padding-top:7px;margin-right:30px;">'.wfMsg('us_totalfulltextnum').': <b>'.$totalFTHits.'</b>, '.wfMsg('us_totaltitlenum').': <b>'.$totalNumTitle.'</b></div>';
        $html .='<div id="us_refineresults">'.wfMsg('us_refinesearch').': '.
                '<a class="us_refinelinks" href="'.$noRefineURL.'">'.wfMsg('us_all').'</a>, '.
                '<a class="us_refinelinks" href="'.$refineInstancesURL.'">'.wfMsg('us_article').'</a>,'.
                '<a class="us_refinelinks" href="'.$refineCategories.'">'.wfMsg('us_category').'</a>,'.
                '<a class="us_refinelinks" href="'.$refineProperties.'">'.wfMsg('us_property').'</a>,'.
                '<a class="us_refinelinks" href="'.$refineTemplates.'">'.wfMsg('us_template').'</a>';
              
        $html .= '</div>';
		// browsing
		$ft_offset_next = array_clone($ft_offset);
		$ft_offset_next[] = $next_ft_offset;
		$ft_offset_prev = array_clone($ft_offset);
		$ft_offset_prev = count($ft_offset_prev) > 1 ? array_slice($ft_offset_prev, 0, count($ft_offset_prev)-1) : $ft_offset_prev;
		
		$next = $this->createBrowsingLink($search, $offset+$limit, $ft_offset_next, $limit, wfMsg('us_browse_next'));
		$previous = $this->createBrowsingLink($search,$offset-$limit >= 0 ? $offset-$limit : 0, $ft_offset_prev, $limit, wfMsg('us_browse_prev'));
		$limit20 = $this->createBrowsingLink($search,$offset, $ft_offset, 20 );
		$limit50 = $this->createBrowsingLink($search,$offset, $ft_offset, 50);
		$limit100 = $this->createBrowsingLink($search,$offset, $ft_offset, 100);
		$limit250 = $this->createBrowsingLink($search,$offset, $ft_offset, 250);
		$limit500 = $this->createBrowsingLink($search,$offset, $ft_offset, 500);

		if ($offset == 0) {
			$html .= "<div id=\"us_browsing\">(".wfMsg('us_browse_prev').") ($next) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</div>";
		} else {
			$html .= "<div id=\"us_browsing\">($previous) ($next) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</div>";
		}
     
		$html .= "<h2>".wfMsg('us_results')."  ".($offset+1)." - ".($offset+$numOfResults)." </h2>";
        
		// search results
		$html .= '<div id="us_searchresults">';
		
		$html .= UnifiedSearchResultPrinter::serialize($searchResults);
		$html .= '</div>';
		$wgOut->addHTML($html);
	}

	private function createBrowsingLink($search, $offset, $ft_offset, $limit, $text="") {
		$searchPage = SpecialPage::getTitleFor("Search");
		return '<a href="'.$searchPage->getFullURL("search=$search&fulltext=true&limit=$limit&offset=$offset&ft_offset=".implode(",", $ft_offset)).'">'.$text." ".$limit.'</a>';

	}

	private function doSearch($limit, $offset, $ft_offset) {
		global $wgRequest;
	  
		// initialize vars
		$search = $wgRequest->getVal('search');
		$restrictNS = $wgRequest->getVal('restrict');
        $tolerance = $wgRequest->getVal('tolerance');
        
		$titleSearchSet = array();
		$skosTitleSearchSet = array();

		$exactQuery = false;
		$allNamespaces = array(NS_MAIN, NS_CATEGORY, SMW_NS_PROPERTY, NS_TEMPLATE);

		// expand query
		$terms = self::parseTerms($search);
				 
		// if query contains boolean operators do not use title search
		if (self::exactQuery($terms, $search)) {
			// exact lucene query. Do not expand. No title search
            $exactQuery = true;
			
		} else {
			$expandedSearch = QueryExpander::expand($terms);
            list($titleSearchSet, $totalNum) = USStore::getStore()->lookUpTitlesByText($terms,
            $restrictNS !== NULL ? array($restrictNS) : $allNamespaces, false, $limit/2 , $offset/2 );
            if (count($titleSearchSet) == 0) {
                list($titleSearchSet, $totalNum) = USStore::getStore()->lookUpTitlesByText($terms,
                $restrictNS !== NULL ? array($restrictNS) : $allNamespaces, true, $limit/2 , $offset/2 );
            }

            $skosTitleSearchSet = USStore::getStore()->lookupTitleBySKOS($terms,
            $restrictNS !== NULL ? array($restrictNS) : $allNamespaces, $limit/2 , $offset/2  );
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
        $resultSet = array_merge($resultSet, self::mergeTitlesUnique($titleSearchSet, $skosTitleSearchSet));
        
		$titleNum = count($resultSet);
		$lastOffset = end($ft_offset);
		
		$searchSet = LuceneSearchSet::newFromQuery( 'search', $ns_ft .
		($exactQuery ? $search : $expandedSearch), $restrictNS !== NULL ? array($restrictNS) : $allNamespaces, $limit - $titleNum, $lastOffset);


		$suggestion = NULL; // did you mean

		// build results
		if ($searchSet!=NULL) {
			$suggestion = $searchSet->getSuggestionQuery();
		 $result = $searchSet->next();
		 while($result !== false) {
		 	if (!$result->isMissingRevision()) {
		 		$resultSet[] = UnifiedSearchResult::newFromLuceneResult($result, $terms);
		 	}
		 	$result = $searchSet->next();
		 }
		}

		
		
		// sort results by score
		UnifiedSearchResult::sortByScore($resultSet);

		// serialize result table
		return array($resultSet, $searchSet, $totalNum, $limit - $titleNum);
	}
	
	/**
	 * Returns true if the $terms contain boolean operators or $search contains namespace prefixes
	 *
	 * @param unknown_type $queryString
	 * @return unknown
	 */
	private static function exactQuery($terms, $search) {
		return in_array('and', $terms) || in_array('or', $terms) || in_array('not', $terms) || preg_match('/\[\d+\]:/', $search) !== 0;
	}
	
	/**
	 * Merges two titles arrays and filters doubles
	 *
	 * @param array $titles1
	 * @param array $titles2
	 * @return unknown
	 */
	private static function mergeTitlesUnique(array $titles1, array $titles2) {
		$result = array();
		foreach($titles2 as $t2) {
			$found = false;
			foreach($titles1 as $t1) {
				if ($t2->getTitle()->equals($t1->getTitle())) { $found=true; break; }
			}
			if (!$found) $result[] = $t2;
		}
		return array_merge($titles1, $result);
	}

	public static function parseTerms($termString) {
		$terms = array();
		// split terms at whitespaces unless they are quoted
		preg_match_all('/([^\s"]+|"[^"]+")+/', $termString, $matches);

		foreach($matches[0] as $term) {
			// unquote if necessary
			if (substr($term, 0, 1) == '"' && substr($term, strlen($term)-1, 1) == '"') {
				$term = substr($term, 1, strlen($term)-2);
				$term = str_replace(' ','_',$term);
			}
			// remove namespace hint
			$term = preg_replace('/\[\d+\]:/', '', $term);
			$terms[] = strtolower($term);
		}
		return $terms;
	}
}




?>
