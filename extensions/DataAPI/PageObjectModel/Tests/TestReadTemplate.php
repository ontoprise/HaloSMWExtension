<?php
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
if (POMUrlUtil::url_exists('http://localhost/mw/index.php?title=Template:Diplomarbeit=raw')){
	$pom = new POMPage('Template:Diplomarbeit', join(file('http://localhost/mw/index.php?title=Template:Diplomarbeit&action=raw')), array('POMExtendedParser'));
}else{
	print ("not existing\n");
}

# iterate trough the templates
$iterator = $pom->getTemplateByTitle("Diplomarbeit")->listIterator();
while($iterator->hasNext()){
	$template = &$iterator->getNextNodeValueByReference(); # get reference for direct changes
	//	var_dump($template);
	if($template->getParameter("hat Beschreibung")!== NULL){
		# check if the parameter exists
		if ($template->getParameter("hat Beschreibung")->getValue()->getElement() !==  NULL){ # a parameter value exists
			# get the first subelement of the page object representing the parameter value
			//			var_dump($template->getParameter("hat Beschreibung"));
			$anElement = &$template->getParameter("hat Beschreibung")->getValueByReference()->getElement();
			var_dump($template->getParameter("hat Beschreibung")->getValueByReference()->getElement());
		}
	}
}

$pom->sync();
print ($pom->text);

?>