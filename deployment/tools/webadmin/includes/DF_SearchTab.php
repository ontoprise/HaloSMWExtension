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
		global $dfgLang;
		$findall = $dfgLang->getLanguageString('df_webadmin_findall');
		$html = "<input type=\"text\" value=\"$findall\" onfocus=\"this.value='';\" style=\"width: 450px;\" value=\"\" id=\"df_searchinput\"></input>";
		$html .= "<input type=\"button\"  value=\"Search\" id=\"df_search\"></input><img id=\"df_search_progress_indicator\" src=\"skins/ajax-loader.gif\" style=\"display:none\"/>";
		$html.= "<div id=\"df_search_results\"></div>";
		return $html;
	}

	public function searializeSearchResults($results, $localPackages, $searchValue) {
		global $dfgLang;

		if (count($results) == 0) {
			$html = $dfgLang->getLanguageString('df_webadmin_nothingfound', array('{{search-value}}' => $searchValue));
			$html .= "<br/><br/>".$dfgLang->getLanguageString('df_webadmin_searchinfoifnothingfound');
			$html .= '<a href="http://dailywikibuilds.ontoprise.com/repository/repository.xml">Ontoprise repository</a>';
			return $html;
		}
		$html = "<table id=\"df_search_results_table\">";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_extension');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_version');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_description');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_action');
		$html .= "</th>";
		$i=0;
		
		$installText = $dfgLang->getLanguageString('df_webadmin_install');
		$updateText = $dfgLang->getLanguageString('df_webadmin_update');
		$checkDependencyText = $dfgLang->getLanguageString('df_webadmin_checkdependency');
		
		foreach($results as $id => $versions) {
			$numOfVersion = count($versions);
			$first = true;
			ksort($versions);
			$versions = array_reverse($versions);
			foreach($versions as $v => $tuple) {
				list($title, $description) = $tuple;
				$j = $i % 2;
				$html .= "<tr class=\"df_row_$j\">";

				if ($first) {
					$html .= "<td rowspan=\"$numOfVersion\" class=\"df_extension_id\" ext_id=\"$id\">";
					$html .= !empty($title) ? $title : $id;
					$html .= "</td>";
						
				}
				$html .= "<td class=\"df_version\" extid=\"$id\" version=\"$v\">";
				$html .= $v;
				$html .= "</td>";
				if ($first) {
					$html .= "<td rowspan=\"$numOfVersion\" class=\"df_description\">"; // FIXME: consider that descriptions for different version can be different
					$html .= $description;
					$html .= "</td>";
				}
				$html .= "<td class=\"df_actions\">";
				if (!array_key_exists($id, $localPackages)) {
					$html .= "<input type=\"button\" class=\"df_install_button\" value=\"$installText\" id=\"df_install__".$id."__$v\"></input>";
					$html .= "<input type=\"button\" class=\"df_check_button\" value=\"$checkDependencyText\" id=\"df_showdependencies__".$id."__$v\"></input>";
				} else {
					$dd = $localPackages[$id];
					list($ver, $patchlevel) = explode("_", $v);

					// already installed
					$versionObj = new DFVersion($ver);
					if ($dd->getVersion()->isEqual($versionObj) && $dd->getPatchlevel() == $patchlevel) {
						$html .= "Installed";
					}

					// mark as updateable
					if ($dd->getVersion()->isLower($versionObj) || ($dd->getVersion()->isEqual($versionObj) && $dd->getPatchlevel() < $patchlevel)) {
						$html .= "<input type=\"button\" class=\"df_update_button_search\" value=\"$updateText\" id=\"df_update__".$id."__$v\"></input>";
					}

					// downgrades are not possible
					if ($dd->getVersion()->isHigher($versionObj)) {
						$html .= "n/a";
					}
				}

				$html .= "</td>";
				$html .= "</tr>";
				$first=false;
			}
			$i++;
		}
		$html .= "</table>";
		return $html;
	}
}