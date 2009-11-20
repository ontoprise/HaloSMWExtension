<?php
/**
 * Reads repository and updates patchlevels.
 * 
 * Usage: php updatePatchlevels.php -o <repository path>
 * 
 * @author: Kai Kühn / ontoprise / 2009
 */
require_once("../../descriptor/DF_DeployDescriptor.php");
require_once("../../tools/smwadmin/DF_Tools.php");

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
                $localPackages[$dd->getID()] = $dd;

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
    echo "\nBe careful: Cannot create symbolic links on Windows!";
}

$outputDir = str_replace("\\", "/", $outputDir);
if (substr($outputDir, -1) != "/") $outputDir .= "/";

$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../../extensions/");
print($rootDir);
if (substr($rootDir, -1) != "/") $rootDir .= "/";

echo "\nRead local packages";
$localPackages = getLocalPackages($rootDir);

echo "\nRead existing repository from ".$outputDir."repository.xml";
$repository_xml = file_get_contents($outputDir."repository.xml");
$repository_dom = simplexml_load_string($repository_xml);
$extensions = $repository_dom->xpath("//extension");

$new_ser = '<?xml version="1.0" encoding="UTF-8"?>'."<root>\n<extensions>\n";
foreach($extensions as $e) {
    $id = (string) $e->attributes()->id;
	
    $new_ser .= "<extension id=\"$id\">";
    foreach($e->version as $v) {
        $url = (string) $v->attributes()->url;
        $ver = (string) $v->attributes()->ver;
        $newPatchlevel = (string) $v->attributes()->patchlevel;
        if ($newPatchlevel == '') $newPatchlevel = 0;
        if (array_key_exists($id, $localPackages) && $localPackages[$id]->getVersion() == $ver) {
            $newPatchlevel = $localPackages[$id]->getPatchlevel();
        	echo "\nUpdating patchlevel of '$id' to $newPatchlevel";
        }
        $new_ser .= "<version ver=\"$ver\" url=\"$url\" patchlevel=\"$newPatchlevel\"/>";
    }
    $new_ser .= "</extension>\n";
}
$new_ser .= "\n</extensions>\n</root>";

echo "\nWrite new repository to ".$outputDir."repository.xml";
$handle = fopen($outputDir."repository.xml", "w");
fwrite($handle, $new_ser);
fclose($handle);
