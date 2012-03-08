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
 * Status tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}

require_once ( $mwrootDir.'/deployment/tools/smwadmin/DF_PackageRepository.php' );
require_once ( $mwrootDir.'/deployment/tools/maintenance/maintenanceTools.inc' );

class DFStatusTab {

	/**
	 * Status tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_statustab');
	}

	public function getHTML() {
		global $mwrootDir;
		global $dfgOut, $dfgLang;
		$cc = new ConsistencyChecker($mwrootDir);

		$html = $dfgLang->getLanguageString('df_webadmin_status_text');
		$html .= " <input type=\"button\" value=\"".$dfgLang->getLanguageString('df_webadmin_refresh')."\" id=\"df_refresh_status\"></input>";
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
        
		// check for finalize
		$packagesToInitialize = PackageRepository::getLocalPackagesToInitialize($mwrootDir);
		if (count($packagesToInitialize) > 0) {
			$html .= "<div id=\"df_non_finalized_extensions\">".$dfgLang->getLanguageString('df_webadmin_finalize_message');
			$html .= "<input type=\"button\" value=\"".$dfgLang->getLanguageString('df_webadmin_finalize')."\" id=\"df_run_finalize\"></input></div>";
		}
		
		// check for updates
		$dfgOut->setVerbose(false);
		$updates = $cc->checksForUpdates();
		if (count($updates) > 0) {
			$html .= "<div id=\"df_updateavailable\">".$dfgLang->getLanguageString('df_webadmin_updatesavailable');
			$html .= "<input type=\"button\" value=\"Global update\" id=\"df_global_update\"></input>";
			$html .= "<img id=\"df_gu_progress_indicator\" src=\"skins/ajax-loader.gif\" style=\"display:none\"/>";
			$html .= "</div>";
		}

		// check for new release
		$latestVersion = PackageRepository::getLatestRelease();
		if ($latestVersion !== false) {
			try {
				$currentVersion = new DFVersion(DFVersion::removePatchlevel(DF_WEBADMIN_TOOL_VERSION));
				if ($currentVersion->isLower($latestVersion)) {
					$repositorylistlink = $dfgLang->getLanguageString('df_webadmin_repository_link');
					$html .= "<div id=\"df_updateavailable\">".$dfgLang->getLanguageString('df_webadmin_newreleaseavailable',
					array("<a target=\"_blank\" href=\"".DF_REPOSITORY_LIST_LINK."\">$repositorylistlink</a></div>"));
				}
			} catch(Exception $e) {
				// ignore, just skip check for new release
			}
		}

		$dfgOut->setVerbose(true);
		$html .= "<table id=\"df_statustable\">";
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
		usort($localPackages, 'df_cmpTitles');
		$i=0;
		foreach($localPackages as $p) {
			$id = $p->getID();
			$j = $i % 2;
			$html .= "<tr class=\"df_row_$j\">";
			$i++;
			$html .= "<td class=\"df_extension_id\" ext_id=\"$id\">";
			$html .= $p->getTitle() != '' ? $p->getTitle() : $id;
			$html .= "</td>";
			$html .= "<td class=\"df_extension_version\">";
			$html .= $p->getVersion()->toVersionString().'_'.$p->getPatchlevel();
			$html .= "</td>";
			$html .= "<td class=\"df_description\">";
			$html .= $p->getDescription();
			$html .= "</td>";
			$html .= "<td class=\"df_actions\">";
			$updateText = $dfgLang->getLanguageString('df_webadmin_update');
			$deinstallText = $dfgLang->getLanguageString('df_webadmin_deinstall');
			$disabledDeInstall = "";
			if ($id == 'mw' || $id == 'wikiadmintool') {
				$disabledDeInstall = 'disabled="true"';
			}
			if (array_key_exists($id, $updates)) {
				list( $tmpid, $tmpversion, $tmppatchlevel) = $updates[$id];
				$tmpversion = $tmpversion->toVersionString();
				$html .= "<input type=\"button\" class=\"df_update_button\" value=\"$updateText\" id=\"df_update__$id"."__$tmpversion"."_$tmppatchlevel\"></input>";
				$html .= "<input type=\"button\" class=\"df_deinstall_button\" value=\"$deinstallText\" id=\"df_deinstall__$id\" $disabledDeInstall></input>";
			} else {
				$html .= "<input type=\"button\" class=\"df_update_button\" value=\"$updateText\" id=\"df_update__invalid\" disabled=\"true\"></input>";
				$html .= "<input type=\"button\" class=\"df_deinstall_button\" value=\"$deinstallText\" id=\"df_update__$id\" $disabledDeInstall></input>";
			}
			$html .= "</td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
	}


}

/**
 * Compares DeployDescriptors by ID or title (title preferred)
 *
 * @param DeployDescriptors $a
 * @param DeployDescriptors $b
 *
 * @return int -1, 0, 1
 */
function df_cmpTitles($a, $b) {
	$a = $a->getTitle() == '' ? $a->getID() : $a->getTitle();
	$b = $b->getTitle() == '' ? $b->getID() : $b->getTitle();
	return strcmp($a, $b);
}
