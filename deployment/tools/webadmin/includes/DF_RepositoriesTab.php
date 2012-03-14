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
 * Settings tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}

require_once ( $mwrootDir.'/deployment/tools/smwadmin/DF_PackageRepository.php' );

class DFRepositoriesTab {

	/**
	 * Settings tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_settingstab');
	}

	public function getHTML() {
		global $dfgLang;
		$addRepositoryText = $dfgLang->getLanguageString('df_webadmin_addrepository');
		$removeRepositoryText = $dfgLang->getLanguageString('df_webadmin_removerepository');
		$html = "<div style=\"margin-bottom: 10px;\">".$dfgLang->getLanguageString('df_webadmin_settingstext')."</div>";
		$html .= "<div id=\"df_addrepository_section\">Add new repository: <input type=\"text\" style=\"width: 100%;\" value=\"\" id=\"df_newrepository_input\"></input>";
		$html .= "<input type=\"button\"  value=\"$addRepositoryText\" id=\"df_addrepository\"></input>'<img id=\"df_settings_progress_indicator\" src=\"skins/ajax-loader.gif\" style=\"display:none\"/></div>";
		$html .= "<div id=\"df_existingrepository_section\">Existing repositories<br/><select size=\"5\" style=\"width: 100%\" id=\"df_repository_list\">".$this->getRepositoriesAsHTMLOptions()."</select></div>";
		$html .= "<input type=\"button\"  value=\"$removeRepositoryText\" id=\"df_removerepository\"></input>";
		return $html;
	}
	
	private function getRepositoriesAsHTMLOptions() {
		$html=""; 
		$repoURLs = PackageRepository::getRepositoryURLs();
		foreach($repoURLs as $url) {
			$html .= "<option>".htmlspecialchars($url)."</option>";
		}
		return $html;
	}
}
