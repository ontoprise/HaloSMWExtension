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
 * @ingroup SemanticRulesMaintenance
 * 
 * @defgroup SemanticRulesMaintenance Semantic rules maintenance scripts
 * @ingroup SemanticRules
 * 
 * Setup database for SemanticRules extension.
 * @author: Kai K�hn
 *
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

if (!defined("SMW_HALO_VERSION")) {
	trigger_error("SMWHalo is required but not installed.");
	die();
}


$help = array_key_exists('h', $options);
$delete = array_key_exists('delete', $options);

if ($help) {
	echo "\nUsage: php setup.php [ --delete | -h ]\n";
	die();
}

if ($delete) {
	print "\Drop SemanticRules.\n\n";
	srfDropSRTables(true);
	die();
}

print "\nSetup SemanticRules.\n\n";
srfSetupSRTables(true);

/**
 * Create SemanticRules tables
 *
 * @param boolean $verbose
 */
function srfSetupSRTables($verbose) {
	global $tscgIP;
	require_once($tscgIP.'/includes/triplestore_client/TSC_RuleStore.php');
	SMWRuleStore::getInstance()->setup($verbose);
}

/**
 * Drop SemanticRules tables
 *
 * @param boolean $verbose
 */
function srfDropSRTables($verbose) {
	global $tscgIP;
    require_once($tscgIP.'/includes/triplestore_client/TSC_RuleStore.php');
	SMWRuleStore::getInstance()->drop($verbose);
}

