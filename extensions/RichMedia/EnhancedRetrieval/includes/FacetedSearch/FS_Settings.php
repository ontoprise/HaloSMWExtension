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
 * This file contains the settings for Faceted Search
 * 
 * @author Thomas Schweitzer
 * Date: 24.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}

###
# This is the path to your installation of the Faceted Search as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$fsgScriptPath = $wgScriptPath . '/extensions/EnhancedRetrieval';

###
# This is the installation path of the extension
$fsgIP = $IP.'/extensions/EnhancedRetrieval';


###
# This array configures the indexer that is used for faceted search. It has the
# following key-value pairs:
# indexer: Type of the indexer. Currently only 'SOLR' is supported.
# source:  The source for indexing semantic data. Currently only the database
#          of SMW is supported: 'SMWDB'
# host:    Name or IP address of the indexer server e.g. 'localhost'
# port:    The port number of the indexer server e.g. 8983
#
##
$fsgFacetedSearchConfig = array(
    'indexer' => 'SOLR',
    'source'  => 'SMWDB',
    'host'    => '127.0.0.1',
    'port'    => 8983
);

###
# If this variable is <true>, a search in the MediaWiki search field is redirected
# to the faceted search special page. 
# If <false>, Enhanced Retrieval is installed. 
$fsgFacetedSearchForMW = true;

###
# This is the pattern for the link that leads to the creation of new pages.
# Faceted Search checks if the entered search term is the name of an existing 
# article. If this is not the case it offers a link for creating this article. 
# The variable {article} will be replace by the actual article name.
# The link will be appended to the base URL like "http://localhost/mediawiki/index.php"
#
//$fsgCreateNewPageLink = "/Create_new_page?target={article}&redlink=1";
//$fsgCreateNewPageLink = "/{article}?action=edit";
$fsgCreateNewPageLink = "?todo=createnewarticle&newarticletitle={article}";