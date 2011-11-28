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
 * LocalSettings tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}

require_once ( $mwrootDir.'/deployment/tools/smwadmin/DF_PackageRepository.php' );

class DFLocalSettingsTab {

	/**
	 * Status tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_localsettingstab');
	}

	public function getHTML() {
		global $dfgLang, $mwrootDir;
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);

		$selectorHTML = "<select id =\"df_settings_extension_selector\">";
		$selectorHTML .= "<option value=\"_no_value_\">".$dfgLang->getLanguageString('df_select_extension')."</option>";
		$selectorHTML .= "<option value=\"all\">all</option>";
		foreach($localPackages as $id => $dd) {
			$selectorHTML .= "<option value=\"$id\">$id</option>";
		}
		$selectorHTML .= "</select>";
		
		$textfield = "<textarea id=\"df_settings_textfield\" cols=\"120\" rows=\"20\" disabled=\"true\"></textarea>";
		
		$buttons = "<input type=\"button\" value=\"Save\" id=\"df_settings_save_button\" disabled=\"true\"></input>";
		
		$description = $dfgLang->getLanguageString("df_webadmin_localsettings_description");
$html = <<<ENDS
<div id=\"df_localsettings\">
$description
<div style="margin: 10px;">
$selectorHTML
</div>
<div>
$textfield
</div>
$buttons
</div>
ENDS
;				
		return $html;
	}

	
}
