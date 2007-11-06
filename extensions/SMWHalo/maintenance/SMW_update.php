<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

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

global $smwgIP;
require_once($smwgIP . '/includes/SMW_GlobalFunctions.php');

global $smwgHaloIP;
require_once($smwgHaloIP . '/includes/SMW_Initialize.php');
 
require_once($smwgIP . '/includes/SMW_Factbox.php');

// Call setup for safety
print "\nSetup database for HALO extension...";
 smwfHaloInitializeTables(false);
print "done!\n";
 
$forceRefresh = false;

if ( array_key_exists( 'r', $options ) ) {
	$forceRefresh = true;
}

$dbr =& wfGetDB( DB_MASTER );

print "\n- Update the database now...";

$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_chf').' WHERE value_datatype = '.$dbr->addQuotes('chemicalformula'));
$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_che').' WHERE value_datatype = '.$dbr->addQuotes('ehemicalequation'));
$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_dat').' WHERE value_datatype = '.$dbr->addQuotes('datetime'));
$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_int').' WHERE value_datatype = '.$dbr->addQuotes('int'));
$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_flt').' WHERE value_datatype = '.$dbr->addQuotes('float'));
$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_enu').' WHERE value_datatype = '.$dbr->addQuotes('enum'));
$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_txt').' WHERE value_datatype = '.$dbr->addQuotes('text'));

print "done!\n";

// get all property pages which have more than one domain AND more than one range annotation at the same time.
$res = $dbr->query("SELECT subject_title FROM smw_relations r WHERE r.relation_title = 'Has_domain_hint' AND r.subject_title IN " .
				"(SELECT subject_title FROM smw_relations r WHERE r.relation_title = 'Has_range_hint' GROUP BY r.subject_title HAVING COUNT(r.subject_title) > 1) " .
			"GROUP BY r.subject_title HAVING COUNT(r.subject_title) > 1;");

// those pages need to be updated manually, so save them		
$needManualUpdate = array();	
if($dbr->numRows( $res ) > 0) {
	while($row = $dbr->fetchObject($res)) {
		$needManualUpdate[] = $row->subject_title;
	}
}
$dbr->freeResult($res);	

print "\n\nUpdate all other properties...\n";

global $wgParser;
$options = new ParserOptions();
if ($forceRefresh) {
	// select all property pages
	$query = "SELECT DISTINCT page_title AS title FROM page WHERE page_namespace = ".SMW_NS_PROPERTY;
} else {
	// select property pages which have a domain or range hint annotation
	$query = "SELECT DISTINCT subject_title AS title FROM smw_relations r WHERE r.relation_title = 'Has_domain_hint' OR r.relation_title = 'Has_range_hint'";
}
$res = $dbr->query($query);
if($dbr->numRows( $res ) > 0) {
	while($row = $dbr->fetchObject($res)) {
		// do only process pages which don't need to be updated manually
		if (!in_array($row->title, $needManualUpdate) || $forceRefresh) {
			print "\n - Updated: ".$row->title;
			
			// load latest revision
			$t = Title::newFromText($row->title, SMW_NS_PROPERTY);
			$revision = Revision::newFromTitle( $t );
			$a = new Article($t);
			if ( $revision === NULL ) continue;
			
			// get old text and transform it
			$oldtext = $revision->getText();
			$newtext = updateDomainRangeAnnotations($oldtext);
			
			// save new text and re-parse article to get new semantic data.
			if ($newtext != NULL) {
				$a->doEdit($newtext, $revision->getComment(), EDIT_UPDATE);
				$wgParser->parse($newtext, $t, $options, true, true, $revision->getID());
				SMWFactbox::storeData($title, true);
			}
		}
	}
}


print "done!\n";

if (count($needManualUpdate) > 0) {
	print "\n Note: The following properties contain more than 1 domain and " .
			"more than 1 range annotation. Please update them manually:\n";
}
foreach($needManualUpdate as $nmu) {
	print "\n - NOT updated: ".$nmu;
}

print "\n\nEverything is OK.\n";

/**
 * Converts old domain/range hint annotations to new.
 * 
 * @param $oldtext old wiki markup
 * @return new wiki markup
 */
function updateDomainRangeAnnotations($oldtext) {
	$domains = array(); 
	$ranges = array();
	preg_match_all('/\[\[\s*has domain hint\s*:[:|=]([^]]*)\]\]/i', $oldtext, $domains);
	preg_match_all('/\[\[\s*has range hint\s*:[:|=]([^]]*)\]\]/i', $oldtext, $ranges);
	
	if (count($domains[0]) == 0) {
		
		foreach($ranges[1] as $r) {
			$replacement = "[[has domain and range::;".$r."]]";
			$oldtext = preg_replace('/\[\[\s*has range hint\s*:[:|=]'.preg_quote($r).'\]\]/i', $replacement, $oldtext);
		}
	} else if (count($ranges[0]) == 0) {
		foreach($domains[1] as $d) {
			$replacement = "[[has domain and range::".$d."]]";
			$oldtext = preg_replace('/\[\[\s*has domain hint\s*:[:|=]'.preg_quote($d).'\]\]/i', $replacement, $oldtext);
		}
	} else {
		foreach($domains[1] as $d) {
			foreach($ranges[1] as $r) {
				$replacement = "[[has domain and range::".$d."; ".$r."]]";
				$oldtext = preg_replace('/\[\[\s*has domain hint\s*:[:|=]'.preg_quote($d).'\]\]/i', "", $oldtext);
				$oldtext = preg_replace('/\[\[\s*has range hint\s*:[:|=]'.preg_quote($r).'\]\]/i', $replacement, $oldtext);
			}
		}
	}
	return $oldtext;
}
?>
