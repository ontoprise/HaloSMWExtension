<?php
/*  Copyright 2011, ontoprise GmbH
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
 * Upload tab
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}

require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Tools.php');

class DFUploadTab {
	/**
	 * Status tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_uploadtab');
	}

	public function getHTML() {
		global $dfgLang;
		$uploadButtonText = $dfgLang->getLanguageString('df_webadmin_upload');
		$html = <<<ENDS
<form action="upload.php" method="post" enctype="multipart/form-data">
<input type="file" name="datei"><br>
<input type="submit" value="$uploadButtonText">
</form>
ENDS
		;
		$html .= '<div class="df_bundlefilelist">';
		$html .= $this->serializePackageTable();
		$html .= '</div>';
		return $html;
	}

	private function serializePackageTable() {
		global $dfgLang;
		$html = "<table>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_file');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_creationdate');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_action');
		$html .= "</th>";
		$uploadDirectory = Tools::getHomeDir()."/df_upload";
		if ($uploadDirectory == '/df_upload') {
			$uploadDirectory = Tools::getTempDir()."/df_upload";
		}
		$handle = @opendir($uploadDirectory);
		if ($handle !== false) {

			while ($entry = readdir($handle) ){
				if ($entry[0] == '.'){
					continue;
				}

				$filepath = $uploadDirectory."/".$entry;
				$file_ext = Tools::getFileExtension($filepath);
				$filename = basename($filepath);

				if ($file_ext == 'zip'
				|| $file_ext == 'owl'
				|| $file_ext == 'rdf'
				|| $file_ext == 'obl'
				|| $file_ext == 'nt'
				|| $file_ext == 'ntriple'
				|| $file_ext == 'n3') {
					$html .= "<tr>";
					$html .= "<td>";
					$html .= $filename;
					$html .= "</td>";

					$html .= "<td>";
					$lastMod = filemtime($filepath);
					$html .= date ("m/d/Y", $lastMod);

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
		return $html;
	}
}