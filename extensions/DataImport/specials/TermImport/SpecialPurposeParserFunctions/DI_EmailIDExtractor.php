<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file provides a parser function for extracting identities from a stron
 * that contains a list of E-mail addresses and names like given by the pop3 import DAL module.
 *
 * @author Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $smwgDIIP;

global $wgExtensionFunctions, $wgHooks;
$wgExtensionFunctions[] ='extractEmailID_Setup';
$wgHooks['LanguageGetMagic'][] = 'extractEmailID_Magic';

/**
 * Set a function hook associating the "extractEMailID" magic word with our function
 */
function extractEmailID_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook( 'extractEmailID', 'extractEmailID_Render' );
}

/**
 * maps the magic word "extractEmailID"to occurences of "extractEmailID:" in the wiki text
 */
function extractEmailID_Magic( &$magicWords, $langCode ) {
	$magicWords['extractEmailID'] = array( 0, 'extractEmailID' );
	return true;
}

function extractEmailID_Render( &$parser) {
	$parameters = func_get_args();

	// the string in which we have to search for entities must be the first parameter
	$searchString = trim($parameters[1]);
	
	// the name of the property which we use to search for real names must be the second parameter
	$propertyName = trim($parameters[2]);
	if(strlen($propertyName) == 0){
		return ("A property name must be specified");
	}
	
	$result = array();
	$searchString = explode(";", $searchString);
	foreach($searchString as $identity){
		$identity = trim($identity);
		if(strpos($identity, ",") === 0){
			$identity = substr($identity, 1);
		}
		$identity = explode(",", $identity);
		$name = "";
		if(count($identity) > 1){
			$name = $identity[0];
			$email = trim($identity[1]);
		} else {
			$email = trim($identity[0]);
		}
		
		SMWQueryProcessor::processFunctionParams(array("[[".$propertyName."::".$email."]]")
			,$querystring,$params,$printouts);
		$qResult = explode("|",
			SMWQueryProcessor::getResultFromQueryString($querystring,$params,
			$printouts, SMW_OUTPUT_WIKI));
		if(strlen(trim($qResult[0])) > 0){
			$name = str_replace("[[:", "", $qResult[0]);
			$name = str_replace("[[", "", $name);
		}
		
		if(strlen($name) > 0){
			$result[] = $name;
		} else {
			$result[] = $email;
		}
	}
	return implode(",", $result);
}
	
