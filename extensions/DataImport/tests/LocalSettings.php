
$wgDefaultSkin = 'ontoskin2';

#Import SMW, SMWHalo and the Webservice extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2');

include_once('extensions/SemanticGardening/includes/SGA_GardeningInitialize.php');

include_once('extensions/DataImport/includes/DI_Initialize.php');
enableDataImportExtension();
$wgGroupPermissions['sysop']['gardening']=true;

//include_once('extensions/SemanticForms/includes/SF_Settings.php');

require_once( "$IP/extensions/ParserFunctions/ParserFunctions.php" );

include_once('extensions/RichMedia/includes/RM_Initialize.php');
$smwgEnableUploadConverter = true;
enableRichMediaExtension();

$wgAllowExternalImagesFrom = $wgServer;

global $smwgEnableRichMedia;
$smwgEnableRichMedia = true;
