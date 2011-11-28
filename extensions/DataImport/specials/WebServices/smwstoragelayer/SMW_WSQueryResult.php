<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


/*
 * This class implements a SMWQueryResult object, which translates
 * a web service call result to an SMW query result.
 */
class SMWWSQueryResult extends SMWQueryResult {
	
	// array of result subjects
	private $mResultSubjects;
	
	public function SMWWSQueryResult($printrequests, $query, $results, $store, $furtherres=false) {
		parent::__construct($printrequests, $query, $results, $store, $furtherres);
		
		// retrieve result subjects for faster access
		$this->mResultSubjects = array();
		foreach($results as $r) {
			$keys = array_keys($r);
			$rs = $r[$keys[0]]->getResultSubject();
			$this->mResultSubjects[] = $rs;
		}
	}
	    
	
	public function getResults() {
		return $this->mResultSubjects;
	}
	
	public function getFullResults() {
		return $this->mResults;
	}
	
    public function getNext() {
        $row = current($this->mResults);
    	next($this->mResults);
        if ($row === false) return false;
       
        return $row;
    }
    
    public function getQueryLink( $caption = false ) {
		$params = array( trim( $this->mQuery->getQueryString() ) );
		
		foreach ( $this->mQuery->getExtraPrintouts() as $printout ) {
			//$params[] = '?result.'.$printout->getData().'='.$printout->getLabel();
			$params[] = '?result.'.$printout->getData().'='.$printout->getLabel();
			
		}
		
		if ( $caption == false ) {
			smwfLoadExtensionMessages( 'SemanticMediaWiki' );
			$caption = ' ' . wfMsgForContent( 'smw_iq_moreresults' ); // The space is right here, not in the QPs!
		}
		
		//unset limit and offset parameter since this is explicitly added by special:ask
		if(array_key_exists('limit', $this->mQuery->params)) unset($this->mQuery->params['limit']);
		if(array_key_exists('offset', $this->mQuery->params)) unset($this->mQuery->params['offset']);
		
		//add other query params like source and webservice to the link
		$params = array_merge($params, $this->mQuery->params);
		
		// Note: the initial : prevents SMW from reparsing :: in the query string.
		$result = SMWInfolink::newInternalLink( $caption, ':Special:Ask', false, $params );
		
		return $result;
	}
}

/*
 * Represents web service call results as a SMW query
 * result array
 */
class SMWWSResultArray extends SMWResultArray {
    
	public function SMWWSResultArray(SMWDIWikiPage $resultPage, SMWPrintRequest $printRequest, $results) {
        parent::__construct($resultPage, $printRequest, new SMWWSSMWStore());
        $this->mContent = $results; // do not reload
    }
    
    public function setContent($content) {
    	$this->mContent = $content;
    }
    
}
