<?php

/**
 * Receives the Ajax call from the javascript when clicking on
 * PathSearch at the Special Page search, of the Unified Search
 * extension. 
 */

$wgAjaxExportList[] = 'us_doPathSearch';
$wgAjaxExportList[] = 'us_getPathDetails';

function us_doPathSearch($input, $nojason = false) {
	
	if (trim($input) == "")
		return ($nojason) ? wfMsg('us_pathsearch_no_results', '') : USPathSearchJasonOutput(wfMsg('us_pathsearch_no_results', ''));
	
	$params = explode('&', $input);
	$search = (isset($params[0])) ? $params[0] : "";
	$origTerms = (isset($params[1])) ? $params[1] : "";
	$limit = (isset($params[2])) ? $params[2] : 10000;
	$offset = (isset($params[3])) ? $params[3] : 0;
	
	if (strpos($search, ',') !== false) {
		$params = explode(',', $search);
		if (count($params) % 2 == 1) 
			return ($nojason) ? wfMsg('us_pathsearch_no_results', $origTerms) : USPathSearchJasonOutput(wfMsg('us_pathsearch_no_results', $origTerms));
		$queryArr = array();
		for ($i = 0, $is = count($params); $i < $is; $i++) {
			$queryArr[] = array($params[$i], $params[++$i]);
		}
	}
	else {
		$queryArr = USPathSearchEvalQueryParams($search);
	}

	$psc = new PathSearchCore();
	$psc->doSearch($queryArr, $limit, $offset);
	$psc->setOutputMethod(0);
	
	// no results found or something else went wrong
	if ($psc->getResultCode() != 0)
		$html =  wfMsg('us_pathsearch_no_results', $origTerms);
	else {
		$html = $psc->getResultAsHtml();
		$totalHits = $psc->numberPathsFound();
		$resultInfo =  wfMsg('us_resultinfo',$offset+1,$offset+$limit > $totalHits ? $totalHits : $offset+$limit, $totalHits, $origTerms);
    	$html = "<div id=\"us_resultinfo\">".wfMsg('us_results').": $resultInfo</div>$html";
	}
	
	return ($nojason) ? $html : USPathSearchJasonOutput($html);
}

function us_getPathDetails($input) {
	
	$path = explode(',', $input);
	$psc = new PathSearchCore();
	$psc->getPathDetails($path);
	$psc->setOutputMethod(PSC_OUTPUT_BOX);
	$html = ($psc->getResultCode() != 0) ? wfMsg('us_pathsearch_no_instances') : $psc->getResultAsHtml();
	
    return $html;
}

function USPathSearchJasonOutput($html) {
	$return['result'] = $html;
	// output correct header
	$xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	header('Content-Type: ' . ($xhr ? 'application/json' : 'text/plain'));
  	echo json_encode($return);
	return ""; 	
}

function USPathSearchEvalQueryParams($query) {
		$wgPathSearchQueryMinLen = 4;
		$queryArr = array();

		$data = explode(" ", $query);

		$add = NULL;
		foreach ($data as $term) {
  			$term = trim($term);
			if (strlen($term) == 0) continue;
  			if (!$add) {
    			if ((strpos($term, '"') === false) && (strpos($term, "'") === false)) {
      				$queryArr[] = $term;
      				continue;
    			}
    			$add = "'";
    			while (1) {
      				$p = strpos($term, $add);
      				if ($p !== false) {
        				$add = $term{$p};
				        break;
      				}
      				$add = '"';
    			}
    			if ($p > 0) $queryArr[] = substr($term, 0, $p);
    			$queryArr[] = '';
    			$term= substr($term, $p + 1);
  			}

			if ($add) {
			    $p = strpos($term, $add);
    			if ($p !== false) {
      				$queryArr[count($queryArr) - 1] .= " ".substr($term, 0, $p);
      				$term = substr($term, $p + 1);
      				if (strlen($term) > 0) $queryArr[] = $term;
      				$add = NULL;
    			}
    			else
      				$queryArr[count($queryArr) - 1] .= " ".$term;
  			}
		}
		for ($i = 0; $i < count($queryArr); $i++) {
  			$queryArr[$i] = trim($queryArr[$i]);
			if (strlen($queryArr[$i]) <  $wgPathSearchQueryMinLen)
			    unset($queryArr[$i]);
		}
		return $queryArr;		
	}
