<?php

/****************************************************************************
**
** This file is part of the Rating Bar extension for MediaWiki
** Copyright (C)2009
**                - Franck Dernoncourt <www.francky.me>
**                - PatheticCockroach <www.patheticcockroach.com>
**
** Home Page : http://www.wiki4games.com/index.php?title=Wiki4Games:RatingBar
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

// Config page where all main variables are set
// Important: some of the settings are likely to be **CASE-SENSITIVE** (passwords, filenames on Linux, etc)

// Database settings
$ratingbar_dbhost					= 'localhost';	// Usually 'localhost'
$ratingbar_dbuser					= '';	// Database user
$ratingbar_dbpass					= '';	// Database password
$ratingbar_dbname					= '';	// Copy value from $wgDBname
$table_prefix						= '';	// Copy value from $wgDBprefix

// Other important settings
$path_to_w4g_rb						= 'extensions/RatingBar/';	// extension path, relative to the path ofyour wiki's index.php
$ratingbar_tablename				= 'ratingbar';		// Table where the extension will store its data. Leaving to default value is fine.

// For advanced users with a very customized MW installation
$user_tablename						= 'user';			// Table where we check if the user exists. Default value in MediaWiki: user
$categorylinks_tablename			= 'categorylinks';	// Table where we check if a page belongs to a category. Default value in MediaWiki: categorylinks

// User rights
$allow_display_user_votes			= true;		// Allow any user to list another user's votes
$allow_display_user_own_votes_only	= false;	// Allow any user to see the list of his own votes
$allow_display_voters_for_one_page	= true;		// Allow listing voters for one page
$allow_display_latest_votes			= true;		// Allow listing latest votes

// To avoid server lag
$max_items							= 20; // Max number of items in an output table. If set to 0, then the max of items that can be output in a table is infinite.
$w4g_ratinglist_max_calls			= 25; // Max number of calls to w4g_ratinglist hook on the same page. If set to 0, then the user can do an infinite number of calls to w4g_ratinglist.

// Miscellaneous settings
$unique_check						= 'user'; // Identification method. 3 possibles values: 'user', 'ip', 'both'. Only 'user' is currently rock-solid.
$min_time_between_votes				= 24; // Minimum time in hours between 2 votes for the same page id from the same IP. If set to 0, one IP can only vote once for a page id. Useless if $unique_check='user'.
$max_votes_per_ip					= 10; // How many times the same IP can vote for the same page. If set to 0, no limit.
$items_name							= 'Video game'; // Default items' name visitors will be rating.
$add_special_page					= true; // Add a special page
$link_vote_not_logged_in			= 'index.php?title=Special:UserLogin'; // Link when voting if the user isn't logged in
$link_vote_not_registered			= 'index.php?title=Special:UserLogin&type=signup'; // Link when voting if the user isn't logged in
$w4g_rb_rating_max					= 100;	// The maximum rating. must be between 1 and 100. If 100, rating will be displayed as "x%". Otherwise it will be display as "x/$w4g_rb_rating_unit".
$w4g_rb_rating_decimals				= 0;	// Number of digits after the decimal point in average rating
$w4g_rb_bar_styles					= array("gradbar", "stars");	// A list of allowed bar styles, must be an array. The first item is the default value.
$w4g_rb_min_vote_count				= 1;	// Minimum amount of votes required to display an average rating.

// Page ID's special characters that create troubles with Javascript and PHP GETs if not properly formatted when being manipulated
// No need to change this unless you find other characters that need to be added
$stupid_characters					= array('&','#','%'); 
$stupid_characters_codes			= array('Ux26','Ux23','Ux25');

// Connect to the database. No need to change this.
$ratingbar_tablename = $table_prefix.$ratingbar_tablename;
$user_tablename = $table_prefix.$user_tablename;
$categorylinks_tablename = $table_prefix.$categorylinks_tablename;
$rating_conn = mysql_connect($ratingbar_dbhost, $ratingbar_dbuser, $ratingbar_dbpass) or die("Failed to connected to the database");

?>