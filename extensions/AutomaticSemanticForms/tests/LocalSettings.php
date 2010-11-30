
#Import SMW, SMWHalo
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2');

#SF
include_once('extensions/SemanticForms/includes/SF_Settings.php');

#ASF
include_once('extensions/AutomaticSemanticForms/includes/ASF_Initialize.php');
enableAutomaticSemanticForms();
