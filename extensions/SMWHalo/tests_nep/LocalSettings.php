/**
 * @file
 * @ingroup LinkedData_Tests
 */

$smwgDeployVersion = false;
$smwghConvertColoumns="utf8";


include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();

$smwgWebserviceEndpoint='localhost:8090';
$lodgNEPEnabled=true;

#Import SMW, SMWHalo and the Gardening extension
include_once('extensions/SemanticMediaWiki/SemanticMediaWiki.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo();


