<?php
/**
 * Copyright (C) 2005 Brion Vibber <brion@pobox.com>
 * http://www.mediawiki.org/
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
 *
 * @file
 * @ingroup Maintenance
 */

$optionsWithArgs = array( 'report' );

require_once( '../../maintenance/commandLine.inc' );
require_once('../io/import/DeployWikiImporter.php');
require_once('../io/import/BackupReader.php');

if( wfReadOnly() ) {
	wfDie( "Wiki is in read-only mode; you'll need to disable it for import to work.\n" );
}

// get parameters
$mode = 0;
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-m => mode
	if ($arg == '-m') {
		$mode = next($argv);
		continue;
	}
	//-f => mode
	if ($arg == '-f') {
		$file = next($argv);
		continue;
	}
	$params[] = $arg;
}

if (!isset($file)) {
	print "Usage: php import.php -f <dump> [ -m <mode> ]";
	die();
}

$reader = new BackupReader($mode);
if( isset( $options['quiet'] ) ) {
	$reader->reporting = false;
}
if( isset( $options['report'] ) ) {
	$reader->reportingInterval = intval( $options['report'] );
}
if( isset( $options['dry-run'] ) ) {
	$reader->dryRun = true;
}
if( isset( $options['debug'] ) ) {
	$reader->debug = true;
}
if( isset( $options['uploads'] ) ) {
	$reader->uploads = true; // experimental!
}

$result = $reader->importFromFile( $file );


if( WikiError::isError( $result ) ) {
	echo $result->getMessage() . "\n";
} else {
	echo "Done!\n";
	echo "You might want to run rebuildrecentchanges.php to regenerate\n";
	echo "the recentchanges page.\n";
}


