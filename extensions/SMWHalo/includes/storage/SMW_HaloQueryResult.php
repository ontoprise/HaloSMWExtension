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
	
	// array of result subjects
	private $mResultSubjects;
	
	public function SMWHaloQueryResult($printrequests, $query, $results, $store, $furtherres=false) {
		parent::__construct($printrequests, $query, $results, $store, $furtherres);
		
		// retrieve result subjects for faster access
		$this->mResultSubjects = array();
		foreach($results as $r) {
			$first = reset($r);
            $rs = $first->getResultSubject();
			$this->mResultSubjects[] = $rs;
		}
	}
	    
	
	/**
	 * Setter method for the results.
	 * @param array(array(SMWHaloResultArray)) $results
	 * 		A table of results
	 */
	public function setResults($results) {
		$this->mResults = $results;
		$this->mResultSubjects = array();
		foreach($results as $r) {
			$rs = $r[0]->getResultSubject();
			$this->mResultSubjects[] = $rs;
		}
	}
	
	public function getResults() {
		return $this->mResultSubjects;
	}
	
	public function getFullResults() {
		return $this->mResults;
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
    
    /**
     * Resets the internal array pointer of the result (rows) and all columns
     * within these rows.
     */
    public function resetResultArray() {
    	reset($this->mResults);
    	foreach ($this->mResults as $row) {
    		foreach ($row as $cell) {
    			$cell->resetContentArray();
    		}
    	}
    	reset($this->mResults);
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
        
        // copy some LOD relevant parameters
        if (array_key_exists('dataspace', $this->mQuery->params)) {
            $params['dataspace'] = $this->mQuery->params['dataspace'];
        }
        if (array_key_exists('metadata', $this->mQuery->params)) {
            $params['metadata'] = $this->mQuery->params['metadata'];
        }
        if (array_key_exists('resultintegration', $this->mQuery->params)) {
            $params['resultintegration'] = $this->mQuery->params['resultintegration'];
        }
        
        // Note: the initial : prevents SMW from reparsing :: in the query string
        $result = SMWInfolink::newInternalLink($caption,':Special:Ask', false, $params);
        
        return $result;
    }
    
    
/*
     * Returns the Query Object 
     */
    public function getQuery(){
    	return $this->mQuery;
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
    
    /**
     * Resets the internal array pointer of the content.
     */
    public function resetContentArray() {
    	reset($this->mContent);
    }
    
    
}