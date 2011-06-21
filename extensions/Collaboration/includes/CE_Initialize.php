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
 * @file
 * @ingroup Collaboration 
 * 
 * This is the main entry file for the Collaboration extension.
 * It contains mainly constants for the configuration of the extension.
 * This file has to be included in LocalSettings.php to enable the extension.
 * 
 * @author Benjamin Langguth
 */

/**
 * This group contains all parts of the Collaboration extension.
 * @defgroup Collaboration
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Collaboration extension. It is not a valid entry point.\n" );
}

define('CE_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

define('CE_COMMENT_ALL', 0);
define('CE_COMMENT_AUTH_ONLY', 1);
define('CE_COMMENT_NOBODY', 2);

global $cegIP, $cegScriptPath, $cegEnableComment, $cegEnableCommentFor, $cegEnableCurrentUsers;

###
# This is the path to your installation of Collaboration as seen on your
# local filesystem. Used against some PHP file path issues.
##
$cegIP = $IP . '/extensions/Collaboration';

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
# Who's allowed to comment?
# Allowed values are: all, auth only, nobody, see constants at top.
###
$cegEnableCommentFor = CE_COMMENT_ALL;

###
# Enable Rating
###
$cegEnableRatingForArticles = true;

###
# Enable full/physical deletion of comments (sysop only)
###
$cegEnableFullDeletion = true;

###
# Enable file attachments for comments.
# Setting this to true adds an additional input field where you can enter links to files.
###
$cegEnableFileAttachments = true;

###
# Use upload functionality from Rich Media.
# Setting this to true adds a link to upload new files next to the input field.
# The name of successfully uploaded files will be entered in the field at the end of the process.
# Note: Rich media needs to be installed and activated.
###
$cegUseRMUploadFunc = true;

###
# Default delimiter for file attachment field.
# This delimiter is used in the file attachment field to distinguish the files.
# Default of this setting is ','
###
$cegDefaultDelimiter = ',';


###
# Use ScriptManager
###
global $smgJSLibs;
$smgJSLibs[] = 'jquery'; 

# load global functions
require_once('CE_GlobalFunctions.php');

###
# If you already have custom namespaces on your site, insert
# 	$cegCommentNamespaceIndex = XYZ;
# into your LocalSettings.php *before* including this file. The number XYZ must
# be the smallest even namespace number that is not in use yet. However, it
# must not be smaller than 100.
# Collaboration Extension uses 700 as standard value for this.
##
cefInitNamespaces();

###
# Comments are searched by default. Remove these lines if not wanted.
###
global $wgNamespacesToBeSearchedDefault;
if( isset($wgNamespacesToBeSearchedDefault) && is_array($wgNamespacesToBeSearchedDefault)) {
	array_push($wgNamespacesToBeSearchedDefault, array(CE_COMMENT_NS => true) );
} else {
	$wgNamespacesToBeSearchedDefault = array(CE_COMMENT_NS => true);
}

###
# Comments are by default shown in the autocompletion.
# The following lines remove comments from the autocompletetion list.
# See http://smwforum.ontoprise.com/smwbugs/show_bug.cgi?id=11724 for the reason.
# Remove these lines or overwrite it in your LocalSettings.php if you do not want this.
###
global $smwhgDefaultACNamespaces;
if( is_array( $smwhgDefaultACNamespaces )
	&& !array_key_exists( -CE_COMMENT_NS, $smwhgDefaultACNamespaces ) )
{
	$smwhgDefaultACNamespaces[] = -CE_COMMENT_NS;
} else {
	$smwhgDefaultACNamespaces = array( -CE_COMMENT_NS );
}