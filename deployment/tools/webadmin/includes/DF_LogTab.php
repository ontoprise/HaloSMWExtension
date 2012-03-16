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

/**
 * @file
 * @ingroup WebAdmin
 *
 * Log tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}


class DFLogTab {

	/**
	 * Log tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_logtab');
	}

	public function getHTML() {
		global $dfgLang, $wgServer, $wgScriptPath;

		$html = "<div style=\"margin-bottom: 10px;\">".$dfgLang->getLanguageString('df_webadmin_logtab_description')."</div>";
		$html .= "<input type=\"button\" value=\"".$dfgLang->getLanguageString('df_webadmin_clearlog')."\" id=\"df_clearlog\"></input>";

		$html .= "<div id=\"df_log_results_table_container\">";
		$html .= "<table id=\"df_log_results_table\">";
		$header = "<th>";
		$header .= $dfgLang->getLanguageString('df_webadmin_loglink');
		$header .= "</th>";
		$header .= "<th>";
		$header .= $dfgLang->getLanguageString('df_webadmin_logdate');
		$header .= "</th>";
		$header = str_repeat($header, 4);
		$html .= $header;

		$logLinkHint = $dfgLang->getLanguageString('df_loglink_hint');

		$readLogLinkTemplate = '<a target="_blank" href="'.$wgServer.$wgScriptPath.'/deployment/tools/webadmin/index.php'.
						'?action=ajax&rs=readLog&rsargs[]=$1$2" title="'.$logLinkHint.'">Log</a>';

		$logs = $this->getLogs();
		$i = 0;
		$totalHeight = (count($logs) % 4 == 0) ? intval(count($logs) / 4) : intval(count($logs) / 4) + 1;

		for($k = 0; $k < $totalHeight; $k++) {
			list($name1, $date1, $type1) = $logs[$k];
			$j = $i % 2;
			$html .= "<tr class=\"df_row_$j\">";

			$html .= $this->getColumn($name1, $date1, $type1, $readLogLinkTemplate);

			if ($k+$totalHeight < count($logs)) {
				list($name2, $date2, $type2) = $logs[$k+$totalHeight];
				$html .= $this->getColumn($name2, $date2, $type2, $readLogLinkTemplate);
			} else {
				$html .= $this->getEmptyColumn();
			}

			if ($k+$totalHeight*2 < count($logs)) {
				list($name3, $date3, $type3) = $logs[$k+$totalHeight*2];
				$html .= $this->getColumn($name3, $date3, $type3, $readLogLinkTemplate);
			} else {
				$html .= $this->getEmptyColumn();
			}

			if ($k+$totalHeight*3 < count($logs)) {
				list($name4, $date4, $type4) = $logs[$k+$totalHeight*3];
				$html .= $this->getColumn($name4, $date4, $type4, $readLogLinkTemplate);
			} else {
				$html .= $this->getEmptyColumn();
			}

			$html .= "</tr>";
			$i++;
		}
		$html .= "</table>";
		$html .= "</div>";
		return $html;
	}

	private function getColumn($name, $date, $type, $readLogLinkTemplate) {
		$html = "<td class=\"df_log_link\">";
		$readLogLink = str_replace('$1', $name, $readLogLinkTemplate);
		$readLogLink = str_replace('$2', "&rsargs[]=$type", $readLogLink);
		$html .= "$readLogLink";
		$html .= "</td>";
		$html .= "<td class=\"df_log_link\">";
		$html .= date ("F d Y H:i:s.", $date);
		$html .= "</td>";
		return $html;
	}

	private function getEmptyColumn() {
		$html = "<td class=\"df_log_link\"></td>";
		$html .= "<td class=\"df_log_link\"></td>";
		return $html;
	}

	/**
	 * Read log directory and returns a descending sorted list of entries.
	 *
	 * @return tuple[] (filename, modification timestamp)
	 */
	private function getLogs() {
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		$result = array();
		$handle = @opendir($logdir);
		if (!$handle) {

			return array();
		}

		while ($entry = readdir($handle) ){
			if ($entry[0] == '.'){
				continue;
			}

			$file = "$logdir/$entry";
			if (strpos($entry, "console_out") === false) {
				continue;
			}
			$date =  filemtime($file);
			$type = strpos($entry, ".txt") !== false ? "text" : "html";
			$result[] = array($entry, $date, $type);
		}
		@closedir($handle);
		usort($result, array($this, "cmpLogEntry"));
		return $result;
	}

	private function cmpLogEntry($a, $b) {
		list($file1, $ts1, $type1) = $a;
		list($file2, $ts2, $type2) = $b;
		return $ts2-$ts1;
	}

}
