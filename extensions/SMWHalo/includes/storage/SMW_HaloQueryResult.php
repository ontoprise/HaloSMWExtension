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
			$rs = $r[0]->getResultSubject();
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