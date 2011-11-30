<?php
/**
 * TODO: merge with default build tools
 * 
 * Helper script for build process.
 * 
 * Replaces the token {{$VERSION}} by the version and patchlevel
 * read from deploy descriptor 
 * 
 * Arg0: this script
 * Arg1: deploy descriptor file location
 * Arg2: File to replace token(s)
 */
require_once("../../deployment/descriptor/DF_DeployDescriptor.php");
require_once("../../deployment/tools/smwadmin/DF_Tools.php");

array_shift($argv);
$dd_file = reset( $argv );
$file_to_replaceHints = next($argv);
$buildnumber = next($argv);

$dd = new DeployDescriptor(file_get_contents($dd_file));
$version = $dd->getVersion()->toVersionString();
$patchlevel = $dd->getPatchlevel();

$content = file_get_contents($file_to_replaceHints);
$content = str_replace('{{$VERSION}}', $version."_".$patchlevel, $content);
$content = str_replace('{{$BUILDNUMBER}}', $buildnumber, $content);
$handle = fopen($file_to_replaceHints, "w");
fwrite($handle, $content);
fclose($handle);

