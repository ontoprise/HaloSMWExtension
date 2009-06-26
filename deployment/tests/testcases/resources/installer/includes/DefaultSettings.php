<?php
/**
 *
 *                 NEVER EDIT THIS FILE
 *
 *
 * To customize your installation, edit "LocalSettings.php". If you make
 * changes here, they will be lost on next upgrade of MediaWiki!
 *
 * Note that since all these string interpolations are expanded
 * before LocalSettings is included, if you localize something
 * like $wgScriptPath, you must also localize everything that
 * depends on it.
 *
 * Documentation is in the source and on:
 * http://www.mediawiki.org/wiki/Manual:Configuration_settings
 *
 */

# This is not a valid entry point, perform no further processing unless MEDIAWIKI is defined
if( !defined( 'MEDIAWIKI' ) ) {
    echo "This file is part of MediaWiki and is not a valid entry point\n";
    die( 1 );
}

/**
 * Create a site configuration object
 * Not used for much in a default install
 */
require_once( "$IP/includes/SiteConfiguration.php" );
$wgConf = new SiteConfiguration;

/** MediaWiki version number */
$wgVersion          = '1.13.2';

