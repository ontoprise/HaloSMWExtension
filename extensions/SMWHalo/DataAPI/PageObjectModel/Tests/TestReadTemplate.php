<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


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

