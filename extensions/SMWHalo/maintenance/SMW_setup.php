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
 * Created on 25.09.2007
 *
 * Author: kai
 */
 if ($_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

 $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 require_once "$mediaWikiLocation/maintenance/commandLine.inc";

 global $smwgIP;
 require_once($smwgIP . '/includes/SMW_GlobalFunctions.php');

 global $smwgHaloIP;
 require_once($smwgHaloIP . '/includes/SMW_Initialize.php');
 
 if (!isset($smwgDefaultCollation)) {
		$smwgDefaultCollation = "latin1_bin"; // default collation
 }
	
 print "\nSetup database for HALO extension...";
 smwfHaloInitializeTables(false);
 print "done!\n";
 
 $onlyTables = array_key_exists("t", $options);
 
 if ($onlyTables) return;
 
 print "\nInstall predefined pages...";
 smwfInstallHelppages($smwgHaloIP.'/libs/predef_pages', 12, 'Help' );
 smwfInstallHelppages($smwgHaloIP.'/libs/predef_pages', 104, 'Type' );
 smwfInstallImages($smwgHaloIP.'/libs/predef_pages/images');
 print "\n\nAll predefined pages imported!\n";
 
 
 /**
  * Reads help pages files (.whp) below the given
  * directory and import it in the wiki. Copies all
  * image files below the given
  * directory to the wiki image directory.
  * 
  * @param $SourceDirectory directory which contains .whp files.
  */
 function smwfInstallHelppages($SourceDirectory, $ns, $ext) {
 	global $mediaWikiLocation;
 	
 	if (basename($SourceDirectory) == "CVS") { // ignore CVS dirs 
 		return;
 	}
 	 // add trailing slashes
    if (substr($SourceDirectory,-1)!='/'){
        $SourceDirectory .= '/';
    }
   
    $handle = @opendir($SourceDirectory);
    if (!$handle) {
		die("\nDirectory '$SourceDirectory' could not be opened.\n");
    }

    while ( ($entry = readdir($handle)) !== false ){
        if ($entry[0] == '.'){
            continue;
        }

        if (is_dir($SourceDirectory.$entry)) {
            // Unterverzeichnis
            $success = smwfInstallHelppages($SourceDirectory.$entry, $ns, $ext);

        } else{
           
            if (strpos($SourceDirectory.$entry, ".".$ext) !== false) {
               	smwfImportHelppage($SourceDirectory.$entry, $ns, $ext);
            	
            } 
        }
    }
 }
 
 /**
  * Copies images 
  */
 function smwfInstallImages($SourceDirectory) {
 	global $mediaWikiLocation, $smwgHaloIP;
 	print "\nCopying images...\n";
 	print $SourceDirectory;
 	if (basename($SourceDirectory) == "CVS") { // ignore CVS dirs 
 		return;
 	}
 	 // add trailing slashes
    if (substr($SourceDirectory,-1)!='/'){
        $SourceDirectory .= '/';
    }
   
    $handle = @opendir($SourceDirectory);
    if (!$handle) {
		die("\nDirectory '$SourceDirectory' could not be opened.\n");
    }
    while ( ($entry = readdir($handle)) !== false ){
	
        if ($entry[0] == '.'){
            continue;
        }
		
		print "\nProcessing ".$SourceDirectory.$entry;
        if (is_dir($SourceDirectory.$entry)) {
            // Unterverzeichnis
            $success = smwfInstallImages($SourceDirectory.$entry);

        } else{
           
          
            	$im_dir_abs = dirname($SourceDirectory.$entry);
            	$img_dir_rel = substr($im_dir_abs, strlen($smwgHaloIP.'/lib/predef_pages/'));
            	$dest_dir = $mediaWikiLocation.$img_dir_rel;
            	if (!file_exists($dest_dir)) {
            		mkpath($dest_dir);
            	}
            	
            	// copy image into filesystem.
            	print "\n - Copy image: ".basename($SourceDirectory.$entry)." to ".$dest_dir."/".basename($SourceDirectory.$entry)."  ";
            	copy($SourceDirectory.$entry, $dest_dir."/".basename($SourceDirectory.$entry));
            	
            	// simulate an upload
            	$im_file = wfLocalFile(Title::newFromText(basename($SourceDirectory.$entry), NS_IMAGE));
            	$im_file->recordUpload2("", "auto-inserted image", "noText");
            	print "done!";
            }
        
    }
 }
 
 /**
  * Insert a new article with input from a file. Filename will
  * be used as title. (Unescaped)
  * 
  * @param path to a file containing wiki markup
  */
 function smwfImportHelppage($filepath, $ns, $ext) {
 	$handle = fopen($filepath, "rb");
	$contents = fread ($handle, filesize ($filepath));
	$filename = basename($filepath, ".".$ext);
	$filename = str_replace("_", " ", rawurldecode($filename));
	print "\nProcessing ".$filename;
	$helpPageTitle = Title::newFromText($filename, $ns);
	$helpPageArticle = new Article($helpPageTitle);
	if (!$helpPageArticle->exists()) {
		print "\nImport help page: ".$filename."...";
		$helpPageArticle->insertNewArticle($contents, $helpPageTitle->getText(), false, false);
		print "done!";
	}
	fclose($handle);
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

