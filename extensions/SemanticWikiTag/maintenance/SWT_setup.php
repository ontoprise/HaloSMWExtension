<?php
/*
 * Created on 15.07.2009
 *
 * Author: Ning
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

global $smwgWTIP;
require_once($smwgWTIP . '/includes/SWT_Initialize.php');

if (!isset($smwgDefaultCollation)) {
	$smwgDefaultCollation = "latin1_bin"; // default collation
}

function reportProgress($msg, $verbose) {
	if (!$verbose) {
		return;
	}
	if (ob_get_level() == 0) { // be sure to have some buffer, otherwise some PHPs complain
		ob_start();
	}
	print $msg;
	ob_flush();
	flush();
}

reportProgress("\nImport delivered pages...", true);
smwfWTInstalPages($smwgWTIP.'/libs/predef_pages', NS_CATEGORY, 'Category' );
smwfWTInstalPages($smwgWTIP.'/libs/predef_pages', NS_TEMPLATE, 'Template' );
smwfWTInstalPages($smwgWTIP.'/libs/predef_pages', SMW_NS_PROPERTY, 'Property' );
reportProgress("\n\nAll pages imported!\n",true);

function smwfWTInstalPages($SourceDirectory, $ns, $ext) {
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
			$success = smwfWTInstalPages($SourceDirectory.$entry, $ns, $ext);

		} else{
			 
			if (strpos($SourceDirectory.$entry, ".".$ext) !== false) {
				smwfWTImportPage($SourceDirectory.$entry, $ns, $ext);
				 
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
function smwfWTImportPage($filepath, $ns, $ext) {
	$handle = fopen($filepath, "rb");
	$contents = fread ($handle, filesize ($filepath));
	$filename = basename($filepath, ".".$ext);
	$filename = str_replace("_", " ", rawurldecode($filename));

	$helpPageTitle = Title::newFromText($filename, $ns);
	$helpPageArticle = new Article($helpPageTitle);
	if (!$helpPageArticle->exists()) {
		reportProgress("\nImport: ".$filename."...", true);
		$helpPageArticle->insertNewArticle($contents, $helpPageTitle->getText(), false, false);
		print "done!";
	} else {
		reportProgress("\nUpdate: ".$filename."...", true);
		$helpPageArticle->updateArticle($contents, $helpPageTitle->getText(), false, false);
		print "done!";
	}
	fclose($handle);
}
?>

