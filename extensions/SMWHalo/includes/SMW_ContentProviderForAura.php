<?php
/**
 *
 * @author Joerg Heizmann
 */
global $smwgHaloIP;

 require_once('SMW_CombinedSearch.php');
 require_once('SpecialSearch.php');
 require_once('SpecialCategories.php');
 require_once($smwgHaloIP . '/specials/SMWExport/SMW_ExportRDF.php');

// Register AJAX functions

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ca_GetCombinedSearchResultsFor';
$wgAjaxExportList[] = 'smwf_ca_GetSemanticSearchResultsFor';
$wgAjaxExportList[] = 'smwf_ca_GetFactboxContentFor';
$wgAjaxExportList[] = 'smwf_ca_GetFTSearchResultsFor';
$wgAjaxExportList[] = 'smwf_ca_GetCategoriesFor';
$wgAjaxExportList[] = 'smwf_ca_HTMLGetCategoriesFor';
$wgAjaxExportList[] = 'smwf_ca_GetAskQueriesFor';

/**
  * Returns the HTML results from the combined search
  */
function smwf_ca_GetCombinedSearchResultsFor($searchstring) {

   $cs = new CombinedSearch();
   $parts = $cs->explodeSearchTerm($searchstring);

  $allEntities = array();
   foreach($parts as $part) {
     $entities = $cs->searchEntity($part);
     $allEntities = array_merge($allEntities, $entities);
   }

   $resultHTML = '';

   // get all links as HTML
   if (count($allEntities) == 0) {
    $resultHTML = '<br>';
   } else {
     $resultHTML .= $cs->getIdentifiedEntitiesAsHTML($allEntities);
     $resultHTML .= $cs->getFurtherQueriesAsHTML($allEntities, null);
   }

   return $resultHTML;

}

function smwf_ca_GetSemanticSearchResultsFor($searchstring) {

	$smwreqo = new SMWRequestOptions();
	$smwreqo->offset = 0;
	$smwreqp->isCaseSensitive = false;
	$smwreqo->addStringCondition($searchstring, SMWStringCondition::STRCOND_MID);
	// SMW_NS_PROPERTY, NS_CATEGORY, NS_MAIN
	$results = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY, NS_CATEGORY, NS_MAIN), $smwreqo, true);
	
	$returnstring = "";
	
	foreach ($results as $res) {
		if ($res instanceof Title) {
			$returnstring .= "<resultentry>";
			$returnstring .= "<title>" . $res->getText() . "</title>";
			$returnstring .= "<url>" . $res->getFullURL() . "</url>";
			$returnstring .= "<ns>" . $res->getNamespace() . "</ns>";
			$returnstring .= "</resultentry>";			
		}
	}

   return $returnstring;

}

/**
 * Get the facts about an article (ín RDF)
 */

function smwf_ca_GetFactboxContentFor($page) {
  return getPageAsRDF($page);
}

/**
 * returns ordinary HTML search results for a given search string (wiki search)
 */

function smwf_ca_GetFTSearchResultsFor($searchstring = '', $limit = 20) {
  global $wgUser;

  $searchPage = new FulltextSearch( $wgUser, $limit );
  
  return $searchPage->getResults( $searchstring );
}
/**
 * fetches the categories a given wiki page belongs to
 */

function smwf_ca_GetHTMLCategoriesFor($page) {
  $title = Title::newFromText($page);
  return getHTMLCategoriesForInstance($title);
}

function smwf_ca_GetCategoriesFor($page) {
  $title = Title::newFromText($page);
  return getCategoriesForInstance($title);
}


/**
 * fetches the first ask query within a given wiki page
 */

function smwf_ca_GetAskQueriesFor($page) {
	$title = Title::newFromText($page);
	$article = getWikiText($title);
	$askqueries = '';
//	$first = true;
	if ($article) {
		// match ask queries...
		$matchcount = preg_match_all('/(<ask[\s\S]*?>[\s\S]*?<\/ask>)/i', $article, $hit, PREG_SET_ORDER);
		if ($matchcount > 0) {
			// for now simply return first matching ask query of wiki page
			for ($index = 0; $index < sizeof($hit); $index++) {
//				if ($first) {
//					$first = false;
//				} else {
//					$askqueries .= ""
//				}
				$askqueries .= $hit[$index][0];
			}
			return $askqueries;
		}
	}
	return '';
}

/**
 * helper functions
 */

function getWikiText(Title $title) {
	$revision = Revision::newFromTitle( $title ) ;
	if ($revision) {
		return $revision->getText();
	} else {
		return null;
	}
}


function getCategoriesForInstance(Title $instanceTitle) {
  $db =& wfGetDB( DB_SLAVE ); // TODO: can we use SLAVE here? Is '=&' needed in PHP5?

  $sql = 'page_title=' . $db->addQuotes($instanceTitle->getDBkey()) . ' AND page_id = cl_from';

   $res = $db->select( array($db->tableName('page'), $db->tableName('categorylinks')),
                      'DISTINCT cl_to',
                      $sql, 'SMW::getCategoriesForInstance');

  $result = '';
  $first = true;

  if($db->numRows( $res ) > 0) {
    while($row = $db->fetchObject($res)) {
      if ($first) {
        $first = false;
      } else {
        $result .= "\n";
      }
      $tmptitle = Title::newFromText($row->cl_to, NS_CATEGORY);
      $result .= $tmptitle->getText();
    }
  }
  $db->freeResult($res);

  return $result;
}

