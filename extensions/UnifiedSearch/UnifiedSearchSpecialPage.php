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
        
        # Otherwise show special search page
        $wgOut->setPageTitle("Unified Search");
        $html = '<div id="us_refineresults"></div><div id="us_searchresults">';
        $html .= $this->doFullTextSearch();
        $html .= '</div>';
        $wgOut->addHTML($html);
    }
    
    private function doFullTextSearch() {
    	global $wgRequest;
    	$print = "";
    	$fulltext = $wgRequest->getVal('search');
    	$searchEngine = new LuceneSearch();
    	$searchSet = $searchEngine->searchText($fulltext);
    	$print .= print_r($searchSet, true);
        $resultSet = array();
    	$result = $searchSet->next();
    	while($result !== false) {
    		//$resultSet[] = Uni
    		$result = $searchSet->next();
    	}
    	return $print;
    }
}




?>
