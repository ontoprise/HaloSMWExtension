<?php
/**
 * Helper script for build process.
 * 
 * Renames the deployable file depending on version and patchlevel 
 * from deploy scriptor.
 * 
 * Arg0: this script
 * Arg1: deploy descriptor file location
 * Arg2: File to rename
 */
require_once("../../../deployment/descriptor/DF_DeployDescriptor.php");
require_once("../../../deployment/tools/smwadmin/DF_Tools.php");

array_shift($argv);
$dd_file = reset( $argv );
$file_to_rename = next($argv);


$dd = new DeployDescriptor(file_get_contents($dd_file));
$version = addSeparatorsForVersionNumber($dd->getVersion());
$patchlevel = $dd->getPatchlevel();
$file_to_rename_parts = explode(".", $file_to_rename);
rename($file_to_rename,$file_to_rename_parts[0]."-".$version."_".$patchlevel.".".$file_to_rename_parts[1]);

function addSeparatorsForVersionNumber($version) {
	$sep_version = "";
	for($i = 0; $i < strlen($version); $i++) {
		if ($i>0) $sep_version .= ".";
		$sep_version .= $version[$i];
	}
	return $sep_version;
}
