<?php
/*  Copyright 2011, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup WebAdmin
 *
 * @defgroup WebAdmin Web-administration tool
 * @ingroup DeployFramework
 *
 * Installation tool.
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 *
 */

$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");
require_once($rootDir.'/tools/smwadmin/DF_Tools.php');
require_once($rootDir.'/settings.php');

// get upload directory
if (array_key_exists('df_uploaddir', DF_Config::$settings)) {
	$uploadDirectory = DF_Config::$settings['df_uploaddir'];
} else {
	$uploadDirectory = Tools::getHomeDir()."/df_upload";
	if ($uploadDirectory == 'df_upload') {
		$uploadDirectory = Tools::getTempDir()."/df_upload";
	}
}

// create upload directory if necessary
Tools::mkpath($uploadDirectory);

// move file
$filename = $_FILES['datei']['name'];
move_uploaded_file($_FILES['datei']['tmp_name'], "$uploadDirectory/$filename");

// redirect to index.php
$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
$proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' ? "https" : "http";
header('Location: '.$proto.'://'.$hostname.($path == '/' ? '' : $path).'/index.php?tab=2');