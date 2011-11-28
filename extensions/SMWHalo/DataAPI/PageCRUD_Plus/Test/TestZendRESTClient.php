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
