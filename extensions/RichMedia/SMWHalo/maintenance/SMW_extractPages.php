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
 * Created on 01.10.2007
 *
 * Extracts pages as wiki markup and their linked
 * images to a directory given in $helpDirectory.
 * 
 * Options: --d=<path>
 * Example: --d=c:\temp\pages
 * 
 * 			--t=<namespace constant>
 * Examples: --t=SMW_NS_TYPE	or	--t=NS_HELP		or	--t=SMW_NS_TYPE,NS_HELP
 * 
 * Author: kai
 */

/**
 *  @file
 *  @ingroup SMWHaloMaintenance
 *  
 *  @author Kai Kühn
 */
 
 if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

 $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 require_once "$mediaWikiLocation/maintenance/commandLine.inc";
 
 global $smwgIP;
 require_once($smwgIP . '/includes/SMW_GlobalFunctions.php');
 //smwfInitNamespaces();
 
 global $smwgHaloIP, $wgContLang, $argv;
 require_once($smwgHaloIP . '/includes/SMW_Initialize.php');
 
 if (array_key_exists('d', $options)) {
 	$helpDirectory = $options['d'];
 } else {
 	$helpDirectory = "c:/temp/pages"; //TODO: make unix compatible
 }
 
 if (!file_exists($helpDirectory)) {
 	mkdir($helpDirectory);
 	mkdir($helpDirectory.'/images');
 }
 
 
 print "\nExtracting in '$helpDirectory'...\n";
 $dbr =& wfGetDB( DB_MASTER );
 
 if (array_key_exists('t', $options)) {
 	$constants = explode(",", $options['t']);
 	foreach($constants as $c) {
 		extractFromNamespace(constant(trim($c)), $wgContLang->getNsText(constant(trim($c))) );
 	}
 
 } else {
 	// extract NS_HELP namespace by default
 	extractFromNamespace(NS_HELP, $wgContLang->getNsText(NS_HELP));
 }
 
 
 
 
 /**
  * Extracts pages from the given namespace. Creates for
  * every page an article with the given extension containing all
  * the wiki markup.
  * 
  * @param $type namespace
  * @param $fext file extension
  */
 function extractFromNamespace($type, $fext) {
	 global $helpDirectory, $dbr;
	 print "\nExtracting pages from namespace $type...";
	 $helpPages = smwfGetSemanticStore()->getPages(array($type));
	 foreach($helpPages as $hp) {
	 	print "\nExtract: ".$hp->getText()."...";
	 	$rev = Revision::loadFromTitle($dbr, $hp);
	 	$wikitext = $rev->getText();
	 	$fname = rawurlencode($hp->getDBKey());
	 	$handle = fopen($helpDirectory."/".$fname.".".$fext, "w");
	 	fwrite($handle, $wikitext);
	 	fclose($handle);
	 	extractImages($hp);
	 	print "done!";
 	}
  	print "\n\nAll pages from namespace $type extracted!\n";
 }
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
		
		$im_file = wfFindFile(Title::newFromText($im_name));
		if ($im_file == NULL) {
			print "\n !! Warning: ".$im_name." is missing."; 
			continue;
		}
		$im_path_abs = $im_file->getPath();
		$im_path_rel = substr($im_path_abs, strlen($wgUploadDirectory));
	
		if (!file_exists($helpDirectory.'/images'.dirname($im_path_rel))) { 
			mkpath($helpDirectory.'/images'.dirname($im_path_rel));
		}
		
		
		
		copy($im_path_abs, $helpDirectory.'/images'.$im_path_rel);
		$thumbnails = $im_file->getThumbnails();
		foreach($thumbnails as $tn) {
			mkpath($helpDirectory.'/images/thumb'.$im_path_rel);
			copy(trim($wgUploadDirectory."/".$im_file->getThumbRel()."/$tn"), $helpDirectory.'/images/thumb'.$im_path_rel."/".$tn);
		}
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
 

