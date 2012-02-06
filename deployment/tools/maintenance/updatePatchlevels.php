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
 * Reads repository and updates patchlevels.
 *
 * Usage: php updatePatchlevels.php -o <repository path>
 *
 * @author: Kai K�hn
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once($rootDir."/descriptor/DF_DeployDescriptor.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");
require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");

$latest = false;
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
	//-o => output
	if ($arg == '-o') {
		$outputDir = next($argv);
		continue;
	}
}

if (!isset($outputDir)) {
	echo "\nSet output dir by using -o <directory>\n";
	die();
}


$outputDir = str_replace("\\", "/", $outputDir);
if (substr($outputDir, -1) != "/") $outputDir .= "/";

$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../../");
print($rootDir);
if (substr($rootDir, -1) != "/") $rootDir .= "/";

echo "\nRead local bundles";
$localPackages = PackageRepository::getLocalPackages($rootDir, true);

echo "\nRead existing repository from ".$outputDir."repository.xml";
$repository_xml = file_get_contents($outputDir."repository.xml");
$repoDoc = DOMDocument::loadXML($repository_xml);

$nodeList = $repoDoc->getElementsByTagName("extension");

for($i = 0; $i < $nodeList->length; $i++) {
	$extensionNode = $nodeList->item($i);
	$id = $extensionNode->getAttribute("id");
	$versionNode = $extensionNode->firstChild;
	
	if (array_key_exists($id, $localPackages)) {
		$dd = $localPackages[$id];
		$patchlevel = $dd->getPatchlevel();
		$versionNode->setAttribute("patchlevel", $patchlevel);
	}
	 
}

echo "\nWrite new repository to ".$outputDir."repository.xml";
$handle = fopen($outputDir."repository.xml", "w");
fwrite($handle, $repoDoc->saveXML());
fclose($handle);


