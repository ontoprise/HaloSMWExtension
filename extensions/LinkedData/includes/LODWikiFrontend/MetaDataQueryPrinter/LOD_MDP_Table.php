<?php
/**
 * @file
 * @ingroup LinkedData
 */

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
 * This file contains the class LODMetaDataFormatter.
 *
 * @author Thomas Schweitzer
 * Date: 21.09.2010
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

//--- Includes ---

/**
 * This class prints the meta data of a query result in form of a table in HTML.
 *
 * @author Thomas Schweitzer
 *
 */
class LODMDPTable extends LODMetaDataPrinter {

	//--- Constants ---
	

	//--- Private fields ---

	/**
	 * Constructor for LODMDPTable
	 *
	 * @param SMWQuery $query
	 * 		The meta-data of the result of this query will be processed.
	 * @param SMWQueryResult $queryResult
	 * 		This is the result of $query.
	 */
	function __construct(SMWQuery $query, SMWQueryResult $queryResult) {
		parent::__construct($query, $queryResult);
	}


	//--- getter/setter ---
	//	public function getXY()           {return $this->mXY;}

	//	public function setXY($xy)               {$this->mXY = $xy;}

	//--- Public methods ---


	/**
	 * This function takes the wiki text of the data value $value and augments
	 * it with the HTML of the value's meta-data.
	 *
	 * @param SMWDataValue $value
	 * 		The meta-data of this value will be formatted by the printer.
	 * @param string $wikiText
	 * 		The wiki text to augment
	 * @return string
	 * 		The augmented wiki text
	 */
	public function attachMetaDataToWikiText(SMWDataValue $value, $wikiText) {
		$md = $value->getMetadataMap();
		$specialMetaDataHTML = $this->filterSpecialMetaData($md);

		if (empty($md)) {
			//			$metaDataHTML = wfMsg('lod_mdp_no_metadata');
			$metaDataHTML = "";
		} else {
			// Generate the HTML for the meta-data as table
			$tprop  = wfMsg('lod_mdp_property');
			$tvalue = wfMsg('lod_mdp_value');
			$title  = wfMsg('lod_mdp_table_title');
				
			$metaDataHTML = '<span class = "lodMdTableTitle">'.$title.'</span>';
			$metaDataHTML .= '<table class="lodMdTable">';
			$metaDataHTML .= "<tr><th>$tprop</th><th>$tvalue</th></tr>";
			foreach ($md as $mdprop => $mdval) {
				$mdprop = $this->translateMDProperty($mdprop);
				$metaDataHTML .= "<tr><td>$mdprop</td><td>";
				$numVal = count($mdval);
				$i = 0;
				foreach ($mdval as $mdve) {
					$metaDataHTML .= $mdve;
					if (++$i !== $numVal) {
						$metaDataHTML .= "<br />";
					}
					$metaDataHTML .= "</td>";
				}
				$metaDataHTML .= "</tr>";
			}
			$metaDataHTML .= '</table>';
		}
        
		// set background class according to source ID (if existing)
		$backgroundClass = "";
		$datasourceID = array_key_exists('swp2_authority_id', $md) ? reset($md['swp2_authority_id']) : false;
		if ($datasourceID !== false) {
			$backgroundClass = ' lod_background'.$this->hashtocolor($datasourceID); 
		} 
		
		return '<span class="lodMetadata '.$backgroundClass.'">'
		. $wikiText
		. '<span class="lodMetadataContent" style="display:none">'
		.  $metaDataHTML
		. "</span>"
		. $specialMetaDataHTML
		. "</span>";

	}
	
	private function hashtocolor($sourceID) {
		return md5($sourceID) % 10;
	}
	/**
	 * This meta-data printer makes use of the jQuery qTip extension for showing
	 * tool-tips with meta-data. It adds a script that enables these tool-tips.
	 */
	public function addJavaScripts() {
		self::addJS("LOD_MetaDataQTip.js");
	}

	/**
	 * Adds the style sheets for the table in the tool-tip.
	 */
	public function addStyleSheets() {
		self::addCSS('metadata.css');
	}

	//--- Private methods ---
}