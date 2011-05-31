<?php

// create upload directory
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");
require_once($rootDir.'/tools/smwadmin/DF_Tools.php');
$uploadDirectory = Tools::getHomeDir()."/df_upload";
if ($uploadDirectory == 'df_upload') {
	$uploadDirectory = Tools::getTempDir()."/df_upload";
}
Tools::mkpath($uploadDirectory);

// move file
$filename = $_FILES['datei']['name'];
move_uploaded_file($_FILES['datei']['tmp_name'], "$uploadDirectory/$filename");

// redirect to index.php
$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
$proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' ? "https" : "http";
header('Location: '.$proto.'://'.$hostname.($path == '/' ? '' : $path).'/index.php');