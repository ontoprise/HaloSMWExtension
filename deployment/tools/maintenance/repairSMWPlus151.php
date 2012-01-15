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
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");

$mwRootDir = dirname(__FILE__);
$mwRootDir = str_replace("\\", "/", $mwRootDir);
$mwRootDir = realpath($mwRootDir."/../../..");

/**
 * There are some errors in the LocalSettings.php file which needs to get
 * repaired before an update takes place.
 *
 * @param string $ls
 */
function repairLocalSettings($ls) {
	$ls = str_replace("require_once('extensions/ScriptManager/SM_Initialize.php');", "/*start-scriptmanager*/\nrequire_once('extensions/ScriptManager/SM_Initialize.php');\n/*end-scriptmanager*/", $ls);
	$ls = str_replace("/*end-unifiedsearch*/", "", $ls);
	$ls = str_replace('$oaiAuditDatabase = \'semwiki_en\';', '$oaiAuditDatabase = \'semwiki_en\';'."\n/*end-unifiedsearch*/", $ls);
	$ls = str_replace("# SemanticFormsInputs", "", $ls);
	$ls = str_replace("require_once('extensions/SemanticFormsInputs/SemanticFormsInputs.php');", "", $ls);
	$ls = str_replace("/*start-semanticformsinputs*/","/*start-semanticformsinputs*/\nrequire_once('extensions/SemanticFormsInputs/SemanticFormsInputs.php');", $ls);

	$ls = str_replace("#Set default searching for document and pdf namespace", "", $ls);
	$startIndex = strpos($ls, "\$wgNamespacesToBeSearchedDefault");
	if ($startIndex !== false) {
		$i = 0;
		$endIndex = $startIndex;
		while ($i <= 4) {
			$endIndex = strpos($ls, "\n", $endIndex)+1;
			$i++;
		}
		$ls = substr($ls, 0, $startIndex).substr($ls, $endIndex);
		$ls = str_replace("/*end-richmedia*/","/*end-richmedia*/\n#Set default searching for document and pdf namespace\n\$wgNamespacesToBeSearchedDefault = array(NS_MAIN => true,NS_DOCUMENT => true,NS_PDF => true);", $ls);
	}
	return $ls;
}

/**
 * Remove LinkedData
 * @param $mw_root MW root dir
 */
function deleteLinkedData($mw_root) {
	Tools::remove_dir($mw_root."/extensions/LinkedData");
}

// question if user uses SMW+ 1.5.1
//print "\nDo you use SMW+ 1.5.1? Please answer 'y' to fix some known issues ";
//$line = trim(fgets(STDIN));
//if ($line != 'y') exit;
print "\nFixing some known issues on SMW+ 1.5.1...";

print "\nUpdate LocalSettings.php...";
$ls = file_get_contents($mwRootDir."/LocalSettings.php");
if ($ls !== false) {
	$ls = repairLocalSettings($ls);
	$handle = fopen($mwRootDir."/LocalSettings.php", "w");
	fwrite($handle, $ls);
	fclose($handle);
    print "done.";
} else {
	print "\n[FAILED] Could not read LocalSettings.php";
}
print "\nRemove LinkedData extension...";
deleteLinkedData($mwRootDir);
print "done.";

