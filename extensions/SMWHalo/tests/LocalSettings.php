// set skin here again to overwrite the default
$wgDefaultSkin = 'ontoskin3';

$wgGroupPermissions['*']['ontologyediting']=true;
$wgGroupPermissions['user']['ontologyediting']=true;

#Import ARC2 library
require_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();

#Import SMW, SMWHalo and the Gardening extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2', 'SMWTripleStore', 'http://publicbuild/ob');
$smwgWebserviceEndpoint="localhost:8090";
$smwhgAutoCompletionTSC=true;
//enableQueryResultsCache();

$wgParserCacheType = CACHE_NONE; //disable parse cache for query results cache tests

#For DataAPI tests
include_once('extensions/SemanticForms/includes/SF_Settings.php');
$wgEnableWriteAPI = true;
$pcpWSServer=true;
include_once('extensions/SMWHalo/DataAPI/PageCRUD_Plus/PCP.php');
$pomWSServer=true;
include_once('extensions/SMWHalo/DataAPI/PageObjectModel/POM.php');
include_once('extensions/SMWHalo/DataAPI/SemanticFormsAPI/SemanticFormsAPI.php');

