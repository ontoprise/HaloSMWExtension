<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Interwiki-Article-Import-module in the Data-Import-Extension.
*
*   The DataImport-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The DataImport-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the command line interface of the Interwiki Article Importer.
 * 
 * @author Thomas Schweitzer
 * Date: 30.10.2009
 * 
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A commandline script MUST NOT accessed from remote.";
    return;
}

/**
 * Initialize Mediawiki
 */
$mediaWikiLocation = dirname(__FILE__) . '/../../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$dir = dirname(__FILE__);
$iaigIP = $dir;


/**
 * Evaluate command line options
 */

//Are there any options at all?
if (empty($options)) {
	echo 'Usage: IAI_Commandline.php --af="<article file>" --a="<article name>" --if="<image file>" --i="<image name>" --ew --aregex="<regular expression for articles>" --iregex="<regular expression for images>" --api="<wiki api>" --dr --tmpl --img'."\n";

	echo "\n--af=\"<article file>\"\n";
	echo "\t<article file> is the name of a text file that contains in each line a name of an article that will be imported.";

	echo "\n--a=\"<article name>\"\n";
	echo "\tName of a single article to import. Don't specify --aregex in this case.\n";

	echo "\n--if=\"<image file>\"\n";
	echo "\t<image file> is the name of a text file that contains in each line a name of an image that will be imported.";

	echo "\n--i=\"<image name>\"\n";
	echo "\tName of a single image to import. Don't specify --iregex in this case.\n";

	echo "\n--ew\n";
	echo "\tImport the articles and images from the english wikipedia.\n";
	echo "\t\tThe format of article names in the article file must be: http://en.wikipedia.org/wiki/<article name>\n";
	echo "\t\tThe format of image names in the image file must be: http://upload.wikimedia.org/.../<image name>\n";
	echo "\tDon't use this option together with --aregex. --iregex or --api.\n";

	echo "\n--aregex=\"<regular expression>\"\n";
	echo "\tThe regular expression is used to extract the name of an article from a line in the article file.\n";
	echo "\tExample for <http://en.wikipedia.org/wiki/SomeArticle>:\n";
	echo "\t\t--aregex=\"<http:\/\/en.wikipedia.org\/wiki\/(.*?)>\"\n";
	echo "\t\tThe term in braces (i.e. (.*?) ) matches the article name.\n";
	echo "\tDon't use this option together with --ew. Don't forget to set --api!\n";

	echo "\n--iregex=\"<regular expression>\"\n";
	echo "\tThe regular expression is used to extract the name of an image from a line in the image file.\n";
	echo "\tExample for <http://upload.wikimedia.org/wikipedia/commons/7/7e/SomeImage>:\n";
	echo "\t\t--iregex=\"<http:\/\/upload.wikimedia.org.*\/(.*?)>\"\n";
	echo "\t\tThe term in braces (i.e. (.*?) ) matches the article name.\n";
	echo "\tDon't use this option together with --ew. Don't forget to set --api!\n";

	echo "\n--api=\"<wiki api>\"\n";
	echo "\tThis is the base URL of the Mediawiki API of the source wiki.\n";
	echo "\tExample for the english Wikipedia: http://en.wikipedia.org/w/\n";
	echo "\tDon't use this option together with --ew. Don't forget to set --aregex!\n";

	echo "\n--dr";
	echo "\tDry run. Show only the names of the articles found in the article file end exit.";

	echo "\n--tmpl";
	echo "\tImport all templates needed for the specified articles.";

	echo "\n--img";
	echo "\tImport all images needed for the specified articles.";
	
	die();
}

$articleFile = @$options["af"];
$imageFile = @$options["if"];
$wikiApi = "";
$articleRegex = "";
$imageRegex = "";
$importTemplates = isset($options["tmpl"]);
$importImages = isset($options["img"]);

if (!@$options["af"] && !@$options["a"] && !@$options["if"] && !@$options["i"]) {
	echo "No articles or images for import given. Please specify --af, --a, --i or --i !\n";
	die();
}

