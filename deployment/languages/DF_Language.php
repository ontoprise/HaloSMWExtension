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
 * Language abstraction.
 *
 * @author: Kai K�hn
 *
 */
abstract class DF_Language {
	protected $language_constants;

	public function getLanguageString($key, $params = array()) {
		$template = $this->language_constants[$key];
		foreach($params as $p => $value) {
			$num = $p+1;
			$template = str_replace("$$num", $value, $template);
		}
		return $template;
	}

	public function getLanguageArray() {
		return $this->language_constants;
	}


}

/**
 * Returns the language code
 * 
 * Note: In case of wiki context, the wiki content language is returned.
 * Otherwise which is configured in settings.php. Anyway, this must always be the SAME!
 */
function dffGetLanguageCode() {
	global $wgLanguageCode;
	if (isset($wgLanguageCode)) {
		// if wiki language code available use it
		$langCode = ucfirst($wgLanguageCode);
	} else {
		// otherwise as configured in settings.php
		$langCode = isset(DF_Config::$df_lang) ? ucfirst(DF_Config::$df_lang) : "En";
	}
	return strtolower($langCode);
}
/**
 * Initializes the language object
 *
 * Note: In case of wiki context, the wiki content language is returned.
 * Otherwise which is configured in settings.php. Anyway, this must always be the SAME!
 */
function dffInitLanguage() {
	global $dfgLang, $mwrootDir, $wgLanguageCode;
	if (isset($wgLanguageCode)) {
		// if wiki language code available use it
		$langCode = $wgLanguageCode;
	} else {
		// otherwise as configured in settings.php
		$langCode = isset(DF_Config::$df_lang) ? ucfirst(DF_Config::$df_lang) : "En";
	}
	$langClass = "DF_Language_$langCode";
	if (!file_exists($mwrootDir."/deployment/languages/$langClass.php")) {
		$langClass = "DF_Language_En";
	}
	require_once($mwrootDir."/deployment/languages/$langClass.php");
	$dfgLang = new $langClass();
}
