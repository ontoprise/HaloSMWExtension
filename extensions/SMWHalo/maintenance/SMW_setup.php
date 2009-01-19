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
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

global $smwgIP;
require_once($smwgIP . '/includes/SMW_GlobalFunctions.php');

global $smwgHaloIP;
require_once($smwgHaloIP . '/includes/SMW_Initialize.php');
require_once($smwgHaloIP . '/includes/SMW_DBHelper.php');

if (!isset($smwgDefaultCollation)) {
	$smwgDefaultCollation = "latin1_bin"; // default collation
}

$removeHelpPages = array_key_exists("removehelppages", $options);
$installHelpPages = array_key_exists("helppages", $options);

if (!$removeHelpPages && !$installHelpPages) {
	print "Syntax: php SMW_setup [--helppages] | [--removehelppages]\n";
	die();
}

if ($removeHelpPages) {
	smwfRemoveHelppages();
	die();
}

if ($installHelpPages) {
	DBHelper::reportProgress("\nImport delivered pages...",true);
	
	smwfInstallImages($smwgHaloIP.'/libs/predef_pages/images');
	smwfInstallHelppages($smwgHaloIP.'/libs/predef_pages', 12, 'Help' );
	smwfInstallHelppages($smwgHaloIP.'/libs/predef_pages', 104, 'Type' );
	DBHelper::reportProgress("\n\nAll pages imported!\n",true);
    die();
};

/**
 * Removes all helpages including the images (FIXME: thumbnails are not removed)
 *
 */
function smwfRemoveHelppages() {
	$pages = smwfGetSemanticStore()->getPages(array(NS_HELP));
	foreach($pages as $p) {
		print "\nRemove page: ".$p->getText();
		smwfRemoveImages($p);
		$a = new Article($p);
		$a->doDelete("SMW+ Update");	
	}
}

/**
 * Removes all linked images 
 *
 * @param Title $title
 */
function smwfRemoveImages($title) {
	$db =& wfGetDB( DB_MASTER );
	$res = $db->select( 'imagelinks' ,
	array( 'il_to' ),
	array("il_from" => $title->getArticleID()),
            'SMW_setup::smwfGetImages',
	NULL );
	if ( $db->numRows( $res ) ) {
		while ( $row = $db->fetchObject( $res ) ) {
			if ( $titleObj = Title::makeTitle( NS_IMAGE, $row->il_to ) ) {
				print "\nRemove image: ".$titleObj->getText();
				$im_file = wfLocalFile($titleObj);
                $im_file->delete("SMW+ Update");
				$a = new Article($titleObj);
				$a->doDelete("SMW+ Update");
			}
		}
	}
}
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
	//print "\nCopying images...\n";
	//print $SourceDirectory;
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

		//print "\nProcessing ".$SourceDirectory.$entry;
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
			DBHelper::reportProgress("\nCopy image: ".basename($SourceDirectory.$entry), true);
			//." to ".$dest_dir."/".basename($SourceDirectory.$entry)."  ";
			copy($SourceDirectory.$entry, $dest_dir."/".basename($SourceDirectory.$entry));
			 
			// simulate an upload
			$im_file = wfLocalFile(Title::newFromText(basename($SourceDirectory.$entry), NS_IMAGE));
			$im_file->recordUpload2("", "auto-inserted image", "noText");
			 
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

	$helpPageTitle = Title::newFromText($filename, $ns);
	$helpPageArticle = new Article($helpPageTitle);
	if (!$helpPageArticle->exists()) {
		DBHelper::reportProgress("\nImport: ".$filename."...", true);
		$helpPageArticle->insertNewArticle($contents, $helpPageTitle->getText(), false, false);
		print "done!";
	} else {
		DBHelper::reportProgress("\nUpdate: ".$filename."...", true);
		$helpPageArticle->updateArticle($contents, $helpPageTitle->getText(), false, false);
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

