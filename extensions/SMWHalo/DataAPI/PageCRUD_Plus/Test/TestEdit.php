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

require_once('../PCP.php');

$editTest = new PCP_Server();
$uc = new PCP_UserCredentials("tester1", "!>ontoprise?");
//if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	#$editTest->getEditToken("Main Page");
	#$editTest->getEditToken("Main Page");
	#global $wgUser;
	
	#var_dump($wgUser);
	#
	#$uc = new PCP_UserCredentials("WikiSysop", "!>ontoprise?");
	#$editTest->login($uc);
	#$__cookies = $editTest->getCookies();
	#print ("\nCookies set to...\n");
	#var_dump($__cookies);
	#$__uc = new PCP_UserCredentials($__cookies['UserName'],NULL, $__cookies['UserID']);
	$__uc = new PCP_UserCredentials("Tester1",NULL, 2);
	#print ("\nSending data...\n");
	#var_dump($__uc);
	#$editTest->setCookies($__cookies['Token'], $__uc);
	$editTest->setCookies("288f64ff3537d7d659766f3e45dcae68", $__uc);
	#var_dump($__cookies);
	#print ("Logged in as WikiSysop");
	#var_dump($wgUser);
	$editTest->getEditToken("Main Page");
	#	
	#$editTest->logout();
//}else{
//	print ("ERROR: Testing failed!".__FILE__);
//}

