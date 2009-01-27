<?php
/*
 * Created on 12.03.2007
 *
 * Author: kai
 */
if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( "$IP/includes/SpecialPage.php" );



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

		$newpage = Title::newFromText($search);


		$searchPage = SpecialPage::getTitleFor("Search");
		# Otherwise show special search page
		list($searchResults,$suggestion) = $this->doSearch($limit, $offset);
		$numOfResults = count($searchResults);
		//$suggestion = "auto";

		$html = "";
		$didyoumeanURL = $searchPage->getFullURL("search=$suggestion");
		$wgOut->setPageTitle(wfMsg('us_search'));
		$html .= '<div id="us_didyoumean">'.($suggestion !== NULL ? "<i style=\"color:red;\">Meinten Sie:</i> <a style=\"text-decoration:underline;\" href=\"$didyoumeanURL\">".$suggestion."</a>" : "").'</div>';
		if ($newpage !== NULL) {
			$newLink = '<a class="new" href="'.$newpage->getFullURL('action=edit').'">'.wfMsg('us_page').'</a>';
			$html .= wfMsg('us_page_does_not_exist', $newLink);
		}
		// refine elements

		$noRefineURL = $searchPage->getFullURL("search=$search");
		$refineInstancesURL = $searchPage->getFullURL("search=$search&restrict=0");
		$refineCategories = $searchPage->getFullURL("search=$search&restrict=14");
		$refineProperties = $searchPage->getFullURL("search=$search&restrict=102");
		$refineTemplates = $searchPage->getFullURL("search=$search&restrict=10");
			
		

		// browsing
		$next = $this->createBrowsingLink($search, $offset+$limit, $limit, wfMsg('us_browse_next'));
		$previous = $this->createBrowsingLink($search,$offset-$limit >= 0 ? $offset-$limit : 0, $limit, wfMsg('us_browse_prev'));
		$limit20 = $this->createBrowsingLink($search,$offset, 20, "20");
		$limit50 = $this->createBrowsingLink($search,$offset, 50, "50");
		$limit100 = $this->createBrowsingLink($search,$offset, 100, "100");
		$limit250 = $this->createBrowsingLink($search,$offset, 250, "250");
		$limit500 = $this->createBrowsingLink($search,$offset, 500, "500");

		if ($offset == 0) {
			$html .= "<div id=\"us_browsing\">(".wfMsg('us_browse_prev').") ($next) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</div>";
		} else {
			$html .= "<div id=\"us_browsing\">($previous) ($next) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)</div>";
		}

		$html .= "<h2>".wfMsg('us_results')."</h2>";
        $html .='<div id="us_refineresults">'.wfMsg('us_refinesearch').': '.
                '<a class="us_refinelinks" href="'.$noRefineURL.'">'.wfMsg('us_all').'</a>, '.
                '<a class="us_refinelinks" href="'.$refineInstancesURL.'">'.wfMsg('us_article').'</a>,'.
                '<a class="us_refinelinks" href="'.$refineCategories.'">'.wfMsg('us_category').'</a>,'.
                '<a class="us_refinelinks" href="'.$refineProperties.'">'.wfMsg('us_property').'</a>,'.
                '<a class="us_refinelinks" href="'.$refineTemplates.'">'.wfMsg('us_template').'</a>'.
                '</div>';
		// search results
		$html .= '<div id="us_searchresults">';
		$html .= $searchResults;
		$html .= '</div>';
		$wgOut->addHTML($html);
	}

	private function createBrowsingLink($search, $offset, $limit, $text="") {
		$searchPage = SpecialPage::getTitleFor("Search");
		return '<a href="'.$searchPage->getFullURL("search=$search&limit=$limit&offset=$offset").'">'.$text.'</a>';

	}

	private function doSearch($limit, $offset) {
		global $wgRequest;
	  
		// initialize vars
		$search = $wgRequest->getVal('search');
		$restrictNS = $wgRequest->getVal('restrict');

		$titleSearchSet = array();
		$skosTitleSearchSet = array();

		$exactQuery = false;
		$allNamespaces = array(NS_MAIN, NS_CATEGORY, SMW_NS_PROPERTY, NS_TEMPLATE);

		// expand query
		$terms = self::parseTerms($search);
				 
		// if query contains boolean operators do not use title search
		if (!in_array('and', $terms) && !in_array('or', $terms) && !in_array('not', $terms) && preg_match('/\[\d+\]:/', $search) === 0) {
			
			$expandedSearch = QueryExpander::expand($terms);
			$titleSearchSet = WikiTitleSearch::getStore()->lookUpTitlesByText($terms,
			$restrictNS !== NULL ? array($restrictNS) : $allNamespaces, false, $limit / 2, $offset);
			if (count($titleSearchSet) == 0) {
				$titleSearchSet = WikiTitleSearch::getStore()->lookUpTitlesByText($terms,
				$restrictNS !== NULL ? array($restrictNS) : $allNamespaces, true, $limit / 2, $offset);
			}

			$skosTitleSearchSet = WikiTitleSearch::getStore()->lookupTitleBySKOS($terms,
			$restrictNS !== NULL ? array($restrictNS) : $allNamespaces, $limit / 2, $offset);
		} else {
			// exact lucene query. Do not expand. No title search
			$exactQuery = true;
		}
        
		
		// query lucene server
		switch($restrictNS) {
			case SMW_NS_PROPERTY: $ns_ft = "[".SMW_NS_PROPERTY."]:"; break;
			case NS_CATEGORY: $ns_ft = "[".NS_CATEGORY."]:"; break;
			case NS_TEMPLATE: $ns_ft = "[".NS_TEMPLATE."]:"; break;
			case NS_MAIN: 
			default: $ns_ft = "";
		}
		$searchSet = LuceneSearchSet::newFromQuery( 'search', $ns_ft .
		($exactQuery ? $search : $expandedSearch), $restrictNS !== NULL ? array($restrictNS) : $allNamespaces, $limit / 2, $offset );


		$suggestion = NULL; // did you mean

		// build results
		$resultSet = array();
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

		// add title matches
		$resultSet = array_merge($resultSet, $titleSearchSet);
		$resultSet = array_merge($resultSet, $skosTitleSearchSet);

		// sort results by score
		UnifiedSearchResult::sortByScore($resultSet);

		// serialize result table
		return array(UnifiedSearchResultPrinter::serialize($resultSet), $suggestion);
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
