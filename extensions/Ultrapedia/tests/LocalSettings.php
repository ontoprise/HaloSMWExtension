$wgGroupPermissions['*']['gardening']=true;
$wgGroupPermissions['user']['gardening']=true;

#Import SMW, SMWHalo and the Webservice extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
require_once("$IP/extensions/ScriptManager/SM_Initialize.php");

require_once( "$IP/extensions/ApplicationProgramming/ParserFunctions/ParserFunctions.php" );

include_once('extensions/Ultrapedia/includes/UP_Initialize.php');