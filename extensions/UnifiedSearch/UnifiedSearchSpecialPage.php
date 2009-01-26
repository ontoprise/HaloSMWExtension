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
		
        $limit =  $wgRequest->getVal('limit') !== NULL ? $wgRequest->getVal('limit') : 20;
        $offset =  $wgRequest->getVal('offset') !== NULL ? $wgRequest->getVal('offset') : 0;
        
        
		$searchPage = SpecialPage::getTitleFor("Search");
		# Otherwise show special search page
		list($searchResults,$suggestion) = $this->doFullTextSearch($limit, $offset);
		$numOfResults = count($searchResults);
		$suggestion = "auto";
		
		$html = "";
		$didyoumeanURL = $searchPage->getFullURL("search=$suggestion");
		$wgOut->setPageTitle("Unified Search");
		$html .= '<div id="us_didyoumean">'.($suggestion !== NULL ? "<i style=\"color:red;\">Meinten Sie:</i> <a style=\"text-decoration:underline;\" href=\"$didyoumeanURL\">".$suggestion."</a>" : "").'</div>';
		
		// refine elements
		
		$refineInstancesURL = $searchPage->getFullURL("search=$search&restrict=0");
		$refineCategories = $searchPage->getFullURL("search=$search&restrict=14"); 
		$refineProperties = $searchPage->getFullURL("search=$search&restrict=102"); 
		$refineTemplates = $searchPage->getFullURL("search=$search&restrict=10");
		 
		$html .='<div id="us_refineresults">Suche verfeinern: <a class="us_refinelinks" href="'.$refineInstancesURL.'">Instances</a>,'.
				'<a class="us_refinelinks" href="'.$refineCategories.'">Categories</a>,'.
				'<a class="us_refinelinks" href="'.$refineProperties.'">Properties</a>,'.
				'<a class="us_refinelinks" href="'.$refineTemplates.'">Templates</a>'.
				'</div>';
		
		// browsing
		$next = $this->createBrowsingLink($search, $offset+$limit, $limit, "n&auml;chste ");
		$previous = $this->createBrowsingLink($search,$offset-$limit >= 0 ? $offset-$limit : 0, $limit, "vorherige ");
		$limit20 = $this->createBrowsingLink($search,$offset, 20);
		$limit50 = $this->createBrowsingLink($search,$offset, 50);
		$limit100 = $this->createBrowsingLink($search,$offset, 100);
		$limit250 = $this->createBrowsingLink($search,$offset, 250);
		$limit500 = $this->createBrowsingLink($search,$offset, 500);
		
		if ($offset == 0) { 
		  $html .= "Siehe (vorherige $limit) ($next) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)";
		} else {
		  $html .= "Siehe ($previous) ($next) ($limit20 | $limit50 | $limit100 | $limit250 | $limit500)";	
		}
		
		$html .= "<h2>Results</h2>";
		
		// search results
		$html .= '<div id="us_searchresults">';
		$html .= $searchResults;
		$html .= '</div>';
		$wgOut->addHTML($html);
	}
	
	private function createBrowsingLink($search, $offset, $limit, $text="") {
		$searchPage = SpecialPage::getTitleFor("Search");
		return '<a href="'.$searchPage->getFullURL("search=$search&limit=$limit&offset=$offset").'">'.$text.$limit.'</a>';
		
	}

	private function doFullTextSearch($limit, $offset) {
		global $wgRequest;
		$print = "";
		$fulltext = $wgRequest->getVal('search');
		
		// expand query
		$qe = new QueryExpander();
		$searchString = $qe->expand($fulltext);
		$terms = $qe->getTerms();

		
        $titleSearchSet = WikiTitleSearch::lookupTitles($fulltext, 
                array(NS_MAIN, NS_CATEGORY, SMW_NS_PROPERTY, NS_TEMPLATE), $limit / 2, $offset);
         
        $numOfFullText = $limit - count($titleSearchSet); // rest are fulltext matches
        
		// query lucene server
		$searchSet = LuceneSearchSet::newFromQuery( 'search',
                $searchString, array(0), $limit / 2, $offset );
				
		
		
		// build results
		$resultSet = array();
		$result = $searchSet->next();
		while($result !== false) {
			if (!$result->isMissingRevision()) {
				$resultSet[] = UnifiedSearchResult::newFromLuceneResult($result, $terms);
			}
			$result = $searchSet->next();
		}

		$resultSet = array_merge($resultSet, $titleSearchSet);
		
		// sort results by score
		UnifiedSearchResult::sortByScore($resultSet);
		
		// serialize result table
		return array(UnifiedSearchResultPrinter::serialize($resultSet), $searchSet->getSuggestionQuery());
	}
}




?>
