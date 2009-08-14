<?php
/**
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


