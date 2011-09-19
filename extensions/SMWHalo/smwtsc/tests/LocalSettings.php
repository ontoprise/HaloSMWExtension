// set skin here again to overwrite the default
$wgDefaultSkin = 'ontoskin3';

$wgGroupPermissions['*']['ontologyediting']=true;
$wgGroupPermissions['user']['ontologyediting']=true;

#Import ARC2 library
require_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();

#Import SMW, SMWHalo and the Gardening extension
include_once('extensions/SemanticMediaWiki/SemanticMediaWiki.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo();
$smwgHaloTripleStoreGraph='http://publicbuild/ob';
$smwgHaloWebserviceEndpoint="localhost:8090";
$smwgHaloAutoCompletionTSC=true;
$smwgHaloNEPEnabled=true;


$wgParserCacheType = CACHE_NONE; //disable parse cache for query results cache tests

###Each extension wich depends on SMWHalo depends also on arclibrary, scriptmanager and deployment framework####
require_once('deployment/Deployment.php');
require_once("extensions/ScriptManager/SM_Initialize.php");
include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();
################################################################################################################

