<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Collaboration-Extension.
*
*   The Collaboration-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Collaboration-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This is the main entry file for the Collaboration extension.
 * It contains mainly constants for the configuration of the extension.
 * This file has to be included in LocalSettings.php to enable the extension.
 * 
 * @author Benjamin Langguth
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Collaboration extension. It is not a valid entry point.\n" );
}

define('CE_VERSION', '0.9');


global $cegIP, $cegScriptPath, $cegEnableComment, $cegEnableCurrentUsers;

###
# This is the path to your installation of Collaboration as seen on your
# local filesystem. Used against some PHP file path issues.
##
$cegIP = $IP . '/extensions/Collaboration';;

###
# This is the path to your installation of CollaborationExtension as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$cegScriptPath = $wgScriptPath . '/extensions/Collaboration';


###
# Enable Comment
###
$cegEnableComment = true;

###
# Enable CurrentUsers
###
$cegEnableCurrentUsers = false;

# load global functions
require_once('CE_GlobalFunctions.php');

###
# If you already have custom namespaces on your site, insert
#    $cegCommentNamespaceIndex = ???;
# into your LocalSettings.php *before* including this file. The number ??? must
# be the smallest even namespace number that is not in use yet. However, it
# must not be smaller than 100.
##
cefInitNamespaces();
