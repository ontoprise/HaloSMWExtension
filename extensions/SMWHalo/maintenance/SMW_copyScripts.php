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
				echo "Copy ".$entry."...";
				copy($SourceDirectory.$entry, $TargetDirectory.$entry);
				echo "done!\n";
			}
		}
	}
	return true;
}

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}


$tempdir = "";
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
	//-t => temp dir
	if ($arg == '-t') {
		$tempdir = next($argv);
		continue;
	}

}

$source = dirname(__FILE__) . '/../../..';
$target = $tempdir != '' ? $tempdir : (isWindows ? 'c:/temp/halo_js_scripts' : '/tmp/halo_js_scripts');

if (!is_dir($target)) {
	echo "\nCreating directory: ".$target."\n";
	mkpath($target);
	chmod($target, 0777);
}

echo "\nCopy Scripts...\n";
copyJSScripts($source, $target);


/**
 * Creates the given directory and creates all
 * dependant directories if necessary.
 *
 * @param $path path of directory.
 */
function mkpath($path) {
	if(mkdir($path) || file_exists($path)) return true;
	return (mkpath(dirname($path)) && mkdir($path));
}

function isWindows() {
	ob_start();
	phpinfo();
	$info = ob_get_contents();
	ob_end_clean();
	//Get Systemstring
	preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
	//Check if it consists 'windows' as string
	preg_match('/[Ww]indows/',$ma[1],$os);
	if($os[0]=='' && $os[0]==null ) {
		return false;
	} else {
		return true;
	}
}
?>
