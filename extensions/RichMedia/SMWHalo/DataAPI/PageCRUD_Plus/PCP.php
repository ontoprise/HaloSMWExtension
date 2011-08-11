<?php

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

