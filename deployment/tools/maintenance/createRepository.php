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
 * Creates a DF repository from the SVN version. Must be done once a new release available
 *
 * Usage:   php createRepsoitory.php -o <repository path>  -r release-num
 *          php createRepsoitory.php -o <repository path>  --head [ --empty ]
 *
 * @author: Kai Kühn
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once($rootDir."/descriptor/DF_DeployDescriptor.php");
require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");


$latest = false;
$emptyRepo = false;
$latestReleaseAttribute="";
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-o => output
	if ($arg == '-o') {
		$outputDir = next($argv);
		if (substr($outputDir,-1)!='/'){
			$outputDir .= '/';
		}
		continue;
	}

	//-r => release num
	if ($arg == '-r') {
		$release = str_replace('.','',next($argv));
		continue;
	}

	//-head => release num
	if ($arg == '--head') {
		$head = true;
		continue;
	}

	if ($arg == '--latest') {
		$latest = true;
		continue;
	}
	
	if ($arg == '--latestrelease') {
		$releasenum = next($argv);
		$latestReleaseAttribute = "latestrelease=\"$releasenum\"";
		continue;
	}

	if ($arg == '--empty') {
		$emptyRepo = true;
		continue;
	}

	if ($arg == '--fixedpatchlevel') {
		$fixedpatchlevel = next($argv);
		continue;
	}
}

if (!isset($outputDir)) {
	echo "\nSet output dir by using -o <directory>\n";
	die();
}
if (!isset($release) && !isset($head)) {
	echo "\nSet release by using -r <releasenum> or use --head\n";
	die();
}

$outputDir = str_replace("\\", "/", $outputDir);
if (substr($outputDir, -1) != "/") $outputDir .= "/";
if (!file_exists($outputDir)) Tools::mkpath($outputDir);

$mwRootDir = dirname(__FILE__);
$mwRootDir = str_replace("\\", "/", $mwRootDir);
$mwRootDir = realpath($mwRootDir."/../../../");
print($mwRootDir);
if (substr($mwRootDir, -1) != "/") $mwRootDir .= "/";

echo "\nRead local bundles";
$localPackages = isset($emptyRepo) && $emptyRepo == true ? array() : PackageRepository::getLocalPackages($mwRootDir);

echo "\nCreate new repository ".$outputDir."repository.xml";


$new_ser = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="repository.xsl"?>'."<root version=\"".DEPLOY_FRAMEWORK_REPOSITORY_VERSION."\" $latestReleaseAttribute>\n<extensions>\n";
foreach($localPackages as $lp) {

	$oldPatchlevel = $lp->getPatchlevel();
	// set fixed patchlevel if necessary
	if (isset($fixedpatchlevel)) {
		$lp = Tools::changeGlobalSection($lp, "patchlevel", $fixedpatchlevel);
	}
	$id = $lp->getID();
	if ($id == 'mw') continue; // special handling for mw
	$title = Tools::escapeForXMLAttribute($lp->getTitle());
	$installdir = $lp->getInstallationDirectory();
	$new_ser .= "<extension id=\"$id\" title=\"$title\">";
	$branch = isset($head) ? "smwhalo" : "smwhalo_".$release."_release";
	$url = "http://dailywikibuilds.ontoprise.com:8080/job/$branch/lastSuccessfulBuild/artifact/SMWHaloTrunk/$installdir/deploy/bin/$id-".$lp->getVersion()->toVersionString()."_".$oldPatchlevel.".zip";
	$ver = $lp->getVersion()->toVersionString();
	$newPatchlevel = $lp->getPatchlevel();
	if ($newPatchlevel == '') $newPatchlevel = 0;
	$maintainer = Tools::escapeForXMLAttribute($lp->getMaintainer());
	$helpurl = Tools::escapeForXMLAttribute($lp->getHelpURL());
	$description = Tools::escapeForXMLAttribute($lp->getDescription());

	$verWithoutDots = str_replace(".","",$ver); // for compatibility to old version numbers
	$new_ser .= "<version ver=\"$verWithoutDots\" version=\"$ver\" url=\"$url\" patchlevel=\"$newPatchlevel\" maintainer=\"$maintainer\" description=\"$description\" helpurl=\"$helpurl\"/>";

	$new_ser .= "</extension>\n";
}
$new_ser .= "\n</extensions>\n</root>";

