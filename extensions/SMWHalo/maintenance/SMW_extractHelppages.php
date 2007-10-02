<?php
/*
 * Created on 01.10.2007
 *
 * Extracts help pages as wiki markup (pages from 'Help' namespace) and their linked
 * images to a directory given in $helpDirectory.
 * 
 * Options: --d=<path>
 * Example: --d=c:\temp\helppages
 * 
 * Author: kai
 */
 $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 require_once "$mediaWikiLocation/maintenance/commandLine.inc";

 global $smwgHaloIP, $argv;
 require_once($smwgHaloIP . '/includes/SMW_Initialize.php');
 
 if (array_key_exists('d', $options)) {
 	$helpDirectory = $options['d'];
 } else {
 	$helpDirectory = "c:/temp/helppages";
 }
 
 if (!file_exists($helpDirectory)) {
 	mkdir($helpDirectory);
 	mkdir($helpDirectory.'/images');
 }
 
 print "\nExtracting in '$helpDirectory'...\n";
 $dbr =& wfGetDB( DB_MASTER );
 
 print "\nExtracting help pages...";
 $helpPages = smwfGetSemanticStore()->getPages(array(NS_HELP));
 foreach($helpPages as $hp) {
 	print "\nExtract: ".$hp->getText()."...";
 	$rev = Revision::loadFromTitle($dbr, $hp);
 	$wikitext = $rev->getText();
 	$fname = rawurlencode($hp->getDBKey());
 	$handle = fopen($helpDirectory."/".$fname.".whp", "w");
 	fwrite($handle, $wikitext);
 	fclose($handle);
 	extractImages($hp);
 	print "done!";
 }
 
 print "\n\nAll help pages extracted!\n";
 
 /**
  * Extracts linked images from an article.
  * 
  * @param $hp article.
  */
 function extractImages($hp) {
 	global $dbr, $wgUploadDirectory, $helpDirectory;
 	$images = $dbr->query('SELECT il_to FROM imagelinks i WHERE il_from = '.$hp->getArticleID());
 	if ($dbr->numRows($images) == 0) return;
 	while( $image = $dbr->fetchObject($images) ) {
		$im_name = $image->il_to;
		$im_path_abs = wfImageDir($im_name);
		$im_path = substr($im_path_abs, strlen($wgUploadDirectory));
	
		if (!file_exists($helpDirectory.'/images'.$im_path)) { 
			mkpath($helpDirectory.'/images'.$im_path);
		}
		
		copy($im_path_abs.'/'.$im_name, $helpDirectory.'/images'.$im_path.'/'.$im_name);
	}
 }
 
 /**
  * Creates the given directory and creates all
  * dependant directories if necessary.
  * 
  * @param $path path of directory.
  */
 function mkpath($path) {
    if(@mkdir($path) || file_exists($path)) return true;
    return (mkpath(dirname($path)) && mkdir($path));
 }
 
?>
