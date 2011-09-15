<?php
/*  Copyright 2010, ontoprise GmbH
 *  This file is part of the LinkedData-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @author Kai Kuehn
 *
 * @ingroup TSCSpecialPage
 * @ingroup SpecialPage
 */
class TSCSourcesPage extends SpecialPage {


	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'TSCSources', 'delete' );

	}

	function execute( $p ) {
		global $wgOut;

		$this->setHeaders();
		wfProfileIn( 'doLODSources (LOD)' );
			
		$allSources = $this->getAllSources();
		if (!is_array($allSources)) {
			global $smwgWebserviceEndpoint;
			$wgOut->addHTML("Error: Triplestore not accessible at: ".$smwgWebserviceEndpoint);
			return;
		}
		$wgOut->addHTML( $this->createSourceTable($allSources) );

		wfProfileOut( 'doLODSources (LOD)' );
	}

	public function getAllSources() {
		$results = array();
		$lodAdminStore = TSCAdministrationStore::getInstance();
		$results = $lodAdminStore->loadAllSourceDefinitions();

		return $results;
	}

	public function createSourceTable($table) {

		$html = '<table id="lod_source_table" class="lod_sp_sources_table">';
		$html .= "<th>".wfMsg('tsc_sp_source_label')."</th>";
		$html .= "<th>".wfMsg('tsc_sp_source_source')."</th>";
		$html .= "<th>".wfMsg('tsc_sp_source_lastimport')."</th>";
		$html .= "<th>".wfMsg('tsc_sp_source_changefreq')."</th>";
		$html .= "<th colspan=\"2\">".wfMsg('tsc_sp_source_options')."</th>";
		$html .= "<th>".wfMsg('tsc_sp_isimported')."</th>";
		$html .= "<th>".wfMsg('tsc_sp_statusmsg')."</th>";

		$linkedDataInstalled = defined('LOD_LINKEDDATA_VERSION');

		foreach($table as $s => $ldSource) {
				
			$dataDumpLocations = is_array($ldSource->getDataDumpLocations()) ? implode(",",$ldSource->getDataDumpLocations()) : '';
			$html .= "<tr>";
			$html .="<td>";
			$html .= $ldSource->getLabel();
			$html .="</td>";
			$html .="<td>";
			$html .=  $ldSource->getSparqlEndpointLocation() == '' ? $dataDumpLocations : $ldSource->getSparqlEndpointLocation();
			$html .="</td>";
			$html .="<td>";
			$html .= $ldSource->getLastImportDate() == '' ? "-" : $ldSource->getLastImportDate();
			$html .="</td>";
			$html .="<td>";
			$html .= $ldSource->getChangeFreq() == '' ? "-" : $ldSource->getChangeFreq();
			$html .="</td>";
			$html .="<td>";
			$html .= wfMsg('tsc_sp_schema_translation')."<input type=\"checkbox\" checked id=\"runSchemaTranslation_".$s."\" value=\"true\" title=\"Include schema translation\" />";
			$html .="</td>";
			$html .="<td>";
			$html .= wfMsg('tsc_sp_identity_resolution')."<input type=\"checkbox\" checked id=\"runIdentityResolution_".$s."\" true\" value=\"true\" title=\"Include identity resolution\" />";
			$html .="</td>";
			$html .="<td>";
			$html .= $ldSource->isImported() === false ? "-" : "yes";
			$html .="</td>";
			$html .="<td>";
			$html .= $ldSource->getErrorMessagesFromLastImport();
			$html .="</td>";

			if (!$ldSource->isImported()) {
				$disabled = 'disabled="disabled"';
			} else {
				$disabled = '';
			}
			$html .="<td>";

			if (!$linkedDataInstalled) {
				$importButtonEnabled = 'disabled="disabled"';
			} else {
				$importButtonEnabled = '';
			}
			$html .= "<input $importButtonEnabled type=\"button\" onclick=\"LOD.sources.doImportOrUpdate(this, '$s', false, jQuery('#runSchemaTranslation_".$s."').attr('checked'), jQuery('#runIdentityResolution_".$s."').attr('checked'))\" value=\"".($ldSource->isImported() ? wfMsg('tsc_sp_source_reimport') : wfMsg('tsc_sp_source_import'))."\"/>";
			$html .="</td>";
			$html .="<td>";
			$html .= "<input $disabled type=\"button\" onclick=\"LOD.sources.doImportOrUpdate(this, '$s', true, jQuery('#runSchemaTranslation_".$s."').attr('checked'), jQuery('#runIdentityResolution_".$s."').attr('checked'))\" value=\"".wfMsg('tsc_sp_source_update')."\"/>";
			$html .="</td>";
			$html .= "</tr>";
				
		}
		$html .= "</table>";
		return $html;
	}
}