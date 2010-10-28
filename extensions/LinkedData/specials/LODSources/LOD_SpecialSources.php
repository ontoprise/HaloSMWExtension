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
		parent::__construct( 'LODSpecialSources', 'delete' );

	}

	function execute( $p ) {
		global $wgOut;
		$this->setHeaders();
		wfProfileIn( 'doLODSources (LOD)' );
		 
		$wgOut->addHTML( $this->createSourceTable($this->getAllSources()) );

		wfProfileOut( 'doLODSources (LOD)' );
	}
	
	private function getAllSources() {
		$results = array();
		$lodAdminStore = LODAdministrationStore::getInstance();
		$sourceIDs = $lodAdminStore->getAllSourceDefinitionIDs();
		foreach($sourceIDs as $s) {
			$sourceDef = $lodAdminStore->loadSourceDefinition($s);
			$results[$s] = $sourceDef;
		}
		return $results;
	}
	
	private function createSourceTable($table) {
		$html = "<table>";
		$html .= "<th>".wfMsg('lod_sp_source_label')."</th>";
		$html .= "<th>".wfMsg('lod_sp_source_sparqlEndpoint')."</th>";
		$html .= "<th>".wfMsg('lod_sp_source_lastmod')."</th>";
		$html .= "<th>".wfMsg('lod_sp_source_changefreq')."</th>";
		
		foreach($table as $s => $ldSource) {
			$html .= "<tr>";
			$html .="<td>";
			$html .= $ldSource->getLabel();
			$html .="</td>";
			$html .="<td>";
            $html .=  $ldSource->getSparqlEndpointLocation() == '' ? "-" : $ldSource->getSparqlEndpointLocation();
            $html .="</td>";
            $html .="<td>";
            $html .= $ldSource->getLastMod() == '' ? "-" : $ldSource->getLastMod();
            $html .="</td>";
            $html .="<td>";
            $html .= $ldSource->getChangeFreq() == '' ? "-" : $ldSource->getChangeFreq();
            $html .="</td>";
            $html .="<td>";
            $html .= "<input type=\"button\" value=\"".wfMsg('lod_sp_source_updateimport')."\"/>";
            $html .="</td>";
			$html .= "</tr>";
			
		}
		$html .= "</table>";
		return $html;
	}
}