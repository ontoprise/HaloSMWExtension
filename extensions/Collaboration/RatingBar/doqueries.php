<?php
// Load config
require_once ('config.php');

if($table_prefix!='') $table_prefix='_'.$table_prefix;
ini_set("session.name", $ratingbar_dbname.$table_prefix."_session");
session_start();
// Just RTFM ^^ http://fr.php.net/session

/**********************************************************************
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


header("Cache-Control: no-cache");
header("Pragma: nocache");


// Get and clean parameters
$action_id		= (isset($_GET['act'])) ? mysql_real_escape_string($_GET['act']) : "vote";
if( !in_array( $action_id, array("vote", "load") ) ) $action_id = "vote";
$uid			= (isset($_SESSION['wsUserID'])) ? intval($_SESSION['wsUserID']) : intval(-1);


// Get and clean $page_id (a bit more complicated than other parameters...)
$page_id = ($_GET['pid']);
$page_id=str_replace($stupid_characters_codes,$stupid_characters,$page_id);
$page_id=str_replace("Ux55","U",$page_id);
$page_id=mysql_real_escape_string($page_id);

// Initiating some variables
$response="";		// initiates output
$curtime=time();	// for recording time in MySQL queries
$canvote = true;	// will be changed to false if we meet a condition that disallows the person to vote
$too_many_votes_with_ip = false;	// will be change to true if user can't vote because of too many votes from their IP
$ip = $_SERVER['REMOTE_ADDR'];		// for a short IP variable

// Check miminum times between 2 votes for the same page id from the same IP.
if ( $min_time_between_votes > 0 ) $min_time = time() - ( $min_time_between_votes * 60 * 60 );
else $min_time = 0;


//  If $unique_check is set to 'both', we must also check if a non-logged in visitor doesn't use an IP already by a logged-in user.
if ( $uid <= 0 && $unique_check=='both') {
	$query3 = mysql_query("SELECT rating FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE ip='".$ip."' AND page_id='".$page_id."' AND user_id > 0;") or die("SQL query failed.");
	if (  ($line3 = mysql_fetch_array($query3) ) ) $canvote = false;
}

// Check if the same IP didn't vote  too many times.
if ( $max_votes_per_ip > 0 && $uid <= 0) {
	$query = mysql_query("SELECT count(*) FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE ip='".$ip."' AND page_id='".$page_id."';") or die("SQL query failed.");
	$line = mysql_fetch_array($query);
	if ( $line[0] > $max_votes_per_ip ) {
		$too_many_votes_with_ip = true; $canvote = false;
	}
	
}

// Record the visitor's vote (update or new entry)
if( $action_id=="vote" && $canvote && ($uid>0 || $unique_check=='both' || $unique_check=='ip') )
{

	// Get vote and fix out-of range values
	$vote_sent		= intval($_GET['vote']);
	if ($vote_sent > 100) $vote_sent=100;
	else if ($vote_sent < 0) $vote_sent=0;
	
	// Check if the visitor doesn't try to vote more than once. If yes, update the existing entry. If no, create a new entry.
	if ( $uid>0 ) $query = mysql_query("SELECT user_id FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE page_id='".$page_id."' AND user_id='".$uid."';") or die("SQL query failed.");
	else {
		$query = mysql_query("SELECT ip FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE time >= ".$min_time." AND page_id='".$page_id."' AND ip='".$ip."';") or die("SQL query failed.");
	}
	if($line = mysql_fetch_array($query))
	{
		if ( $uid>0 ) $action = "UPDATE ".$ratingbar_dbname.".".$ratingbar_tablename." SET rating='".$vote_sent."',time='".$curtime."', ip='".$ip."' WHERE page_id='".$page_id."' AND user_id='".$uid."';";
		else $action = "UPDATE ".$ratingbar_dbname.".".$ratingbar_tablename." SET rating='".$vote_sent."',time='".$curtime."' WHERE time >= ".$min_time." AND page_id='".$page_id."' AND user_id='".$uid."' AND ip='".$ip."';";
	}
	else
	{
		$action = "INSERT INTO ".$ratingbar_dbname.".".$ratingbar_tablename." SET user_id='".$uid."',rating='".$vote_sent."',page_id='".$page_id."',time='".$curtime."',ip='".$ip."';";
	}
	$result = mysql_query($action) or die("SQL query failed.");
}

// What's the new global rating?
$query = mysql_query("SELECT AVG(rating) FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE page_id='".$page_id."';") or die("SQL query failed.");
$line = mysql_fetch_array($query);
$score = $line[0];
if($w4g_rb_rating_max==100) $score=$score*100;
$score = round($score/$w4g_rb_rating_max,$w4g_rb_rating_decimals);
$rating_unit= ($w4g_rb_rating_max==100) ? '%' : '/'.$w4g_rb_rating_max;

// How many users voted?
$query = mysql_query("SELECT count(*) FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE page_id='".$page_id."';") or die("SQL query failed.");
$line = mysql_fetch_array($query);
$vote_count = $line[0];

// Output
if(!isset($w4g_rb_min_vote_count)) $w4g_rb_min_vote_count = 0;
if($vote_count>=$w4g_rb_min_vote_count)
	{
	$plural = ($vote_count>1) ? 's' : '';
	$response .= 'Current user rating: <b>'.$score.$rating_unit.' </b>';
	$response .= '('.$vote_count.' vote'.$plural.') <br/>';
	}
else if($vote_count==0) $response .= 'Nobody voted on this yet.<br/>';
else $response .= 'To few votes (needs '.$w4g_rb_min_vote_count-$vote_count.' more).<br/>';


// Display the new vote
if ( $too_many_votes_with_ip ) $response .= 'Too many votes with your IP.';

elseif( $uid>0 or $unique_check=='ip' or $unique_check=='both')
{
	if($action_id=="vote" && $canvote)
		$response .= "You voted <b><span id=\"w4g_rating_value\">".$vote_sent.'</span>%</b>';
	else {
		if ( $uid > 0 && $unique_check!='ip') $query = mysql_query("SELECT rating FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE page_id='".$page_id."' AND user_id='".$uid."';") or die("SQL query failed.");
		elseif ( $unique_check=='ip')  $query = mysql_query("SELECT rating FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE page_id='".$page_id."' AND ip='".$ip."';") or die("SQL query failed.");
		
		if ( $line = mysql_fetch_array($query) )
			$response .= 'You voted <b><span id="w4g_rating_value">'.$line['rating'].'</span>%</b>';
		
		// Check if the user didn't vote just before logging in. It's only a problem if $unique_check is set to 'both'.
		else if ( $uid > 0 && $unique_check=='both')
			{
			$query2 = mysql_query("SELECT rating FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE time >= ".$min_time." AND ip='".$ip."' AND page_id='".$page_id."' AND user_id=0 ORDER BY time DESC;") or die("SQL query failed.");
			if ( $line2 = mysql_fetch_array($query2) )
			$response .= 'You voted <b><span id="w4g_rating_value">'.$line2['rating'].'</span>%</b> before logging in.';
			else $response .= 'You didn&#39;t vote on this yet.';
			}
		//  If $unique_check is set to 'both', we must also check if a non-logged in visitor doesn't use an IP already by a logged-in user.
		else if ( $uid <= 0 && $unique_check=='both')
			{
			$query3 = mysql_query("SELECT rating FROM ".$ratingbar_dbname.".".$ratingbar_tablename." WHERE ip='".$ip."' AND page_id='".$page_id."' AND user_id > 0 ORDER BY time DESC;") or die("SQL query failed.");
			if ( $line3 = mysql_fetch_array($query3) ) 
				$response .= 'A logged in user voted <b><span id="w4g_rating_value">'.$line3['rating'].'</span>%</b> with the same IP.';
			else $response .= 'You didn&#39;t vote on this yet.';
			}
		else $response .= 'You didn&#39;t vote on this yet.';
	}
}
else $response .= '<span id="w4g_rating_value" style="display:none;">0</span>You must <a href="'.$link_vote_not_logged_in.'">log in</a> or <a href="'.$link_vote_not_registered.'">register</a> to vote.';

// Output the response
echo $response;


?>