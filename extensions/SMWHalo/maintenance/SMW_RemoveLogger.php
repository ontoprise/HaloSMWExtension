<?php
/*
 * Created on 24.07.2007
 *
 * Author: Robert
 * 
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

