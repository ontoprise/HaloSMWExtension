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
 * This file contains the class FSSolrSMWDB. It creates the index from the database
 * tables of SMW.
 * 
 * @author Thomas Schweitzer
 * Date: 22.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}

/**
 * This class is the indexer for the SMW database tables.
 * 
 * @author thsc
 *
 */
class FSSolrSMWDB extends FSSolrIndexer {

	//--- Private fields ---
	
	
	//--- getter/setter ---
	
	//--- Public methods ---

	
	/**
	 * Creates a new FSSolrSMWDB indexer object.
	 * @param string $host
	 * 		Name or IP address of the host of the server
	 * @param int $port
	 * 		Server port of the Solr server
	 */
	public function __construct($host, $port) {
		parent::__construct($host, $port);
	}
	
	
}
