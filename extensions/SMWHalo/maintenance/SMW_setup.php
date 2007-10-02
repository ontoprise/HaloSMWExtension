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
 
 print "\nInstall help pages...";
 smwfInstallHelppages(dirname(__FILE__).'/../libs/helppages');
 print "\n\nAll help pages imported!\n";
 
 
 /**
  * Reads help pages files (.whp) below the given
  * directory and import it in the wiki. Copies all
  * image files below the given
  * directory to the wiki image directory.
  * 
  * @param $SourceDirectory directory which contains .whp files.
  */
 function smwfInstallHelppages($SourceDirectory) {
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

    while ($entry = readdir($handle) ){
        if ($entry[0] == '.'){
            continue;
        }

        if (is_dir($SourceDirectory.$entry)) {
            // Unterverzeichnis
            $success = smwfInstallHelppages($SourceDirectory.$entry);

        } else{
           
            if (strpos($SourceDirectory.$entry, ".whp") !== false) {
               	smwfImportHelppage($SourceDirectory.$entry);
            	
            } else  { // assume that it is an image
            	$im_dir_abs = dirname($SourceDirectory.$entry);
            	$img_dir_rel = substr($im_dir_abs, strlen(dirname(__FILE__).'/../libs/helppages'));
            	$dest_dir = $mediaWikiLocation.$img_dir_rel;
            	mkpath($dest_dir);
            	print "\n - Copy image: ".basename($SourceDirectory.$entry)."...";
            	copy($SourceDirectory.$entry, $dest_dir.'/'.basename($SourceDirectory.$entry));
            	print "done!";
            }
        }
    }
 }
 
 /**
  * Insert a new article with input from a file. Filename will
  * be used as title. (Unescaped)
  * 
  * @param path to a file containing wiki markup
  */
 function smwfImportHelppage($filepath) {
 	$handle = fopen($filepath, "rb");
	$contents = fread ($handle, filesize ($filepath));
	$filename = basename($filepath, ".hlp");
	$filename = str_replace("_", " ", rawurldecode($filename));
	$helpPageTitle = Title::newFromText($filename, NS_HELP);
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

