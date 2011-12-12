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
 * @ingroup TreeView
 *
 * This is the main entry file for the TreeView-extension.
 * It contains mainly constants for the configuration of the extension. This 
 * file has to be included in LocalSettings.php to enable the extension. The 
 * constants defined here can be overwritten in LocalSettings.php. After that
 * the function enableTreeView() must be called.
 * 
 * @author Thomas Schweitzer
 * Date: 30.11.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}

define('TV_TREEVIEW_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');



###
# This is the path to your installation of the TreeView as seen on your
# local filesystem. Used against some PHP file path issues.
##
$tvgIP = $IP . '/extensions/Treeview';
##

###
# This is the path to your installation of the TreeView as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$tvgScriptPath = $wgScriptPath . '/extensions/Treeview';

##
# Path to the themes of the tree as seen from the web.
##
$tvgTreeThemes = $tvgScriptPath . '/skin/themes/';

##
# Include the global functions
require_once('TV_GlobalFunctions.php');