if (@$options["ew"]) {
	if (@$options["aregex"] || @$options["iregex"] || @$options["api"]) {
		echo "Do not use --ew together with --aregex. --iregex or --api!\n";
		die();
	}
	$wikiApi = "http://en.wikipedia.org/w/";
	$articleRegex = "<http:\/\/en.wikipedia.org\/wiki\/(.*?)>";
	$imageRegex = "<http:\/\/upload.wikimedia.org.*\/(.*?)>";
}

if (@$options["aregex"]) {
	if (!@$options["api"] || !@$options["af"]) {
		echo "You are using --aregex. Please specify --api and --af as well.\n";
		die();
	}
	$articleRegex = $options["aregex"];
}

if (@$options["iregex"]) {
	if (!@$options["api"] || !@$options["if"]) {
		echo "You are using --iregex. Please specify --api and --if as well.\n";
		die();
	}
	$imageRegex = $options["iregex"];
}

if (@$options["api"]) {
	$wikiApi = $options["api"];
}

if (!@$options["ew"] && !@$options["aregex"] && !@$options["iregex"] && !@$options["api"]) {
	echo "You have to specify the option --ew or --aregex, --iregex and --api !\n";
	die();
}

$dryRun = isset($options["dr"]);

/**
 * Extract article names from the article file.
 */

$articles = array();

if (isset($options["a"])) {
	$articles[] = $options["a"];
}

if (!empty($articleFile)) {
	if (!file_exists($articleFile)) {
		echo "The file \"$articleFile\" does not exist.\n";
		die();
	}
	
	$f = fopen($articleFile, "r");
	if ($f === false) {
		echo "The file \"$articleFile\" could not be opened.\n";
		die();
	}
	
	// Read each line and extract the article names.
	while (!feof($f)) {
	    $line = fgets($f);
	    $matches = array();
	    if (preg_match_all("/$articleRegex/", $line, $matches) > 0) {
	    	foreach ($matches[1] as $m) {
	    		$articles[] = urldecode($m);
	    	}
	    }
	}
	fclose ($f); 
}

/**
 * Extract image names from the image file.
 */

$images = array();

if (isset($options["i"])) {
	$images[] = $options["i"];
}

if (!empty($imageFile)) {
	if (!file_exists($imageFile)) {
		echo "The file \"$imageFile\" does not exist.\n";
		die();
	}
	
	$f = fopen($imageFile, "r");
	if ($f === false) {
		echo "The file \"$imageFile\" could not be opened.\n";
		die();
	}
	
	// Read each line and extract the image names.
	while (!feof($f)) {
	    $line = fgets($f);
	    $matches = array();
	    if (preg_match_all("/$imageRegex/", $line, $matches) > 0) {
	    	foreach ($matches[1] as $m) {
	    		$images[] = urldecode($m);
	    	}
	    }
	}
	fclose ($f); 
}

if ($dryRun) {
	echo "The following articles were specified:\n";
	foreach ($articles as $a) {
		echo $a."\n";
	}
	
	echo "\nThe following images were specified:\n";
	foreach ($images as $i) {
		echo $i."\n";
	}
	
	die();
}

/**
 * Do the import
 */
include_once("$iaigIP/IAI_Initialize.php");
enableIAI();

$ai = new IAIArticleImporter($wikiApi);

$articleImp = "";
$imageImp = "";
try {
	if (!empty($articles)) {
		$articleImp = $ai->importArticles($articles, $importTemplates, $importImages);
	}
	if (!empty($images)) {
		$imageImp = $ai->importImages($images, true);
	}
} catch (Exception $e) {
	echo "Caught an exception: \n".$e->getMessage();
	die();
}
if (!empty($articleImp)) {
	echo "Saved report for articles in: $articleImp\n";
}

if (!empty($imageImp)) {
	echo "Saved report for images in: $imageImp\n";
}
