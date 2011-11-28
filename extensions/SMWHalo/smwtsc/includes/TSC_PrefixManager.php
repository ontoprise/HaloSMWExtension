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
 * This file defines the class TSCPrefixManager
 * 
 * @author Thomas Schweitzer
 * Date: 12.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class is a singleton. It stores a map of "popular" prefixes of URIs. 
 * Further prefixes with their namespace URI can be added.
 * 
 * @author Thomas Schweitzer
 * 
 */
class TSCPrefixManager  {
	
	//--- Constants ---
	// The base URI of all triples of the Linked Data Extension
	const TSC_BASE_URI = "http://www.example.org/smw-lde/";
	// OWL properties of the LDE
	const TSC_SMW_LDE = "smw-lde.owl#";
	// Graphs of the LDE
	const TSC_SMW_GRAPHS = "smwGraphs/";
	// data sources of the LDE
	const TSC_SMW_DATASOURCES = "smwDatasources/";
    // datasource links (aka mappings)
    const TSC_SMW_DATASOURCELINKS = "smwDatasourceLinks/";

	// trust stuff
    const TSC_SMW_TRUSTPOLICIES = "smwTrustPolicies/";
	const TSC_SMW_USERS = "smwUsers/";
	
	
	// Provenance graph
	const TSC_SWP = "http://www.w3.org/2004/03/trix/swp-2/";
	
			
	//--- Private fields ---
	
	//TSCPrefixManager: The only instance of this class
	private static $mInstance = null;    
	
	// array<string=>string>: Map from prefixes to namespace URIs
	private $mPrefixMap = null;		
	
	/**
	 * Constructor for  TSCPrefixManager
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	private function __construct() {
		self::$mInstance = $this;
		$this->mPrefixMap = array();
		$this->mPrefixMap['xsd']  = "http://www.w3.org/2001/XMLSchema#";
		$this->mPrefixMap['owl']  = "http://www.w3.org/2002/07/owl#";
		$this->mPrefixMap['rdfs'] = "http://www.w3.org/2000/01/rdf-schema#";
		$this->mPrefixMap['rdf']  = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
		
		$this->mPrefixMap['swp']  			= self::TSC_SWP;
		$this->mPrefixMap['smw-lde']		= self::TSC_BASE_URI.self::TSC_SMW_LDE;
		$this->mPrefixMap['smwGraphs']		= self::TSC_BASE_URI.self::TSC_SMW_GRAPHS;
		$this->mPrefixMap['smwDatasources']	= self::TSC_BASE_URI.self::TSC_SMW_DATASOURCES;
		$this->mPrefixMap['smwTrustPolicies']	= self::TSC_BASE_URI.self::TSC_SMW_TRUSTPOLICIES;
		$this->mPrefixMap['smwUsers']		= self::TSC_BASE_URI.self::TSC_SMW_USERS;
		
		$this->mPrefixMap['smwDatasourceLinks']		= self::TSC_BASE_URI.self::TSC_SMW_DATASOURCELINKS;
		
		// Add wiki prefixes
		global $smwgHaloTripleStoreGraph;
		if (isset($smwgHaloTripleStoreGraph)) {
			$this->mPrefixMap["a"] 		= "$smwgHaloTripleStoreGraph/a/";
			$this->mPrefixMap["prop"]	= "$smwgHaloTripleStoreGraph/property/";
			$this->mPrefixMap["cat"] 	= "$smwgHaloTripleStoreGraph/category/";
		}		
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	
	/**
	 * Returns the only instance of this class.
	 * 
	 * @return TSCPrefixManager 
	 * 		The instance of the prefix manager
	 */
	public static function getInstance() {
		return is_null(self::$mInstance) ? new self : self::$mInstance;
	}

	/**
	 * Returns the namespace URI for a prefix
	 * @param string $prefix
	 * 		The prefix
	 * @return string/NULL 
	 * 		The namespace URI for the prefix or NULL if the prefix is unknown.
	 */
	public function getNamespaceURI($prefix) {
		$p = array_key_exists($prefix, $this->mPrefixMap)? $this->mPrefixMap[$prefix] : NULL;
		return $p;
	}
	
	/**
	 * Adds a pair $prefix and $namespaceURI to the prefix manager.
	 * 
	 * @param string $prefix
	 * 		The prefix
	 * @param string $namespaceURI
	 * 		The namespace URI to which the prefix is expanded
	 */
	public function addPrefix($prefix, $namespaceURI) {
		$this->mPrefixMap[$prefix] = $namespaceURI;
	}
	
