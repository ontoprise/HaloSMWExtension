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

$res = $dbr->query("SELECT subject_title FROM smw_relations r WHERE r.relation_title = 'Has_domain_hint' AND r.subject_title IN " .
				"(SELECT subject_title FROM smw_relations r WHERE r.relation_title = 'Has_range_hint' GROUP BY r.subject_title HAVING COUNT(r.subject_title) > 1) " .
			"GROUP BY r.subject_title HAVING COUNT(r.subject_title) > 1;");
		
$needManualUpdate = array();	

if($dbr->numRows( $res ) > 0) {
	while($row = $dbr->fetchObject($res)) {
		
		$needManualUpdate[] = $row->subject_title;
	}
}
$dbr->freeResult($res);	

print "\n\nUpdate all other properties...\n";

$res = $dbr->query("SELECT DISTINCT subject_title FROM smw_relations r WHERE r.relation_title = 'Has_domain_hint' OR r.relation_title = 'Has_range_hint'");
if($dbr->numRows( $res ) > 0) {
	while($row = $dbr->fetchObject($res)) {
		if (!in_array($row->subject_title, $needManualUpdate)) {
			print "\n - Updated: ".$row->subject_title;
			$t = Title::newFromText($row->subject_title, SMW_NS_PROPERTY);
			$a = new Article($t);
			$oldtext = $a->getContent();
			$newtext = updateDomainRangeAnnotations($oldtext);
			if ($newtext != NULL) $a->updateArticle($newtext, $a->getComment(), false, false);
			//print "\nOldText: ".$oldtext;
			//print "\nNewText: ".$newtext;
		}
	}
}


print "done!\n";

print "\n Note: The following properties contain more than 1 domain and more than 1 range annotation. Please update them manually:\n";
foreach($needManualUpdate as $nmu) {
	print "\n - NOT updated: ".$nmu;
}

print "\n\nEverything is OK.\n";

function updateDomainRangeAnnotations($oldtext) {
	$domains = array(); 
	$ranges = array();
	preg_match_all('/\[\[\s*has domain hint\s*:[:|=]([^]]*)\]\]/i', $oldtext, $domains);
	preg_match_all('/\[\[\s*has range hint\s*:[:|=]([^]]*)\]\]/i', $oldtext, $ranges);
	
	if (count($domains[0]) == 0) {
		
		foreach($ranges[1] as $r) {
			$replacement = "[[has domain and range::;".$r."]]";
			$oldtext = preg_replace('/\[\[\s*has range hint\s*:[:|=]'.$r.'\]\]/i', $replacement, $oldtext);
		}
	} else if (count($ranges[0]) == 0) {
		foreach($domains[1] as $d) {
			$replacement = "[[has domain and range::".$d."]]";
			$oldtext = preg_replace('/\[\[\s*has domain hint\s*:[:|=]'.$d.'\]\]/i', $replacement, $oldtext);
		}
	} else {
		foreach($domains[1] as $d) {
			foreach($ranges[1] as $r) {
				$replacement = "[[has domain and range::".$d."; ".$r."]]";
				$oldtext = preg_replace('/\[\[\s*has domain hint\s*:[:|=]'.$d.'\]\]/i', "", $oldtext);
				$oldtext = preg_replace('/\[\[\s*has range hint\s*:[:|=]'.$r.'\]\]/i', $replacement, $oldtext);
			}
		}
	}
	return $oldtext;
}
?>
