<?php
/**
 * TODO: merge with default build tools 
 * 
 * Helper script for build process.
 * 
 * Renames the deployable file depending on version and patchlevel 
 * from deploy scriptor.
 * 
 * Arg0: this script
 * Arg1: deploy descriptor file location
 * Arg2: File to rename
 */
require_once("../../deployment/descriptor/DF_DeployDescriptor.php");
require_once("../../deployment/tools/smwadmin/DF_Tools.php");

array_shift($argv);
$dd_file = reset( $argv );
$file_to_rename = next($argv);


$dd = new DeployDescriptor(file_get_contents($dd_file));
$version = $dd->getVersion()->toVersionString();
$patchlevel = $dd->getPatchlevel();
$file_to_rename_parts = explode(".", $file_to_rename);
rename($file_to_rename,$file_to_rename_parts[0]."-".$version."_".$patchlevel.".".$file_to_rename_parts[1]);

