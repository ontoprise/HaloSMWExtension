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
$smwgCEIP = $IP . '/extensions/Collaboration';;

###
# This is the path to your installation of CollaborationExtension as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$smwgCEScriptPath = $wgScriptPath . '/extensions/Collaboration';

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
include_once($smwgCEIP.'/ArticleComments/ArticleComments.php');

# B:RatingBar
include_once($smwgCEIP.'/RatingBar/ratingbar.php');

# C: CurrentUser
include_once($smwgCEIP.'/CurrentUsers/CurrentUsers.php');

function smwfCEAddHTMLHeader(&$out) {
	global $smwgCEScriptPath;
	// All scripts that have been added later on... 
	$out->addScript("<script type=\"text/javascript\" src=\"".$smwgCEScriptPath .  "/scripts/CollapsibleTables.js\"></script>");
	// style definitions
	$out->addLink(array(
		'rel'   => 'stylesheet',
		'type'  => 'text/css',
		'media' => 'screen, projection',
		'href'  => $smwgCEScriptPath . '/skins/common.css'
	));

	return true;
}

?>