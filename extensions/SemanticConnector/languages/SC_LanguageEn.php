<?php
/*  Copyright 2007, ontoprise GmbH
 *  This file is part of the Connector-Extension.
 *
 *   The Connector-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Connector-Extension is distributed in the hope that it will be useful,
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

global $smwgConnectorIP;
include_once($smwgConnectorIP . '/languages/SC_Language.php');

class SC_LanguageEn extends SC_Language {

	protected $smwContentMessages = array(
    
	);


	protected $smwUserMessages = array(
		'viewrest' => 'REST Sandbox',
		'restful' => 'REST-ful Apis',
		'sc_restful_forbidden' => 'You\'ve got no privilege to edit, please contact Wiki administrator.',
		'sc_restful_badurl' => 'You must specify the rest-ful api in the URL; the URL should look like \'Special:RESTful/&lt;rest command&gt;\'.',
		'sc_editmapping' => 'Edit form schema map',
		'sc_loading' => 'Loading...',
	);


	protected $smwSpecialProperties = array(
	);


	var $smwSpecialSchemaProperties = array (
	);

	var $smwSpecialCategories = array (
	);

	var $smwConnectorDatatypes = array(
	);

	protected $smwConnectorNamespaces = array(
	);

	protected $smwConnectorNamespaceAliases = array(
	);

	/**
	 * Function that returns the namespace identifiers. This is probably obsolete!
	 */
	public function getNamespaceArray() {
		return array();
	}


}


