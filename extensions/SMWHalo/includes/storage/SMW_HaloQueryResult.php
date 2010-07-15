<?php

/**
 * @file
 * @ingroup SMWHaloSMWDeviations
 *
 * Derived version of SMWQueryResult to provide user-defined link
 * to Special:Ask page. Necessary for other storages.
 *
 */

class SMWHaloQueryResult extends SMWQueryResult {

	public function SMWHaloQueryResult($printrequests, $query, $results, $store, $furtherres=false) {
		parent::__construct($printrequests, $query, $results, $store, $furtherres);
	}
	
	/**
	 * Setter method for the results.
	 * @param array(array(SMWHaloResultArray)) $results
	 * 		A table of results
	 */
	public function setResults($results) {
		$this->mResults = $results;
	}
    
    /**
     * Return the next result row as an array of SMWResultArray objects, and
     * advance the internal pointer.
     */
    public function getNext() {
        $row = current($this->mResults);
    	next($this->mResults);
        if ($row === false) return false;
       
        return $row;
    }
    
	public function getQueryLink($caption = false) {

		$params = array(trim($this->mQuery->getQueryString()));
        foreach ($this->mQuery->getExtraPrintouts() as $printout) {
            $params[] = $printout->getSerialisation();
        }
        if ( count($this->mQuery->sortkeys)>0 ) {
            $psort  = '';
            $porder = '';
            $first = true;
            foreach ( $this->mQuery->sortkeys as $sortkey => $order ) {
                if ( $first ) {
                    $first = false;
                } else {
                    $psort  .= ',';
                    $porder .= ',';
                }
                $psort .= $sortkey;
                $porder .= $order;
            }
            if (($psort != '')||($porder != 'ASC')) { // do not mention default sort (main column, ascending)
                $params['sort'] = $psort;
                $params['order'] = $porder;
            }
        }
        if ($caption == false) {
            wfLoadExtensionMessages('SemanticMediaWiki');
            $caption = ' ' . wfMsgForContent('smw_iq_moreresults'); // the space is right here, not in the QPs!
        }
        $askPage = $this->mQuery instanceof SMWSPARQLQuery ? "AskTSC" : "Ask";
        $result = SMWInfolink::newInternalLink($caption,':Special:'.$askPage, false, $params);
       	
		// Note: the initial : prevents SMW from reparsing :: in the query string
		return $result;
	}
}

/**
 * @ingroup SMWHaloSMWDeviations
 * 
 * Subclass is required to pre-set content for Halo result sets. 
 * They can not be loaded on demand. 
 * 
 * @author Kai K�hn
 *
 */
class SMWHaloResultArray extends SMWResultArray {
    public function SMWHaloResultArray(SMWWikiPageValue $resultpage, SMWPrintRequest $printrequest, SMWStore $store, $results) {
        parent::__construct($resultpage, $printrequest, $store);
        $this->mContent = $results; // do not reload
    }
    
    public function setContent($content) {
    	$this->mContent = $content;
    }
    
    
}