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
 * @ingroup LOD_Maintenance
 */
/**
 * Maintenance script for updating registered data sources.
 * 
 * @author Kai Kuehn
 * Date: 05.07.2010
 * 
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$dir = dirname(__FILE__);
$lodgIP = "$dir/../../LinkedData";

if (!defined('LOD_LINKEDDATA_VERSION')) {
      echo "\nPlease configure LinkData extension before executing this script.\n";
    return;
}

require_once("$lodgIP/includes/LOD_Storage.php");
require_once("$lodgIP/includes/LOD_GlobalFunctions.php");
require_once("$smwgHaloIP/includes/storage/SMW_RESTWebserviceConnector.php");

global $smwgHaloWebserviceEndpoint;

$update = "false";
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

    //-update => mode
    if ($arg == '-update') {
        $update = "true";
        continue;
    }
}

$ids = TSCAdministrationStore::getInstance()->getAllSourceDefinitionIDs();
list($host, $port) = explode(":", $smwgHaloWebserviceEndpoint);

//TODO: add credentials
$con = new RESTWebserviceConnector($host, $port, "ldimporter");

foreach($ids as $id) {
    $sd = TSCAdministrationStore::getInstance()->loadSourceDefinition($id);
    print ($update ? "Updating " : "Importing ") . $sd->getLabel() . " [$id] ... ";
    $payload = "dataSourceId=$id&update=$update";
    list($header, $status, $res) = $con->send($payload, "/runImport");
    print "done.\n";
    print "Status: $status\n";
    print "Result: $res\n\n";
}
