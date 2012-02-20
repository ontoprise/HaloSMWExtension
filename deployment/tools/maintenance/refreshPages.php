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
 * @ingroup DFMaintenance
 *
 * Refreshes pages contained in a dump file. It also refreshes pages which
 * uses properties that are newly imported or overwritten. This is necessary
 * in case the type changes.
 *
 * Usage: php refreshPages -d <dump file> -b <bundle ID>
 *
 * @author: Kai Kühn
 *
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once( $rootDir.'/../maintenance/commandLine.inc' );
require_once( $rootDir.'/../maintenance/backup.inc' );
require_once($rootDir."/descriptor/DF_DeployDescriptor.php");
require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");
require_once($rootDir."/tools/smwadmin/DF_UserInput.php");
require_once($rootDir.'/io/import/DF_DeployWikiBundleImporter.php');
require_once($rootDir.'/io/import/DF_OntologyDetector.php');
require_once($rootDir.'/io/DF_Log.php');

global $wgLanguageCode, $dfgLang;
$langClass = "DF_Language_$wgLanguageCode";
if (!file_exists("$rootDir/languages/$langClass.php")) {
	$langClass = "DF_Language_En";
}
//Load Settings
if(file_exists($rootDir.'/settings.php'))
{
	require_once($rootDir.'/settings.php');
}
require_once("$rootDir/languages/$langClass.php");
$dfgLang = new $langClass();

for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-d => repository directory
	if ($arg == '-d') {
		$dumpFilePath = next($argv);
		continue;
	}

	//-b => bundleID
	if ($arg == '-b') {
		$bundleID = next($argv);
		continue;
	}

}

if (!isset($dumpFilePath) || !isset($bundleID)) {
	echo "\nUsage: php refreshPages.php -d <dump-dir> -b <bundle-ID>\n";
	die(1);
}

// get pages from bundle
$handle = fopen( $dumpFilePath, 'rt' );
$source = new ImportStreamSource( $handle );
$importer = new DeployWikiImporterDetector( $source, $bundleID);
$importer->setDebug( false );
$importer->doImport();
$pageTitles = $importer->getResult();

// get pages which use the imported properties
$subjectToRefresh = array();
foreach($pageTitles as $tuple) {
	list($t, $status) = $tuple;
	if ($t->getNamespace() == SMW_NS_PROPERTY) {
		$subjects = smwfGetStore()->getAllPropertySubjects(SMWDIProperty::newFromUserLabel($t->getText()));
		foreach($subjects as $s) {
			$subjectToRefresh[] = $s->getTitle();
		}
	}
}
$subjectToRefresh = dffMakeTitleListUnique($subjectToRefresh);

// refresh imported pages
$logger = Logger::getInstance();
$logger->info("Refreshing pages: $dumpFilePath");
print "\n[Refreshing pages: $dumpFilePath. Total number of pages: ".count($pageTitles);

$i = 0;
foreach($pageTitles as $tuple) {
	list($t, $status) = $tuple;

	$i++;
	if ($t->getNamespace() == NS_FILE) continue;

	smwfGetStore()->refreshData($t->getArticleId(), 1, false, false);
	$logger->info("($i) ". $t->getPrefixedText()." refreshed.");
	print "\n\t[ ($i) ".$t->getPrefixedText()." refreshed]";
}
print "\ndone.]";

// refresh other pages which use the imported properties
$logger->info("Refreshing existing pages");
print "\n[Refreshing existing pages. Total number of pages: ".count($subjectToRefresh);

$i = 0;
foreach($subjectToRefresh as $t) {
	$i++;

	smwfGetStore()->refreshData($t->getArticleId(), 1, false, false);
	$logger->info("($i) ". $t->getPrefixedText()." refreshed.");
	print "\n\t[ ($i) ".$t->getPrefixedText()." refreshed]";
}
print "\ndone.]";

// update TSC (if configured)
if (defined('SMW_HALO_VERSION') && smwfIsTripleStoreConfigured()) {
	if (isset(DF_Config::$df_refresh_TSC) && DF_Config::$df_refresh_TSC === true) {
		print "\nSending sync commands to TSC...";
		smwfGetStore()->initialize(false, true);
		print "\nIt may take some time for the TSC to re-sync, check Special:TSA. It depends on the size of your wiki.";
	}
}


function dffMakeTitleListUnique($titles) {
	usort($titles, "dffCompareTitles");

	$result = array();
	$last = reset($titles);
	if ($last !== false) $result[] = $last;
	for($i = 1, $n = count($titles); $i < $n; $i++ ) {
		if ($titles[$i]->getPrefixedText() == $last->getPrefixedText()) {
			$titles[$i] = NULL;
			continue;
		}
		$last = $titles[$i];
		$result[] = $titles[$i];
	}

	return $result;
}

/* callback methods */
function dffCompareTitles($a, $b) {
	return strcmp($a->getPrefixedText(), $b->getPrefixedText());
}