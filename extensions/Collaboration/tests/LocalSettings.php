
#Application Programming
require_once( 'extensions/ApplicationProgramming/ParserFunctions/ParserFunctions.php' );
require_once( 'extensions/ApplicationProgramming/StringFunctions/StringFunctions.php' );
require_once( 'extensions/ApplicationProgramming/Variables/Variables.php' );

#Import SMW
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);

#Collaboration
include_once('extensions/Collaboration/includes/CE_Initialize.php');
enableCollaboration();
