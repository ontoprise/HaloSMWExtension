#Import SMW, SMWHalo and the Webservice extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2');

$pcpWSServer=true;
include_once('extensions/DataAPI/PageCRUD_Plus/PCP.php');

$pomWSServer=true;
include_once('extensions/DataAPI/PageObjectModel/POM.php');

include_once('extensions/SemanticForms/includes/SF_Settings.php');

