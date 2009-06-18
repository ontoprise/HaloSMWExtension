$smwgMessageBroker='localhost';
$smwgWebserviceEndpoint='localhost:8060';


#Import SMW, SMWHalo and the Gardening extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://mywiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
//enableSMWHalo('SMWHaloStore2');
enableSMWHalo('SMWHaloStore2', 'SMWTripleStore');

include_once('extensions/HaloACL/includes/HACL_Initialize.php');
enableHaloACL(); 

