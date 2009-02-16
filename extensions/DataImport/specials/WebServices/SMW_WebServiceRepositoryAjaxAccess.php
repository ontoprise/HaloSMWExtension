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
global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ws_updateCache';
$wgAjaxExportList[] = 'smwf_ws_confirmWWSD';

/**
 * This provides some methods for the special page webservice repository, that are
 * accessed by ajax-calls
 *
 * @author Ingo Steinbauer
 *
 */

	/**
	 * method for confirming a new webservice
	 *
	 * @param string $wsId
	 *
	 */
	function smwf_ws_confirmWWSD($wsId){
		global $smwgDIIP;
		require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');
		WSStorage::getDatabase()->setWWSDConfirmationStatus($wsId, "true");
		return $wsId;
	}

	?>