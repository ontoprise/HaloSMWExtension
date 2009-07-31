$wgDefaultSkin = 'ontoskin2';

#Import SMW, SMWHalo and the Webservice extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2');

#RichMedia Extension
$wgAllowImageMoving = true;
$smwgEnableUploadConverter = true;
include_once('extensions/RichMedia/includes/RM_Initialize.php');
enableRichMediaExtension();