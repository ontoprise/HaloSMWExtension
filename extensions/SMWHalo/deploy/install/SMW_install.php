<?php
/*
 * Created on 14.01.2008
 * 
 * Generates config files for a complete MediaWiki+SMW+SMWHalo installation
 * 
 * Author: kai
 */
 
 $xamppDir = dirname(__FILE__);
 $apache = $xamppDir.'/apache/conf/httpd.conf';
 $localsettings = $xamppDir.'/htdocs/mediawiki/LocalSettings.php';
 
 // main
 
 generateFileFromTemplate($localsettings, array('xamp-install' => $xamppDir, 'script-path' => 'mediawiki'));
 print "HaloWiki configured!";
 /**
  * Replaces in $dest.template the parameters in $param and save as $dest
  */
 function generateFileFromTemplate($dest, $params) {
 	
 	// read template
 	print "\nRead template file...";
 	global $xamppDir;
 	$filename = $dest.".template";
 	$handle = @fopen($filename, "rb");
 	if ($handle === false) {
 	  print "\n\nCould not open file: ".$filename."\n";
 	  die;
  }
 	$contents = fread ($handle, filesize ($filename));
 	fclose($handle);
 	print "done!";
 	
 	// insert parameters
 	foreach($params as $key => $value) {
 		//$value = str_replace("{", "", $value);
 		//$value = str_replace("}", "", $value);
 		$value = str_replace("\\", "/", $value);
 		$contents = str_replace("{{{$key}}}", $value, $contents);
 	}
 	
 	print "\nWrite configuration for $dest...";
 	// write to destination
 	$handle = fopen($dest,"wb");
	fwrite($handle, $contents);
	fclose($handle);
	print "done!.";
	print "\n";
 }
?>
