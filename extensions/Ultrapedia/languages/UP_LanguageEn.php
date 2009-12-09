<?php

global $smwgUltraPediaIP;
include_once($smwgUltraPediaIP . '/languages/UP_Language.php');

class UP_LanguageEn extends UP_Language {

	protected $smwContentMessages = array(
    
	);


	protected $smwUserMessages = array(
		'viewrest' => 'REST Sandbox',
		'restful' => 'REST-ful Apis',
	);


	protected $smwSpecialProperties = array(
	);


	var $smwSpecialSchemaProperties = array (
	);

	var $smwSpecialCategories = array (
	);

	var $smwUltraPediaDatatypes = array(
	);

	protected $smwUltraPediaNamespaces = array(
	);

	protected $smwUltraPediaNamespaceAliases = array(
	);

	/**
	 * Function that returns the namespace identifiers. This is probably obsolete!
	 */
	public function getNamespaceArray() {
		return array();
	}


}


