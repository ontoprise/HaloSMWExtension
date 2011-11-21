<?php

global $wgAjaxExportList;

$wgAjaxExportList[] = 'smwf_aq_Access';

function smwf_aq_Access($method, $params) {
	global $smwgQEnabled;

	$result="Semantic disabled.";
	if($method == "ajaxAsk"){
		
		if ($smwgQEnabled) {
			global $smwgIQRunningNumber;
			$p = explode(',', $params, 2);

			// pay attention to $smwgIQRunningNumber
			$smwgIQRunningNumber = intval($p[0]);
			$text = '{{#ask: ' . $p[1] . '}}';
           
			global $wgParser, $wgOut;
			if ( ($wgParser->getTitle() instanceof Title) && ($wgParser->getOptions() instanceof ParserOptions) ) {
				$result = $wgParser->recursiveTagParse($text);
			} else {
				global $wgTitle;
				$popt = new ParserOptions();
				$popt->setEditSection(false);
				$pout = $wgParser->parse($text . '__NOTOC__', $wgTitle, $popt);
				// NOTE: as of MW 1.14SVN, there is apparently no better way to hide the TOC
				SMWOutputs::requireFromParserOutput($pout);
				
				$result = $pout->getText();
			}
			return $result;
		}
	}else if($method == "ajaxSparql"){
		if ($smwgQEnabled) {
			global $smwgIQRunningNumber;
			$p = explode(',', $params, 2);

			// pay attention to $smwgIQRunningNumber
			$smwgIQRunningNumber = intval($p[0]);
			$text = '{{#sparql: ' . $p[1] . '}}';

			global $wgParser, $wgOut;
			if ( ($wgParser->getTitle() instanceof Title) && ($wgParser->getOptions() instanceof ParserOptions) ) {
				$result = $wgParser->recursiveTagParse($text);
			} else {
				global $wgTitle;
				$popt = new ParserOptions();
				$popt->setEditSection(false);
				$pout = $wgParser->parse($text . '__NOTOC__', $wgTitle, $popt);
				// NOTE: as of MW 1.14SVN, there is apparently no better way to hide the TOC
				SMWOutputs::requireFromParserOutput($pout);
				$result = $pout->getText();
			}
			return $result;
		}
	} else {
		return "Operation failed, please retry later.";
	}
	return $result;
}
?>