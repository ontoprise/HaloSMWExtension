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
 * @ingroup SemanticGardeningMaintenance
 * 
 * @author Kai Kühn
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

// include bots
require_once( $mediaWikiLocation . '/extensions/SemanticGardening/includes/SGA_GardeningBot.php');
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

// import bots
sgagImportBots("$sgagIP/includes/bots");
require_once( $mediaWikiLocation . "/extensions/SemanticGardening/includes/SGA_GardeningLog.php");

require_once("$sgagIP/includes/SGA_PeriodicExecutors.php");

// read periodic jobs
$pe = SGAPeriodicExecutors::getPeriodicExecutors();
$bots = $pe->getBotsToRun();

if (count($bots) == 0) {
	print "\nNothing to do.\n";
}
// execute them
foreach($bots as $b) {
	list($id, $botid, $params, $lastrun, $interval) = $b;
	print "\n*Run $botid\n";
	GardeningBot::runBotNoAuth($botid, $params);
	$pe->updateLastRun($id);
}