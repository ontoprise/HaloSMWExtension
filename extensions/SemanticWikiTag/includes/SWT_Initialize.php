<?php
/*
 * Created on 14.7.2009
 *
 * Author: Ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMW_WT_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

global $smwgWTIP;
$smwgWTIP = $IP . '/extensions/SemanticWikiTag';

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'smwgWTSetupExtension';

$wgAuthDomains = array();

/**
 * Intializes Semantic NotifyMe Extension.
 * Called from SNM during initialization.
 */
function smwgWTSetupExtension() {
	global $smwgWTIP, $wgExtensionCredits, $wgAjaxExportList;

	$wgAjaxExportList[] = 'smwf_wt_getWSDL';

	if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'wsmethodaddin' ) {
		require_once( $smwgWTIP . '/includes/webservices/SMW_Webservices_AddIn2.php' );
		exit; // stop immediately
	}
	if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'mailAttachmentUpload')
	{
		require_once( $smwgWTIP . '/includes/AttachmentUpload.php' );
		exit;
	}

	global $wgMessageCache, $wgLang;
	$wgMessageCache->addMessages( array(
	/*Messages for WikiTags*/
		'stopword' => 'Stopword list',
	)
		, $wgLang->getCode() );
	
	global $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups;
	$wgAutoloadClasses['SWTStopword'] = $smwgWTIP . '/specials/SWTStopword.php';
	$wgSpecialPages['Stopword'] = array( 'SWTStopword' );
	$wgSpecialPageGroups['Stopword'] = 'smw_group';
	
	// Register Credits
	$wgExtensionCredits['parserhook'][]= array(
	'name'=>'Semantic&nbsp;WikiTag&nbsp;Extension', 'version'=>SMW_WT_VERSION,
			'author'=>"Ning Hu, Justin Zhang, [http://smwforum.ontoprise.com/smwforum/index.php/Jesse_Wang Jesse Wang], sponsored by [http://projecthalo.com Project Halo], [http://www.vulcan.com Vulcan Inc.]", 
			'url'=>'http://wiking.vulcan.com/dev', 
			'description' => 'Webservice support for MS Office WikiTag product.');

	return true;
}

function smwf_wt_getWSDL($wsdlID) {
	global $smwgWTIP;
	if ($wsdlID == 'get_addin') {
		$wsdl = "$smwgWTIP/includes/webservices/addin2.wsdl";
		$handle = fopen($wsdl, "rb");
		$contents = fread ($handle, filesize ($wsdl));
		fclose($handle);
		global $smwgHaloWebserviceEndpoint;
		if (isset($smwgHaloWebserviceEndpoint)) return str_replace("{{webservice-endpoint}}", $smwgHaloWebserviceEndpoint, $contents);
		else echo "No webservice endpoint defined! Set \$smwgHaloWebserviceEndpoint in your LocalSettings.php. E.g.: \$smwgHaloWebserviceEndpoint = \"localhost:8080\"";
		exit;
	}
}
?>