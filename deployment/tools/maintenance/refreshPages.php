<?php

/*  Copyright 2011, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DFMaintenance
 * 
 * Refreshes pages contained in a dump file.
 *
 * Usage: php refreshPages -d <dump file> -b <bundle ID>
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */

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

$handle = fopen( $dumpFilePath, 'rt' );
$source = new ImportStreamSource( $handle );
$importer = new DeployWikiImporterDetector( $source, $bundleID);

$importer->setDebug( false );

$importer->doImport();

$pageTitles = $importer->getResult();


// refresh imported pages
$logger = Logger::getInstance();

$logger->info("Refreshing ontology: $dumpFilePath");
print "\n[Refreshing ontology: $dumpFilePath. Total number of pages: ".count($pageTitles);

$i = 0;
foreach($pageTitles as $tuple) {
	list($t, $status) = $tuple;

	$i++;
	if ($t->getNamespace() == NS_FILE) continue;
	
	smwfGetStore()->refreshData($t->getArticleId(), 1, false, false);
	$logger->info("($i)". $t->getPrefixedText()." refreshed.");
	print "\n\t[($i)".$t->getPrefixedText()." refreshed]";
}