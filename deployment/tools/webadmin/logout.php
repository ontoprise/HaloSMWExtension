<?php
session_start();
session_destroy();

$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
$currentDir = dirname(__FILE__);
unlink("$currentDir/userloggedin");
header('Location: http://'.$hostname.($path == '/' ? '' : $path).'/login.php');
?>