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
function smwLog($logmsg, $type = "" , $userid = "", $location="", $function="", $timestamp = ""){
		global $smwhgEnableLogging;
		if($smwhgEnableLogging !== true){
			return;
		}
		$db = wfGetDB( DB_MASTER );
		$fname = 'SMW::smwLog';
		$now = wfTimestampNow();
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
