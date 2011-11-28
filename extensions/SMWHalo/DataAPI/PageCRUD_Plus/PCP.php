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
  * @ingroup DAPCP
  * This file is the starting point for using the PageCRUD_Plus functions.
 *
 * @author Dian
 * @version 0.1
 */
global $pcpPREFIX, $pcpWSServer;
require_once($pcpPREFIX.'Config/UserCredentials.php');
include_once($pcpPREFIX.'Config/WikiSystem.php');
include_once($pcpPREFIX.'PCP/Page.php');
include_once($pcpPREFIX.'PCP/Any.php');
include_once($pcpPREFIX.'PCP/Server.php');
include_once($pcpPREFIX.'PCP/Client.php');
include_once($pcpPREFIX.'Util/Util.php');

if($pcpWSServer){
	#include_once('WS/Server.php');
	include_once($pcpPREFIX.'WS/ServerAPI.php');
}



/**
 * In order to use the PCP functions, the following line must be added to the
 * LocalSettings.php file of the wiki system:<br/>
 * <i>
 * include_once ('extensions/PageCRUD_Plus/PCP.php');
 * </i>
 * <br/>
 * This is the path to the starting file of the package. Make sure the package
 * exists under the path given.<br/>
 * In the directory <PCP_HOME>/PCP/Examples you can find examples how to use the package.
 *
 */
class PCP{
	var $conf = NULL;

	public function __construct($configuration=NULL){
		$this->conf = $configuration;
	}
}

