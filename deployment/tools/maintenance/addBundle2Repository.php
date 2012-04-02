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
 * Adds a bundle or a set of bundles to a repository.
 *
 * Usage: php addBundle2Repository.php
 *                  -r <repository-dir>         The repository root directory
 *                  -b <bundle file or dir>     The bundle file or a directory containing bundle files
 *                  --list <bundle-list>        Selects bundles from a bundle list 
 *                  --url <repository-url>      The download base URL
 *                  [--latest]                  Latest version?
 *                  [--mediawiki]               Include Mediawiki?
 *                  [--mwversion]               Mediawiki version (if missing it is read from the underlying installation)
 *                  [--contains <substring> ]   File name contains a substring
 *                  [--transient <ID> ]			Creates a transient bundle entry with the given ID.
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

$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");

require_once($rootDir."/descriptor/DF_DeployDescriptor.php");
require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");


$latest = false;
$createSymlinks = true;
$fileNamecontains = false;
$transientID=false;
$recursive=false;
$mediawiki = false;
$bundleListFilepath=false;
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-r => repository directory
	if ($arg == '-r') {
		$repositoryDir = next($argv);
		continue;
	}

	// -b => bundle file or directory containing bundles
	if ($arg == '-b') {
		$bundlePath = next($argv);
		continue;
	}

	// --list => bundle list
	if ($arg == '--list') {
		$bundleListFilepath = next($argv);
		continue;
	}

	// --url => base URL for downloads
	if ($arg == '--url') {
		$repositoryURL = next($argv);
		continue;
	}

	if ($arg == '--latest') {
		$latest = true;
		continue;
	}
	if ($arg == '--mediawiki') {
		$mediawiki = true;
		continue;
	}

	if ($arg == '--mwversion') {
		$mwversion =next($argv);
		continue;
	}

	if ($arg == '--contains') {
		$fileNamecontains = next($argv);
		continue;
	}

	if ($arg == '--transient') {
		$transientID = next($argv);
		continue;
	}

	if ($arg == '--recursive') {
		$recursive = true;
		continue;
	}

	if ($arg == '--fixedpatchlevel') {
		$fixedpatchlevel = next($argv);
		continue;
	}
}


if (!isset($repositoryDir) || (!(isset($bundlePath) || isset($mediawiki)))) {
	echo "\nUsage: php addBundle2Repository.php -r <repository-dir> -b <bundle file or dir> --url <repository-url>\n";
	echo "\n       php addBundle2Repository.php -r <repository-dir> -b <bundle file or dir> --list <bundle list file> [--url <repository-url>]\n";
	die(1);
}

// create symlinks for Linux and Windows 7
if (Tools::isWindows($os) && $latest) {
	$createSymlinks = ($os == 'Windows 7');
	if (!$createSymlinks) {
		echo "Be careful: Cannot create symbolic links on Windows <= 7!";
	}
}

// create binary path
Tools::mkpath($repositoryDir."/bin");

$descriptors=array();
// readbundleList
if ($bundleListFilepath !== false && isset($bundlePath)) {
	echo "\nRead from a bundle list...";
	echo "\n$bundleListFilepath";
	list($url, $bundleList) = getBundleList($bundleListFilepath);
	if ($url != "" && !isset($repositoryURL)) {
		$repositoryURL = $url;
	}
	foreach($bundleList as $b) {
		echo "\nSearching deploy descriptor for $b...";
		$dd = extractDeployDescriptors($bundlePath, $b, false);
		if (count($dd) > 0) {
			echo "found!";
			$descriptors = array_merge($dd, $descriptors);
		} else {
			echo "NOT found!";
		}
	}
	echo "...done.";

} else {

	// read bundles and extract the deploy descriptors
	$descriptors = array();
	if (isset($bundlePath)) {
		echo "\nExtract deploy descriptors";
		$descriptors = extractDeployDescriptors($bundlePath, $fileNamecontains, $recursive);
		echo "..done.";

	}
}

if (!isset($repositoryURL)) {
	echo "\nERROR: No repository URL given.";
    die(0);
}

if (count($descriptors) == 0) {
	echo "\nWARNING: No bundles found in '$bundlePath'. Nothing to do.";
	die(0);
}

// load existing repository
echo "\nLoading repository...";
$repoDoc = loadRepository($repositoryDir."/repository.xml");
echo "..done.";

$nodeList = $repoDoc->getElementsByTagName("extensions");
$extensionsNode = $nodeList->item(0);

