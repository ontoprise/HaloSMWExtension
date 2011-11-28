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
 * Setup database for SMWHalo extension.
 * @file
 * @ingroup SMWHaloMaintenance
 *
 * @defgroup SMWHaloMaintenance SMWHalo maintenance scripts
 * @ingroup SMWHalo
 *
 * Usage: php SMW_refreshTSC.php [ --d=<sleep time> ]
 * 
 *   --d : a sleep time to wait until script terminates (in seconds)
 *   
 * @author: Kai Kuehn
 *
 * Created on: 11.10.2010
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}



$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

$delay = 5;

if (array_key_exists('d', $options)) {
	$delay = $options['d'];
}

print "\nSending init commands...";
smwfGetStore()->initialize(false);
print "done.";

print "\nChecking status...";
try {
	TSConnection::getConnector()->connect();
	$status = TSConnection::getConnector()->getStatus($smwgHaloTripleStoreGraph);
	print "\n";
	print "\nTSC version: ".$status['tscversion'];
	print "\ndriver info: ".$status['driverInfo'];
	print "\ninitialized: ".($status['isInitialized']? "true" : "false");
	print "\nfeatures: ".implode(", ",$status['features']);
	print "\n\n";
	print "The loading process may take some depending on the size of your wiki. Check the TSC log file for a LOADING DONE message.";
	print "\n\n";
	sleep($delay);
} catch(Exception $e) {
	print "Error occured: ".$e->getMessage();
}
