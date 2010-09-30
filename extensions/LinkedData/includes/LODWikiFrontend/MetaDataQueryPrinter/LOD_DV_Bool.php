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
 * This file contains the class LODBoolValue. 
 * @author Thomas Schweitzer
 * Date: 21.09.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * The class LODBoolValue is derived from SMWBoolValue and overwrites the 
 * methods for getting wiki text. The returned values are augemented with 
 * meta-data from the triple store.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  LODBoolValue extends SMWBoolValue {
	//--- Private fields ---
	private $mMetaDataPrinter;	// LODMetaDataPrinter: actual printer for meta data
	
	//--- Getter/Setter ---
	public function getMetaDataPrinter()     { return $this->mMetaDataPrinter; }
	public function setMetaDataPrinter($mdp) { $this->mMetaDataPrinter = $mdp; }
	
	//--- Public methods ---
	
	
	/**
	 * Calls the same method in the super class and augments the returned value
	 * with meta-data. This happens only while a query is being processed.
	 */
	public  function getShortWikiText($linked = null) {
		return $this->augmentMetaData(parent::getShortWikiText($linked));
	}

	/**
	 * Calls the same method in the super class and augments the returned value
	 * with meta-data. This happens only while a query is being processed.
	 */
	public function getLongWikiText($linked = null) {
		return $this->augmentMetaData(parent::getLongWikiText($linked));
	}
	
	//--- Private methods ---
	/**
	 * Augments the given wiki text with meta data, if appropriate.
	 * 
	 * @param string $wikiText
	 * 	The wiki text that will be augmented with meta data.
	 * 
	 */
	private function augmentMetaData($wikiText) {
		return (isset($this->mMetaDataPrinter)) 
			? $this->mMetaDataPrinter->attachMetaDataToWikiText($this, $wikiText)
			: $wikiText;
	}
}