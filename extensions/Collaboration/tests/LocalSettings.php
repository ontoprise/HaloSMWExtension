require_once('extensions/ScriptManager/SM_Initialize.php');

#Application Programming
require_once( 'extensions/ApplicationProgramming/ParserFunctions/ParserFunctions.php' );
require_once( 'extensions/ApplicationProgramming/StringFunctions/StringFunctions.php' );
require_once( 'extensions/ApplicationProgramming/Variables/Variables.php' );

#Import SMW and SMWHalo
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);

include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2');

#Collaboration
include_once('extensions/Collaboration/includes/CE_Initialize.php');
enableCollaboration();
