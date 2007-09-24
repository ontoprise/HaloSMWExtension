<?php
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
		global $smwhgEnableLogging, $wgUser, $wgTitle;
		if($smwhgEnableLogging !== true){
			return;
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
?>
