require_once('extensions/ScriptManager/SM_Initialize.php');

require_once( "$IP/extensions/ApplicationProgramming/ParserFunctions/ParserFunctions.php" );
require_once ("$IP/extensions/ApplicationProgramming/StringFunctions/StringFunctions.php");

#Import SMW, SMWHalo and the Webservice extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);

include_once('extensions/SemanticForms/includes/SF_Settings.php');
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2');

#RichMedia Extension
$wgAllowImageMoving = true;
$wgAllowExternalImages=true;
$smwgEnableUploadConverter = true;
include_once('extensions/RichMedia/includes/RM_Initialize.php');
enableRichMediaExtension();