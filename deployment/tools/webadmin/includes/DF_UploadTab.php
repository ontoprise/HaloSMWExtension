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
 * Upload tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}

require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Tools.php');

class DFUploadTab {

	private $uploadDirectory;

	/**
	 * Status tab
	 *
	 */
	public function __construct() {
		if (array_key_exists('df_uploaddir', DF_Config::$settings)) {
			$uploadDirectory = DF_Config::$settings['df_uploaddir'];
		} else {
			if (array_key_exists('df_homedir', DF_Config::$settings)) {
				$homedir = DF_Config::$settings['df_homedir'];
			} else {
				$homedir = Tools::getHomeDir();
			}
			if (is_null($homedir)) {
				throw new DF_SettingError(DEPLOY_FRAMEWORK_NO_HOME_DIR, "No homedir found. Please configure one in settings.php");
			}
			$wikiname = DF_Config::$df_wikiName;
			$this->uploadDirectory = "$homedir/$wikiname/df_upload";
		}
	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_uploadtab');
	}

	public function getHTML() {
		global $dfgLang;
        $tabHeaderMessage = $dfgLang->getLanguageString('df_webadmin_upload_message', array('*.zip', '*.owl/*.rdf/*.n3/*.ttl/*.ntriple/*.nt/*.obl'));
		$html = <<<ENDS
$tabHeaderMessage 
<form id="df_upload_file_form" action="upload.php" method="post" enctype="multipart/form-data">
<input id="df_upload_file_input" type="file" name="datei" size="100">
<img id="df_upload_progress_indicator" src="skins/ajax-loader.gif" style="display:none"/>
<br>
</form>
ENDS
		;
		$html .= '<div id="df_bundlefilelist">';
		$html .= $this->serializePackageTable();
		$html .= '</div>';
		return $html;
	}

	private function serializePackageTable() {
		global $dfgLang;
		$html = "<table id=\"df_bundlefilelist_table\" cellspacing=\"0\" cellpadding=\"0\">";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_file');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_creationdate');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_action');
		$html .= "</th>";




		$uploadFilesCounter = 0;
		$handle = @opendir($this->uploadDirectory);
		if ($handle !== false) {
			$i=0;
			while ($entry = readdir($handle) ){
				if ($entry[0] == '.'){
					continue;
				}

				$filepath = $this->uploadDirectory."/".$entry;
				$file_ext = Tools::getFileExtension($filepath);
				$filename = basename($filepath);

				if ($file_ext == 'zip'
				|| $file_ext == 'owl'
				|| $file_ext == 'rdf'
				|| $file_ext == 'obl'
				|| $file_ext == 'nt'
				|| $file_ext == 'ttl'
				|| $file_ext == 'ntriple'
				|| $file_ext == 'n3') {
					$uploadFilesCounter++;
					$j = $i % 2;
					$html .= "<tr class=\"df_row_$j\">";
					$i++;
					$html .= "<td>";
					$html .= $filename;
					$html .= "</td>";

					$html .= "<td>";
					$lastMod = filemtime($filepath);
					$html .= date ("m/d/Y H:i:s", $lastMod);

					$html .= "</td>";

					$html .= "<td>";
					$html .= "<input type=\"button\" class=\"df_installfile_button\" value=\"".$dfgLang->getLanguageString('df_webadmin_install')."\" id=\"df_install__$filename\" loc=\"".htmlspecialchars($filepath)."\"></input>";
					$html .= "<input type=\"button\" class=\"df_removefile_button\" value=\"".$dfgLang->getLanguageString('df_webadmin_remove')."\" id=\"df_removefile__$filename\" loc=\"".htmlspecialchars($filepath)."\"></input>";
					$html .= "</td>";
					$html .= "</tr>";
				}


			}
		}
		@closedir($handle);
		$html .= "</table>";
		if ($uploadFilesCounter == 0) {
			$html .= "</table><br/>";
			$html .= $dfgLang->getLanguageString('df_webadmin_nouploadedfiles');
		}

		return $html;
	}
}
