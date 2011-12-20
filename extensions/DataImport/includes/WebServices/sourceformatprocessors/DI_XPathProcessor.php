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
 * @ingroup DIWebServices
 * 
 * @author Ingo Steinbauer
 */

/**
 * This class allows to evaluate xpath statements on xml strings
 *
 * @author Ingo Steinbauer
 *
 */
class DIXPathProcessor {

	// instance of DOMXPath with registered namespaces
	private $domXPath;

	/*
	 * constructs a new DIXPathProcessor object; creates an instance of
	 * DOMXpath for a given xml string and registers necessary namespaces
	 *
	 *  @parameter string $xmlString : xml string for which an instance of DOMXPath
	 *  	will be created
	 */
	function __construct($xmlString = ""){
		$this->doConstruct($xmlString);	
	}
	
	private function doConstruct($xmlString){
		$domDocument = new DOMDocument();
		$domDocument->loadXML($xmlString);
		
		$this->domXPath = new DOMXPath($domDocument);
		
		$nodes = $this->domXPath->query('//namespace::*');
		$xmlns = false;
		$replacedXMLNSs = array();
		foreach ($nodes AS $node) {
			if($node->localName == 'xmlns'){
				if($xmlns){
					if(!in_array($node->nodeValue, $replacedXMLNSs) && $node->nodeValue != $xmlns){
						$replacedXMLNSs[] = $node->nodeValue;
						$xmlString = str_replace('xmlns="'.$node->nodeValue.'"', '', $xmlString);
					}
					continue;
				}
				$xmlns = $node->nodeValue;
			}	
			$this->domXPath->registerNamespace($node->localName, $node->nodeValue);
		}
		
		if(count($replacedXMLNSs) > 0){
			$this->doConstruct($xmlString);
		}
	}

	/*
	 * evaluates a query in xpath-syntax and returns the result as
	 * an array of strings
	 *
	 * @parameter string $query : a query in xpath syntax
	 * @return string[] : the result of the query evaluation
	 */
	function evaluateQuery($query){
		$queryResults = array();

		$entries = $this->domXPath->evaluate($query);
		
		// check if the result of the query evaluation is an object
		// or a simple string
		if(is_object($entries)){
			foreach ($entries as $entry) {
				$queryResults[] = $entry->nodeValue;
			}
		} else {
			$queryResults[] = $entries;
		}

		return $queryResults;
	}

}
