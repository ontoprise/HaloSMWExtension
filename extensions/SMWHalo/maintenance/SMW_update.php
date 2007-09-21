<?php
/*
 * Created on 21.09.2007
 *
 * Update database for HALO extension.
 * 
 * 
 * Author: kai
 */
 

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

$dbr =& wfGetDB( DB_MASTER );

print "\nUpdate the database now...";

$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('Chemical_formula').' WHERE value_datatype = '.$dbr->addQuotes('_chf'));
$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('Chemical_equation').' WHERE value_datatype = '.$dbr->addQuotes('_che'));

print "done!\n";
 
?>
