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
 
 function smwfInstallHelppages($SourceDirectory) {
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
           
            if (strpos($SourceDirectory.$entry, ".hlp") !== false) {
            	echo "\nImport help page: ".$SourceDirectory.$entry."...";
            	smwfImportHelppage($SourceDirectory.$entry);
            	echo "done!";
            }
        }
    }
 }
 
 /**
  * Insert a new article with input from a file. Title of new article 
  * is the filename + "?".
  * 
  * @param path to a file containing wiki markup
  */
 function smwfImportHelppage($filepath) {
 	$handle = fopen($filepath, "rb");
	$contents = fread ($handle, filesize ($filepath));
	$filename = basename($filepath, ".hlp");
	$filename = str_replace("_", " ", $filename);
	$helpPageTitle = Title::newFromText($filename, NS_HELP);
	$helpPageArticle = new Article($helpPageTitle);
	if (!$helpPageArticle->exists()) {
		$helpPageArticle->insertNewArticle($contents, $helpPageTitle->getText()+"?", false, false);
	}
	fclose($handle);
 }
?>

