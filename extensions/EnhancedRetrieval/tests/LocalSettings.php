
#Import SMW
include_once('extensions/SemanticMediaWiki/SemanticMediaWiki.php');
enableSemantics('http://wiki', true);

# Import SMWHalo
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo();


## Shared memory settings
$wgMainCacheType = CACHE_MEMCACHED;
$wgMemCachedServers = array('localhost:11211');

include_once('extensions/HaloACL/includes/HACL_Initialize.php');
enableHaloACL();

#EnhancedRetrieval
$wgSearchType = 'LuceneSearch';
$wgLuceneHost = 'localhost';
$wgLucenePort = 8123;
$wgLuceneSearchVersion = 2.1;
$wgUSPathSearch=true;
$wgLuceneSearchTimeout=5;
require_once('extensions/EnhancedRetrieval/includes/EnhancedRetrieval.php');

###Each extension wich depends on SMWHalo depends also on arclibrary, scriptmanager and deployment framework####
require_once('deployment/Deployment.php');
require_once("extensions/ScriptManager/SM_Initialize.php");
include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();
################################################################################################################
