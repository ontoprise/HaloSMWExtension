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
define('CE_VERSION', '1.0');

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
# Enable ArticleComments
# For more information visit: http://www.mediawiki.org/wiki/Extension:ArticleComments
###
$cegEnableArticleComments = true;

###
# Enable rating
# For more information visit: http://www.mediawiki.org/wiki/Extension:Rating_Bar
###
$cegEnableRating = true;

###
# Enable CurrentUsers
# For more information visit: http://www.mediawiki.org/wiki/Extension:CurrentUsers
###
$cegEnableCurrentUsers = true;

$wgHooks['BeforePageDisplay'][] = 'smwfCEAddHTMLHeader';

//--- credits (see "Special:Version") ---
global $wgExtensionCredits;

$wgExtensionCredits['other'][]= array(
        'name'=>'Collaboration',
        'version'=>CE_VERSION,
        'author'=>"Benjamin Langguth and others",
        'url'=>'http://smwforum.ontoprise.de',
        'description' => 'Some fancy collaboration tools.');

# A: ArticleComments
if ( $cegEnableArticleComments ) {
	###
	# Hides the commentForm until the "Make a comment" link is clicked
	# This i no longer needed because The commentForm is wrapped in a CollapsibleTable
	# see http://www.mediawiki.org/wiki/Manual:Collapsible_tables
	###
	$wgArticleCommentDefaults['hideForm'] = false;
	
	###
	# Show the comments inside the page, under the "Make a comment" link.
	# Also wrapped in a CollapsibleTable
	###
	$wgArticleCommentDefaults['displaycomments'] = true;
	
	###
	# Hides the comment text until the "Show comments" link is clicked
	# Also wrapped in a CollapsibleTable
	###
	$wgArticleCommentDefaults['hidecomments'] = false;
	
	###
	# Show a HTML input field for the poster's url in the commentForm 
	###
	$wgArticleCommentDefaults['showurlfield'] = false;
	
	include_once($cegIP.'/ArticleComments/ArticleComments.php');
	
	###
	# Add the comment tag to all pages of a specific namespace
	###
	$cegArticleCommentsOnNamespace = array(NS_MAIN);
	$wgHooks['ParserBeforeStrip'][] = 'ArticleComments::addCommentTag';
	
}
# B:RatingBar
if ( $cegEnableRating ) {
	###
	# see /RatingBar/config.php for more configuration options
	###
	include_once($cegIP.'/RatingBar/ratingbar.php');
}
# C: CurrentUser
if ( $cegEnableCurrentUsers ) {
	include_once($cegIP.'/CurrentUsers/CurrentUsers.php');
}

function smwfCEAddHTMLHeader(&$out) {
	global $cegScriptPath;
	// All scripts that have been added later on... 
	$out->addScript("<script type=\"text/javascript\" src=\"".$cegScriptPath .  "/scripts/CollapsibleTables.js\"></script>");
	// style definitions
	$out->addLink(array(
		'rel'   => 'stylesheet',
		'type'  => 'text/css',
		'media' => 'screen, projection',
		'href'  => $cegScriptPath . '/skins/common.css'
	));

	return true;
}