<?php
/*  Copyright 2011, ontoprise GmbH
*  This file is part of the Facetted Search Module of the Enhanced Retrieval Extension.
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
 * This file contains the class FSSolrIndexer. It encapsulates access to the
 * Apache Solr indexing service.
 * 
 * @author Thomas Schweitzer
 * Date: 22.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}

/**
 * This class offers methods for accessing an Apache Solr indexing server.
 * 
 * @author thsc
 *
 */
class FSSolrIndexer implements IFSIndexer {

	//--- Private fields ---
	
	// string: Name or IP address of the host of the server
	private $mHost;
	
	// int: Server port of the Solr server
	private $mPort;
	
	
	//--- getter/setter ---
	public function getHost()	{ return $this->mHost; }
	public function getPort()	{ return $this->mPort; }
	
	//--- Public methods ---
	
	/**
	 * Creates a new Solr indexer object. This method can only be called from
	 * derived classes.
	 * 
	 * @param string $host
	 * 		Name or IP address of the host of the server
	 * @param int $port
	 * 		Server port of the Solr server
	 */
	protected function __construct($host, $port) {
		$this->mHost = $host;
		$this->mPort = $port;
	}
	
	/**
	 * Creates a full index of all available semantic data.
	 * 
	 * @param bool $clean
	 * 		If <true> (default), the existing index is cleaned before the new
	 * 		index is created.
	 */
	public function createFullIndex($clean = true) {
		
	}
	
	/**
	 * Deletes the complete index.
	 */
	public function deleteIndex() {
		
	}
	
}
