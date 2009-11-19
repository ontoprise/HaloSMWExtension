<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Collaboration-Extension.
*
*   The Collaboration-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Collaboration-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * @author Benjamin Langguth
 */

/**
 * Base class for all language classes.
 */
abstract class CELanguage {

	
	### Constants ###
	
	### IDs of parser functions ###
	const CE_PF_SHOWCOMMENTS = 1;
	const CE_PF_SHOWFORM = 2;
	
	### IDs of parser function parameters ###
	const CE_PFP_RATINGSTYLE = 11;
	
	// the message arrays ...
	protected $mUserMessages;
	protected $mNamespaces;
	protected $mNamespaceAliases;
	protected $mParserFunctions = array();
	protected $mParserFunctionsParameters = array();
	
	
	 
	/**
	 * Function that returns all user messages (those that are given only to
	 * the current user, and can thus be given in the individual user language).
	 */
	function getUserMsgArray() {
		return $this->mUserMessages;
	}
	
	/**
	 * Returns the name of the namespace with the ID <$namespaceID>.
	 *
	 * @param int $namespaceID
	 * 		ID of the namespace whose name is requested
	 * @return string
	 * 		Name of the namespace or <null>.
	 * 
	 */
	public function getNamespace($namespaceID) {
		return $this->mNamespaces[$namespaceID];
	}
	
	/**
	 * Returns the array with all namespaces of the Collaboration extension.
	 *
	 * @return string
	 * 		Array of additional namespaces.
	 * 
	 */
	public function getNamespaces() {
		return $this->mNamespaces;
	}
	
	/**
	 * Returns the array with all namespace aliases of the Collaboration extension. 
	 *
	 * @return string
	 * 		Array of additional namespace aliases.
	 * 
	 */
	public function getNamespaceAliases() {
		return $this->mNamespaceAliases;
	}
	
	/**
	 * This method returns the language dependent name of a parser function.
	 * 
	 * @param int $parserFunctionID
	 * 		ID of the parser function i.e. one of CE_PF_SHOWCOMMENTS or CE_PF_SHOWFORM
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
	 * 		ID of the parser function parameter i.e. one of CE_PFP_RATINGSTYLE
	 * 
	 * @return string 
	 * 		The language dependent name of the parser function.
	 */
	public function getParserFunctionParameter($parserFunctionParameterID) {
		return $this->mParserFunctionsParameters[$parserFunctionParameterID];
	}
}