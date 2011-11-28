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

/**
 * This group contains all parts of the DataAPI that deal with tests for the PCP component
 * @defgroup DAPCPTest
 * @ingroup DAPCP
 */

chdir('F:\xampp\htdocs\mw');
require_once('F:/xampp/htdocs/mw/includes/Webstart.php');

$editTest = new PCPServer();
$uc = new PCPUserCredentials("WikiSysop", "", 1, "93f49f1cb77a00ae03b3eb90069d76dd");
//$editTest->login($uc);
//if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	#$editTest->getEditToken("Main Page");
	var_dump($editTest->createPage($uc, "Another test", "Adding some content"));
	$editTest->logout();
//}else{
//	print ("ERROR: Testing failed!".__FILE__);
//}

