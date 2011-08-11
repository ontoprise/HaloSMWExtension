<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * @file
*  @ingroup SMWHaloLanguage
*  
*  @defgroup SMWHaloLanguage SMWHalo Language files
*  @ingroup SMWHalo
*  
 * @author Ontoprise
 */

/**
 * Base class for all language classes.
 */
abstract class SMW_HaloLanguage {

	// the message arrays ...
	protected $smwContentMessages;
	protected $smwUserMessages;
	protected $smwDatatypeLabels;
	protected $smwSpecialProperties;
	protected $smwSpecialSchemaProperties;
	protected $smwHaloDatatypes;
	protected $smwHaloNamespaces;
	protected $smwHaloNamespaceAliases;
	
	/**
	 * Function that returns an array of namespace identifiers. This function 
	 * is obsolete
	 */
	abstract function getNamespaceArray();


	/**
	 * Find the internal message id of some localised message string
	 * for a datatype. If no type of the given name exists (maybe a 
	 * custom of compound type) then FALSE is returned.
	 */
	function findDatatypeMsgID($label) {
		return array_search($label, $this->smwDatatypeLabels);
	}
	
	/**
	 * Registers all special properties of this extension in Semantic Media Wiki.
	 * 
	 * The language files of the Halo extension contain a mapping from special 
	 * property constants to their string representation. These mappings are
	 * added to the mapping defined by Semantic Media Wiki.
	 */
	function registerSpecialProperties() {
		global $smwgContLang;
		foreach ($this->smwSpecialProperties as $key => $prop) {
			list($typeid, $label) = $prop;
			SMWPropertyValue::registerProperty($key, $typeid, $label, true);
		
		}
	}
	
	/**
	 * Returns the label of the special property with the ID $propID.
	 * @param int propID
	 * 			ID of the special property
	 * @return String Label of the special property
	 */
	function getSpecialPropertyLabel($propID) {
		return $this->smwSpecialProperties[$propID];
	}
	
	/**
	 * Returns all labels of the special properties.
	 * @return array<String> Labels of the special properties
	 */
	function getSpecialPropertyLabels() {
		return $this->smwSpecialProperties;
	}
	
	function getSpecialSchemaPropertyArray() {
		return $this->smwSpecialSchemaProperties;
	}

	function getSpecialCategoryArray() {
		return $this->smwSpecialCategories;
	}
	
	function getHaloDatatype($datatypeID) {
		return $this->smwHaloDatatypes[$datatypeID];
	}

	/**
	 * Function that returns all content messages (those that are stored
	 * in some article, and can thus not be translated to individual users).
	 */
	function getContentMsgArray() {
		return $this->smwContentMessages;
	}

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
		return $this->smwHaloNamespaces[$namespaceID];
	}
	
	/**
	 * Returns the array with all namespaces of the halo extension.
	 *
	 * @return string
	 * 		Array of additional namespaces.
	 * 
	 */
	public function getNamespaces() {
		return $this->smwHaloNamespaces;
	}
	
	/**
	 * Returns the array with all namespace aliases of the halo extension. 
	 *
	 * @return string
	 * 		Array of additional namespace aliases.
	 * 
	 */
	public function getNamespaceAliases() {
		return $this->smwHaloNamespaceAliases;
	}
	
	
	
}


