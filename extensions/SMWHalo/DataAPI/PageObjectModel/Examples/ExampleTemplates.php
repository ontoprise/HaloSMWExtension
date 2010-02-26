<?php

/**
 * @file
  * @ingroup DAPOMExample
  * 
  * @author Dian
 */

/**
 * This group contains all parts of the DataAPI that provide examples for the POM component
 * @defgroup DAPOMExample
 * @ingroup DAPOM
 */

chdir('C:\xampp\htdocs\wiki');
require_once('C:/xampp/htdocs/wiki/includes/Webstart.php');

# Get document from MediaWiki server
$pom = new POMPage('Testeintrag', join(file('http://localhost/wiki/index.php?title=Testeintrag&action=raw')), array('POMExtendedParser'));

# iterate trough the templates
$iterator = $pom->getTemplateByTitle("Post")->listIterator();
while($iterator->hasNext()){
	$template = &$iterator->getNextNodeValueByReference(); # get reference for direct changes
	if($template->getParameter("PARAMETERNAME")!== NULL){
		# check if the parameter exists
		if ($template->getParameter("PARAMETERNAME")->getValue()->getElement() !==  NULL){ # a parameter value exists
			# get the first subelement of the page object representing the parameter value
			var_dump($template->getParameter("PARAMETERNAME"));
			$anElement = &$template->getParameter("PARAMETERNAME")->getValueByReference()->getElement();
			var_dump($anElement);
		}else{
			# add a new text element
			$parameterValue = &$template->getParameter("PARAMETERNAME")->getValueByReference();
			$simpleText = new POMSimpleText("A simple text element!");
			$parameterValue->addElement($simpleText);
			# show the changes			
			$pom->sync();
			print ($pom->text);
			print ("### Now changing the parameter value .. ###\n");
			$parameterValue = new POMPage("A-STRING", "");
			$simpleText = new POMSimpleText("String changed!");
			$parameterValue->addElement($simpleText);
		}
	}
}

$pom->sync();
print ($pom->text);

