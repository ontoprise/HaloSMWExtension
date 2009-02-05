<?
/**
 * In order to use the POM functions, the following line must be added to the 
 * LocalSettings.php file of the wiki system:<br/>
 * <i>
 * <code>
 * include_once ('extensions/PageObjectModel/POM.php');
 * </code>
 * </i>
 * <br/>
 * This is the path to the starting file of the package. Make sure the package 
 * exists under the path given.<br/>
 * In the directory <POM_HOME>/POM/Examples you can find examples how to use the package
 * or you can check the description of each class.
 *
 */
global $pomPREFIX, $pomWSServer;

require_once($pomPREFIX.'POM/Element.php');
require_once($pomPREFIX.'POM/DcbElement.php');
require_once($pomPREFIX.'POM/Page.php');
require_once($pomPREFIX.'Parsing/Parser.php');
require_once($pomPREFIX.'POM/Template.php');
require_once($pomPREFIX.'POM/TemplateParameter.php');

require_once($pomPREFIX.'POM/ParserFunction.php');
require_once($pomPREFIX.'POM/BuiltInParserFunction.php');
require_once($pomPREFIX.'POM/ExtensionParserFunction.php');
require_once($pomPREFIX.'POM/AskFunction.php');

require_once($pomPREFIX.'Parsing/ExtendedParser.php');
require_once($pomPREFIX.'POM/Annotation.php');
require_once($pomPREFIX.'POM/SimpleText.php');

require_once($pomPREFIX.'Util/Util.php');
require_once($pomPREFIX.'Util/UtilData.php');

if($pomWSServer){
	require_once($pomPREFIX.'WS/PageAPI.php');
}