foreach($descriptors as $tuple) {
	list($dd, $zipFilepath) = $tuple;

	// set fixed patchlevel if necessary
	if (isset($fixedpatchlevel)) {
		$dd = Tools::changeGlobalSection($dd, "patchlevel", $fixedpatchlevel);
	}

	// 1. create extensions substructure
	$id = $dd->getID();
	$version = $dd->getVersion()->toVersionString();
	$versionNoDots = str_replace(".","", $version);

	echo "\nCreate extension entry for $id";
	Tools::mkpath($repositoryDir."/extensions/$id");

	@unlink($repositoryDir."/extensions/$id/deploy-$version.xml");
	@unlink($repositoryDir."/extensions/$id/deploy-$versionNoDots.xml");
	@unlink($repositoryDir."/extensions/$id/deploy.xml");

	// write deploy descriptor
	$xml = $dd->getXML();
	$handle = fopen($repositoryDir."/extensions/$id/deploy.xml", "w");
	fwrite($handle, $xml);
	fclose($handle);

	copy($repositoryDir."/extensions/$id/deploy.xml", $repositoryDir."/extensions/$id/deploy-$version.xml");
	// compatibility fix to DF 1.56 (to be removed in future versions)
	copy($repositoryDir."/extensions/$id/deploy.xml", $repositoryDir."/extensions/$id/deploy-$versionNoDots.xml");
	@unlink($repositoryDir."/extensions/$id/deploy.xml");

	if ($createSymlinks && $latest) {
		// remove symbolic link if existing
		if (file_exists($repositoryDir."/$id/deploy.xml")) {
			unlink($repositoryDir."/$id/deploy.xml");
		}
		// create symbolic link
		if (Tools::isWindows()) {
			$target = str_replace("/", "\\", "$repositoryDir/extensions/$id/deploy-$version.xml");
			$link = str_replace("/", "\\", "$repositoryDir/extensions/$id/deploy.xml");
			exec("mklink \"$link\" \"$target\"", $out, $res);
		} else{
			exec("ln -s $repositoryDir/extensions/$id/deploy-$version.xml $repositoryDir/extensions/$id/deploy.xml", $out, $res);
		}
		if ($res == 0) print "\n\tCreated link: ".$repositoryDir."/extensions/".$id.'/deploy.xml';
	}
	echo "\n..done.";

	// 2. Add to repository.xml
	echo "\nAdd to repository: ".$id;
	list($newExt, $extAlreadyExists) = createRepositoryEntry($repoDoc, $dd, $repositoryURL);
	if (!$extAlreadyExists) $extensionsNode->appendChild($newExt);
	echo "..done.";

	// 3. copy binary package
	$targetPackageFile = $repositoryDir."/bin/$id-".$dd->getVersion()->toVersionString()."_".$dd->getPatchlevel().".zip";
	echo "\nCopy package $id to $targetPackageFile";
	copy($zipFilepath, $targetPackageFile);
	echo "..done.";
}

if ($mediawiki) {
	$xml = Tools::createMWDeployDescriptor(realpath($rootDir."/../"), isset($mwversion) ? new DFVersion($mwversion) : NULL);

	$id = 'mw';
	$version = Tools::getMediawikiVersion(realpath($rootDir."/../"));
	$versionNoDots = str_replace(".","", $version);
	Tools::mkpath($repositoryDir."/extensions/$id");
	$handle = fopen($repositoryDir."/extensions/$id/deploy-$version.xml", "w");
	fwrite($handle, $xml);
	fclose($handle);

	// compatibility fix to DF 1.56 (to be removed in future versions)
	copy($repositoryDir."/extensions/$id/deploy-$version.xml", $repositoryDir."/extensions/$id/deploy-$versionNoDots.xml");

	// creates links
	if ($createSymlinks && $latest) {
		// remove symbolic link if existing
		if (file_exists($repositoryDir."/extensions/$id/deploy.xml")) {
			unlink($repositoryDir."/extensions/$id/deploy.xml");
		}
		// create symbolic link
		if (Tools::isWindows()) {
			$target = str_replace("/", "\\", "$repositoryDir/extensions/$id/deploy-$version.xml");
			$link = str_replace("/", "\\", "$repositoryDir/extensions/$id/deploy.xml");
			exec("mklink \"$link\" \"$target\"", $out, $res);
		} else{
			exec("ln -s $repositoryDir/extensions/$id/deploy-$version.xml $repositoryDir/extensions/$id/deploy.xml", $out, $res);
		}
		if ($res == 0) print "\n\tCreated link: ".$repositoryDir."/extensions/".$id.'/deploy.xml';
	}

	$dd = new DeployDescriptor($xml);

	echo "\nAdd to repository: ".$id;
	list($newExt, $extAlreadyExists) = createRepositoryEntry($repoDoc, $dd, $repositoryURL);
	if (!$extAlreadyExists) $extensionsNode->appendChild($newExt);
	echo "..done.";

	// assume binary package exists

}

// save repository.xml
echo "\nSave repository";
saveRepository($repositoryDir."/repository.xml", $repoDoc);
echo "..done.";
echo "\ns";




/**
 * Save a repository.
 *
 * @param $filePath
 * @param $doc
 */
function saveRepository($filePath, $doc) {
	$xml = $doc->saveXML();
	$handle = fopen($filePath, "w");
	fwrite($handle, $xml);
	fclose($handle);
}

/**
 * Load a repository
 *
 * @param $filePath
 */
function loadRepository($filePath) {
	$xml = file_get_contents($filePath);
	$dom = new DOMDocument("1.0");
	$dom->loadXML($xml);
	return $dom;
}

