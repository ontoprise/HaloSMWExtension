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
# proxyHost: Protocol and name or IP address of the proxy to the indexer server 
#          as seen from the client e.g. 'http://www.mywiki.com' or $wgServer
# proxyPort: The port number of the indexer server e.g. 8983 as seen from the 
#          client. 
#          If the solrproxy is used this can be omitted.
# proxyServlet: Servlet of the indexer proxy as seen from the client. If the 
#          solrproxy is used it should be
#          "$wgScriptPath/extensions/EnhancedRetrieval/includes/FacetedSearch/solrproxy.php"
#          If the indexer is addressed directly it should be '/solr/select' (for SOLR)
# indexerHost: Name or IP address of the indexer server as seen from the wiki server
#          e.g. 'localhost'
#          If the solrproxy is used and the indexer host (SOLR) is different from 
#          'localhost', i.e. SOLR is running on another machine than the wiki server, 
#          the variable $SOLRhost must be set in solrproxy.php.
# indexerPort: The port number of the indexer server e.g. 8983 as seen from the 
#          wiki server.
#          If the solrproxy is used and the port of the indexer host (SOLR) is 
#          different from 8983, the variable $SOLRport must be set in solrproxy.php.
##
$fsgFacetedSearchConfig = array(
    'indexer' => 'SOLR',
    'source'  => 'SMWDB',
    'proxyHost'    => $wgServer,
//	'proxyPort'    => 8983,		
	'proxyServlet' => "$wgScriptPath/extensions/EnhancedRetrieval/includes/FacetedSearch/solrproxy.php",
	'indexerHost' => 'localhost', // must be equal to $SOLRhost in solrproxy.php
	'indexerPort' => 8983         // must be equal to $SOLRport in solrproxy.php
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

###
# If this variable is <true>, changed pages will be indexed incrementally i.e.
# when they are saved, moved or deleted.
# Setting it to <false> can make sense for example during the installation when
# SOLR is not yet running. 
$fsgEnableIncrementalIndexer = true;
