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
 * @ingroup DFMaintenance
 *  
 * Returns settings of the installation, e.g. variable values.
 * 
 * It is assured that this MUST NOT be accessed via web request.
 * 
 * @author: Kai Kühn
 *
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

    //-v => get variable value
    if ($arg == '-v') {
        $variable = next($argv);
        continue;
    }
}

// include MW
$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

if (isset($variable)) {
    echo eval('echo $'.$variable.';');
    exit(0);
}
