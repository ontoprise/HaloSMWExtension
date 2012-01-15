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
 * Maintenance tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}

require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Rollback.php');

class DFMaintenanceTab {

	/**
	 * Maintenance tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_maintenacetab');
	}

	public function getHTML() {
		global $dfgLang;
		$html = "<div style=\"margin-bottom: 10px;\">".$dfgLang->getLanguageString('df_webadmin_maintenancetext')."</div>";
		$html .= "<input type=\"text\" style=\"width: 450px;\" value=\"\" id=\"df_restorepoint\"></input>";
		$html .= "<input type=\"button\" value=\"Create\" id=\"df_create_restorepoint\"></input>";
		$html .= "<br/>";
		$html .= $this->serializeRestorePoints($this->getAllRestorePoints());
		return $html;
	}

	public function serializeRestorePoints($restorepoints) {
		global $dfgLang;
		
		$html = "<div class=\"df_restorepoints\"><table id=\"df_restorepoint_table\" cellspacing=\"0\" cellpadding=\"0\">";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_restorepoint');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_creationdate');
		$html .= "</th>";
		$html .= "<th>";
		$html .= $dfgLang->getLanguageString('df_webadmin_action');
		$html .= "</th>";
        
		$restoreButtonText = $dfgLang->getLanguageString('df_webadmin_restore');
		$restoreRemoveButtonText = $dfgLang->getLanguageString('df_webadmin_removerestore');
		
		if (count($restorepoints) == 0) {
			$html .= "</table></div><br/>";
			$html .= $dfgLang->getLanguageString('df_webadmin_norestorepoints');
			return $html;
		}
		$i=0;
		foreach($restorepoints as $rp) {
			$j = $i % 2;
			$html .= "<tr class=\"df_row_$j\">";
			$i++;
			$html .= "<td>";
			$name = basename($rp);
			$html .= $name;
			$html .= "</td>";
			$html .= "<td>";
			$lastMod = filemtime($rp);
			$html .= date ("Y-m-d", $lastMod);
			$html .= "</td>";
			$html .= "<td>";
			$html .= "<input type=\"button\" class=\"df_restore_button\" value=\"$restoreButtonText\" id=\"df_restore__$name\"></input>";
			$html .= "<input type=\"button\" class=\"df_remove_restore_button\" value=\"$restoreRemoveButtonText\" id=\"df_remove_restore__$name\"></input>";
			$html .= "</td>";
			$html .= "</tr>";
		}
		$html .= "</table></div>";
		return $html;
	}

	public function getAllRestorePoints() {
		global $mwrootDir, $dfgOut;
		$rollback = Rollback::getInstance($mwrootDir);
		return $rollback->getAllRestorePoints();

	}
}
