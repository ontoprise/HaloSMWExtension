<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains all functions of the ajax interface of the Linked Data
 * Extension. 
 * 
 * @author Thomas Schweitzer
 * Date: 25.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

$wgAjaxExportList[] = "lodafGetRatingEditorForKey";
$wgAjaxExportList[] = "lodafSaveRating";
$wgAjaxExportList[] = "lodafGetRatingsForTriple";
$wgAjaxExportList[] = "lodImportOrUpdate";
$wgAjaxExportList[] = "lodGetDataSourceTable";


/**
 * Returns the HTML of the editor for rating the triples that are determined by
 * the given $ratingKey.
 * 
 * @param string $ratingKey
 * 		The rating key determines the triples that can be rated.
 * @param string $value
 * 		The value whose relations will be rated.
 * @return AjaxResponse
 * 		The HTML for the rating editor.
 */
function lodafGetRatingEditorForKey($ratingKey, $value) {
    $response = new AjaxResponse();
    $response->setContentType("json");
    
    $triples = LODRatingAccess::getTriplesForRatingKey($ratingKey);
    
    $value = urldecode($value);
	$html = LODQueryResultRatingUI::getRatingHTML($ratingKey, $value, $triples);
    $response->addText($html);
	return $response;
}

/**
 * Adds a rating for a triple. The rating and the triple are JSON-encoded in
 * $rating.
 * 
 * @param string $rating
 * 		The JSON encoded rating.
 * @return AjaxResponse
 */
function lodafSaveRating($rating) {
	$rating = json_decode($rating);
	
	$ra = new LODRatingAccess();
	$r = new LODRating($rating->rating, $rating->comment);
	$t = new LODTriple($rating->triple->subject, 
					   $rating->triple->predicate,
					   $rating->triple->object);
	$ra->addRating($t, $r);
	
    $response = new AjaxResponse();
    $response->setContentType("json");
    
    $response->addText(wfMsg('lod_rt_rating_saved'));
	return $response;
}

/**
 * Retrieves all ratings for a triple. Returns the HTML that contains the number
 * of "correct" and "wrong" ratings and all comments.
 * 
 * @param string $triple
 * 		The JSON encoded triple
 */
function lodafGetRatingsForTriple($triple) {
	$triple = json_decode($triple);
	
	$t = new LODTriple($triple->subject, 
					   $triple->predicate,
					   $triple->object);
					   
	$html = LODQueryResultRatingUI::getRatingsForTripleHTML($t);
					   
    $response = new AjaxResponse();
    $response->setContentType("html");
    
    $response->addText($html);
	return $response;
					   
}

/**
 * Triggers an import or update operation.
 * 
 * @param string $dataSource datasource ID
 * @param string $update (true/false)
 * 
 * @return AjaxResponse true if successful. Otherwise an error message.
 *       
 */
function lodImportOrUpdate($dataSource, $update) {
    $response = new AjaxResponse();
    $response->setContentType("text/plain");

    try {
        $con = TSConnection::getConnector();
        $con->connect();
        $con->runImport($dataSource, $update);
        $response->addText("true");
    } catch(Exception $e) {
    	$response->setResponseCode($e->getCode());
    	$response->addText($e->getMessage());
    }
    
    return $response;
}

/**
 * Returns the datasource table as HTML
 *  
 * @return AjaxResponse HTML. Otherwise an error message.
 *       
 */
function lodGetDataSourceTable() {
    $response = new AjaxResponse();
    $response->setContentType("text/html");

    try {
       $lodSourcePage = new LODSourcesPage();
       $response->addText($lodSourcePage->createSourceTable($lodSourcePage->getAllSources()));
    } catch(Exception $e) {
        $response->setResponseCode($e->getCode());
        $response->addText($e->getMessage());
    }
    
    return $response;
}
