<?php

function applyQueryHighlighting($querystring, $params, $inline = true, $format = '', $printouts = array()){
	global $smwgIP, $smwgHaloIP;
	require_once($smwgIP . '/includes/SMW_QueryProcessor.php');
	require_once($smwgHaloIP . '/includes/SMWH_QP_Table.php');

	$format = getResultFormat($params);
	
	$gi_store = SMWGardening::getGardeningIssuesAccess();
	$query  = SMWQueryProcessor::createQuery($querystring, $params, $inline, $format, $printouts);
	if ($query instanceof SMWQuery) { // query parsing successful
		$res = smwfGetStore()->getQueryResult($query);
		$format = getFormat($params, $res);
		$printer = getResultPrinter($format, true);
		$html = $printer->getResultHTML($res, $params);

		if($format == "list" || $format == "ol" || $format == "ul"){
			$regex = '|<a.*?title="(.*?)".*?</a>|i';
			$titles = array();
		
			preg_match_all($regex, $html, $titles);
			for($i = 0; $i<sizeof($titles[1]); $i++){
				$title = Title::newFromText($titles[1][$i]);
				$gIssues = $gi_store->getGardeningIssues("smw_consistencybot", NULL, NULL, $title, NULL, NULL);
				$messages = array();
				for($j = 0; $j<sizeof($gIssues); $j++){
					array_push($messages, '<ul><li>' . $gIssues[$j]->getRepresentation() . '</li></ul>');
				}
				$tt = smwfEncodeMessages($messages);
				$regex = '|<a.*?title="' . $titles[1][$i] . '".*?</a>|i';
				$replacement = "$0$tt";
				$html = preg_replace($regex, $replacement, $html);
			}
			return $html;	
		}
		return $html;
	}
	else {
		return $query;
	}
}

function getResultFormat($params){
	$format = 'auto';
	if (array_key_exists('format', $params)) {
		$format = strtolower(trim($params['format']));
		if ( !array_key_exists($format, SMWQueryProcessor::$formats) ) {
			$format = 'auto'; // If it is an unknown format, defaults to list/table again
		}
	}
	return $format;
}

function getFormat($params, $res){
	$format = 'auto';
	if (array_key_exists('format', $params)) {
		$format = strtolower($params['format']);
		if ( !in_array($format,SMWQueryProcessor::$formats) ) {
			$format = 'auto'; // If it is an unknown format, defaults to list/table again
		}
	}
	if ( 'auto' == $format ) {
		if ( ($res->getColumnCount()>1) && ($res->getColumnCount()>0) )
			$format = 'table';
		else $format = 'list';
	}
	return $format;
}

function getResultPrinter($format,$inline) {

		switch ($format) {
			case 'table': case 'broadtable':
				return new SMWHaloTableResultPrinter($format,$inline);
			case 'ul': case 'ol': case 'list':
				return new SMWListResultPrinter($format,$inline);
			case 'timeline': case 'eventline':
				return new SMWTimelineResultPrinter($format,$inline);
			case 'embedded':
				return new SMWEmbeddedResultPrinter($format,$inline);
			case 'template':
				return new SMWTemplateResultPrinter($format,$inline);
			default: return new SMWListResultPrinter($format,$inline);
		}
	}

?>