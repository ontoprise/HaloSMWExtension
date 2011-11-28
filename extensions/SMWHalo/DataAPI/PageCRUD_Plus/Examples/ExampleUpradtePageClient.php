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

require_once('.../extensions/DataAPI/PageCRUD_Plus/PCP.php');

// create the target system object
$smwSystem = new PCPWikiSystem("http://localhost/smw");

// create the user credentials object  
$uc = new PCPUserCredentials(
		"user", # the username
		"pass" # the password
		);

// create the PCP client object 
$updateTest = new PCPClient($uc, $smwSystem);

if ($updateTest->login()){	
	echo $updateTest->updatePage($uc, "Testpage", "Updating some content");
	$updateTest->logout();
}else{
	print ("ERROR: Testing failed!");
}

