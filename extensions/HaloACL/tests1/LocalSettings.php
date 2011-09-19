$smwgMessageBroker='localhost';
$smwgHaloWebserviceEndpoint='localhost:8060';


#Import SMW, SMWHalo and the Gardening extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://mywiki', true);

include_once('extensions/SemanticForms/includes/SF_Settings.php');

include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
//enableSMWHalo('SMWHaloStore2');
enableSMWHalo('SMWHaloStore2', 'SMWTripleStore', "http://mywiki/ob");

include_once('extensions/HaloACL/includes/HACL_Initialize.php');
$haclgProtectProperties = true;
$haclgNewUserTemplate = "ACL:Template/NewUserTemplate";
enableHaloACL(); 

//include_once('extensions/RichMedia/includes/RM_Initialize.php');
//enableRichMediaExtension();
    
$wgAllowExternalImagesFrom=$wgServer;   //This need to be set to allow the templates creating image links.

$smwgNamespacesWithSemanticLinks[300] = true;
$smwgNamespacesWithSemanticLinks[NS_USER_TALK] = true;


###Each extension wich depends on SMWHalo depends also on arclibrary, scriptmanager and deployment framework####
require_once('deployment/Deployment.php');
require_once("extensions/ScriptManager/SM_Initialize.php");
include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();
################################################################################################################
