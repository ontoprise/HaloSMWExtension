<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the Data Import-Extension.
*
*   The Data Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * @author Markus Krötzsch
 */

/**
 * Base class for all language classes.
 */
abstract class SMW_DILanguage {

	// the message arrays ...
	protected $smwUserMessages;
	protected $smwDINamespaces;
	protected $smwDINamespaceAliases;
	
	 
	/**
	 * Function that returns all user messages (those that are given only to
	 * the current user, and can thus be given in the individual user language).
	 */
	function getUserMsgArray() {
		return $this->smwUserMessages;
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
		return $this->smwDINamespaces[$namespaceID];
	}
	
	/**
	 * Returns the array with all namespaces of the Data Import extension.
	 *
	 * @return string
	 * 		Array of additional namespaces.
	 * 
	 */
	public function getNamespaces() {
		return $this->smwDINamespaces;
	}
	
	/**
	 * Returns the array with all namespace aliases of the Data Import extension. 
	 *
	 * @return string
	 * 		Array of additional namespace aliases.
	 * 
	 */
	public function getNamespaceAliases() {
		return $this->smwDINamespaceAliases;
	}
}


