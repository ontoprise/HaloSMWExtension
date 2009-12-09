<?php

global $wgAjaxExportList;
global $smwgUltraPediaIP;

//require_once($smwgUltraPediaIP . '/includes/UP_Processor.php');
$wgAjaxExportList[] = 'smwf_up_Access';

function smwf_up_Access($method, $params) {
	global $smwgUltraPediaEnabled;

	$result="Semantic UltraPedia disabled.";
	if($method == "ajaxAsk"){
		global $smwgQEnabled;
		if ($smwgUltraPediaEnabled && $smwgQEnabled) {
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
	} else if($method == "internalLoad"){
		if ($smwgUltraPediaEnabled) {
			$p = explode(',', $params, 2);
			$title = $p[1];
			$html = $title;
			global $wgTitle, $smwgIQRunningNumber;
			// pay attention to $smwgIQRunningNumber
			$smwgIQRunningNumber = intval($p[0]) * 10;
			$wgTitle = Title::newFromText( $title );
			$revision = Revision::newFromTitle( $wgTitle );
			if ( $revision !== NULL ) {
				global $wgParser, $wgOut;
				$popts = $wgOut->parserOptions();
				$popts->setTidy(true);
				$popts->enableLimitReport();
				$html = $wgParser->parse( $revision->getText(), $wgTitle, $popts )->getText();
			}
			return $html;
		}
	} else {
		return "Operation failed, please retry later.";
	}
	return $result;
}
?>