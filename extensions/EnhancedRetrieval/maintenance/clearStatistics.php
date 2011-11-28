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
 * @ingroup EnhancedRetrievalMaintenance
 * 
 * @defgroup EnhancedRetrievalMaintenance Enhanced retrieval maintenance scripts
 * @ingroup EnhancedRetrieval
 * 
 * Clear the search statistics table.
 *
 * @author: Kai Kühn
 * 
 * Created on: 27.01.2009
 *
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

$onlyNonNull = array_key_exists('n', $options);
$clearAll = array_key_exists('a', $options);

if (!$clearAll && !$onlyNonNull) {
	print "\nClears statistics\n\n";
	print "Use with option -a to clear all search terms.\n";
    print "Use with option -n to clear only search terms with more than zero hits.\n";
	die();
}

$db = wfGetDB( DB_MASTER );
$smw_searchmatch = $db->tableName('smw_searchmatches');

if ($onlyNonNull) {
	$db->query('DELETE FROM '.$smw_searchmatch.' WHERE hits > 0');
	print "\nAll search terms with more than zero hits removed.\n";
}

if ($clearAll) {
	$db->query('DELETE FROM '.$smw_searchmatch);
	print "\nAll search terms removed.\n";
}


