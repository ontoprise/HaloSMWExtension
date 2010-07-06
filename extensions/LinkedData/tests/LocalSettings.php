/**
 * @file
 * @ingroup LinkedData_Tests
 */

$smwgDeployVersion = false;

//$smwgMessageBroker='localhost';
$smwgWebserviceEndpoint='localhost:8090';
$smwgEnableObjectLogicRules=true;
$smwgWebserviceProtocol="rest";

#Import SMW, SMWHalo and the Gardening extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2', "SMWTripleStore");

include_once('extensions/LinkedData/includes/LOD_Initialize.php');
enableLinkedData(); 

