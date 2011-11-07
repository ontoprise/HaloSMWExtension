<?php

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_lq_refresh';

function smwf_lq_refresh($id, $query){
	
	//todo:find a better solution
	//this is necessary, since occurences of ' ?' in the first line of the query are replaced by this string
	$query = str_replace('&nbsp;?', ' ?', $query);
	
	$query = trim(substr($query, 5, strlen($query)-11));
	$query = str_replace(array('&lt;', '&gt;'), array('<','>'), $query);
	
	global $wgParser, $wgOut;
	if ( ($wgParser->getTitle() instanceof Title) && ($wgParser->getOptions() instanceof ParserOptions) ) {
		$result = $wgParser->recursiveTagParse($query);
	} else {
		global $wgTitle;
		$popt = new ParserOptions();
		$popt->setEditSection(false);
		$pout = $wgParser->parse($query . '__NOTOC__', $wgTitle, $popt);
		// NOTE: as of MW 1.14SVN, there is apparently no better way to hide the TOC
		SMWOutputs::requireFromParserOutput($pout);
				
		$result = $pout->getText();
	}
	return $result;
}
