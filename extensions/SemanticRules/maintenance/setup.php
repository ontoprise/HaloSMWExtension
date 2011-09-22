<?php
/**
 * @file
 * @ingroup SemanticRulesMaintenance
 * 
 * @defgroup SemanticRulesMaintenance Semantic rules maintenance scripts
 * @ingroup SemanticRules
 * 
 * Setup database for SemanticRules extension.
 * @author: Kai K�hn / ontoprise / 2009
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

