<?php
/*
 * Created on 24.03.2009
 *
 * Author: Benjamin
 */

//this extension does only work if the Halo extension is enabled
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the RichMedia extension. It is not a valid entry point.\n" );
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
$wgArticleCommentDefaults['hideForm']=false;
$wgArticleCommentDefaults['displaycomments'] = true;
$wgArticleCommentDefaults['hidecomments'] = false;
$wgArticleCommentDefaults['showurlfield'] = false;
include_once($cegIP.'/ArticleComments/ArticleComments.php');

# B:RatingBar
include_once($cegIP.'/RatingBar/ratingbar.php');

# C: CurrentUser
include_once($cegIP.'/CurrentUsers/CurrentUsers.php');

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

?>