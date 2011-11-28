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
$uc = new PCP_UserCredentials("WikiSysop", "!>ontoprise?");
if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	print("\nMoving ...");
	$editTest->movePage("Anotehr test has moved again", "Anotehr test moves on", true, false);
	$editTest->logout();
}else{
	print ("ERROR: Testing failed!".__FILE__);
}