function getHTMLCategoriesForInstance(Title $instanceTitle) {
  $db =& wfGetDB( DB_SLAVE ); // TODO: can we use SLAVE here? Is '=&' needed in PHP5?

  $sql = 'page_title=' . $db->addQuotes($instanceTitle->getDBkey()) . ' AND page_id = cl_from';

   $res = $db->select( array($db->tableName('page'), $db->tableName('categorylinks')),
                      'DISTINCT cl_to',
                      $sql, 'SMW::getCategoriesForInstance');

  $result = '';
  $first = true;

  if($db->numRows( $res ) > 0) {
    while($row = $db->fetchObject($res)) {
      if ($first) {
        $first = false;
      } else {
        $result .= " | ";
      }
      $tmptitle = Title::newFromText($row->cl_to, NS_CATEGORY);
      $result .= '<a href=\'' . $tmptitle->escapeFullURL() . '\'>'.$tmptitle->getText().'</a>';
    }
  }
  $db->freeResult($res);

  return $result;
}


/**
 * class for ordinary fulltext search
 */

class FulltextSearch {

  private $limit;
  private $offset = 0;

  function FulltextSearch( &$user, $limit ) {
    $this->limit = $limit;
    $this->namespaces = $this->userNamespaces( $user );
    $this->searchRedirects = true;
  }

  /**
   * @param string $term
   * @public
   */
  function getResults( $term ) {
    $result = '';

    if( '' === trim( $term ) ) {
      return '';
    }

    $search = SearchEngine::create();
    $search->setLimitOffset( $this->limit, $this->offset );
    $search->setNamespaces( $this->namespaces );
    $search->showRedirects = $this->searchRedirects;
    $titleMatches = $search->searchTitle( $term );
    $textMatches = $search->searchText( $term );

    $num = ( $titleMatches ? $titleMatches->numRows() : 0 )
      + ( $textMatches ? $textMatches->numRows() : 0);

    if( $titleMatches ) {
      if( $titleMatches->numRows() ) {
        $result .= '<b>' . wfMsg( 'titlematches' ) . "</b>\n";
        $result .= $this->showMatches( $titleMatches );
      } else {
        $result .= '<b>' . wfMsg( 'notitlematches' ) . "</b><br>\n";
      }
    }

    if( $textMatches ) {
      if( $textMatches->numRows() ) {
        $result .= '<b>' . wfMsg( 'textmatches' ) . "</b>\n";
        $result .= $this->showMatches( $textMatches ) ;
      } elseif( $num == 0 ) {
        # Don't show the 'no text matches' if we received title matches
        $result .= '<b>' . wfMsg( 'notextmatches' ) . "</b><br>\n";
      }
    }

    return $result;
  }

  /**
   * Extract default namespaces to search from the given user's
   * settings, returning a list of index numbers.
   *
   * @param User $user
   * @return array
   * @private
   */
  function userNamespaces( &$user ) {
    $arr = array();
    foreach( SearchEngine::searchableNamespaces() as $ns => $name ) {
      if( $user->getOption( 'searchNs' . $ns ) ) {
        $arr[] = $ns;
      }
    }
    return $arr;
  }

  /**
   * @param SearchResultSet $matches
   * @param string $terms partial regexp for highlighting terms
   */
  function showMatches( &$matches ) {
    $fname = 'SpecialSearch::showMatches';
    wfProfileIn( $fname );

    global $wgContLang;
    $tm = $wgContLang->convertForSearchResult( $matches->termMatches() );
    $terms = implode( '|', $tm );

    $off = $this->offset + 1;
    $out = "<ol start='{$off}'>\n";

    while( $result = $matches->next() ) {
      $out .= $this->showHit( $result, $terms );
    }
    $out .= "</ol>\n";

    // convert the whole thing to desired language variant
    global $wgContLang;
    $out = $wgContLang->convert( $out );
    wfProfileOut( $fname );
    return $out;
  }

  /**
   * Format a single hit result
   * @param SearchResult $result
   * @param string $terms partial regexp for highlighting terms
   */
  function showHit( $result, $terms ) {
    $fname = 'SpecialSearch::showHit';
    wfProfileIn( $fname );
    global $wgUser, $wgContLang, $wgLang;

    $t = $result->getTitle();
    if( is_null( $t ) ) {
      wfProfileOut( $fname );
      return "<!-- Broken link in search result -->\n";
    }
    $sk = $wgUser->getSkin();

    $contextlines = $wgUser->getOption( 'contextlines',  5 );
    $contextchars = $wgUser->getOption( 'contextchars', 50 );

    $link = $sk->makeKnownLinkObj( $t );
    $revision = Revision::newFromTitle( $t );
    $text = $revision->getText();
    $size = wfMsgExt( 'nbytes', array( 'parsemag', 'escape'),
      $wgLang->formatNum( strlen( $text ) ) );

    $lines = explode( "\n", $text );

    $max = intval( $contextchars ) + 1;
    $pat1 = "/(.*)($terms)(.{0,$max})/i";

    $lineno = 0;

    $extract = '';
    wfProfileIn( "$fname-extract" );
    foreach ( $lines as $line ) {
      if ( 0 == $contextlines ) {
        break;
      }
      ++$lineno;
      $m = array();
      if ( ! preg_match( $pat1, $line, $m ) ) {
        continue;
      }
      --$contextlines;
      $pre = $wgContLang->truncate( $m[1], -$contextchars, '...' );

      if ( count( $m ) < 3 ) {
        $post = '';
      } else {
        $post = $wgContLang->truncate( $m[3], $contextchars, '...' );
      }

      $found = $m[2];

      $line = htmlspecialchars( $pre . $found . $post );
      $pat2 = '/(' . $terms . ")/i";
      $line = preg_replace( $pat2,
        "<span class='searchmatch'>\\1</span>", $line );

      $extract .= "<br /><small>{$lineno}: {$line}</small>\n";
    }
    wfProfileOut( "$fname-extract" );
    wfProfileOut( $fname );
    return "<li>{$link} ({$size}){$extract}</li>\n";
  }
}
