<?php

/****************************************************************************
**
** This file is part of the Rating Bar extension for MediaWiki
** Copyright (C)2009
**                - Franck Dernoncourt <www.francky.me>
**                - PatheticCockroach <www.patheticcockroach.com>
**
** Home Page : http://www.wiki4games.com/Wiki4Games:RatingBar
**
** This program is free software; you can redistribute it and/or
** modify it under the terms of the GNU General Public License
** as published by the Free Software Foundation; either
** version 3 of the License, or (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
** <http://www.gnu.org/licenses/>
*********************************************************************/


// Check to make sure we're actually in MediaWiki.
if (!defined('MEDIAWIKI')) die('This file is part of MediaWiki. It is not a valid entry point.');

// Get some variables
// require ('config.php');
	
// Credits stuff
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Rating Bar',
	'version' => '1.1-b1',
	'author' => array( '[http://www.francky.me Franck Dernoncourt]', '[http://www.patheticcockroach.com PatheticCockroach]' ),
	'url' => 'http://www.wiki4games.com/index.php?title=Wiki4Games:RatingBar',
	'description' => 'Display a rating bar using Ajax.',
);

// Tag creation
$wgExtensionFunctions[] = "wfRatingBar";
function wfRatingBar() {
    global $wgParser;
    $wgParser->setHook( "w4g_ratingbar", "render_w4g_ratingbar" );
	$wgParser->setHook( "w4g_ratinglist", "render_w4g_ratinglist" );
	$wgParser->setHook( "w4g_ratingraw", "render_w4g_ratingraw" );
}

// Get directory path
$w4g_rb_dir = dirname(__FILE__) . '/';

// Add the special page
if ( $add_special_page ) {
	$wgAutoloadClasses['Ratings'] 		= $w4g_rb_dir . 'specialpage.php'; // Tell MediaWiki to load the extension body.
	$wgSpecialPageGroups['Ratings'] 	= 'wiki';
	$wgExtensionMessagesFiles['Ratings']= $w4g_rb_dir . 'ratingbar.i18n.php';
	$wgSpecialPages['Ratings'] 			= 'Ratings'; // Let MediaWiki know about your the special page.
}

// This boolean is set to false it there is no ratingbar on the page, true if there is one. 
// It prevents from having strictly more than 1 rating bar on the same page.
$alreadyratingbar = false;

// Set the counter of the calls to w4g_ratinglist hook to 0. 
// It will be used to check if a user doesn't use the w4g_ratinglist hook on the same page more than he's allowed by the administrator, so as to avoid server lag.
$w4g_ratinglist_calls = 0;

// Make a link to another page of the wiki using MediaWiki's API
function MakeLink( $page_namespace, $page_title ) {
	
	// Global object variables
	global $wgUser; // For $wgUser->getSkin();
	
	$title = Title::makeTitleSafe($page_namespace, $page_title);
	$skin = $wgUser->getSkin();
	if( !is_null( $title ) ) {
		$link = $skin->makeKnownLinkObj( $title );
	} else {
		$link = "There is no <i>" . htmlspecialchars( $page_title ) . "</i> in namespace <i>" . htmlspecialchars( $page_namespace )."</i>";
	}
	
	return $link;
}

// Make a link to a user page of the wiki using MediaWiki's API
function MakeLinkUser( $user_id, $user_name, $displayusertools = false ) {

	// Global object variables
	global $wgUser; // For $wgUser->getSkin();
	$skin = $wgUser->getSkin();
	
	// Get link to the user
	$link = $skin->userLink( $user_id, $user_name );
	
	// Generate user tools
	if ( $displayusertools ) $link .= $skin->userToolLinks( $user_id, $user_name );
	
	return $link;
}


