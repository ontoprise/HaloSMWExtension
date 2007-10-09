<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

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
