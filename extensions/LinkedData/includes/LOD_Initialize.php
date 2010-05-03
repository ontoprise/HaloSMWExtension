<?php
/**
 * @file
 * @ingroup LinkedData
 */
/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This is the main entry file for the LinkedData extension.
 * It contains mainly constants for the configuration of the extension. This 
 * file has to be included in LocalSettings.php to enable the extension. The 
 * constants defined here can be overwritten in LocalSettings.php. After that
 * the function enableLinkedData() must be called.
 * 
 * @author Thomas Schweitzer
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

define('LOD_LINKEDDATA_VERSION', '{{$VERSION}}');

###
# This is the path to your installation of LinkedData as seen on your
# local filesystem. Used against some PHP file path issues.
##
$lodgIP = $IP . '/extensions/LinkedData';
##

###
# This is the path to your installation of LinkedData as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$lodgScriptPath = $wgScriptPath . '/extensions/LinkedData';

# load global functions
require_once('LOD_GlobalFunctions.php');

###
# If you already have custom namespaces on your site, insert
#    $lodgNamespaceIndex = ???;
# into your LocalSettings.php *before* including this file. The number ??? must
# be the smallest even namespace number that is not in use yet. However, it
# must not be smaller than 100.
##
lodfInitNamespaces();


// Tell the script manager, that we need prototype
//global $smgJSLibs; 
//$smgJSLibs[] = 'prototype'; 
