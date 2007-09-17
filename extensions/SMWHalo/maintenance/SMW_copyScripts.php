<?php
/*
 * Created on 12.09.2007
 * 
 * Copies all JS script files to a temporary directory.
 *
 * Author: kai
 */
 function copyJSScripts($SourceDirectory, $TargetDirectory)
{

    // add trailing slashes
    if (substr($SourceDirectory,-1)!='/'){
        $SourceDirectory .= '/';
    }
    if (substr($TargetDirectory,-1)!='/'){
        $TargetDirectory .= '/';
    }

    $handle = @opendir($SourceDirectory);
    if (!$handle) {
        die("Das Verzeichnis $SourceDirectory konnte nicht geöffnet werden.");
    }

    

    while ($entry = readdir($handle) ){
        if ($entry[0] == '.'){
            continue;
        }

        if (is_dir($SourceDirectory.$entry)) {
            // Unterverzeichnis
            $success = copyJSScripts($SourceDirectory.$entry, $TargetDirectory);

        }else{
            //$target = $TargetDirectory.$entry;
            if (strpos($SourceDirectory.$entry, ".js") !== false) {
            	echo "Copy ".$SourceDirectory.$entry."...\n";
            	copy($SourceDirectory.$entry, $TargetDirectory.$entry);
            	
            }
        }
    }
    return true;
}

echo "\nCopy Scripts...\n";
$source = dirname(__FILE__) . '/../../..';
$target = 'c:/temp/halo_js_scripts';
if (!is_dir($target)) {
    mkdir($target);
    chmod($target, 0777); 
}
copyJSScripts($source, $target);
?>
