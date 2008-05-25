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
 */

/**
 * This file is responsible for detecting the usaga of WebServices 
 * as values for semantic properties.
 *
 * @author Ingo Steinbauer
 *
 */
 
// necessary for querying used properties
global $smwgIP;
require_once($smwgIP. "/includes/SMW_SemanticData.php");

// necessary for finding possible ws-property-pairs before they are processed 
// by the responsible parsers
$wgHooks['ParserBeforeInternalParse'][] = 'findWSPropertyPairs';

// necessary in order to valide ws-property-pairs found before after  
// they are processed by the responsible parsers
$wgHooks['ParserAfterTidy'][] = 'validateWSPropertyPairs';


/*
 * find possible ws-property-pairs
 */
function findWSPropertyPairs(&$parser, &$text){
	$pattern = "/\[\[		# beginning of semantic attribute
				[^]]*		# no closing squared bracket but everything else
				::			# identifies a semantic property
				[^]]*		# no closing squared bracket but everything else
				\{\{
				[^]]*		# no closing squared bracket but everything else
				\#ws:		# beginning of webservice usage declaration
				[^]]*		# no closing squared bracket but everything else
				\|
				/xu";
	
	initWSPropertyMemory();
	preg_replace_callback($pattern, extractWSPropertyPairNames, $text);
	return true;
}

/*
 * extract names from possible ws-property-pairs and
 * remember them for later validation
 */
function extractWSPropertyPairNames($hits){
	foreach ($hits as $hit){
		$hit = trim($hit);
		$sColPos = strPos($hit, "::");
		$propertyName = substr($hit, 2 ,$sColPos-2);
		$propertyName = trim($propertyName);
		
		$wsColPos = strPos($hit, "#ws:");
		$wsSPos = strPos($hit, "|", $wsColPos);
		$wsName = substr($hit, $wsColPos+4, $wsSPos-5-$wsColPos);
		$wsName = trim($wsName);
		
		rememberWSPropertyPair($propertyName, $wsName);
		
		// returned string is not used
		return $hit."prop: ".$propertyName."ws: ".$wsName."-ende";
	}
}

/*
 * validate the possible ws-property-pairs found before
 * and store them in the database
 */
function validateWSPropertyPairs(&$parser, &$text){
	//query properties used on this page
	$semanticData = new	SMWSemanticData($parser->getTitle());
	$smwProperties = SMWFactbox::$semdata->getProperties();
	 
	
	$rememberedWSPropertyPairs = getRememberedWSPropertyPairs();
	  
	for($i=0; $i <  sizeof($rememberedWSPropertyPairs); $i++){
		$validated = false;
		$validatedProperty;
		
		foreach($smwProperties as $key => $property) {
			if(strtolower($rememberedWSPropertyPairs[$i][0]) == strtolower($key)){
				$validated = true;
				$validatedProperty = $property;
			}
		}
		if($validated){
			$text.= $rememberedWSPropertyPairs[$i][0];
			$text.= " propertyId: " .getPageIdOfProperty($validatedProperty);
			$text.= " subject: " .getPageIdOfSubject($semanticData);
		}
	}
	return true;
}

/**
 * remember possible ws-property-pair for later validation
 *
 * @param string $propertyName
 * @param string $wsName
 */
function rememberWSPropertyPair($propertyName, $wsName){
	WSPropertyMemory::rememberWSPropertyPair($propertyName, $wsName);
}

/*
 * get remembered ws-property-pairs for validation
 */
function getRememberedWSPropertyPairs(){
	return WSPropertyMemory::getWSPropertyPairs();
}
/*
 * initialize the ws-property memory
 */
function initWSPropertyMemory(){
	WSPropertyMemory::refresh();
}

function getPageIdOfProperty($validatedProperty){
	return $validatedProperty->getArticleId();
}

function getPageIdOfSubject($semanticData){
	return $semanticData->getSubject()->getArticleId();
}

function getWSId(){
	return("todo");
	// todo: how is it possible to get a webservice object if
	// only the web service name is known?
}

function getParameterSetId(){
	return "todo";
	// i know the webservice id and I know the"subjectId
	// but what if the it the ws is used on the same page
	// with different parameter sets??
}

/*
 * store detected ws-property-pair
 */
function storeWSPropertyPair($pageIdOfProperty, $pageIdOfSubject, $wSId, $parameterSetId){
	WSStorage::getDatabase()->addWSProperty($pageIdOfProperty, $wSId, $parameterSetId, $pageIdOfSubject);
}

/*
 * helper class for remembering ws-property-airs
 * todo: replace this with a better solution
 */
class WSPropertyMemory{

	static private $wsPropertyPairs;

	static public function rememberWSPropertyPair($propertyName, $wsName){
		array_push(WSPropertyMemory::$wsPropertyPairs, array($propertyName, $wsName));
	}

	static public function getWSPropertyPairs(){
		return WSPropertyMemory::$wsPropertyPairs;
	}

	static public function refresh(){
		WSPropertyMemory::$wsPropertyPairs = array();
	}

}

?>