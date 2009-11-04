<?php

/*
 * This class is based on the MediaWiki WebDAV extension
 * which is available at http://www.mediawiki.org/wiki/Extension:WebDAV
 */

# Initialise common code
chdir('./../../../../');
require_once("./includes/WebStart.php");
require_once( 'WD_WebDavServer.php' );

$server = new WebDavServer;
$server->handleRequest();

?>