// The callback function for converting the input text to HTML output
function render_w4g_ratingraw( $input, $argv, $parser ) {
		
	// Get some variables
	require ('config.php');
	
	// Disable cache
	header("Cache-Control: no-cache");
	header("Pragma: nocache");
	$parser->disableCache();
	
	// Add CSS => David: not needed here I think
	// $parser->mOutput->addHeadItem('<link rel="stylesheet" type="text/css" href="'.$path_to_w4g_rb.'styles.css"/>');
	
	// Global object variables
	global $wgOut; // For $wgOut->getPageTitle();
	
	// Gets $page_id
	if( isset( $argv['idpage'] ) && '{{FULLPAGENAME}}' != $argv['idpage'] )	{
		$page_id = $argv['idpage'];
	} else {
		$page_id = $parser->getTitle();
	}
	
	// What's the max rate?
	if( !isset( $argv['maxrate'] ) || intval($argv['maxrate']) <= 0 )	$maxrate = 100;
	else 																$maxrate = intval($argv['maxrate']);
	
	// Cleans $page_id
	$page_id=mysql_real_escape_string($page_id);
	
	// What's the current rating?
	$query = mysql_query("SELECT AVG(rating) rating FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE page_id='".$page_id."';") or die("Sorry, MySQL query failed.");
	$line = mysql_fetch_array($query);
	$average_rating = round(intval($line['rating']) * $maxrate / 100);
	
	$output = $average_rating;

	return $output;
}


