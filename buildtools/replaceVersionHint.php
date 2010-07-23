<?php
/**
 * Helper script for build process.
 * 
 * Replaces the token {{$VERSION}} by the version and patchlevel
 * read from deploy descriptor 
 * 
 * Arg0: this script
 * Arg1: deploy descriptor file location
 * Arg2: File to replace token(s)
 */
require_once("../../../deployment/descriptor/DF_DeployDescriptor.php");
require_once("../../../deployment/tools/smwadmin/DF_Tools.php");

array_shift($argv);
$dd_file = reset( $argv );
$file_to_replaceHints = next($argv);
$buildnumber = next($argv);

$dd = new DeployDescriptor(file_get_contents($dd_file));
$version = addSeparatorsForVersionNumber($dd->getVersion());
$patchlevel = $dd->getPatchlevel();

$content = file_get_contents($file_to_replaceHints);
$content = str_replace('{{$VERSION}}', $version."_".$patchlevel, $content);
$content = str_replace('{{$BUILDNUMBER}}', $buildnumber, $content);
$handle = fopen($file_to_replaceHints, "w");
fwrite($handle, $content);
fclose($handle);

function addSeparatorsForVersionNumber($version) {
    $sep_version = "";
    for($i = 0; $i < strlen($version); $i++) {
        if ($i>0) $sep_version .= ".";
        $sep_version .= $version[$i];
    }
    return $sep_version;
}