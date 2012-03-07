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

$dfgRunFromCommandLine = true;
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    $dfgRunFromCommandLine = false;
}
$mwrootDir = realpath(dirname(__FILE__)."/../../");
if (!file_exists("$mwrootDir/deployment/settings.php")) {
    print "\n[ERROR] settings.php not found! Copy it from deployment/config to deployment.\n";
    die();
}


require_once("checkPHP.inc");
echo dffDoPHPChecks();