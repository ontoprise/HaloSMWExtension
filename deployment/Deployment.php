<?php
$wgExtensionFunctions[] = 'dfgSetupExtension';
$smwgDFIP = $IP . '/deployment';

function dfgSetupExtension() {
	dfgInitializeLanguage();
	global $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups,$smwgDFIP;
	$wgAutoloadClasses['SMWCheckInstallation'] = $smwgDFIP . '/specials/SMWCheckInstallation/SMW_CheckInstallation.php';
	$wgSpecialPages['CheckInstallation'] = array('SMWCheckInstallation');
	$wgSpecialPageGroups['CheckInstallation'] = 'smwplus_group';
}

function dfgInitializeLanguage() {
    global $wgLanguageCode, $dfgLang, $wgMessageCache, $wgLang, $wgLanguageCode, $smwgDFIP;
    $langClass = "DF_Language_$wgLanguageCode";
    if (!file_exists("$smwgDFIP/languages/$langClass.php")) {
        $langClass = "DF_Language_En";
    }
    require_once("$smwgDFIP/languages/$langClass.php");
    $dfgLang = new $langClass();
    $wgMessageCache->addMessages($dfgLang->getLanguageArray(), $wgLang->getCode());
}