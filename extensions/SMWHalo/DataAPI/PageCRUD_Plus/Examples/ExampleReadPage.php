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
  * @ingroup DAPCPExample
  *
  * @author Dian
 */

// the path to your wiki installation home
chdir('/xampp/htdocs/wiki');
require_once('/xampp/htdocs/wiki/includes/Webstart.php');

$readTest = new PCPServer();
$uc = new PCPUserCredentials("TestUser", "TestPassword");
if ($readTest->login($uc)){	
	echo $readTest->readPage($uc,"Main Page");
	$readTest->logout();
}else{
	print ("ERROR: Testing failed!");
}

