<?php
/**
 * @author Markus Krötzsch
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

	/**
	 * Function that returns an array of namespace identifiers.
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
//			$smwgContLang->addSpecialProperty($key, $prop);
		}
	}
	
	function getSpecialSchemaPropertyArray() {
		return $this->smwSpecialSchemaProperties;
	}

	function getSpecialCategoryArray() {
		return $this->smwSpecialCategories;
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
	
	
	
}


