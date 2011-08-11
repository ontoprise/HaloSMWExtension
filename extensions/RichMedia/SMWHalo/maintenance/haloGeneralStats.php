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






$dbr =& wfGetDB( DB_SLAVE );

$wikistats = $dbr->selectRow( 'site_stats', '*', false, __METHOD__ );
$views = $wikistats->ss_total_views;
$edits = $wikistats->ss_total_edits;
$good = $wikistats->ss_good_articles;
$images = $wikistats->ss_images;
$users = $wikistats->ss_users;



		$relations_table = $dbr->tableName( 'smw_relations' );
		$attributes_table = $dbr->tableName( 'smw_attributes' );
		$category_table = $dbr->tableName( 'categorylinks' );
		$page_table = $dbr->tableName( 'page' );


		$sql = "SELECT Count(DISTINCT relation_title) AS anzahl FROM $relations_table";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$relations = $row->anzahl;
		$dbr->freeResult( $res );
		
		$sql = "SELECT Count(DISTINCT subject_title) AS anzahl FROM smw_subprops";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$subprops = $row->anzahl;
		$dbr->freeResult( $res );

		$sql = "SELECT Count(*) AS anzahl FROM $relations_table";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$relation_instance = $row->anzahl;
		$dbr->freeResult( $res );
		
		$sql  = "SELECT Count(*) AS anzahl ";
		$sql .= "FROM $page_table ";
		$sql .= "where page_title IN ";
		$sql .= "(SELECT DISTINCT $relations_table.relation_title FROM $relations_table);";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$relation_pages = $row->anzahl;
		$dbr->freeResult( $res );

		$sql = "SELECT Count(DISTINCT attribute_title) AS count FROM $attributes_table";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$attributes = $row->count;
		$dbr->freeResult( $res );

		$sql = "SELECT Count(*) AS count FROM $attributes_table";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$attribute_instance = $row->count;
		$dbr->freeResult( $res );

		$sql = "SELECT Count(DISTINCT cl_to) AS count FROM $category_table";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$categories = $row->count;
		$dbr->freeResult( $res );
		
		$sql = "SELECT Count(DISTINCT cl_from) AS count FROM $category_table, page WHERE $page_table.page_id = $category_table.cl_from AND $page_table.page_namespace = 0";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$instancecat = $row->count;
		$dbr->freeResult( $res );
		
		$sql = "select avg(n) as x from (SELECT count(*) as n from categorylinks, page WHERE page.page_id = categorylinks.cl_from AND page.page_namespace = 0 group by cl_to) x";
		//$sql = "SELECT avg(n) FROM (SELECT count(*) AS n FROM categorylinks, page WHERE page.page_id = categorylinks.cl_from AND page.page_namespace = 0 GROUP BY cl_from) x";
		$res = $dbr->query( $sql );
		$row = $dbr->fetchObject( $res );
		$avginstancecat = $row->x;
		$dbr->freeResult( $res );
		
$csvheader = "Date;Total number of pages;Total number of views;Total number of page edits;Total number of images;Total number of users;Total number of categories;Total number of category instances;Average number of instances per category;Total number of relations;Total number of relation instances;Average number of instances per relation;Total number of pages about relations;Total number of attributes;Total number of attribute instances;Average number of instances per attribute;";
$csvreport = "\n" . date("d.m.y", $ts) . ";";

$csvreport.= $good . ";" . $views . ";" . $edits . ";" . $images . ";" . $users . ";";

$csvreport.= $categories . ";" . $instancecat . ";" . $avginstancecat . ";";

$realrelations=$relations-$subprops;
$relval = ($relation_instance!=0 ? $relation_instance / $realrelations : 0);
$relval = number_format($relval, 2, ',', '');

$csvreport.= $relations . ";" . $relation_instance . ";" . $relval . ";" . $relation_pages . ";";

$insval = ($attribute_instance!=0 ? $attribute_instance / $attributes : 0 );
$insval = number_format($insval, 2, ',', '');

$csvreport.= $attributes . ";" . $attribute_instance . ";" . $insval . ";";

$myFile = "generalwikistats.csv";
$exists = file_exists($myFile);

$fh = fopen($myFile, 'a') or die("can't open file");
if(!$exists)
	fwrite($fh, $csvheader);
fwrite($fh, $csvreport);
fclose($fh);

