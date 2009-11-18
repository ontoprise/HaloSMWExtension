<?php

if ( !defined( 'SF_VERSION' ) ){ 
	die("The Semantic Forms Data API requires the Semantic Forms extension");
}
	
$wgAPIModules['sfdata'] = 'SFDataAPI';
//todo: path anpassen
//todo: nur laden wenn semantic forms aktiv
$wgAutoloadClasses['SFDataAPI'] = $smwgHaloIP."/DataAPI/SemanticFormsAPI/WS/SF_DataAPI.php";