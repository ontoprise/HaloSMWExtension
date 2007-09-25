<?php
/*
 * Created on 25.09.2007
 *
 * Author: kai
 */
 $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 require_once "$mediaWikiLocation/maintenance/commandLine.inc";

 global $smwgHaloIP;
 require_once($smwgHaloIP . '/includes/SMW_Initialize.php');
 
 if (!isset($smwgDefaultCollation)) {
		$smwgDefaultCollation = "latin1_bin"; // default collation
 }
	
 print "\nSetup database for HALO extension...";
 smwfHaloInitializeTables(false);
 print "done!\n";
?>

