<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Base class for all HaloACL language classes.
 * @author Thomas Schweitzer
 */
abstract class HACLLanguage {

	// the special message arrays ...
	protected $mNamespaces;
	protected $mNamespaceAliases = array();
	protected $mPermissionDeniedPage;


	/**
	 * Function that returns an array of namespace identifiers.
	 */
	public function getNamespaces() {
		return $this->mNamespaces;
	}

	/**
	 * Function that returns an array of namespace aliases, if any.
	 */
	public function getNamespaceAliases() {
		return $this->mNamespaceAliases;
	}
	
	/**
	 * Returns the name of the page that informs the user, that access to
	 * a requested page is denied. A page with this name must be created in the 
	 * wiki.
	 */
	public function getPermissionDeniedPage() {
		return $this->mPermissionDeniedPage;
	}
	
}


