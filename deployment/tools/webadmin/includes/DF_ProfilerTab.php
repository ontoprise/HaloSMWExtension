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
 * Profiler tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}


class DFProfilerTab {

	/**
	 * DFProfilerTab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_profilertab');
	}

	public function getHTML() {
		global $dfgLang, $wgServer, $wgScriptPath;

		$html = "<div style=\"margin-bottom: 10px;\">".$dfgLang->getLanguageString('df_webadmin_profilertab_description');
		$html .= '<a id="df_profiler_downloadlog" href="'.$wgServer.$wgScriptPath.'/deployment/tools/webadmin/index.php'.
                        '?action=ajax&rs=downloadProfilingLog">'.$dfgLang->getLanguageString('df_webadmin_download_profilinglog').'</a></div>';
		$html .= "<input type=\"button\" value=\"refreshing...\" disabled=\"true\" id=\"df_enableprofiling\"></input>";
		$html .= "<div style=\"display:none\" id=\"df_webadmin_profiler_content\">";
		$html .= $dfgLang->getLanguageString('df_webadmin_profilertab_requests');
		$html .= "<div style=\"text-align:center\"><div style=\"margin:auto\">";
		$html .= "<img id=\"df_webadmin_profiler_refresh_progress_indicator\" src=\"skins/ajax-loader.gif\" style=\"display:none;\"/>";
		$html .= "</div></div>";
		try {
			$logFileSize = $this->getLogFileSize();
			$html .= "<select id=\"df_webadmin_profiler_selectlog\" size=\"5\" logfilesize=\"$logFileSize\">";
			$old = 0;
			$indices = $this->getProfilingLogIndices()->indices;
			foreach($indices as $i) {
				list($index, $logUrl) = $i;
				if ($logUrl == '') continue;
				$html .= "<option from=\"$index\" to=\"$old\">".htmlspecialchars($logUrl)."</option>";
				$old = $index;
			}
		} catch(Exception $e) {
			// ignore
		}
		$html .= "</select>";
		$html .= "<div>";
		$html .= "<input type=\"button\" value=\"".$dfgLang->getLanguageString('df_webadmin_refresh')."\" id=\"df_refreshprofilinglog\"></input>";
		$html .= "<input type=\"button\" value=\"".$dfgLang->getLanguageString('df_webadmin_clearlog')."\" id=\"df_clearprofilinglog\"></input>";
		$html .= "<span id=\"df_profiler_requests_filtering_box\">".$dfgLang->getLanguageString('df_webadmin_filter').": <input size=\"40\" type=\"text\" id=\"df_profiler_requests_filtering\"></input></span>";
		$html .= "</div>";
		$html .= "<div id=\"df_webadmin_profilerlog_container\">";
		$html .= "<div style=\"text-align:center\"><div style=\"margin:auto\">";
		$html .= "<img id=\"df_webadmin_profiler_progress_indicator\" src=\"skins/ajax-loader.gif\" style=\"display:none;\"/>";
		$html .= "</div></div>";
		$html .= "<table id=\"df_webadmin_profilerlog\">";
		$html .= "</table>";
		$html .= "</div>";
		$html .= "<div id=\"df_webadmin_profiler_filtering\">";
		$html .= $dfgLang->getLanguageString('df_webadmin_filter').": <input size=\"40\" type=\"text\" id=\"df_profiler_filtering\"></input>";
		$html .= "</div>";
		$html .= "</div>";
		return $html;
	}
	public function getProfilingLogIndices() {
		if (array_key_exists('df_homedir', DF_Config::$settings)) {
			$homeDir = DF_Config::$settings['df_homedir'];
		} else {
			$homeDir = Tools::getHomeDir();
			if (is_null($homeDir)) throw new DF_SettingError(DEPLOY_FRAMEWORK_NO_HOME_DIR, "No homedir found. Please configure one in settings.php");
		}
		if (!is_writable($homeDir)) {
			throw new DF_SettingError(DF_HOME_DIR_NOT_WRITEABLE, "Homedir not writeable.");
		}
		$wikiname = DF_Config::$df_wikiName;
		$loggingdir = "$homeDir/$wikiname/df_profiling";
		$logFile = "$loggingdir/$wikiname-debug_log.txt";
		if (!file_exists($logFile)) {
			throw new Exception("Log file does not exist");
		}
		global $wgScriptPath;
		$sizeOfLog = filesize($logFile);
		$handle = fopen($logFile, "r");
		$i = 0;

		$startIndex[0] = array($sizeOfLog, "");
		while(true) {

			do {
				$i++;
				$lengthToRead = 32 * 1024;
				if (32 * 1024 * $i > $sizeOfLog) {
					$lengthToRead = $sizeOfLog % (32 * 1024 );
				}
				fseek($handle, max(array(-32 * 1024 * $i, -$sizeOfLog)), SEEK_END);
				$text = fread($handle, $lengthToRead);
			} while(strrpos($text, "$wgScriptPath/") === false && 32 * 1024 * $i < $sizeOfLog);
			$this->addProfilingLog($text, $i, $sizeOfLog, $startIndex);
			if (32 * 1024 * $i > $sizeOfLog) break;
		}
		fclose($handle);
		$o = new stdClass();
		$o->indices = $startIndex;
		$o->filesize = $sizeOfLog;
		
		return $o;

	}

	public function getLogFileSize() {
		if (array_key_exists('df_homedir', DF_Config::$settings)) {
			$homeDir = DF_Config::$settings['df_homedir'];
		} else {
			$homeDir = Tools::getHomeDir();
			if (is_null($homeDir)) throw new DF_SettingError(DEPLOY_FRAMEWORK_NO_HOME_DIR, "No homedir found. Please configure one in settings.php");
		}
		if (!is_writable($homeDir)) {
			throw new DF_SettingError(DF_HOME_DIR_NOT_WRITEABLE, "Homedir not writeable.");
		}
		$wikiname = DF_Config::$df_wikiName;
		$loggingdir = "$homeDir/$wikiname/df_profiling";
		$logFile = "$loggingdir/$wikiname-debug_log.txt";
		if (!file_exists($logFile)) {
			throw new Exception("Log file does not exist");
		}
		return filesize($logFile);
	}

	private function addProfilingLog($text, $i, $sizeOfLog, & $startIndex) {
		global $wgScriptPath;
		$index = strrpos($text, "$wgScriptPath/");
		while($index !== false) {
			if (strpos($text, "\n", $index) !== false) {
				$logurl = substr($text, $index, strpos($text, "\n", $index)-$index);
			} else {
				$logurl = substr($text, $index);
			}
			$startIndex[] = array(max(array(-32 * 1024 * $i + $index, -$sizeOfLog+ $index)), $logurl);
			$text = substr($text, 0, $index);
			$index = strrpos($text, "$wgScriptPath/");
		}
	}

}
