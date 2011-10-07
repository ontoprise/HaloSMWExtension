
#Import SMW
include_once('extensions/SemanticMediaWiki/SemanticMediaWiki.php');
enableSemantics('http://wiki', true);

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
