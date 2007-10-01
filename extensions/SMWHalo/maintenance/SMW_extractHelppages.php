<?php
/*
 * Created on 01.10.2007
 *
 * Extracts help pages as wiki markup (pages from 'Help namespace) to a directory
 * given in $helpDirectory
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
 }
 
 print "\nExtracting in '$helpDirectory'...\n";
 $dbr =& wfGetDB( DB_MASTER );
 
 print "\nExtracting help pages...";
 $helpPages = smwfGetSemanticStore()->getPages(array(NS_HELP));
 foreach($helpPages as $hp) {
 	print "\nExtract: ".$hp->getText()."...";
 	$rev = Revision::loadFromTitle($dbr, $hp);
 	$wikitext = $rev->getText();
 	$handle = fopen($helpDirectory."/".$hp->getDBKey(), "w");
 	fwrite($handle, $wikitext);
 	fclose($handle);
 	print "done!";
 }
 
 print "\n\nAll help pages extracted!\n";
?>
