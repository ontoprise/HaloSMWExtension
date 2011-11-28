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
		
		global $wgOut;
		$result .= $wgOut->getScript();
		$result .= '<script type="text/javascript">';
		$result .= 'mw.loader.using(["';
		$result .= implode('","', array_unique(
			array_merge($wgParser->getOutput()->getModules(), $wgOut->getModules())));
		$result .= '"], LiveQuery.helper.executeInitMethods);';
		
		$result .= '</script>';
	}
	return $result;
}
