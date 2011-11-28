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
 * @ingroup DFMaintenance
 *
 * Export the mappings.
 *
 * Usage:   php exportMappings.php -o <output dir> [ -s <source> ]
 *
 * @author: Kai Kuehn
 *
 */

require_once( '../../../../maintenance/commandLine.inc' );

// get root dir of DF
global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once($rootDir."/tools/smwadmin/DF_Tools.php");

$first = true;
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	if ($arg == '-o') {
		$outputDir = trim(next($argv));
		if (substr($outputDir, -1) != "/") $outputDir .= "/";
		Tools::mkpath($outputDir);
		continue;
	} if ($arg == '-s') {
		$exportSource = trim(next($argv));
		continue;
	}else if ($arg == '--help') {
		$help = true;
		continue;
	}
	$first = false;
}



if (isset($help) || !isset($outputDir)) {
	print "\n\nUsage";
	print "\n\t --help : Shows help";
	print "\n\t -o <output dir>: Use this dir as output directory [required]";
	print "\n\t -s <source>: Export only mappings of this source  [optional]";
	print "\n\n";
	die();
}

print "\nRead mappings...";
$mappings = readMappings(isset($exportSource) ? $exportSource : NULL);
print "done.";

print "\nWrite mappings...";
$handleXML = fopen($outputDir."mappings.xml", "w");
foreach($mappings as $source => $list) {
	$first = true;
	foreach($list as $tuple) {
		list($target, $mapping_text) = $tuple;
		if ($first) {
			print "\n\t[$source] => [$target]";
			$first = false;
		} else print ".";
		$handleMap = fopen($outputDir.$target.".map", "w");
		fwrite($handleMap, $mapping_text);
		fwrite($handleXML, "\n<file source=\"$source\" target=\"$target\" loc=\"mappings/$target.map\"/>");
		fclose($handleMap);
	}
}
fclose($handleXML);

print "\n\nDONE.";


function readMappings($source = NULL) {
	$db =& wfGetDB( DB_SLAVE );
	$lod_mapping_table = $db->tableName( 'lod_mapping_persistence' );
	$result = array();
	$cond = "";
	if (!is_null($source)) {
		$cond = "WHERE source=".$db->addQuotes($source);
	}
	$sql = "SELECT source, target, mapping_text FROM $lod_mapping_table $cond ORDER BY source";
	$res = $db->query( $sql );
	if($db->numRows( $res ) > 0) {
		while($row = $db->fetchObject($res)) {
			if (!array_key_exists($row->source, $result)) {
				$result[$row->source] = array();
			}
			$result[$row->source][] = array($row->target, $row->mapping_text);
		}
	}
	$db->freeResult($res);
	return $result;
}
