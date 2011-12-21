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
 * @ingroup TreeView_Language
 *
 * Base class for all Treeview language classes.
 * @author Thomas Schweitzer
 */
abstract class TVLanguage {

	//-- Constants --
	
	//---IDs of parser functions ---
	const PF_TREE = 1;
	const PF_GENERATE_TREE = 2;

	//---IDs of parser function parameters ---
	const PFP_ROOT	= 100;
	const PFP_THEME	= 101;
	const PFP_PROPERTY = 102;
	const PFP_SOLR_QUERY = 103;
	const PFP_ROOT_LABEL = 104;
	const PFP_FILTER     = 105;
	const PFP_WIDTH      = 106;
	const PFP_HEIGHT     = 107;
	
	
	// the special message arrays ...
	protected $mParserFunctions = array();
	protected $mParserFunctionsParameters = array();
	
	/**
	 * This method returns the language dependent name of a parser function.
	 * 
	 * @param int $parserFunctionID
	 * 		ID of the parser function i.e. one of PF_TREE, 
	 *      PF_GENERATE_TREE
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
	 * 		ID of the parser function parameter i.e. one of PFP_...
	 * 
	 * @return string 
	 * 		The language dependent name of the parser function.
	 */
	public function getParserFunctionParameter($parserFunctionParameterID) {
		return $this->mParserFunctionsParameters[$parserFunctionParameterID];
	}
	
}


