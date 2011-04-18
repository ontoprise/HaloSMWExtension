<?php
define( 'DF_VERSION', '{{$VERSION}} [B{{$BUILD_NUMBER}}]' );

$wgExtensionFunctions[] = 'dfgSetupExtension';
$smwgDFIP = $IP . '/deployment';


    
function dfgSetupExtension() {
	dfgInitializeLanguage();
	global $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups,$smwgDFIP, $wgExtensionCredits;
	$wgAutoloadClasses['SMWCheckInstallation'] = $smwgDFIP . '/specials/SMWCheckInstallation/SMW_CheckInstallation.php';
	$wgAutoloadClasses['DFBundleTools'] = $smwgDFIP . '/io/DF_BundleTools.php';
	$wgSpecialPages['CheckInstallation'] = array('SMWCheckInstallation');
	$wgSpecialPageGroups['CheckInstallation'] = 'smwplus_group';
	
	$wgExtensionCredits['other'][] = array(
        'path' => __FILE__,
        'name' => 'Deployment framework',
        'version' => DF_VERSION,
        'author' => "Kai K&uuml;hn. Owned by [http://www.ontoprise.de ontoprise GmbH].",
        'url' => 'http://smwforum.ontoprise.com/smwforum/index.php/Deployment_Framework',
	    'description' => 'Eases the installation and updating of extensions.'
    );
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