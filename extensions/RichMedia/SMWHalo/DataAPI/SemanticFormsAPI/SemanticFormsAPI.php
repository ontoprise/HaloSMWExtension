<?php

/**
 * @file
  * @ingroup DASemanticForms
  * 
  * @author Dian
 */

/**
 * This group contains all parts of the DataAPI that deal with the SemanticForms component
 * @defgroup DASemanticForms
 * @ingroup DataAPI
 */

if ( !defined( 'SF_VERSION' ) ){ 
	die("The Semantic Forms Data API requires the Semantic Forms extension");
}
	
$wgAPIModules['sfdata'] = 'SFDataAPI';
//todo: path anpassen
//todo: nur laden wenn semantic forms aktiv
$wgAutoloadClasses['SFDataAPI'] = $smwgHaloIP."/DataAPI/SemanticFormsAPI/WS/SF_DataAPI.php";