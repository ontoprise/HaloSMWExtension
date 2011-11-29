#Import ARCLibrary, SMW, SMWHalo
include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();

include_once('extensions/SemanticMediaWiki/SemanticMediaWiki.php');
enableSemantics('http://wiki', true);

include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2', "SMWTripleStore");

$smwgHaloWebserviceEndpoint='localhost:8090';
$smwgHaloEnableObjectLogicRules=true;
$smwgWebserviceProtocol="rest";

###Each extension wich depends on SMWHalo depends also on arclibrary, scriptmanager and deployment framework####
require_once('deployment/Deployment.php');
require_once("extensions/ScriptManager/SM_Initialize.php");
include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();
################################################################################################################