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


global $wgAjaxExportList;
global $smwgUltraPediaIP;

//require_once($smwgUltraPediaIP . '/includes/UP_Processor.php');
$wgAjaxExportList[] = 'smwf_up_Access';

function smwf_up_Access($method, $params) {
	global $smwgQEnabled, $smwgUltraPediaEnabled;

	$result="Semantic UltraPedia disabled.";
	if($method == "ajaxAsk"){
		
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
	}else if($method == "ajaxSparql"){
		if ($smwgUltraPediaEnabled && $smwgQEnabled) {
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
	}
	else if($method == "internalLoad"){
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
