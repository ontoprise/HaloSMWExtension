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

//--- credits (see "Special:Version") ---
global $wgExtensionCredits;

$wgExtensionCredits['other'][]= array(
        'name'=>'Collaboration',
        'version'=>CE_VERSION,
        'author'=>"Benjamin Langguth and others",
        'url'=>'http://smwforum.ontoprise.de',
        'description' => 'Some fancy collaboration tools.'
);

global $cegIP, $cegScriptPath, $cegEnableComments, $cegEnableCurrentUsers;

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
# Enable Comments
###
$cegEnableComments = true;

###
# Enable CurrentUsers
###
$cegEnableCurrentUsers = false;


function enableCollaborationExtension() {
	global $wgExtensionFunctions, $cegEnableCollaborationExtension, $cegIP,
			$cegEnableComments, $cegEnableCurrentUsers;
	
	//so that other extensions like the gardening framework know about
	//the Collaboration-Extension
	$cegEnableCollaborationExtension = true;
	
	$wgAutoloadClasses['Collaboration'] = $cegIP . 'MyExtension_body.php';
	$wgExtensionMessagesFiles['Collaboration'] = $cegIP . 'MyExtension.i18n.php';
	$wgExtensionAliasesFiles['Collaboration'] = $cegIP . 'MyExtension.alias.php';
		
	#$wgExtensionFunctions[] = 'smwfSetupCEExtension';
	
	# A: Comments
	if ( $cegEnableComments ) {
		
		$cegCommentsNamespace = array(NS_MAIN);
		require_once($cegIP.'/specials/Comments/CE_Comments.php');
	
		require_once($cegIP. '/specials/Comments/CE_CommentsAjaxAccess.php');	
	
		//require the displayComments parser function
		#require_once("$cegIP/specials/Comments/CE_CommentsDisplayParserFunction.php");
		
		$wgAutoloadClasses['Collaboration'] = $cegIP . 'MyExtension_body.php';
		$wgHooks['BeforePageDisplay'][] = 'CEComments::smwfCEAddHTMLHeader';
		
		#$wgSpecialPages['MyExtension'] = 'MyExtension'; # Let MediaWiki know about your new special page.
	}
	
	# B: CurrentUser
	if ( $cegEnableCurrentUsers ) {
		include_once($cegIP.'/specials/CurrentUsers/CE_CurrentUsers.php');
	}
			
	
}

#$wgHooks['ParserBeforeStrip'][] = 'ArticleComments::addCommentTag';
#-> into DislpayParserFunction