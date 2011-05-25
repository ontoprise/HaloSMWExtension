#Import ARCLibrary, SMW, SMWHalo
include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();

include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);

include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2', "SMWTripleStore");

$smwgWebserviceEndpoint='localhost:8090';
$smwgEnableObjectLogicRules=true;
$smwgWebserviceProtocol="rest";