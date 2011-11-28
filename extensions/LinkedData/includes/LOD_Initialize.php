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
 * This is the main entry file for the LinkedData extension.
 * It contains mainly constants for the configuration of the extension. This 
 * file has to be included in LocalSettings.php to enable the extension. The 
 * constants defined here can be overwritten in LocalSettings.php. After that
 * the function enableLinkedData() must be called.
 * 
 * @author Thomas Schweitzer
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

// check if a triplestore with quad driver is available
// print notice if not.
global $smwgDefaultStore;
if (!defined( 'DO_MAINTENANCE' ) && (!isset($smwgDefaultStore) || $smwgDefaultStore !== "SMWTripleStoreQuad" )) {
	trigger_error("The LinkedData extension will not work without a properly configured triplestore.".
	" Take a look at: http://smwforum.ontoprise.com");
}

define('LOD_LINKEDDATA_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

define('LOD_STORE_SQL', 'LODStoreSQL');

// buildnumber index for MW to define a script's version.
$lodgStyleVersion = preg_replace('/[^\d]/', '', '{{$BUILDNUMBER}}' );
if (strlen($lodgStyleVersion) > 0) {
    $lodgStyleVersion= '?'.$lodgStyleVersion;
}
###
# This is the path to your installation of LinkedData as seen on your
# local filesystem. Used against some PHP file path issues.
##
$lodgIP = $IP . '/extensions/LinkedData';
##

###
# This is the path to your installation of LinkedData as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$lodgScriptPath = $wgScriptPath . '/extensions/LinkedData';

###
# By design several databases can be connected to the LDE. (However, in the first
# version there is only an implementation for MySQL.) With this variable you can
# specify which store will actually be used.
# Possible values:
# - LOD_STORE_SQL
##
$lodgBaseStore = LOD_STORE_SQL;


####
# Mappings for a data source always have a source and a target. Mappings are 
# stored in wiki articles, where the target should be specified. If the targets
# are omitted, the default mapping target is set.
$lodgDefaultMappingTarget = "wiki";


####
# Settings for meta-data query printers
#
# The results of SPARQL queries can be augmented with meta-data. The following
# settings configure this feature.

####
# boolean - The meta data query printer augments the results of a query with meta
# data. This is also need for rating triples.
# It is enabled by setting this variable <true>.
$lodgEnableMetaDataQueryPrinter = true;


# load global functions
require_once('LOD_GlobalFunctions.php');


###
# If you already have custom namespaces on your site, insert
#    $lodgNamespaceIndex = ???;
# into your LocalSettings.php *before* including this file. The number ??? must
# be the smallest even namespace number that is not in use yet. However, it
# must not be smaller than 100.
##
lodfInitNamespaces();

/**
 * This function is called during the initialization of the extension. The stores
 * of the extension are configured.
 *
 */
function lodfInitStores() {
	
	###
	# Mappings for different LOD sources are stored with the LODMappingStore. The
	# actual store for this data can be set with setIOStrategy().
	##
	LODMappingStore::setStore(new LODMappingTripleStore());
	
}


// Tell the script manager, that we need jQuery
global $smgJSLibs; 
$smgJSLibs[] = 'jquery'; 
$smgJSLibs[] = 'qtip';
$smgJSLibs[] = 'json'; 
$smgJSLibs[] = 'fancybox'; 
