<?php

/**
 * Receives the Ajax call from the javascript when clicking on
 * PathSearch at the Special Page search, of the Unified Search
 * extension. 
 */

$wgAjaxExportList[] = 'us_doPathSearch';

function us_doPathSearch($input) {
	$html = USPathSearchStart($input);

	$return['result'] = $html;
	// output correct header
	$xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	header('Content-Type: ' . ($xhr ? 'application/json' : 'text/plain'));
  	echo json_encode($return);
	return ""; 	
}


function USPathSearchStart($input) {
	
	if (trim($input) == "") return "";
	
	if (strpos($input, ',') !== false) {
		$params = explode(',', $input);
		if (count($params) % 2 == 1) return "";
		$queryArr = array();
		for ($i = 0, $is = count($params); $i < $is; $i++) {
			$queryArr[] = array($params[$i], $params[++$i]);
		}
	}
	else {
		$queryArr = USPathSearchEvalQueryParams($input);
	}
	
	$psc = new PathSearchCore();
	$psc->doSearch($queryArr);
	$errorCode = $psc->getResultCode();
	return $psc->getResultAsHtml();
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

?>
