
$wgGroupPermissions['*']['gardening']=true;
$wgGroupPermissions['user']['gardening']=true;

#Import SMW, SMWHalo and the Gardening extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2');

include_once('extensions/SemanticGardening/includes/SGA_GardeningInitialize.php');