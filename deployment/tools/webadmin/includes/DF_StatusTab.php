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
 * Status tab
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */

require_once ( $mwrootDir.'/deployment/tools/smwadmin/DF_PackageRepository.php' );
require_once ( $mwrootDir.'/deployment/tools/maintenance/maintenanceTools.inc' );

class DFStatusTab {

	/**
	 * Status tab
	 *
	 */
	public function __construct() {

	}

	public function getHTML() {
		global $mwrootDir;
		global $dfgOut;
		$cc = new ConsistencyChecker($mwrootDir);
		$html = "";
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		$dfgOut->setVerbose(false);
		$updates = $cc->checksForUpdates();
		$dfgOut->setVerbose(true);
		$html .= "<table>";
		$html .= "<th>";
		$html .= "Extension";
		$html .= "</th>";
		$html .= "<th>";
		$html .= "Description";
		$html .= "</th>";
		$html .= "<th>";
		$html .= "Action";
		$html .= "</th>";
		foreach($localPackages as $id => $p) {
			$html .= "<tr>";
			$html .= "<td class=\"df_extension_id\">";
			$html .= $id;
			$html .= "</td>";
			$html .= "<td class=\"df_description\">";
			$html .= $p->getDescription();
			$html .= "</td>";
			$html .= "<td class=\"df_actions\">";
			if (array_key_exists($id, $updates)) {
				$html .= "<input type=\"button\" class=\"df_install_button\" value=\"De-Install\" id=\"df_deinstall__$id\"></input>";
			} else {
				$html .= "<input type=\"button\" class=\"df_install_button\" value=\"Update\" id=\"df_update__$id\"></input>";
			}
			$html .= "</td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
	}


}