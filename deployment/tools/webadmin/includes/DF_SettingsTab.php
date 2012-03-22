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
 * The WAT settings tab allows specifying options the user would be
 * asked interactively in the command line version.
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}


class DFSettingsTab {

	/**
	 * WAT settings tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_watsettingstab');
	}

	public function getHTML() {
		global $dfgLang, $wgServer, $wgScriptPath;

		$html = "<div style=\"margin-bottom: 10px;\">".$dfgLang->getLanguageString('df_webadmin_watsettingstab_description')."</div>";
		$html .= "<input type=\"button\"  value=\"Reset defaults\" id=\"df_resetdefault_settings\">";
		$html .= "<div id=\"df_watsettings\">";
		
		$html .= "<h2>".$dfgLang->getLanguageString('df_webadmin_watsettings_bundleimport')."</h2>";
		$html .= "<table>";
		$html .= "<tr>";
		$html .= "<td class=\"df_setting\"><input id=\"df_watsettings_overwrite_always\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_overwrite_always')."</input>";
		$html .= "<div class=\"df_settings_help\">".$dfgLang->getLanguageString('df_watsettings_overwrite_always_help')."</div>";
		$html .= "</td></tr>";
		$html .= "<tr>";
		$html .= "<td class=\"df_setting\"><input id=\"df_watsettings_merge_with_other_bundle\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_merge_with_other_bundle')."</input>";
		$html .= "<div class=\"df_settings_help\">".$dfgLang->getLanguageString('df_watsettings_merge_with_other_bundle_help')."</div>";
        $html .= "</td></tr>";
		$html .= "</tr>";
		$html .= "</table>";

		$html .= "<h2>".$dfgLang->getLanguageString('df_webadmin_watsettings_installation')."</h2>";
		$html .= "<table>";
		$html .= "<tr>";
		$html .= "<td class=\"df_setting\"><input id=\"df_watsettings_install_optionals\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_install_optionals')."</input>";
		$html .= "<div class=\"df_settings_help\">".$dfgLang->getLanguageString('df_watsettings_install_optionals_help')."</div>";
        $html .= "</td></tr>";
		$html .= "</tr>";
		$html .= "<tr>";
        $html .= "<td class=\"df_setting\"><input id=\"df_watsettings_deinstall_dependant\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_deinstall_dependant')."</input>";
        $html .= "<div class=\"df_settings_help\">".$dfgLang->getLanguageString('df_watsettings_deinstall_dependant_help')."</div>";
        $html .= "</td></tr>";
        $html .= "</tr>";
		$html .= "<tr>";
		$html .= "<td class=\"df_setting\"><input id=\"df_watsettings_apply_patches\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_apply_patches')."</input>";
		$html .= "<div class=\"df_settings_help\">".$dfgLang->getLanguageString('df_watsettings_apply_patches_help')."</div>";
        $html .= "</td></tr>";
		$html .= "</tr>";
		$html .= "</table>";

		$html .= "<h2>".$dfgLang->getLanguageString('df_webadmin_watsettings_restore_points')."</h2>";
		$html .= "<table>";
		$html .= "<tr>";
		$html .= "<td class=\"df_setting\"><input id=\"df_watsettings_create_restorepoints\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_create_restorepoints')."</input>";
		$html .= "<div class=\"df_settings_help\">".$dfgLang->getLanguageString('df_watsettings_create_restorepoints_help')."</div>";
        $html .= "</td></tr>";
		$html .= "</tr>";
		$html .= "</table>";

		$html .= "<h2>".$dfgLang->getLanguageString('df_webadmin_watsettings_ontologyimport')."</h2>";
		$html .= "<table>";
		$html .= "<tr>";
		$html .= "<td class=\"df_setting\"><input id=\"df_watsettings_hidden_annotations\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_hidden_annotations')."</input>";
		$html .= "<div class=\"df_settings_help\">".$dfgLang->getLanguageString('df_watsettings_hidden_annotations_help')."</div>";
        $html .= "</td></tr>";
		$html .= "</tr>";
		// the last is commented out because this option is not yet available in onto2mwxml
		$html .= "<tr>";
		$html .= "<td class=\"df_setting\"><input disabled=\"true\" id=\"df_watsettings_use_namespaces\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_use_namespaces')."</input>";
		$html .= "<div class=\"df_settings_help\">".$dfgLang->getLanguageString('df_watsettings_use_namespaces_help')."</div>";
        $html .= "</td></tr>";
		$html .= "</tr>";
		$html .= "</table>";
		$html .= "</div>";
		return $html;
	}


}
