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

/**
 *  @file
 *  @ingroup SMWHaloMaintenance
 *  
 *  Create CSV file with gardening issue statistics
 *  
 *  @author Markus Nitsche
 */
if ($_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

global $smwgIP;
require_once($smwgIP . '/includes/SMW_GlobalFunctions.php');

global $smwgHaloIP;
require_once($smwgHaloIP . '/includes/SMW_Initialize.php');
 
require_once($smwgIP . '/includes/SMW_Factbox.php');

$ts = time();
$csvheader = "Date;Consistency Bot Total;Anomalies Bot Total;Missing Annotations Bot Total;Undefined Entities Bot Total;CB Covariance Issues;CB Not Defined Issues;CB Double Issues; CB Wrong/Missing Values/Entity Issues;CB Incompatible Entity Issues;CB Other Issues;AB Category Leafs;AB Subcategory Anomalies;MAB Not Annotated Pages;UEB Instances Without Categories;UEB Undefined Properties;UEB Undefined Categories;UEB Undefined Property Targets;";
$csvreport = "\n" . date("d.m.y", $ts) . ";";

$report = "\n\n\nVERIFICATION WIKI GARDENING ISSUE STATISTICS " . date("d.m.y H:i:s", $ts) . "\n";
$report .= "--------------------------------------------------------------\n\n";


$dbr =& wfGetDB( DB_SLAVE );
$res = $dbr->query("SELECT bot_id, COUNT(bot_id) AS num FROM smw_gardeningissues GROUP BY bot_id");
if($dbr->numRows( $res ) > 0) {
	while($row = $dbr->fetchObject($res)) {
		$stats[$row->bot_id] = $row->num;
	}
}
$dbr->freeResult($res);	

$report .= "TOTAL ENTRIES\n";
$report .= "-------------\n";
$report .= "Consistency bot: " . ($stats["smw_consistencybot"]?$stats["smw_consistencybot"]:"0") . "\n";
$report .= "Anomalies bot: " . ($stats["smw_anomaliesbot"]?$stats["smw_anomaliesbot"]:"0") . "\n";
$report .= "Missing annotations bot: " . ($stats["smw_missingannotationsbot"]?$stats["smw_missingannotationsbot"]:"0") . "\n";
$report .= "undefined entities bot: " . ($stats["smw_undefinedentitiesbot"]?$stats["smw_undefinedentitiesbot"]:"0") . "\n\n";

$csvreport .= ($stats["smw_consistencybot"]?$stats["smw_consistencybot"]:"0") . ";" . ($stats["smw_anomaliesbot"]?$stats["smw_anomaliesbot"]:"0") . ";" . ($stats["smw_missingannotationsbot"]?$stats["smw_missingannotationsbot"]:"0") . ";" . ($stats["smw_undefinedentitiesbot"]?$stats["smw_undefinedentitiesbot"]:"0") . ";";

$res = $dbr->query("SELECT gi_class, COUNT(gi_class) AS num FROM smw_gardeningissues GROUP BY gi_class");
if($dbr->numRows( $res ) > 0) {
	while($row = $dbr->fetchObject($res)) {
		$stats[$row->gi_class] = $row->num;
	}
}
$dbr->freeResult($res);	

$report .= "CONSISTENCY BOT\n";
$report .= "---------------\n";
$report .= "Covariance issues: " . ($stats["100"]?$stats["100"]:"0") . "\n";
$report .= "Not defined issues: " . ($stats["101"]?$stats["101"]:"0") . "\n";
$report .= "Double issues: " . ($stats["102"]?$stats["102"]:"0") . "\n";
$report .= "Wrong/missing values / entity issues: " . ($stats["103"]?$stats["103"]:"0") . "\n";
$report .= "Incompatible entity issues: " . ($stats["104"]?$stats["104"]:"0") . "\n";
$report .= "Others: " . ($stats["105"]?$stats["105"]:"0") . "\n\n";

$csvreport .= ($stats["100"]?$stats["100"]:"0") . ";" . ($stats["101"]?$stats["101"]:"0") . ";" . ($stats["102"]?$stats["102"]:"0") . ";" . ($stats["103"]?$stats["103"]:"0") . ";" . ($stats["104"]?$stats["104"]:"0") . ";" . ($stats["105"]?$stats["105"]:"0") . ";"; 

$res = $dbr->query("SELECT gi_type, COUNT(gi_type) AS num FROM smw_gardeningissues GROUP BY gi_type");
if($dbr->numRows( $res ) > 0) {
	while($row = $dbr->fetchObject($res)) {
		$stats[$row->gi_type] = $row->num;
	}
}
$dbr->freeResult($res);	


$report .= "ANOMALIES BOT\n";
$report .= "-------------\n";
$report .= "Category leafs: " . ($stats["60001"]?$stats["60001"]:"0") . "\n";
$report .= "Subcategory anomalies: " . ($stats["60002"]?$stats["60002"]:"0") . "\n\n";

$csvreport .= ($stats["60001"]?$stats["60001"]:"0") . ";" . ($stats["60002"]?$stats["60002"]:"0") . ";";

$report .= "MISSING ANNOTATIONS BOT\n";
$report .= "-----------------------\n";
$report .= "Not annotated pages: " . ($stats["50001"]?$stats["50001"]:"0") . "\n\n";

$csvreport .= ($stats["50001"]?$stats["50001"]:"0") . ";";

$report .= "UNDEFINED ENTITIES BOT\n";
$report .= "----------------------\n";
$report .= "Instances without categories: " . ($stats["30001"]?$stats["30001"]:"0") . "\n";
$report .= "Undefined properties: " . ($stats["30102"]?$stats["30102"]:"0") . "\n";
$report .= "Undefined categories: " . ($stats["30203"]?$stats["30203"]:"0") . "\n";
$report .= "Undefined property targets: " . ($stats["30304"]?$stats["30304"]:"0") . "\n";

$csvreport .= ($stats["30001"]?$stats["30001"]:"0") . ";" . ($stats["30102"]?$stats["30102"]:"0") . ";" . ($stats["30203"]?$stats["30203"]:"0") . ";" . ($stats["30304"]?$stats["30304"]:"0") . ";";

$myFile = "wikistats_current.txt";
$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh, $report);
fclose($fh);

$myFile = "wikistats_all.txt";
$fh = fopen($myFile, 'a') or die("can't open file");
fwrite($fh, $report);
fclose($fh);


$exists = file_exists("wikistats.csv");
$myFile = "wikistats.csv";
$fh = fopen($myFile, 'a') or die("can't open file");
if(!$exists)
	fwrite($fh, $csvheader);
fwrite($fh, $csvreport);
fclose($fh);

