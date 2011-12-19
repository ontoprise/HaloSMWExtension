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

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ws_updateCache';
$wgAjaxExportList[] = 'smwf_ws_confirmWWSD';
$wgAjaxExportList[] = 'smwf_ws_deleteWWSD';
$wgAjaxExportList[] = 'smwf_ti_update';
$wgAjaxExportList[] = 'smwf_ti_deleteTermImport'; 


/**
 * @file
 * @ingroup DIWebServices
 * Thisfile provides some methods for the special page webservice repository, that are
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
	
	global $wgUser;
	$allowed = false;
	$user = $wgUser;
	if($user != null){
		$groupsOfUser = $user->getGroups();
		foreach($groupsOfUser as $key => $group) {
			if($group == 'sysop'){
				$allowed = true;
			}
		}
	}
	
	if($allowed){
		difGetWSStore()->setWWSDConfirmationStatus($wsId, "true");
		return $wsId;
	}
	return 0;
}

function smwf_ws_deleteWWSD($wsId){
	global $smwgDIIP, $wgUser;
	
	$pageName = Title::newFromID($wsId)->getFullText();
	
	smwf_om_DeleteArticle($pageName, $wgUser->getName(), 'Deleted via the WebServiceRepository special page.');
	
	return $wsId;
}


function smwf_ti_update($tiArticleName){
	$res = DICL::importTerms($tiArticleName, true);
			
	if($res === true){
		return "success";
	} else {			
		return $res;
	}
}

function smwf_ti_deleteTermImport($tiName){
	global $wgUser;
	
	smwf_om_DeleteArticle("TermImport:".$tiName, $wgUser->getName(), 'Deleted via the WebServiceRepository special page.');
	
	return $tiName;
}
