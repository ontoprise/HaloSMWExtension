<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * This group contains all parts of the DataAPI that deal with the POM component
 * @defgroup DAPOM
 * @ingroup DataAPI
 */

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

include_once($pomPREFIX.'POM/Element.php');
include_once($pomPREFIX.'POM/DcbElement.php');
include_once($pomPREFIX.'POM/Page.php');
include_once($pomPREFIX.'Parsing/Parser.php');
include_once($pomPREFIX.'POM/Template.php');
include_once($pomPREFIX.'POM/TemplateParameter.php');

include_once($pomPREFIX.'POM/ParserFunction.php');
include_once($pomPREFIX.'POM/BuiltInParserFunction.php');
include_once($pomPREFIX.'POM/ExtensionParserFunction.php');
include_once($pomPREFIX.'POM/AskFunction.php');

include_once($pomPREFIX.'Parsing/ExtendedParser.php');
include_once($pomPREFIX.'POM/Annotation.php');
include_once($pomPREFIX.'POM/SimpleText.php');

include_once($pomPREFIX.'Util/Util.php');
include_once($pomPREFIX.'Util/UtilData.php');

if($pomWSServer){
	include_once($pomPREFIX.'WS/PageAPI.php');
}

