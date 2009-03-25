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

global $smwgRMIP;
include_once($smwgRMIP . '/languages/SMW_RMLanguage.php');

class SMW_RMLanguageDe extends SMW_RMLanguage {

	protected $smwUserMessages = array(
		//'specialpages-group-di_group' => 'Data Import',

		/* Messages of the Document and Media Ontology */
		'smw_ti_welcome' => 'welcome message...',

		/* Messages for the Media File Upload */
		'smw_wws_articles_header' => 'Seiten, die den Web-Service "$1" benutzen',
	);

	protected $smwRMNamespaces = array(

	);

	protected $smwRMNamespaceAliases = array(

	);

}


