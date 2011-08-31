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
$new_file_name = next($argv);


$dd = new DeployDescriptor(file_get_contents($dd_file));
$version = $dd->getVersion()->toVersionString();
$patchlevel = $dd->getPatchlevel();
if ($new_file_name) {
    $new_file_name = str_replace('%s', $version."_".$patchlevel, $new_file_name);
    rename($file_to_rename,$new_file_name);
} else {
    $file_to_rename_parts = explode(".", $file_to_rename);
    rename($file_to_rename,$file_to_rename_parts[0]."-".$version."_".$patchlevel.".".$file_to_rename_parts[1]);
}

