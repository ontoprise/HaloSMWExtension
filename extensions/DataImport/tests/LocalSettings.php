$wgGroupPermissions['*']['gardening']=true;
$wgGroupPermissions['user']['gardening']=true;

#Import SMW, SMWHalo and the Webservice extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
require_once("$IP/extensions/ScriptManager/SM_Initialize.php");

include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2', 'SMWTripleStoreQuad', 'http://halowiki/ob');
$smwgWebserviceEndpoint='localhost:8080';

include_once('extensions/SemanticGardening/includes/SGA_GardeningInitialize.php');

require_once($IP."/extensions/LinkedData/includes/LOD_Initialize.php");
enableLinkedData();

include_once('extensions/DataImport/includes/DI_Initialize.php');
enableDataImportExtension();
$wgGroupPermissions['sysop']['gardening']=true;

require_once( "$IP/extensions/ApplicationProgramming/ParserFunctions/ParserFunctions.php" );

$smwgEnableUploadConverter = true;
include_once('extensions/RichMedia/includes/RM_Initialize.php');

//enableRichMediaExtension();

$wgAllowExternalImagesFrom = $wgServer;

require_once("$IP/extensions/DataImport/IAI/includes/IAI_Initialize.php");
enableWUM();

//required for the subject creation pattern tests
require_once($IP."/extensions/ApplicationProgramming/StringFunctions/StringFunctions.php");
require_once($IP."/extensions/ApplicationProgramming/ParserFunctions/ParserFunctions.php");

