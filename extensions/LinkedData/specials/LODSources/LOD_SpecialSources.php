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
 * @ingroup LODSpecialPage
 * @ingroup SpecialPage
 */
class LODSourcesPage extends SpecialPage {


	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'LODSources', 'delete' );

	}

	function execute( $p ) {
		global $wgOut;
		global $lodgScriptPath;
        
        $scriptFile = $lodgScriptPath . "/scripts/LOD_SpecialSources.js";
        SMWOutputs::requireHeadItem("LOD_SpecialSources.js",
            '<script type="text/javascript" src="' . $scriptFile . '"></script>');
            SMWOutputs::requireHeadItem("lod_sources.css",
            '<link rel="stylesheet" type="text/css" href="' . $lodgScriptPath . '/skins/sources.css" />');
        
        SMWOutputs::commitToOutputPage( $wgOut );
		$this->setHeaders();
		wfProfileIn( 'doLODSources (LOD)' );
		 
		$wgOut->addHTML( $this->createSourceTable($this->getAllSources()) );

		wfProfileOut( 'doLODSources (LOD)' );
	}
	
	public function getAllSources() {
		$results = array();
		$lodAdminStore = LODAdministrationStore::getInstance();
		$sourceIDs = $lodAdminStore->getAllSourceDefinitionIDs();
		foreach($sourceIDs as $s) {
			$sourceDef = $lodAdminStore->loadSourceDefinition($s);
			$results[$s] = $sourceDef;
		}
		return $results;
	}
	
	public function createSourceTable($table) {
		$html = '<table id="lod_source_table" class="lod_sp_sources_table">';
		$html .= "<th>".wfMsg('lod_sp_source_label')."</th>";
		$html .= "<th>".wfMsg('lod_sp_source_source')."</th>";
		$html .= "<th>".wfMsg('lod_sp_source_lastmod')."</th>";
		$html .= "<th>".wfMsg('lod_sp_source_changefreq')."</th>";
		$html .= "<th>".wfMsg('lod_sp_isimported')."</th>";
		
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
            $html .= $ldSource->getLastMod() == '' ? "-" : $ldSource->getLastMod();
            $html .="</td>";
            $html .="<td>";
            $html .= $ldSource->getChangeFreq() == '' ? "-" : $ldSource->getChangeFreq();
            $html .="</td>";
            $html .="<td>";
            $html .= $ldSource->isImported() === false ? "-" : "yes";
            $html .="</td>";
            
            if (!$ldSource->isImported()) {
            	$disabled = 'disabled="disabled"';
            } else {
            	$disabled = '';
            }
            $html .="<td>";
            $html .= "<input type=\"button\" onclick=\"LOD.sources.doImportOrUpdate(this, '$s', false);\" value=\"".($ldSource->isImported() ? wfMsg('lod_sp_source_reimport') : wfMsg('lod_sp_source_import'))."\"/>";
            $html .="</td>";
            $html .="<td>";
            $html .= "<input $disabled type=\"button\" onclick=\"LOD.sources.doImportOrUpdate(this, '$s', true);\" value=\"".wfMsg('lod_sp_source_update')."\"/>";
            $html .="</td>";
			$html .= "</tr>";
			
		}
		$html .= "</table>";
		return $html;
	}
}