echo "\nWrite new repository to ".$outputDir."repository.xml";
$handle = fopen($outputDir."repository.xml", "w");
fwrite($handle, $new_ser);
fclose($handle);

echo "\nWriting deploy descriptors...";

// create symlinks for Linux and Windows 7
$createSymlinks=true;
if (Tools::isWindows($os) && $latest) {

	$createSymlinks = ($os == 'Windows 7');
	if (!$createSymlinks) {
		echo "Be careful: Cannot create symbolic links on Windows <= 7!";
	}
}

$outputDir = str_replace("\\", "/", $outputDir);
if (substr($outputDir, -1) != "/") $outputDir .= "/";
$outputDir .= "extensions/";

$rootDir = realpath(dirname(__FILE__)."/../../../");
$rootDir = str_replace("\\", "/", $rootDir);
if (substr($rootDir, -1) != "/") $rootDir .= "/";

// create substructure with deploy descriptors
$localPackages = isset($emptyRepo) && $emptyRepo == true ? array() : PackageRepository::getLocalPackages($rootDir);
foreach($localPackages as $dd_file => $dd) {
	if (isset($fixedpatchlevel)) {
		$dd = Tools::changeGlobalSection($dd, "patchlevel", $fixedpatchlevel);
	}
	$id = $dd->getID();
	if ($id == 'mw') continue;
	$instdir = $dd->getInstallationDirectory();
	createEntry($dd, $rootDir."/$instdir/deploy.xml", $outputDir, $latest, $createSymlinks);
}

print "\nDONE.\n\n";

/**
 * Creates a DD entry for a deployed entity.
 *
 * @param DeployDescriptor $dd
 * @param string $dd_file Full path of deploy.xml
 * @param string $outputDir Output directory
 * @param boolean $latest
 * @param boolean $createSymlinks
 */
function createEntry($dd, $dd_file, $outputDir, $latest, $createSymlinks) {
	$version = $dd->getVersion()->toVersionString();
	$targetFile = str_replace("deploy.xml", "deploy-".$version.".xml", $dd_file);
	Tools::mkpath($outputDir.$dd->getID());

	$xml = $dd->getXML();
	$handle = fopen($outputDir.$dd->getID()."/deploy-".$version.".xml", "w");
	fwrite($handle, $xml);
	fclose($handle);
	$handle = fopen($outputDir.$dd->getID()."/deploy-".str_replace(".","",$version).".xml", "w");
	fwrite($handle, $xml);
	fclose($handle);
	 
	print "\nCreated: $outputDir$targetFile";

	// creates links
	$id = $dd->getID();
	$version = $dd->getVersion()->toVersionString();
	if ($createSymlinks && $latest) {
		// remove symbolic link if existing
		if (file_exists($outputDir."/$id/deploy.xml")) {
			unlink($outputDir."/$id/deploy.xml");
		}
		// create symbolic link
		if (Tools::isWindows()) {
			$target = str_replace("/", "\\", "$outputDir/$id/deploy-$version.xml");
			$link = str_replace("/", "\\", "$outputDir/$id/deploy.xml");
			exec("mklink \"$link\" \"$target\"", $out, $res);
		} else{
			exec("ln -s $outputDir/$id/deploy-$version.xml $outputDir/$id/deploy.xml", $out, $res);
		}
		if ($res == 0) print "\nCreated link: $outputDir/".$id.'/deploy.xml';
	}

}


