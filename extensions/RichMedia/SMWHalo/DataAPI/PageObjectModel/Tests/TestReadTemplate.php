<?php

/**
 * @file
  * @ingroup DAPOMTest
  * 
  * @author Dian
 */

/**
 * This group contains all parts of the DataAPI that deal with tests for the POM component
 * @defgroup DAPOMTest
 * @ingroup DAPOM
 */

//$__curdir = getcwd();
//chdir('C:/xampp/htdocs/mw');
//require_once('C:/xampp/htdocs/mw/includes/Webstart.php');
//chdir($__curdir);
global $pcpPREFIX, $pcpWSServer;
$pcpPREFIX = 'C:\xampp\htdocs\mw\extensions\PageCRUD_Plus'."\\";
$pcpWSServer = false;
require_once 'C:\xampp\htdocs\mw\extensions\PageCRUD_Plus\PCP.php';
require_once 'C:\xampp\htdocs\mw\extensions\PageObjectModel\POM.php';
# Get document from MediaWiki server
$__pageTitle = "Template:Diplomarbeitsthema";
$__pageTitle = "Mobile_Location_Display_Advertising";
$__pageTitle = "Testpage";

if (POMUrlUtil::url_exists('http://localhost/mw/index.php?title='.$__pageTitle.'&action=raw')){
	$pom = new POMPage($__pageTitle, join(file('http://localhost/mw/index.php?title='.$__pageTitle.'&action=raw')), array('POMExtendedParser'));
}else{
	print ("not existing\n");
}

# iterate trough the templates
//$iterator = $pom->getTemplateByTitle("Diplomarbeit")->listIterator();
//while($iterator->hasNext()){
//	$template = &$iterator->getNextNodeValueByReference(); # get reference for direct changes
//	//	var_dump($template);
//	if($template->getParameter("hat Beschreibung")!== NULL){
//		# check if the parameter exists
//		if ($template->getParameter("hat Beschreibung")->getValue()->getElement() !==  NULL){ # a parameter value exists
//			# get the first subelement of the page object representing the parameter value
//			//			var_dump($template->getParameter("hat Beschreibung"));
//			$anElement = &$template->getParameter("hat Beschreibung")->getValueByReference()->getElement();
//			var_dump($template->getParameter("hat Beschreibung")->getValueByReference()->getElement());
//		}
//	}
//}

$__property = POMProperty::createProperty("has Test","Test"," ");
		$pom->addElement($__property);
		

$pom->sync();
var_dump($pom->getPropertyByName(NULL)->listIterator()->getNextNodeValueByReference());
//print ($pom->text);

