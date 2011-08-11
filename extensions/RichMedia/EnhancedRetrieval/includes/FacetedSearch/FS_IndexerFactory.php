<?php
/*  Copyright 2011, ontoprise GmbH
*  This file is part of the Faceted Search Module of the Enhanced Retrieval Extension.
*
*   The Enhanced Retrieval Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Enhanced Retrieval Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the factory class for the Faceted Search Indexer.
 * 
 * @author Thomas Schweitzer
 * Date: 22.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}

//--- Includes ---

/**
 * This factory creates indexer objects that encapsulate access to index servers
 * for faceted search.
 * 
 * @author Thomas Schweitzer
 * 
 */
class FSIndexerFactory  {

	/**
	 * Creates an indexer object which is described by the given configuration.
	 * 
	 * @param array $indexerConfig
	 * 	This array has the following key value pairs:
	 *    'indexer' => 'SOLR'
     *    'source'  => 'SMWDB'
	 *    'host'    => hostname
     *    'port'    => portnumber
     *  If <null> (default), the global configuration which is stored in the 
     *  variable $fsgFacetedSearchConfig is used.
     *    
     * @return IFSIndexer
     * 	An instance of the interface IFSIndexer
     * @throws ERFSException
     * 	INCOMPLETE_CONFIG: If the configuration is incomplete
     *  UNSUPPORTED_VALUE: If a value for a field in the configuration is not supported
	 */
	public static function create(array $indexerConfig = null) {
		if (is_null($indexerConfig)) {
			global $fsgFacetedSearchConfig;
			$indexerConfig = $fsgFacetedSearchConfig;
		}
		// Check if the configuration is complete
		$expKeys = array('indexer' => 0, 'source' => 0, 'host' => 0, 'port' => 0);
		$missingKeys = array_diff_key($expKeys, $indexerConfig);
		if (count($missingKeys) > 0) {
			$missingKeys = "The following keys are missing: ".implode(', ', array_keys($missingKeys));
			throw new ERFSException(ERFSException::INCOMPLETE_CONFIG, $missingKeys); 
		}
		
		// Check if the configuration is supported
		$unsupported = array();
		if (!in_array($indexerConfig['indexer'], array('SOLR'))) {
			$unsupported[] = "indexer => {$indexerConfig['indexer']}";
		}
		if (!in_array($indexerConfig['source'], array('SMWDB'))) {
			$unsupported[] = "source => {$indexerConfig['source']}";
		}
		if (count($unsupported) > 0) {
			$unsupported = "The following values are not supported:\n".implode("\n", $unsupported);
			throw new ERFSException(ERFSException::UNSUPPORTED_VALUE, $unsupported); 
		}
				
		// Create the indexer object
		if ($indexerConfig['indexer'] == 'SOLR') {
			// Indexer is Apache SOLR
			if ($indexerConfig['source'] == 'SMWDB') {
				// The SMW database is indexed
				return new FSSolrSMWDB($indexerConfig['host'], $indexerConfig['port']);
			}
		}
		return null;
	}
}
