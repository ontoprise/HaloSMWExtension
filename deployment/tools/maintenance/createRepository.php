<?php
/**
 * @file
 * @ingroup DFMaintenance
 * 
 * Creates a repository XML file. Must be done once a new release available
 * 
 * Usage:   php createRepsoitory -o <repository path>  -r release-num 
 *          php createRepsoitory -o <repository path>  --head 
 *          
 * @author: Kai K�hn / ontoprise / 2009
 */

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once($rootDir."/descriptor/DF_DeployDescriptor.php");
require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");


$latest = false;
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

    //-o => output
    if ($arg == '-o') {
        $outputDir = next($argv);
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
$mwRootDir = realpath($mwRootDir."/../../../extensions/");
print($mwRootDir);
if (substr($mwRootDir, -1) != "/") $mwRootDir .= "/";

echo "\nRead local packages";
$localPackages = PackageRepository::getLocalPackages($mwRootDir);

echo "\nCreate new repository ".$outputDir."repository.xml";


$new_ser = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="repository.xsl"?>'."<root>\n<extensions>\n";
foreach($localPackages as $lp) {
    $id = $lp->getID();
    $installdir = $lp->getInstallationDirectory();
    $new_ser .= "<extension id=\"$id\">";
        $branch = isset($head) ? "smwhalo" : "smwhalo_".addSeparators($release,"_")."_release";
        $url = "http://dailywikibuilds.ontoprise.com:8080/job/$branch/lastSuccessfulBuild/artifact/SMWHaloTrunk/$installdir/deploy/bin/$id-".addSeparators($lp->getVersion(),".")."_".$lp->getPatchlevel().".zip";
        $ver = $lp->getVersion();
        $newPatchlevel = $lp->getPatchlevel();
        if ($newPatchlevel == '') $newPatchlevel = 0;
        $maintainer = escapeForXMLAttribute($lp->getMaintainer());
        $description = escapeForXMLAttribute($lp->getDescription());
        
        $new_ser .= "<version ver=\"$ver\" url=\"$url\" patchlevel=\"$newPatchlevel\" maintainer=\"$maintainer\" description=\"$description\"/>";
    
    $new_ser .= "</extension>\n";
}
$new_ser .= "\n</extensions>\n</root>";

echo "\nWrite new repository to ".$outputDir."repository.xml";
$handle = fopen($outputDir."repository.xml", "w");
fwrite($handle, $new_ser);
fclose($handle);

function addSeparators($version, $sep = ".") {
    $sep_version = "";
    for($i = 0; $i < strlen($version); $i++) {
        if ($i>0) $sep_version .= $sep;
        $sep_version .= $version[$i];
    }
    return $sep_version;
}

function escapeForXMLAttribute($text) {
	return str_replace('"', "&quot;", $text);
}