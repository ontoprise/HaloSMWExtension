# SMW
include_once('extensions/SemanticMediaWiki/SemanticMediaWiki.php');
enableSemantics('http://wiki', true);

# SMWHalo
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2', "SMWTripleStore");

$smwgHaloWebserviceEndpoint='localhost:8090';
$smwgHaloEnableObjectLogicRules=true;
$smwgWebserviceProtocol="rest";

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
