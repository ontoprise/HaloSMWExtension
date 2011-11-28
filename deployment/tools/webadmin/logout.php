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
 * Script for handling logout
 *
 * @author: Kai Kühn
 *
 */
session_start();
session_destroy();

$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
$currentDir = dirname(__FILE__);
@unlink("$currentDir/sessiondata/userloggedin");
$proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' ? "https" : "http";
header('Location: '.$proto.'://'.$hostname.($path == '/' ? '' : $path).'/login.php');
?>
