<?php

function di_utils_getParamSetId($wsId, $pId){
	$db =& wfGetDB( DB_SLAVE );
	$tn = $db->tableName("smw_ws_articles");
	$query = 'SELECT param_set_id FROM '.$tn.' WHERE web_service_id='.$wsId.' AND page_id='.$pId;
	$result = $db->query($query);
	$result = $db->fetchObject($result);
	
	if(!$result->param_set_id){
		return 0;
	}
	return $result->param_set_id;
}

function di_utils_getPageId($pageName){
	$db =& wfGetDB( DB_SLAVE );
	$tn = $db->tableName("page");
	$query = 'SELECT page_id FROM '.$tn.' WHERE page_title="'.$pageName.'" AND page_namespace=0';
	$result = $db->query($query);
	$result = $db->fetchObject($result);
	return $result->page_id;
}

function di_utils_getWSId($wsName){
	$ws = WebService::newFromName($wsName);
	return ($ws != null) ? $ws->getArticleID() : null;
}

function di_utils_setupWebServices($titles, $confirm=true){
	global $wgScriptPath;
	foreach($titles as $title){
		$text = smwf_om_GetWikiText('WebService:'.$title);
		$text = str_replace("http://localhost/MashupWiki"
			, "http://localhost".$wgScriptPath, $text);
		smwf_om_EditArticle('WebService:'.$title, 'PHPUnit', $text, '');
		$ws = WebService::newFromName($title);
		if($ws != null){
			if($confirm){
				WSStorage::getDatabase()->setWWSDConfirmationStatus($ws->getArticleID(), "true");
			}
		}
	}
}

function di_utils_setupWSUsages($titles){
	foreach($titles as $title){
		$text = smwf_om_GetWikiText($title);
		smwf_om_EditArticle($title, 'PHPUnit', $text, '');
	}
}

function di_utils_truncateWSTables(){
	$db =& wfGetDB( DB_MASTER );
	$tables = array('smw_ws_articles', 'smw_ws_cache' , 'smw_ws_parameters', 'smw_ws_wwsd');
	foreach($tables as $table){
		$tn = $db->tableName($table);
		$query = "TRUNCATE TABLE ".$tn;
		$db->query($query);
	}
}

?>