/**
 * Extracts the deploy descriptor(s) from a bundle or a set of bundles.
 *
 * @param string $bundlePath (file or directory)
 * @param string $fileNamecontains Filter for files
 * @param boolean $recursive Scans the directory recursively for bundles
 * @return array of (DeployDescriptor, Bundle file path)
 */
function extractDeployDescriptors($bundlePath, $fileNamecontains = false, $recursive = false) {
	global $transientID, $mwrootDir;
	$tmpFolder = Tools::isWindows() ? 'c:\temp\mw_deploy_tool' : '/tmp/mw_deploy_tool';
	Tools::mkpath($tmpFolder);
	if (is_dir($bundlePath)) {
		$result = array();
		$dirHandle=opendir($bundlePath);
		while(false !== ($file=readdir($dirHandle))) {
			if($file!="." && $file!="..") {
				$__file=$bundlePath."/".$file;
				if (is_dir($__file) && $recursive) {
					$descriptors = extractDeployDescriptors($__file, $fileNamecontains, $recursive);
					$result = array_merge($result, $descriptors);
				}
				$fileExtension = Tools::getFileExtension($file);
				if (strtolower($fileExtension) != 'zip') continue;
				if ($fileNamecontains !== false) {
					if (strpos($file, $fileNamecontains) === false) continue;
				}
				$dd = Tools::unzipDeployDescriptor($__file, $tmpFolder, $mwrootDir);
				if (is_null($dd)) {
					print "\nWARNING: $__file does not contain a deploy descriptor. It is skipped.";
					continue;
				}
				if ($transientID !== false) {
					$xml = $dd->getXML($transientID);
					$dd = new DeployDescriptor($xml);
				}
				$result[] = array($dd, $__file);
			}
		}
		return $result;
	} else {
		if (!file_exists($bundlePath)) {
			return array();
		}
		$dd = Tools::unzipDeployDescriptor($bundlePath, $tmpFolder, $mwrootDir);

		if ($transientID !== false) {
			$xml = $dd->getXML($transientID);
			$dd = new DeployDescriptor($xml);
		}
		return array(array($dd, $bundlePath));
	}
}


function createRepositoryEntry($repoDoc, $dd, $repositoryURL) {

	// find existing extension
	$nodeList = $repoDoc->getElementsByTagName("extension");
	$i=0;
	$newExt = NULL;

	while($i < $nodeList->length) {
		$ext = $nodeList->item($i);
		$id = $ext->getAttribute("id");
		if ($id == $dd->getID()) {
			$newExt = $ext;
		}
		$i++;
	}

	$extAlreadyExists = true;
	if (is_null($newExt)) {
		// create new extension node
		$extAlreadyExists = false;
		$newExt = $repoDoc->createElement("extension");
		$idAttr = $repoDoc->createAttribute("id");
		$idAttr->value = $dd->getID();
		$newExt->appendChild($idAttr);
		$titleAttr = $repoDoc->createAttribute("title");
		$titleAttr->value = $dd->getTitle();
		$newExt->appendChild($titleAttr);
	}

	$newVer = $repoDoc->createElement("version");
	$newExt->appendChild($newVer);

	$urlAttr = $repoDoc->createAttribute("url");
	$urlAttr->value = $repositoryURL."/bin/".$dd->getID()."-".$dd->getVersion()->toVersionString()."_".$dd->getPatchlevel().".zip";
	$newVer->appendChild($urlAttr);

	$versionAttr = $repoDoc->createAttribute("version");
	$versionAttr->value = $dd->getVersion()->toVersionString();
	$newVer->appendChild($versionAttr);

	$versionOldAttr = $repoDoc->createAttribute("ver");
	$versionOldAttr->value = str_replace(".", "", $dd->getVersion()->toVersionString());
	$newVer->appendChild($versionOldAttr);


	$patchlevelAttr = $repoDoc->createAttribute("patchlevel");
	$patchlevelAttr->value = $dd->getPatchlevel();
	$newVer->appendChild($patchlevelAttr);

	$maintainerAttr = $repoDoc->createAttribute("maintainer");
	$maintainerAttr->value = $dd->getMaintainer();
	$newVer->appendChild($maintainerAttr);

	$descriptionAttr = $repoDoc->createAttribute("description");
	$descriptionAttr->value = $dd->getDescription();
	$newVer->appendChild($descriptionAttr);

	$helpurlAttr = $repoDoc->createAttribute("helpurl");
	$helpurlAttr->value = $dd->getHelpURL();
	$newVer->appendChild($helpurlAttr);

	return array($newExt, $extAlreadyExists);
}

function getBundleList($bundleListFilepath) {
	if (!file_exists($bundleListFilepath)) {
		print "\nWARNING: $bundleListFilepath does not exist. Stop here.";
		die(1);
	}
	$content = file_get_contents($bundleListFilepath);
	$dom = simplexml_load_string($content);
	$bundleNodes = $dom->xpath("//bundle");
	$repositoryNode = $dom->xpath("//repository");
	$repository = (string) $repositoryNode[0];
	foreach($bundleNodes as $bundle) {
		$bundleList[] = (string) $bundle;
	}
	return array($repository, $bundleList);
}

