<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

require_once('Zend/Rest/Client.php');

$client = new Zend_Rest_Client('http://localhost/mw/api.php?action=wspcp&format=xml');

#$client->readPage("WikiSysop", NULL, NULL, NULL, NULL, "Main Page")->get();
$client->method("readPage");
$client->title("Main Page");
$result = $client->get();
var_dump($result->wspcp()->text);
 
//echo $client->createPage("WikiSysop", NULL, NULL, NULL, NULL, "REST Test", "Adding some content")->get()->getIterator()->asXML();


//$__obj = $client->readPage("WikiSysop", NULL, NULL, NULL, NULL, "Main Page")->get();
//$__res = $__obj->__toString();
//var_dump($client->readPage("WikiSysop", NULL, NULL, NULL, NULL, "Main Page")->get());
//echo $client->login("WikiSysop", "!>ontoprise?")->get(); 
//var_dump($client->sayHello("Tester", "now")->get());
