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