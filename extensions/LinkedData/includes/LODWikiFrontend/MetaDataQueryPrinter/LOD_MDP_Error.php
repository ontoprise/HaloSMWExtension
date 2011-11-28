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
 * @ingroup LinkedData
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
 * This meta-data printer shows an error message if the requested printer was
 * not found.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODMDPError extends LODMetaDataPrinter {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
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
		$mdp = $this->mQuery->params['metadataformat'];
		$metaDataHTML = wfMsg('lod_mdp_no_printer', $mdp);
		SMWOutputs::requireHeadItem( SMW_HEADER_TOOLTIP );
		return '<span class="smwttinline">' 
				. $wikiText 
				. '<span class="smwttcontent">' 
				.  $metaDataHTML
				. '</span></span>';
		
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
