
#Import SMW, SMWHalo
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2', 'SMWTripleStore', 'http://publicbuild/ob');
$smwgWebserviceEndpoint="localhost:8090";
$smwhgAutoCompletionTSC=true;

#SemanticGardening
$phpInterpreter="c:\Programme\xampp\php";
include_once('extensions/SemanticGardening/includes/SGA_GardeningInitialize.php');

#SemanticNotifications
$smwgEnableSemanticNotifications = true;
include_once('extensions/SemanticNotifications/includes/SN_Initialize.php');
enableSemanticNotifications();

###Each extension wich depends on SMWHalo depends also on arclibrary, scriptmanager and deployment framework####
require_once('deployment/Deployment.php');
require_once("extensions/ScriptManager/SM_Initialize.php");
include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();
################################################################################################################
