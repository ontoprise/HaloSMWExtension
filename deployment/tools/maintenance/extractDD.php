<?php
/**
 * @file
 * @ingroup DFMaintenance
 *
 * Extracts deploy descriptors and writes them to a given location.
 *
 * @author: Kai K�hn / ontoprise / 2009
 */

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once($rootDir."/descriptor/DF_DeployDescriptor.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");

function getLocalPackages($ext_dir) {

	$localPackages = array();
	// add trailing slashes
	if (substr($ext_dir,-1)!='/'){
		$ext_dir .= '/';
	}

	$handle = @opendir($ext_dir);
	if (!$handle) {
		throw new IllegalArgument('Extension directory does not exist: '.$ext_dir);
	}

	while ($entry = readdir($handle) ){
		if ($entry[0] == '.'){
			continue;
		}

		if (is_dir($ext_dir.$entry)) {
			// check if there is a deploy.xml
			if (file_exists($ext_dir.$entry.'/deploy.xml')) {
				$dd = new DeployDescriptor(file_get_contents($ext_dir.$entry.'/deploy.xml'));
				$localPackages[$entry.'/deploy.xml'] = $dd;

			}
		}

	}

	return $localPackages;
}

$latest = false;
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-o => output
	if ($arg == '-o') {
		$outputDir = next($argv);
		if (substr($outputDir,-1)!='/'){
			$outputDir .= '/';
		}
		continue;
	}

	if ($arg == '--latest') {
		$latest = true;
		continue;
	}
}

if (!isset($outputDir)) {
	echo "\nSet output dir by using -o <directory>\n";
	die();
}

$noSymlink=false;
if (Tools::isWindows() && $latest) {
	$noSymlink=true;
	echo "Be careful: Cannot create symbolic links on Windows!";
}

$outputDir = str_replace("\\", "/", $outputDir);
if (substr($outputDir, -1) != "/") $outputDir .= "/";

$rootDir = realpath(dirname(__FILE__)."/../../../");
$rootDir = str_replace("\\", "/", $rootDir);
if (substr($rootDir, -1) != "/") $rootDir .= "/";

// create DD for extensions
$localPackages = getLocalPackages($rootDir."/extensions");
foreach($localPackages as $dd_file => $dd) {
	createEntry($dd, $rootDir."/extensions/".$dd_file, $outputDir, $latest, $noSymlink);
}

// create DD for DF
$dd = new DeployDescriptor(file_get_contents(realpath($rootDir."/deployment/deploy.xml")));
createEntry($dd, $rootDir."/deployment/deploy.xml", $outputDir, $latest, $noSymlink);

print "\nDONE.\n\n";

/**
 * Creates a DD entry for a deployed entity. 
 * 
 * @param DeployDescriptor $dd
 * @param string $dd_file Full path of deploy.xml
 * @param string $outputDir Output directory
 * @param boolean $latest
 * @param boolean $noSymlink
 */
function createEntry($dd, $dd_file, $outputDir, $latest, $noSymlink) {
	$version = $dd->getVersion();
	$targetFile = str_replace("deploy.xml", "deploy-".$version.".xml", $dd_file);
	Tools::mkpath($outputDir.$dd->getID());
	copy($dd_file, $outputDir.$dd->getID()."/deploy-".$version.".xml");
	print "\nCreated: $outputDir$targetFile";

	if (!$noSymlink && $latest) {
		// remove symbolic link if existing
		if (file_exists($outputDir.$dd->getID().'/deploy.xml')) {
			unlink($outputDir.$dd->getID().'/deploy.xml');
		}
		// create symbolic link
		exec('ln -s '.$outputDir.$dd->getID().'/deploy-'.$version.'.xml '.$outputDir.$dd->getID().'/deploy.xml');
		print "\nCreated link: ".$outputDir.$dd->getID().'/deploy.xml';
	}
}
