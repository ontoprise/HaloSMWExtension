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
 * This file defines the class LODMetaDataPrinter.
 * 
 * @author Thomas Schweitzer
 * Date: 23.09.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This is the abstract base class of all meta-data printers.
 * 
 * In the LOD environment meta-data like provenance is attached to results of
 * a query. This meta-data can be shown when the results are inspected by the+
 * user e.g. by hovering the mouse over a result in a table.
 * Meta-data printers will format the available meta-data for a result and 
 * attach it to the data value. Different printers can generate different 
 * appearances of this data.
 * 
 * @author Thomas Schweitzer
 * 
 */
abstract class LODMetaDataPrinter  {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	protected $mQuery;   	// SMWQuery: the query whose result is augmented with
							//		meta-data
	protected $mQueryResult;// SMWQueryResult: the result of the query. Each value
							//		of the result is augmented with formatted 
							//		meta-data	

	
	/**
	 * Constructor for  LODMetaDataPrinter
	 *
	 * @param SMWQuery $query
	 * 		The meta-data of the result of this query will be processed. 
	 * @param SMWQueryResult $queryResult
	 * 		This is the result of $query. 
	 */		
	protected function __construct(SMWQuery $query, SMWQueryResult $queryResult) {
		$this->mQuery = $query;
		$this->mQueryResult = $queryResult;
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
	public abstract function attachMetaDataToWikiText(SMWDataValue $value, $wikiText);
	
	/**
	 * Some meta-data printers may want to add one or more JavaScript files to
	 * the output. This method is called once for each query in an article.
	 * The default implementation adds no script.
	 */
	public function addJavaScripts() {
	}
	
	/**
	 * Some meta-data printers may want to add one or more style sheet files to
	 * the output. This method is called once for each query in an article.
	 * The default implementation adds no script.
	 */
	public function addStyleSheets() {
	}
	
	//--- Protected methods ---
	
	/**
	 * Adds a JavaScripts to the output. 
	 * 
	 * @param string $script
	 * 		The name of the JavaScript files to add. It must be located in
	 * 		the "scripts" folder of this extension.
	 */
	protected function addJS($script) {
		global $lodgScriptPath;
		
		$scriptFile = $lodgScriptPath . "/scripts/$script";
		SMWOutputs::requireHeadItem($script,
			'<script type="text/javascript" src="' . $scriptFile . '"></script>');
		
	}

	/**
	 * Adds a style sheet to the output. 
	 * 
	 * @param string $css
	 * 		The name of the style sheet files to add. It must be located in
	 * 		the "skins" folder of this extension.
	 */
	protected function addCSS($css) {
		global $lodgScriptPath;
		
		$cssFile = $lodgScriptPath . "/skins/$css";
		SMWOutputs::requireHeadItem($css,
			'<link rel="stylesheet" media="screen, projection" type="text/css" href="'.$cssFile.'" />');
		
	}
	
	/**
	 * Translates the IDs of meta-data properties to human readable labels
	 * e.g. SWP2_AUTHORITY => "Data source".
	 * 
	 * @param string $mdProperty
	 * 		Name of the meta-data property
	 * 
	 * @return string
	 * 		Label for this property
	 */
	protected function translateMDProperty($mdProperty) {
		return wfMsg("lod_mdpt_$mdProperty");
	}
	
	/**
	 * Some meta-data properties will not be displayed but become part of the
	 * HTML structure that is generated for meta-data e.g. the rating-key.
	 * This method removes this meta-data and creates the HTML for it.
	 * 
	 * @param array(string => array(string)) $metaDataMap
	 * 		This map of meta-data will be filtered.
	 * 
	 */
	protected function filterSpecialMetaData(&$metaDataMap) {
		$html = "";
		foreach ($metaDataMap as $mdprop => $mdval) {
			if ($mdprop === "rating-key") {
				// The rating key is not added to the content of the meta-data
				// table.
				$html .= "<span class=\"lodRatingKey\" style=\"display:none\">{$mdval[0]}</span>";
				unset($metaDataMap[$mdprop]);
			}
		}
		return $html;
	}
	
	//--- Private methods ---
}