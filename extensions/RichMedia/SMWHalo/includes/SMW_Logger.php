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

/*
 * Created on Sep 5, 2007
 *
 */
 
 $wgAjaxExportList[] = 'smwLog';
 
 
 /**
 * This function is exported to the ajax interface and will log msg
 * to the database
 */
function smwLog($logmsg, $type = "" , $function="", $locationForce = "" , $timestamp = ""){
		global $smwgEnableLogging, $wgUser, $wgTitle;
		if($smwgEnableLogging !== true){
			return "";
		}
		$db = wfGetDB( DB_MASTER );
		$fname = 'SMW::smwLog';
		$userid = $wgUser->getID() != null ? $wgUser->getID() : "";
		$location = $wgTitle != null && $locationForce == '' ? $wgTitle->getNsText().":".$wgTitle->getText() : $locationForce;
		$db->insert( 'smw_logging',
			array(
				  'user'      		=>  $userid,
				  'location'		=>	$location ,
				  'type'			=>	$type ,
				  'function'		=>	$function ,
				  'remotetimestamp'	=>	$timestamp ,
				  'text'			=>  $logmsg 
			),
			$fname
		);
		return 'true';
}

