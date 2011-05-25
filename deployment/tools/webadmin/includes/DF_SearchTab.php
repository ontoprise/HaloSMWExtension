<?php
/*  Copyright 2009, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup WebAdmin
 *
 * Search tab
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
    die();
}

class DFSearchTab {

	/**
	 * Status tab
	 *
	 */
	public function __construct() {

	}
	
public function getTabName() {
        global $dfgLang;
        return $dfgLang->getLanguageString('df_webadmin_searchtab');
    }

	public function getHTML() {
		$html = "<input type=\"text\" style=\"width: 450px;\" value=\"\" id=\"df_searchinput\"></input>";
		$html .= "<input type=\"button\"  value=\"Search\" id=\"df_search\"></input>";
		$html.= "<div id=\"df_search_results\"></div>";
		return $html;
	}

	public function searializeSearchResults($results, $localPackages) {
		$html = "<table>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_extension');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_description');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_action');
		$html .= "</th>";
		foreach($results as $id => $description) {

			$html .= "<tr>";
			$html .= "<td class=\"df_extension_id\">";
			$html .= $id;
			$html .= "</td>";
			$html .= "<td class=\"df_description\">";
			$html .= $description;
			$html .= "</td>";
			$html .= "<td class=\"df_actions\">";
			if (!array_key_exists($id, $localPackages)) {
				$html .= "<input type=\"button\" class=\"df_install_button\" value=\"Install\" id=\"df_install__$id\"></input>";
				$html .= "<input type=\"button\" class=\"df_check_button\" value=\"Check\" id=\"df_showdependencies__$id\"></input>";
			} else {
				$html .= "Installed";
			}

			$html .= "</td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
	}
}