	/**
	 * Replaces the prefix of the $prefixedURI by the namespace URI for that
	 * prefix and encloses the result in <>.
	 * If the URI begins with "<http://" or "http://" it is treated as being
	 * absolute.
	 * 
	 * @param string $prefixedURI
	 * 		The URI with a prefix and a local name e.g. ex:Example
	 * @param bool $braced
	 * 		If <true>, the URI is enclosed in <>
	 * @return string
	 * 		An absolute URI
	 * @throws TSCPrefixManagerException
	 * 		MISSING_COLON: There is no colon that separates the prefix from the
	 * 			local name of the URI in $prefixedURI
	 * 		UNKNOWN_PREFIX_IN_URI: The prefix in $prefixedURI is unknown
	 */
	public function makeAbsoluteURI($prefixedURI, $braced = true) {
		// Is the URI already absolute?
		if (strpos($prefixedURI, "http://") === 0) {
			return $braced ? "<$prefixedURI>" 
						   : $prefixedURI;
		} else if (strpos($prefixedURI, "<http://") === 0) {
			return ($braced) ? $prefixedURI 
							 : substr($prefixedURI, 1, strlen($prefixedURI)-2);
		}
		$matched = preg_match("/^([^:]+):(.*)$/", $prefixedURI, $matches);
		if ($matched === 1) {
			$prefix = $matches[1];
			$localName = $matches[2];
		}
		if (empty($localName)) {
			throw new TSCPrefixManagerException(TSCPrefixManagerException::MISSING_COLON, $prefixedURI);
		}
		if (!array_key_exists($prefix, $this->mPrefixMap)) {
			throw new TSCPrefixManagerException(TSCPrefixManagerException::UNKNOWN_PREFIX_IN_URI, $prefixedURI);
		}
		$uri = "{$this->mPrefixMap[$prefix]}$localName";
		if ($braced) {
			$uri = "<$uri>";
		}
		return $uri;		
	}
	
	/**
	 * Returns the prefixed URI for an absolute URI, if the prefix is known.
	 * 
	 * @param string $uri
	 * 		An absolute URI
	 * @return string 
	 * 		The prefixed URI or the original $uri if there is no prefix that 
	 * 		matches the namespace of the URI.
	 */
	public function makePrefixedURI($uri) {
		foreach ($this->mPrefixMap as $p => $ns) {
			if (strpos($uri, $ns) === 0) {
				return "$p:".substr($uri, strlen($ns));
			}
		}	
		return $uri;
	}
	
	/**
	 * Returns a serialization of prefixes in SPARQL format. For example,
	 * the prefix 'xsd' will be serialized as 
	 * "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>\n"
	 * 
	 * @param array $prefixes
	 * 		An array of prefixes to serialize.
	 * @return string
	 * 		The SPARQL serialization of the requested prefixes.
	 */
	public function getSPARQLPrefixes(array $prefixes) {
		$seri = "";
		
		foreach ($prefixes as $prefix) {
			if (!array_key_exists($prefix, $this->mPrefixMap)) {
				throw new TSCPrefixManagerException(
						TSCPrefixManagerException::UNKNOWN_PREFIX, $prefix);
			}
			$namespaceURI = $this->mPrefixMap[$prefix];
			$seri .= "PREFIX $prefix: <$namespaceURI>\n";
		}
		
		return $seri;
	}
	
	
	/**
	 * Parses the given query and stores its prefixes
	 * .
	 * @param string $query
	 * 		The query that contains the prefixes.
	 */
	public static function addPrefixesFromQuery($query) {
		$parser = new TSCSparqlQueryParser($query);
		$prefixExtractor = new TSCPrefixExtractor();
		$parser->visitQuery($prefixExtractor);
	}
	
	//--- Private methods ---
}

/**
 * This class defines a sparql query visitor which extracts the prefixes of a 
 * query.
 * 
 * @author thsc
 *
 */
class TSCPrefixExtractor extends TSCSparqlQueryVisitor {
	
	/**
	 * Extract the prefixes of the query and store them in the rating manager.
	 * @param $pattern
	 * 		The pattern of the root node contains the prefixes
	 */
	public function preVisitRoot(&$pattern) {
		if (isset($pattern['prefixes'])) {
			
			$pm = TSCPrefixManager::getInstance();
			
			$prefixes = $pattern['prefixes'];
			foreach ($prefixes as $p => $ns) {
				// remove the final colon from the prefix
				$p = substr($p,0,strlen($p)-1);
				$pm->addPrefix($p, $ns);
			}
		}
		
	}
}
