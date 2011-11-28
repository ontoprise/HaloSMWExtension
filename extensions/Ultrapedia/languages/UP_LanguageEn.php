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


