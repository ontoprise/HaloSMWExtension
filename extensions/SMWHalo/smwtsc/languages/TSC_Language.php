<?php
/**
 * @file
 * @ingroup LinkedData_Language
 */

/*  Copyright 2010, ontoprise GmbH
*   This file is part of the LinkedData-Extension.
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
 * Base class for all LinkedData language classes.
 * @author Thomas Schweitzer
 */
abstract class TSCLanguage {

	//-- Constants --
	
	//---IDs of parser functions ---
	
	const PF_LSD	 = 2; // TSC source definition
	

	
	
	const PFP_LSD_ID						= 200;
	const PFP_LSD_CHANGEFREQ				= 201;
	const PFP_LSD_DATADUMPLOCATION			= 202;
	const PFP_LSD_DESCRIPTION				= 203;
	const PFP_LSD_HOMEPAGE					= 204;
	const PFP_LSD_LABEL						= 205;
	const PFP_LSD_LASTMOD					= 206;
	const PFP_LSD_LINKEDDATAPREFIX			= 207;
	const PFP_LSD_SAMPLEURI					= 208;
	const PFP_LSD_SPARQLENDPOINTLOCATION	= 209;
	const PFP_LSD_SPARQLGRAPHNAME			= 210;
	const PFP_LSD_URIREGEXPATTERN			= 211;
	const PFP_LSD_VOCABULARY				= 212;
	const PFP_LSD_SPARQLGRAPHPATTERN		= 213;
	const PFP_LSD_PREDICATETOCRAWL			= 214;
        const PFP_LSD_LEVELSTOCRAWL			= 215;
	
	
	
	
	// the special message arrays ...
	
	protected $mNamespaceAliases = array();
	protected $mParserFunctions = array();
	protected $mParserFunctionsParameters = array();

	

	/**
	 * Function that returns an array of namespace aliases, if any.
	 */
	public function getNamespaceAliases() {
		return $this->mNamespaceAliases;
	}
	
	/**
	 * This method returns the language dependent name of a parser function.
	 * 
	 * @param int $parserFunctionID
	 * 		ID of the parser function i.e. one of PF_MAPPING...
	 * 
	 * @return string 
	 * 		The language dependent name of the parser function.
	 */
	public function getParserFunction($parserFunctionID) {
		return $this->mParserFunctions[$parserFunctionID];
	}
	
	/**
	 * This method returns the language dependent name of a parser function 
	 * parameter.
	 * 
	 * @param int $parserFunctionParameterID
	 * 		ID of the parser function parameter i.e. one of ...
	 * 
	 * @return string 
	 * 		The language dependent name of the parser function.
	 */
	public function getParserFunctionParameter($parserFunctionParameterID) {
		return $this->mParserFunctionsParameters[$parserFunctionParameterID];
	}
	

}


