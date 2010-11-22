<?php

/*  Copyright 2008, ontoprise GmbH
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
class XPathProcessor {

	// instance of DOMXPath with registered namespaces
	private $domXPath;

	/*
	 * constructs a new XPathProcessor object; creates an instance of
	 * DOMXpath for a given xml string and registers necessary namespaces
	 *
	 *  @parameter string $xmlString : xml string for which an instance of DOMXPath
	 *  	will be created
	 */
	function __construct($xmlString = ""){
		
		$domDocument = new DOMDocument();
		@ $domDocument->loadXML($xmlString);

		$this->domXPath = new DOMXPath($domDocument);
		
		$nodes = $this->domXPath->query('//namespace::*');
		
		foreach ($nodes AS $node) {
			$this->domXPath->registerNamespace($node->localName, $node->nodeValue);
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
