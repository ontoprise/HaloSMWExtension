<?php

/**
 * This is the main web entry point for MediaWiki.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the README, INSTALL, and UPGRADE files for basic setup instructions
 * and pointers to the online documentation.
 *
 * http://www.mediawiki.org/
 *
 * ----------
 *
 * Copyright (C) 2001-2008 Magnus Manske, Brion Vibber, Lee Daniel Crocker,
 * Tim Starling, Erik Möller, Gabriel Wicke, Ævar Arnfjörð Bjarmason,
 * Niklas Laxström, Domas Mituzas, Rob Church and others.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */


# Initialise common code
$preIP = dirname( __FILE__ );
require_once( "$preIP/includes/WebStart.php" );

# Initialize MediaWiki base class
require_once( "$preIP/includes/Wiki.php" );
$mediaWiki = new MediaWiki();

wfProfileIn( 'main-misc-setup' );
OutputPage::setEncodings(); # Not really used yet

$maxLag = $wgRequest->getVal( 'maxlag' );
if ( !is_null( $maxLag ) ) {
	if ( !$mediaWiki->checkMaxLag( $maxLag ) ) {
		exit;
	}
}

# Query string fields
$action = $wgRequest->getVal( 'action', 'view' );
$title = $wgRequest->getVal( 'title' );

$wgTitle = $mediaWiki->checkInitialQueries( $title,$action,$wgOut, $wgRequest, $wgContLang );
if ($wgTitle == NULL) {
	unset( $wgTitle );
}

#
# Send Ajax requests to the Ajax dispatcher.
#
if ( $wgUseAjax && $action == 'ajax' ) {
	require_once( $IP . '/includes/AjaxDispatcher.php' );

	$dispatcher = new AjaxDispatcher();
	$dispatcher->performAction();
	$mediaWiki->restInPeace( $wgLoadBalancer );
	exit;
}

#
# Handle webservice call
#
if ($action == 'wsmethod' ) {
    require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_Webservices.php' );
    $mediaWiki->restInPeace( $wgLoadBalancer );
    exit;
}

#
# Handle 'quick query' call
# (answers URL-encoded ASK queries formatted as table in HTML)
#
if ($action == 'query') {
	require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
	echo query($wgRequest->getVal( 'querytext' ), "exceltable");
	$mediaWiki->restInPeace( $wgLoadBalancer );
	exit;
}

#
# Returns WSDL file for wiki webservices
#
if ($action == 'get_eqi') {
    $wsdl = "extensions/SMWHalo/includes/webservices/eqi.wsdl";
    $handle = fopen($wsdl, "rb");
    $contents = fread ($handle, filesize ($wsdl));
    fclose($handle);
    
    echo str_replace("{{wiki-path}}", $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME'], $contents);
    exit;
} else if ($action == 'get_sparql') {
    $wsdl = "extensions/SMWHalo/includes/webservices/sparql.wsdl";
    $handle = fopen($wsdl, "rb");
    $contents = fread ($handle, filesize ($wsdl));
    fclose($handle);
    global $smwgSPARQLEndpoint;
    if (isset($smwgSPARQLEndpoint)) echo str_replace("{{sparql-endpoint}}", $smwgSPARQLEndpoint, $contents); 
        else echo "No SPARQL endpoint defined! Set \$smwgSPARQLEndpoint in your LocalSettings.php. E.g.: \$smwgSPARQLEndpoint = \"localhost:8080\"";
    exit;
} else if ($action == 'get_flogic') {
    $wsdl = "extensions/SMWHalo/includes/webservices/flogic.wsdl";
    $handle = fopen($wsdl, "rb");
    $contents = fread ($handle, filesize ($wsdl));
    fclose($handle);
    global $smwgFlogicEndpoint;
    if (isset($smwgFlogicEndpoint)) echo str_replace("{{flogic-endpoint}}", $smwgFlogicEndpoint, $contents); 
        else echo "No FLogic endpoint defined! Set \$smwgFlogicEndpoint in your LocalSettings.php. E.g.: \$smwgFlogicEndpoint = \"localhost:8080\"";
    exit;
} else if ($action == 'get_explanation') {
    $wsdl = "extensions/SMWHalo/includes/webservices/explanation.wsdl";
    $handle = fopen($wsdl, "rb");
    $contents = fread ($handle, filesize ($wsdl));
    fclose($handle);
    global $smwgExplanationEndpoint;
    if (isset($smwgExplanationEndpoint)) echo str_replace("{{explanation-endpoint}}", $smwgExplanationEndpoint, $contents); 
        else echo "No FLogic endpoint defined! Set \$smwgExplanationEndpoint in your LocalSettings.php. E.g.: \$smwgExplanationEndpoint = \"localhost:8080\"";
    exit;
}

wfProfileOut( 'main-misc-setup' );

# Setting global variables in mediaWiki
$mediaWiki->setVal( 'Server', $wgServer );
$mediaWiki->setVal( 'DisableInternalSearch', $wgDisableInternalSearch );
$mediaWiki->setVal( 'action', $action );
$mediaWiki->setVal( 'SquidMaxage', $wgSquidMaxage );
$mediaWiki->setVal( 'EnableDublinCoreRdf', $wgEnableDublinCoreRdf );
$mediaWiki->setVal( 'EnableCreativeCommonsRdf', $wgEnableCreativeCommonsRdf );
$mediaWiki->setVal( 'CommandLineMode', $wgCommandLineMode );
$mediaWiki->setVal( 'UseExternalEditor', $wgUseExternalEditor );
$mediaWiki->setVal( 'DisabledActions', $wgDisabledActions );

$wgArticle = $mediaWiki->initialize ( $wgTitle, $wgOut, $wgUser, $wgRequest );
$mediaWiki->finalCleanup ( $wgDeferredUpdateList, $wgLoadBalancer, $wgOut );

# Not sure when $wgPostCommitUpdateList gets set, so I keep this separate from finalCleanup
$mediaWiki->doUpdates( $wgPostCommitUpdateList );

$mediaWiki->restInPeace( $wgLoadBalancer );

