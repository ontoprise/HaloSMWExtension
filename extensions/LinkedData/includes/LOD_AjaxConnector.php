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
 * @ingroup LinkedData
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
$wgAjaxExportList[] = "lodafGetRatingEditorForTriple";
$wgAjaxExportList[] = "lodImportOrUpdate";
$wgAjaxExportList[] = "lodListSources";
$wgAjaxExportList[] = "lodListR2RMappings";
$wgAjaxExportList[] = "lodGetR2RMapping";
$wgAjaxExportList[] = "lodUpdateR2RMapping";
$wgAjaxExportList[] = "lodRemoveR2RMapping";

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
 * Returns the HTML of the editor for rating the given triple.
 * 
 * @param string $triple
 * 		The triple that can be rated.
 * @param string $value
 * 		The value whose relations will be rated.
 * @return AjaxResponse
 * 		The HTML for the rating editor.
 */
function lodafGetRatingEditorForTriple($triple, $value) {
    $response = new AjaxResponse();
    $response->setContentType("json");
    $triple = json_decode($triple);
	// Object can be given with type
	$objType = null;
	$obj = $triple->object;
	$type = null;
	if (preg_match("/\"(.*?)\"\^\^(.*)/", $obj, $objType) == 1) {
		$obj = $objType[1];
		$type = $objType[2];
	}
    
    $triple = new TSCTriple($triple->subject, $triple->predicate, $obj, $type);
    $triples = array(array(array($triple), array()));
    
    $value = urldecode($value);
	$html = LODQueryResultRatingUI::getRatingHTML(null, $value, $triples);
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
	$t = new TSCTriple($rating->triple->subject, 
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
	
	$t = new TSCTriple($triple->subject, 
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
function lodImportOrUpdate($dataSource, $update, $synchronous, $runSchemaTranslation ,$runIdentityResolution) {
    $response = new AjaxResponse();
    $response->setContentType("text/plain");

    try {
        $con = TSConnection::getConnector();
        $con->connect();
        $con->runImport($dataSource, $update, $synchronous, $runSchemaTranslation, $runIdentityResolution);
        $response->addText("true");
    } catch(Exception $e) {
    	$response->setResponseCode($e->getCode());
    	$response->addText($e->getMessage());
    }
    
    return $response;
}



/**
 * Outputs a list of all Linked Data source as JSON
 * @return AjaxResponse
 */
function lodListSources() {
	$lodAdminStore = TSCAdministrationStore::getInstance();
    $response = new AjaxResponse();
	$sources = $lodAdminStore->loadAllSourceDefinitions();
	$results = array();
	foreach ($sources as $id => $source) {
		if(!$id) {
			continue;
		}
		$results[] = $id;
	}
    $response->setContentType("json");
    $response->addText(json_encode($results));
	return $response;
}

/**
 * Outputs a list of all R2R mappings as JSON
 * @return AjaxResponse
 */
function lodListR2RMappings() {
	$lodMappingStore = LODMappingStore::getStore();
    $response = new AjaxResponse();
	$allMappings = $lodMappingStore->getAllMappings();
	$results = array();
	foreach ($allMappings as $uri => $mapping) {
		if(!($mapping instanceof LODR2RMapping)) {
			continue;
		}
		$results[$uri] = array(
			"uri"		=> $mapping->getUri(),
			"id"		=> $mapping->getID(),
			"source"	=> $mapping->getSource(),
			"target"	=> $mapping->getTarget(),
		);
	}
    $response->setContentType("json");
    $response->addText(json_encode($results));
	return $response;
}

/**
 * Outputs an R2R mapping as TTL
 * 
 * @param string $mappingURI
 * @return AjaxResponse	The JSON encoded mapping
 */
function lodGetR2RMapping($mappingURI) {
    $response = new AjaxResponse();
	$lodMappingStore = LODMappingStore::getStore();
	if ($mapping = $lodMappingStore->getMapping($mappingURI)) {
	    $response->setContentType("text/turtle");
	    $response->addText($mapping->getMappingText());
	} else {
		$response->setResponseCode(500);
		$response->addText("Mapping <" + $mappingUri + "> not found.");
	}
	return $response;
}

/**
 * Updates an R2R mapping
 * 
 * @param string $mappingURI	null to create a new mapping
 * @param $source
 * @param $target
 * @param $sourceCode
 * @return AjaxResponse	The JSON encoded mapping
 */
function lodUpdateR2RMapping($mappingURI, $source, $target, $sourceCode) {
    $response = new AjaxResponse();
	$lodMappingStore = LODMappingStore::getStore();

	if ($mappingUri) {
		$lodMappingStore->removeMapping($mappingURI);
	}

	$mapping = new LODR2RMapping($uri, $sourceCode, $source, $target);

	if ($lodMappingStore->addMapping($mapping, "R2Redit")) {
		$response->addText("Mapping <" + $mappingUri + "> updated.");
	} else {
		$response->setResponseCode(500);
		$response->addText("Unable to update mapping <" + $mappingUri + ">.");
	}
	return $response;
}

/**
 * Removes an R2R mapping
 * 
 * @param string $mappingURI
 * @return AjaxResponse
 */
function lodRemoveR2RMapping($mappingURI) {
    $response = new AjaxResponse();
	$lodMappingStore = LODMappingStore::getStore();
	try {
		$lodMappingStore->removeMapping($mappingURI);
		$response->addText("Mapping removed.");
	} catch(Exception $e) {
		$response->setResponseCode(500);
		$response->addText("Unable to remove mapping <" + $mappingUri + ">.");
	}
	return $response;
}
