<?php
/*  Copyright 2008, ontoprise GmbH
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
*   
* 
*   Author: kai
*/

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

# Process command line arguments
# Parse arguments

$logPath="logs"; // default path

$params = array();
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
    //-l log file
    if ($arg == '-l') {
        $logPath = next($argv);
        continue;
    }
    if ($arg == '-b') {
        $botID = next($argv);
        continue;
    }   
    $params[] = $arg;
}

if (!isset($botID)) {
    echo "\n\nPlease set bot using -b <bot ID>.\n";
    die();
}
echo "\n------ Check $botID -------\n";
$mediaWikiLocation = dirname(__FILE__) . '/../../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
 
$issuesFound = false;
array_shift($params); // get rid of path

$db = wfGetDB(DB_SLAVE);
$res = $db->select($db->tableName('smw_gardeningissues'), '*', array('bot_id'=>$botID));
if (!file_exists($logPath."/$botID.log")) {
    echo "\n\nNo saved log at '$logPath/$botID.log'.\n";
    die();
}
$handle = fopen($logPath."/$botID.log","rb");
echo "\nRead from input file: ".$logPath."/$botID.log"."\n";
do {
    $line = fgets($handle);
    if ($line !== false ) $saved_log[] = trim($line);
} while($line !== false);
fclose($handle);


// load current log and compare to saved log
// determines all issues which appear in current log but not in saved log
echo "\nCompare results...";
$current_log = array();
if($db->numRows( $res ) > 0) {
    while($row = $db->fetchObject($res)) {
        $gi_type = $row->gi_type == NULL ? "NULL" : $row->gi_type;
        $p1_id = $row->p1_id == NULL ? "NULL" : $row->p1_id;
        $p2_id = $row->p2_id == NULL ? "NULL" : $row->p2_id;
        $value = $row->value == NULL ? "NULL" : $row->value;
        $valueint = $row->valueint == NULL ? "NULL" : $row->valueint;
        $line = "$gi_type|$p1_id|$p2_id|$value|$valueint";
        if (!in_array($line, $saved_log)) {
            echo "Saved log does not contain: $line\n";
            $issuesFound = true;
        }
        $current_log[] = trim($line);
        
    }
}
$db->freeResult($res);

// determines issues which appear in saved log but not in current log
$diff = array_diff($saved_log, $current_log);

foreach($diff as $d) {
    echo "Saved log contains additionally: $d\n";
    $issuesFound = true;
}

echo "done!\n";
if (!$issuesFound) echo "\nNo issues found. Everything OK.";
echo "\n######################################################\n\n"
?>