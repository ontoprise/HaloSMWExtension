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
	require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');
	
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
		WSStorage::getDatabase()->setWWSDConfirmationStatus($wsId, "true");
		return $wsId;
	}
	return 0;
}

function smwf_ws_deleteWWSD($wsId){
	global $smwgDIIP, $wgUser;
	require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');
	
	$pageName = Title::newFromID($wsId)->getFullText();
	
	smwf_om_DeleteArticle($pageName, $wgUser->getName(), 'Deleted via the WebServiceRepository special page.');
	
	return $wsId;
}


function smwf_ti_update($tiArticleName){
	global $smwgDIIP;
	require_once($smwgDIIP."/specials/TermImport/SMW_WIL.php");
	
	$xmlString = smwf_om_GetWikiText('TermImport:'.$tiArticleName);
	$start = strpos($xmlString, "<ImportSettings>");
	$end = strpos($xmlString, "</ImportSettings>") + 17 - $start;
	$xmlString = substr($xmlString, $start, $end);
	$simpleXMLElement = new SimpleXMLElement($xmlString);

	$moduleConfig = $simpleXMLElement->xpath("//ModuleConfiguration");
	$moduleConfig = trim($moduleConfig[0]->asXML());
	$dataSource = $simpleXMLElement->xpath("//DataSource");
	$dataSource = trim($dataSource[0]->asXML());
	$mappingPolicy = $simpleXMLElement->xpath("//MappingPolicy");
	$mappingPolicy = trim($mappingPolicy[0]->asXML());
	$conflictPolicy = $simpleXMLElement->xpath("//ConflictPolicy");
	$conflictPolicy = trim($conflictPolicy[0]->asXML());
	$inputPolicy = $simpleXMLElement->xpath("//InputPolicy");
	$inputPolicy = trim($inputPolicy[0]->asXML());
	$importSets = $simpleXMLElement->xpath("//ImportSets");
	$importSets = trim($importSets[0]->asXML());
	$wil = new WIL();
		
	$terms = $wil->importTerms($moduleConfig, $dataSource, $importSets, $inputPolicy,
		$mappingPolicy, $conflictPolicy, $tiArticleName, true);
			
	if($terms != wfMsg('smw_ti_import_successful')){
		return $terms;
	} else {			
		return "success";
	}
}

function smwf_ti_deleteTermImport($tiName){
	global $wgUser;
	
	smwf_om_DeleteArticle("TermImport:".$tiName, $wgUser->getName(), 'Deleted via the WebServiceRepository special page.');
	
	return $tiName;
}
