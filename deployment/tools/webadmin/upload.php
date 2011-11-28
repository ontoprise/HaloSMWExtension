<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup WebAdmin
 *
 * @defgroup WebAdmin Web-administration tool
 * @ingroup DeployFramework
 *
 * Script for handling upload.
 *
 * @author: Kai Kühn
 *
 */

$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");
require_once($rootDir.'/tools/smwadmin/DF_Tools.php');
require_once($rootDir.'/settings.php');

// get upload directory
try {
	if (array_key_exists('df_uploaddir', DF_Config::$settings)) {
		$uploadDirectory = DF_Config::$settings['df_uploaddir'];
	} else {
		if (array_key_exists('df_homedir', DF_Config::$settings)) {
			$homedir = DF_Config::$settings['df_homedir'];
		} else {
			$homedir = Tools::getHomeDir();
		}
		if (is_null($homedir)) {
			throw new DF_SettingError(DEPLOY_FRAMEWORK_NO_HOME_DIR, "No homedir found. Please configure one in settings.php");
		}
		$wikiname = DF_Config::$df_wikiName;
		$uploadDirectory = "$homedir/$wikiname/df_upload";
	}
} catch(DF_SettingError $e) {
	echo "<h1>Installation problem</h1>";
	echo $e->getMsg();
	die();
}
// create upload directory if necessary
Tools::mkpath($uploadDirectory);

// move file
$filename = $_FILES['datei']['name'];
if (file_exists("$uploadDirectory/$filename")) {
	// if it already exists, add a counting number
	$i = 1;
	$file_wo_ending = Tools::removeFileEnding($filename);
	$file_ext = Tools::getFileExtension($filename);
	while(file_exists("$uploadDirectory/$file_wo_ending($i).$file_ext")) $i++;
	$filename = "$file_wo_ending($i).$file_ext";
}
$uploadDone = move_uploaded_file($_FILES['datei']['tmp_name'], "$uploadDirectory/$filename");
if (!$uploadDone) {
	echo "<h1>Problem occured</h1>";
	echo "Could not upload file '$filename' to '$uploadDirectory'. Missing rights? Uploads not enabled?";
	die();
}

// redirect to index.php
$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
$proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' ? "https" : "http";
header('Location: '.$proto.'://'.$hostname.($path == '/' ? '' : $path).'/index.php?tab=2');
