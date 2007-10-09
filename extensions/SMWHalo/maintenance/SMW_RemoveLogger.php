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
 * Used to remove logger code from the js
 */
 
 // directory where the scripts are located
 $mediaWikiLocation = dirname(__FILE__) . '/..';
 $path = 'c:/temp/halo_js_scripts/';

 /**
  * Build one script file consisting of all scripts given in $scripts array.
  */
 
$scripts = array();
if ($dir_list = opendir($path)) {
	echo "Checking for javascript files\n";
	while( ($filename = readdir($dir_list)) !== false){
		
		//Add only js files
		if( preg_match('/.*\\.[Jj][Ss]/', $filename) != 0) {
			echo "found ".$filename."\n";
			array_push($scripts, $filename);
		}	
		
	}
closedir($dir_list);
}

removeLogger($scripts);
  
 function removeLogger($scripts) { 
 	 global $path, $addScriptName;
	 $result = "";
	 echo "\n\nChecking scripts: $outputFile\n";
	 foreach($scripts as $s) {
	 	$filename = $path.$s;
	 	$handle = fopen($filename, "rb");
	 	$contents = fread ($handle, filesize ($filename));
		fclose($handle);
		
	 	//echo "Checking ".$filename."\n";
		if( preg_match("/\/\*STARTLOG\*\/.*\/\*ENDLOG\*\//sU", $contents) != 0) {
			$handle = fopen($filename, "wb");
			echo "removing from ".$filename."\n";
			$contents = preg_replace("/\/\*STARTLOG\*\/.*\/\*ENDLOG\*\//sU", "", $contents);
	 		//echo $contents;
	 		fwrite($handle, $contents);
			fclose($handle);
		}	
	 	
	 }
 }
 
?>

