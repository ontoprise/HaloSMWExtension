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

define('SMW_CE_VERSION', '1.0');

global $smwgCEIP;
$smwgCEIP = $IP . '/extensions/Collaboration';;
$smwgCEScriptPath = $wgScriptPath . '/extensions/Collaboration';

$wgHooks['BeforePageDisplay'][] = 'smwfCEAddHTMLHeader';

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