// The callback function for converting the input text to HTML output
function render_w4g_ratinglist( $input, $argv, $parser ) {

	// Get some variables
	require ('config.php');

	// Disable cache
	header("Cache-Control: no-cache");
	header("Pragma: nocache");
	$parser->disableCache();
	
	// Global object variables
	global $wgUser; // For $wgUser->getID();
	
	// Check if the number of calls to w4g_ratinglist hook isn't too high.
	global $w4g_ratinglist_calls;
	if($w4g_ratinglist_calls==0)
		$parser->mOutput->addHeadItem('<link rel="stylesheet" type="text/css" href="'.$path_to_w4g_rb.'styles.css"/>');
	$w4g_ratinglist_calls += 1;
	if ( $w4g_ratinglist_max_calls != 0 && $w4g_ratinglist_calls > $w4g_ratinglist_max_calls )
		return '<span class="w4g_ratinglist-error">Sorry, you are not allowed to use w4g_ratinglist more than '.$w4g_ratinglist_max_calls.' on the same page.</span><br/>';
	
	// Get the user's ID and name
	$uid = $wgUser->getID();
	$user_name = $wgUser->getName();
	
	// Does the user want to display titles? 
	if( isset( $argv['notitle'] ) )	$displaytitle = false;
	else 							$displaytitle = true;
	
	// Does the user want a sortable table ?
	if( isset( $argv['nosort'] ) )	$sortable = "";
	else 							$sortable = "sortable";
			
	/* Check if the user only wants page ID that are in the main namespace
	if( isset( $argv['mainnamespace'] ) )	$mainnamespace = true;
	else 									$mainnamespace = false;
	*/
	
	// Check if the user has specified a category
	if( isset( $argv['category'] ) )	{
		$category = $argv['category'];
		// Clean category name
		$category=str_replace(" ","_",$category); // Windows games should become Windows_games.
		$category=mysql_real_escape_string($category);
	} else {
		$category = "";
	}
	
	
	// IF THE USER WANTS TO DISPLAY THE LATEST VOTES
	if( isset( $argv['latestvotes'] ) )	{
		$latestvotes = intval($argv['latestvotes']);
		
		// Check if we don't display more items than the maximum numbers of items allowed by the administrator.
		if ( $max_items != 0 && $latestvotes > $max_items ) $latestvotes = $max_items;
		
		// Check if there is a specified period of time
		if( isset( $argv['numberofdays'] ) &&  $argv['numberofdays'] > 0)	{
			$numberofdays = intval($argv['numberofdays']);
			$starttime = time() - ( $numberofdays * 24 * 60 * 60 );
		} else {
			$starttime = 0;
		}
		
		// Check if the user has the right to list all voters for one page
		if ( !$allow_display_latest_votes) return '<span class="w4g_ratinglist-error">Sorry, you are not allowed to list the latest votes.</span><br/>';
		
		// Queries
		if ( $category == "" ) {
			if ( $latestvotes > 0 ) $query = mysql_query("SELECT users.user_name, ratingbar.rating, ratingbar.time, users.user_id, ratingbar.page_id, ratingbar.page_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users WHERE ratingbar.user_id = users.user_id ORDER BY time DESC LIMIT 0, ".$latestvotes.";") or die("SQL query failed.");
			else $query = mysql_query("SELECT users.user_name, ratingbar.rating, ratingbar.time, users.user_id, ratingbar.page_id, ratingbar.page_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users WHERE ratingbar.user_id = users.user_id ORDER BY time DESC;") or die("SQL query failed.");
		} else {
			if ( $latestvotes > 0 ) $query = mysql_query("SELECT users.user_name, ratingbar.rating, ratingbar.time, users.user_id, ratingbar.page_id, ratingbar.page_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users, ".$ratingbar_dbname.".".$categorylinks_tablename." categorylinks WHERE categorylinks.cl_to = '".$category."' AND categorylinks.cl_sortkey = ratingbar.page_id AND ratingbar.user_id = users.user_id ORDER BY time DESC LIMIT 0, ".$latestvotes.";") or die("SQL query failed.");
			else $query = mysql_query("SELECT users.user_name, ratingbar.rating, ratingbar.time, users.user_id, ratingbar.page_id, ratingbar.page_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users, ".$ratingbar_dbname.".".$categorylinks_tablename." categorylinks WHERE categorylinks.cl_to = '".$category."' AND categorylinks.cl_sortkey = ratingbar.page_id AND ratingbar.user_id = users.user_id ORDER BY time DESC;") or die("SQL query failed.");
		}
		
		// Display the title
		$output = "";
		if ( $displaytitle ) {
			$output .= '<b>';
			if ( $starttime != 0 && $latestvotes="" ) {
				if ( $numberofdays == 1 ) $output .= 'Last 24 hours votes';
				if ( $numberofdays > 1 ) $output .= 'Last '.$numberofdays.' days votes';
			} else {
				 $output .= 'Latest votes ('.$latestvotes.')';
			}
			$output .= '</b>';
		}
		
		// Display the table
		$output .= "<table class=\"wikitable w4g_ratinglist ".$sortable."\" >";
		$output .= "<tr class='w4g_ratinglist-headercell'>";
		$output .= "<td>Time</td>";
		$output .= "<td>".$items_name."</td>";
		$output .= "<td>Rating</td>";
		$output .= "<td>User</td>";
		$output .= "</tr>";
		while($line = mysql_fetch_array($query)) {
			$output .= "<tr class='w4g_ratinglist-contentcell'><td>".date("F j, Y, g:i a",($line['time']))."</td>";
			$output .= "<td>".MakeLink( 'Main', $line['page_id'])."</td></td>";
			$output .= "<td>".intval($line['rating'])."%</td>";
			$output .= "<td>".MakeLinkUser($line['user_id'], $line['user_name'])."</td></tr>";
		}
		$output .= "</table>";
		return $output;
		
	} 
	
	
	// IF THE USER WANTS TO DISPLAY WHO VOTED FOR ONE PAGE
	if( isset( $argv['idpage'] ) && isset( $argv['displayvoters'] ))	{
		$page_id=mysql_real_escape_string($argv['idpage']);
		
		// Check if the user has the right to list all voters for one page
		if ( !$allow_display_voters_for_one_page) return '<span class="w4g_ratinglist-error">Sorry, you are not allowed to list the users who voted for this page.</span>';
		
		// If a number of items is specified
		if( isset( $argv['numberofitems'] ) )	{
			$numberofitems = intval($argv['numberofitems']);
		} else {
			$numberofitems = 0;
		}
		
		// Check if we don't display more items than the maximum numbers of items allowed by the administrator.
		if ( ( $max_items != 0 && $numberofitems > $max_items ) || $numberofitems <= 0) $numberofitems = $max_items;
		
		// Check if there is a specified period of time
		if( isset( $argv['numberofdays'] ) )	{
			$numberofdays = intval($argv['numberofdays']);
			$starttime = time() - ( $numberofdays * 24 * 60 * 60 );
		} else {
			$starttime = 0;
		}
		
		// Do query
		if ( $numberofitems > 0 ) $query = mysql_query("SELECT users.user_name, ratingbar.rating, ratingbar.time, users.user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users WHERE page_id='".$page_id."' AND ratingbar.time >= ".$starttime." AND ratingbar.user_id = users.user_id GROUP BY ratingbar.user_id ORDER BY time DESC LIMIT 0, ".$numberofitems.";") or die("MySQL query failed");
		else $query = mysql_query("SELECT users.user_name, ratingbar.rating, ratingbar.time, users.user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users WHERE page_id='".$page_id."' AND ratingbar.time >= ".$starttime." AND ratingbar.user_id = users.user_id GROUP BY ratingbar.user_id ORDER BY time DESC ;") or die("MySQL query failed");
		
		// Display the title
		$output = "";
		if ( $displaytitle ) $output .= '<b>Voters for '.$page_id.'</b>';
		
		// Display the table
		$output .= "<table class=\"wikitable w4g_ratinglist ".$sortable."\" >";
		$output .= "<tr class='w4g_ratinglist-headercell'>";
		$output .= "<td>User</td>";
		$output .= "<td>Rating</td>";
		$output .= "<td>Time</td>";
		$output .= "</tr>";
		while($line = mysql_fetch_array($query)) {
			$output .= "<tr class='w4g_ratinglist-contentcell'><td>".MakeLinkUser($line['user_id'], $line['user_name'])."</td>";
			$output .= "<td>".intval($line['rating'])."%</td>";
			$output .= "<td>".date("F j, Y, g:i a",($line['time']))."</td></tr>";
		}
		$output .= "</table>";
		
		return $output;
	} 
	
	
	// IF THE USER WANTS TO DISPLAY THE TOP VOTERS
	if( isset( $argv['topvoters'] ) )	{
		$topvoters = intval($argv['topvoters']);
		
		// Check if we don't display more items than the maximum numbers of items allowed by the administrator.
		if ( ( $max_items != 0 && $topvoters > $max_items ) || $topvoters <= 0) $topvoters = $max_items;
		
		// Check if there is a specified period of time
		if( isset( $argv['numberofdays'] ) )	{
			$numberofdays = intval($argv['numberofdays']);
			$starttime = time() - ( $numberofdays * 24 * 60 * 60 );
		} else {
			$starttime = 0;
		}

		// Query
		if ( $category == "" ) {
			if ( $topvoters > 0 ) $query = mysql_query("SELECT users.user_name, avg(rating) average, count(*) cnt, users.user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users WHERE ratingbar.time >= ".$starttime." AND ratingbar.user_id = users.user_id GROUP BY ratingbar.user_id ORDER BY count(rating) DESC LIMIT 0, ".$topvoters.";") or die("SQL query failed.");
			else $query = mysql_query("SELECT users.user_name, avg(rating) average, count(*) cnt, users.user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users WHERE ratingbar.time >= ".$starttime." AND ratingbar.user_id = users.user_id GROUP BY ratingbar.user_id ORDER BY count(rating) DESC ;") or die("SQL query failed.");
		} else {
			if ( $topvoters > 0 ) $query = mysql_query("SELECT users.user_name, avg(rating) average, count(*) cnt, users.user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users, ".$ratingbar_dbname.".".$categorylinks_tablename." categorylinks WHERE categorylinks.cl_to = '".$category."' AND categorylinks.cl_sortkey = ratingbar.page_id AND ratingbar.time >= ".$starttime." AND ratingbar.user_id = users.user_id GROUP BY ratingbar.user_id ORDER BY count(rating) DESC LIMIT 0, ".$topvoters.";") or die("SQL query failed.");
			else $query = mysql_query("SELECT users.user_name, avg(rating) average, count(*) cnt, users.user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$user_tablename." users, ".$ratingbar_dbname.".".$categorylinks_tablename." categorylinks WHERE categorylinks.cl_to = '".$category."' AND categorylinks.cl_sortkey = ratingbar.page_id AND ratingbar.time >= ".$starttime." AND ratingbar.user_id = users.user_id GROUP BY ratingbar.user_id ORDER BY count(rating) DESC ;") or die("SQL query failed.");
		}
		
		// Display the title
		$output = "";
		if ( $displaytitle ) {
			$output .= '<b>';
			if ( $topvoters > 0 ) $output .= 'Top '.$topvoters.' voters ';
			else $output .= 'Top voters ';
			
			if ( $starttime != 0 ) {
				if ( $numberofdays == 1 ) $output .= 'over the last 24 hours';
				if ( $numberofdays > 1 ) $output .= 'over the last '.$numberofdays.' days';
			} else {
				$output .= 'all time';
			}
			$output .= '</b>';
		}
		
		// Display the table
		$output .= "<table class=\"wikitable w4g_ratinglist ".$sortable."\" >";
		$output .= "<tr class='w4g_ratinglist-headercell'>";
		$output .= "<td>User</td>";
		$output .= "<td>Average rating</td>";
		$output .= "<td>Votes</td>";
		$output .= "</tr>";
		while($line = mysql_fetch_array($query)) {
			$output .= "<tr class='w4g_ratinglist-contentcell'><td>".MakeLinkUser($line['user_id'], $line['user_name'])."</td>";
			$output .= "<td>".intval($line['average'])."%</td>";
			$output .= "<td>".intval($line['cnt'])."</td></tr>";
		}
		$output .= "</table>";
		return $output;
	} 
	
	
	// IF THE USER WANTS TO DISPLAY THE TOP RATED PAGES
	if( isset( $argv['numberofitems'] ) and !isset( $argv['user'] ) )	{
		$numberofitems = intval($argv['numberofitems']);
		
		// Check if we don't display more items than the maximum numbers of items allowed by the administrator.
		if ( ( $max_items != 0 && $numberofitems > $max_items ) || $numberofitems <= 0) $numberofitems = $max_items;
		
		// Check if there is a specified period of time
		if( isset( $argv['numberofdays'] ) &&  $argv['numberofdays'] > 0)	{
			$numberofdays = intval($argv['numberofdays']);
			$starttime = time() - ( $numberofdays * 24 * 60 * 60 );
		} else {
			$starttime = 0;
		}
		
		// Check if there is enough votes to display the votes
		if( isset( $argv['minimumvotenumber'] ) )	{
			$minimumvotenumber = intval($argv['minimumvotenumber']);
		} else {
			$minimumvotenumber = 0;
		}
		
		// Check if the user wants to display the number of votes
		if( isset( $argv['hidenumberofvotes'] ) )	{
			$hidenumberofvotes = true;
		} else {
			$hidenumberofvotes = false;
		}
		
		// Query
		if ( $category == "" ) $query = mysql_query("SELECT avg(rating) average, page_id, count(*) cnt FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE time >= ".$starttime." GROUP BY page_id HAVING count(*) >= ".$minimumvotenumber." ORDER BY avg(rating) DESC LIMIT 0, ".$numberofitems.";") or die("SQL query failed.");
		else $query = mysql_query("SELECT avg(ratingbar.rating) average, ratingbar.page_id, count(*) cnt FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$categorylinks_tablename." categorylinks WHERE categorylinks.cl_to = '".$category."' AND categorylinks.cl_sortkey = ratingbar.page_id AND ratingbar.time >= ".$starttime." GROUP BY ratingbar.page_id HAVING count(*) >= ".$minimumvotenumber." ORDER BY avg(ratingbar.rating) DESC LIMIT 0, ".$numberofitems.";") or die("SQL query failed.");
		
		// Display the title
		$output = "";
		if ( $displaytitle ) {
		$output .= '<b>';
			if ( $starttime != 0 ) {
				if ( $numberofdays == 1 ) $output .= 'Last 24 hours';
				if ( $numberofdays > 1 ) $output .= 'Last '.$numberofdays.' days';
			} else {
				 $output .= 'All time';
			}
			$output .= ' (Top '.$numberofitems.')';
			$output .= '</b>';
		}
		
		// Display the table
		$output .= "<table class=\"wikitable w4g_ratinglist ".$sortable."\" >";
		$output .= "<tr class='w4g_ratinglist-headercell'>";
		$output .= "<td>".$items_name."</td>";
		$output .= "<td>Rating</td>";
		if ( !$hidenumberofvotes) $output .= "<td>Votes #</td>";
		$output .= "</tr>";
		while($line = mysql_fetch_array($query)) {
			$output .= "<tr class='w4g_ratinglist-contentcell'><td>".MakeLink( 'Main', $line['page_id'])."</td>";
			$output .= "<td>".intval($line['average'])."%</td>";
			if ( !$hidenumberofvotes) $output .= "<td>".intval($line['cnt'])."</td>";
			$output .= "</tr>";
		}
		$output .= "</table>";
		return $output;
	} 
	
	
	// IF THE USER WANTS TO DISPLAY A USER'S VOTES
	if( isset( $argv['user'] ) )	{
		$user = $argv['user'];
		
		// If a number of items is specified
		if( isset( $argv['numberofitems'] ) )	{
			$numberofitems = intval($argv['numberofitems']);
		} else {
			$numberofitems = 0;
		}
		
		// Check if we don't display more items than the maximum numbers of items allowed by the administrator.
		if ( ( $max_items != 0 && $numberofitems > $max_items ) || $numberofitems <= 0) $numberofitems = $max_items;
		
		// If a number of items is specified
		if( isset( $argv['displaytimestamp'] ) )	{
			$displaytimestamp = true;
		} else {
			$displaytimestamp = false;
		}
		
		// Check if there is a specified period of time
		if( isset( $argv['numberofdays'] ) )	{
			$numberofdays = intval($argv['numberofdays']);
			$starttime = time() - ( $numberofdays * 24 * 60 * 60 );
		} else {
			$starttime = 0;
		}
		
		// Check if the administrator allows user vote listing.
		if ( !$allow_display_user_votes ) return '<span class="w4g_ratinglist-error">Sorry, the administrator has disabled user\'s vote listing.</span>';
		if ( $allow_display_user_own_votes_only && $user!= $user_name ) return '<span class="w4g_ratinglist-error">Sorry, you are not allowed to see another user\'s votes.</span>';
		$user_name = $user;
		
		// Does the user exist?
		$query = mysql_query("SELECT user_id FROM ".$ratingbar_dbname.".".$user_tablename." WHERE user_name='".$user_name."';") or die("SQL query failed.");
		if ( mysql_num_rows($query) == 0 ) return 'The user '.$user_name.' doesn\'t exist.'; 
		$line = mysql_fetch_array($query);
		$uid = $line[0];
		
		// Query
		if ( $category == "" ) {
			if ( $numberofitems > 0 ) $query = mysql_query("SELECT ratingbar.page_id, ratingbar.rating, ratingbar.time, ratingbar.user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar WHERE user_id=".$uid." AND time >= ".$starttime." ORDER BY rating DESC LIMIT 0, ".$numberofitems.";") or die("SQL query failed.");
			else $query = mysql_query("SELECT page_id, rating, time, user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar WHERE user_id=".$uid." AND time >= ".$starttime." ORDER BY rating DESC;") or die("SQL query failed.");
		} else {
			if ( $numberofitems > 0 ) $query = mysql_query("SELECT ratingbar.page_id, ratingbar.rating FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$categorylinks_tablename." categorylinks WHERE categorylinks.cl_to = '".$category."' AND categorylinks.cl_sortkey = ratingbar.page_id AND ratingbar.user_id=".$uid." AND ratingbar.time >= ".$starttime." ORDER BY ratingbar.rating DESC LIMIT 0, ".$numberofitems.";") or die("SQL query failed.");
			else $query = mysql_query("SELECT ratingbar.page_id, ratingbar.rating, ratingbar.time, ratingbar.user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." ratingbar, ".$ratingbar_dbname.".".$categorylinks_tablename." categorylinks WHERE categorylinks.cl_to = '".$category."' AND categorylinks.cl_sortkey = ratingbar.page_id AND ratingbar.user_id=".$uid." AND ratingbar.time >= ".$starttime." ORDER BY ratingbar.rating DESC;") or die("SQL query failed.");
		}
		
		// Display the title
		$output = "";
		if ( $displaytitle ) {
			$output = MakeLinkUser($line['user_id'], $user_name);
			$output = "<b>".$output."'s votes</b>";
		}
		$output .= "<table class=\"wikitable w4g_ratinglist ".$sortable."\" >";
		$output .= "<tr class='w4g_ratinglist-headercell'>";
		$output .= "<td>".$items_name."</td>";
		$output .= "<td>User's vote</td>";
		if ( $displaytimestamp ) $output .= "<td>Voting time</td>";
		$output .= "</tr>";
		$ncount = 0; // resets the count
		while($line = mysql_fetch_array($query)) {
			$output .= "<tr class='w4g_ratinglist-contentcell'><td>".MakeLink( 'Main', $line['page_id'] )."</td>";
			$output .= "<td>".intval($line['rating'])."%</td>"; 
			if ( $displaytimestamp ) $output .= '<td>'.date("F j, Y, g:i a",($line['time'])).'</td>';
			$output .= "</tr>";
		}
		$output .= "</table>";
		return $output;
	} 
	
	return '<span class="w4g_ratinglist-error">Something\'s wrong is your parameters.</span>';
}

// The callback function for converting the input text to HTML output
function render_w4g_ratingbar( $input, $argv, $parser ) {
	
	// Check if there isn't any other rating bar on the same page
	global $alreadyratingbar;
	if ( $alreadyratingbar ) return '<span class="w4g_ratinglist-error">You can\'t display over 1 rating bar on the same page.</br></span>';
	$alreadyratingbar = true;
	
	// Global object variables
	global $wgUser;		// For $wgUser->getID();
	global $wgOut;		// For $wgOut->getPageTitle();
	
	// Get some variables
	require ('config.php');
	
	// Add CSS and Javscript
	$parser->mOutput->addHeadItem('<link rel="stylesheet" type="text/css" href="'.$path_to_w4g_rb.'styles.css"/>');
	$parser->mOutput->addHeadItem('<link rel="stylesheet" type="text/css" href="'.$path_to_w4g_rb.'ratingstars.css"/>');
	$parser->mOutput->addHeadItem('<script type="text/javascript" src="'.$path_to_w4g_rb.'script.js"></script>');
	
	// Gets $page_id
	if( isset( $argv['idpage'] ) && '{{FULLPAGENAME}}' != $argv['idpage'] )	{
		$page_id = $argv['idpage'];
	} else {
		$page_id = $parser->getTitle();
	}
	// Cleans $page_id for not breaking GET queries
	$page_id_js=str_replace("U","Ux55",$page_id);
	$page_id_js=mysql_real_escape_string(str_replace($stupid_characters,$stupid_characters_codes,$page_id_js));
	// Cleans $page_id for use in MySQL queries - DO NOT confuse with $page_id_js afterwards!
	$page_id=mysql_real_escape_string($page_id);
	
	// Get rating bar style
	$w4g_rb_bar_styles = isset($w4g_rb_bar_styles) ? $w4g_rb_bar_styles : array("gradbar", "stars");
	$w4g_rb_barstyle = (isset($argv['style'])) ? mysql_real_escape_string($argv['style']) : "gradbar";
	if( !in_array($w4g_rb_barstyle, $w4g_rb_bar_styles) ) $w4g_rb_barstyle = $w4g_rb_bar_styles[0];
	// === TEMPORARY: DISABLE stars STYLE ===
	//$w4g_rb_barstyle = "gradbar";
	
	// Initiating some variables
	$output="";
	$user_rating=0;
	$ip = $_SERVER['REMOTE_ADDR'];
	
	
	// What's the current rating?
	$query = mysql_query("SELECT AVG(rating) FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE page_id='".$page_id."';") or die("Sorry, MySQL query failed.");
	$line = mysql_fetch_array($query);
	$average_rating = intval($line[0]);
	
	$output .= '<span id="w4g_rb_area">'; // Open the AJAX field
	$output .= 'Current user rating: <b>'.$average_rating.'% </b>';
	
	// How many users voted?
	$query = mysql_query("SELECT count(*) FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE page_id='".$page_id."';") or die("MySQL query failed");
	$line = mysql_fetch_array($query);
	$output .= '('.$line[0].' votes) <br/>';
		
	$output .= '</span>'; // Close the AJAX field
	
	// You MUST leave the line breaks in $output as they are, otherwise MediaWiki won't be able to display the rating bar in a table. (Parsing error: a <p> tag will otherwise be added AFTER <script type="text/javascript">, causing trouble)
	$output .= ' 
<script type="text/javascript">
//<![CDATA[
var average_rating='.$average_rating.';
var user_rating=0;
var pid="'.$page_id_js.'";
var base_query_url="'.$path_to_w4g_rb.'doqueries.php";
query2page(base_query_url+"?act=load\x26pid="+pid,"w4g_rb_area",2,"'.$w4g_rb_barstyle.'");
var query_url = base_query_url+"?pid="+pid;
//]]>
</script>
';

switch ($w4g_rb_barstyle)
{
	case "stars":
		$output .= '<div class="w4g_rb_starbox" id="w4g_rb_starbox1" onmouseout="updateStars(\'w4g_rb_starbox1\',user_rating)">
<span class="w4g_rb_nojs">&nbsp;You need to enable Javascript to vote</span>
</div>
<script type="text/javascript">
//<![CDATA[
loadStars("w4g_rb_starbox1");
updateStars("w4g_rb_starbox1",average_rating);
//]]>
</script>
';
		break;
	case "stars_old":
		$output .='<div class="ratingblock">
<div id="unit_long1">
<ul id="unit_ul1" class="unit-rating" style="width:150px;">
<li class="current-rating" style="width:109px;">Currently 3.64/5</li>';
	for($i=1;$i<=5;$i++)
	{
		$output.='<li><a onclick="user_rating='.intval($i*20).';query2page(\''.$path_to_w4g_rb.'doqueries.php?pid='.$page_id_js.'&vote='.intval($i*20).'\',\'w4g_rb_area\')" title="'.$i.' out of 5" class="r'.$i.'-unit rater" rel="nofollow">'.$i.'</a></li>';
	}
	$output .='</ul>
</div></div>';
		break;
	default:
	case "gradbar":
		$output .='
<div id="rating_box">
<div id="rating_target" onmouseout="updatebox(\'rating_target\',user_rating)"><span class="w4g_rb_nojs">&nbsp;You need to enable Javascript to vote</span>
</div>
<div id="rating_text"></div>
</div>
<script type="text/javascript">
//<![CDATA[
loadbox("rating_target");
updatebox("rating_target",average_rating);
//]]>
</script>
';
		break;
}

	return $output;
}