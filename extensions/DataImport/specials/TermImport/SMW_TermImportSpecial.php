<?php
/*  Copyright 2008, ontoprise GmbH
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
 * @file
 * @ingroup DITermImport
 * A special page for the import of terms into the wiki.
 *
 *
 * @author Thomas Schweitzer
 */

 if (!defined('MEDIAWIKI')) die();



global $IP, $smwgDIIP;
require_once( $IP . "/includes/SpecialPage.php" );
require_once( $smwgDIIP . "/specials/TermImport/SMW_CL.php" );

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWTermImportSpecial extends SpecialPage {
	public function __construct() {
		parent::__construct('TermImport', 'delete');
	}
	/**
	 * Overloaded function that is resopnsible for the creation of the Special Page
	 */
	public function execute($par) {

		global $wgOut, $wgUser;

		if ( ! $wgUser->isAllowed('delete') ) {
			$wgOut->permissionRequired('delete');
			return;
		}
		
		$wgOut->setPageTitle(wfMsg('smw_ti_termimport'));

		$cl = new CL();
		$cl->execute();
	}

}